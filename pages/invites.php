<?php
$errorMsg = "";
if (filter_input ( INPUT_POST, 'email', FILTER_SANITIZE_STRING )) {
	$eUp = filter_input ( INPUT_POST, 'email', FILTER_SANITIZE_STRING );
	$mUp = filter_input ( INPUT_POST, 'memId', FILTER_SANITIZE_NUMBER_INT );
	$u = filter_input ( INPUT_POST, 'u', FILTER_SANITIZE_NUMBER_INT );
	if ($mUp == 0) {
		$nUp = filter_input ( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
		$pwd1 = filter_input ( INPUT_POST, 'pwd1', FILTER_SANITIZE_STRING );
		$pwd2 = filter_input ( INPUT_POST, 'pwd2', FILTER_SANITIZE_STRING );
		if ($pwd1 != "" && $pwd1 != " " && $pwd1 === $pwd2) {
			$salt = mt_rand ( 100000, 999999 );
			$hidepwd = hash ( 'sha512', ($salt . $pwd1), FALSE );
			$stmt = $db->prepare ( "INSERT INTO users VALUES(NULL,?,?,?,?,?,'0','0','0','0')" );
			$stmt->execute ( array (
					$nUp,
					$eUp,
					$hidepwd,
					$salt,
					$time
			) );
			$stmt2 = $db->prepare ( "SELECT id FROM users WHERE name = ? AND email = ? AND password = ?" );
			$stmt2->execute ( array (
					$nUp,
					$eUp,
					$hidepwd
			) );
			$stmt2R = $stmt2->fetch ();
			if ($stmt2R) {
				$mUp = $stmt2R ['id'];
			}
		} else {
			$errorMsg = "There was either no password entered, or your passwords did not match.";
		}
	}
	if ($errorMsg == "") {
		foreach ( $_POST as $key => $val ) {
			if (preg_match ( "/^group([1-9][0-9]*)$/", $key, $match )) {
				if ($val == 1) {
					$groupId = $match [1];
					$gm = $db->prepare ( "INSERT INTO groupMembership VALUES(NULL,?,?,?)" );
					$gm->execute ( array (
							$mUp,
							$groupId,
							'0'
					) );
				}
			}
		}
		$del = $db->prepare ( "DELETE FROM invites WHERE email = ?" );
		$del->execute ( array (
				$eUp
		) );
		echo ($myId >= 1) ? "" : "Please log in to see the groups you have elected to join.<br><br>";
		echo "You now have access to the groups you selected. Have fun working on the new projects.";
	}
}
if (filter_input ( INPUT_GET, 'u', FILTER_SANITIZE_NUMBER_INT ) >= 1 || $errorMsg != "") {
	$u = (filter_input ( INPUT_GET, 'u', FILTER_SANITIZE_NUMBER_INT )) ? filter_input ( INPUT_GET, 'u', FILTER_SANITIZE_NUMBER_INT ) : $u;
	$g = array (
			0
	);
	$getU = $db->prepare ( "SELECT name, email FROM invites WHERE id = ?" );
	$getU->execute ( array (
			$u
	) );
	$getUR = $getU->fetch ();
	if ($getUR) {
		$n = $getUR ['name'];
		$e = $getUR ['email'];
		$getAll = $db->prepare ( "SELECT inviteGroup FROM invites WHERE email = ?" );
		$getAll->execute ( array (
				$e
		) );
		while ( $getAllR = $getAll->fetch () ) {
			if ($getAllR) {
				$g [] = $getAllR ['inviteGroup'];
			}
		}
		$groups = array_unique ( $g );
		echo ($errorMsg != "") ? "<div style='text-align:center; font-weight:bold; margin:10px 0px;'>$errorMsg</div>\n" : "";
		echo "<div style='font-weight:bold; margin:20px;'>You have been invited to join group(s) in The Writers Arbor</div>\n";
		echo "<form action='index.php?page=invites' method='post'>\n";
		echo "<input type='hidden' name='email' value='$e'>\n";
		echo "<input type='hidden' name='u' value='$u'>\n";
		$checkU = $db->prepare ( "SELECT COUNT(*) FROM users WHERE email = ?" );
		$checkU->execute ( array (
				$e
		) );
		$checkUR = $checkU->fetch ();
		if ($checkUR) {
			$mem = $checkUR [0];
			if ($mem == 0) {
				$memId = 0;
				echo "Please set up your log in info for the site, and then you will be able to access the group(s) you select to join.<br><br>\n";
				echo "<input type='hidden' name='memId' value='0'>\n";
				echo "<label for='name'>Your Name:</label>\n";
				echo "<input type='text' name='name' value='$n' required><br><br>\n";
				echo "<label for='pwd1'>Enter the password you want use (twice):</label>\n";
				echo "<input type='password' name='pwd1' value='' required><br>\n";
				echo "<input type='password' name='pwd2' value='' required><br><br>\n";
			} else {
				echo "Please select the group(s) you wish to join.<br><br>\n";
				$getId = $db->prepare ( "SELECT id FROM users WHERE email = ?" );
				$getId->execute ( array (
						$e
				) );
				$getIdR = $getId->fetch ();
				if ($getIdR) {
					$memId = $getIdR ['id'];
				}
			}
			echo "<input type='hidden' name='memId' value='$memId'>\n";
		}
		if ($memId >= 1) {
			foreach ( $groups as $k => $v ) {
				$blocked = $db->prepare ( "SELECT COUNT(*) FROM groupMembership WHERE userId = ? AND groupId = ? AND blocked = ?" );
				$blocked->execute ( array (
						$memId,
						$v,
						'1'
				) );
				$blockedR = $blocked->fetch ();
				if ($blockedR) {
					$b = $blockedR [0];
					if ($b >= 1) {
						$groups [$k] = 0;
					}
				}
			}
		}
		echo "<div class='clearfix' style='margin:20px 0px;'><table cellspacing='0px'>";
		foreach ( $groups as $k => $v ) {
			if ($v >= 1) {
				$getG = $db->prepare ( "SELECT creatorId, title, description, logoPic FROM groups WHERE id = ?" );
				$getG->execute ( array (
						$v
				) );
				$getGR = $getG->fetch ();
				if ($getGR) {
					$creatorId = $getGR ['creatorId'];
					$title = html_entity_decode ( $getGR ['title'], ENT_QUOTES );
					$desc = html_entity_decode ( $getGR ['description'], ENT_QUOTES );
					$logoPic = $getGR ['logoPic'];
					$img = ($logoPic != 'x.png') ? "<img src='images/$creatorId/thumbs/$logoPic' alt='' style='float:left; margin:10px;'>\n" : "";
					echo "<tr><td style='border-top:1px solid black; border-bottom:1px solid black; text-align:center;'><input type='checkbox' name='group$v' value='1'></td>\n";
					echo "<td style='border-top:1px solid black; border-bottom:1px solid black;'>$img<span style='font-weight:bold;'>$title</span><br>$desc</td></tr>\n";
				}
			}
		}
		echo "</table></div>\n";
		echo "<input type='submit' value=' Save Info '></form>\n";
	}
} else {
	include "pages/home.php";
}