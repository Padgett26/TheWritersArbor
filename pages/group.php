<?php
if (filter_input ( INPUT_GET, 'joinGroup', FILTER_SANITIZE_NUMBER_INT )) {
	$join = filter_input ( INPUT_GET, 'joinGroup', FILTER_SANITIZE_NUMBER_INT );
	$jg = $db->prepare ( "INSERT INTO groupMembership VALUES(NULL,?,?,'0')" );
	$jg->execute ( array (
			$myId,
			$join
	) );
}
if (filter_input ( INPUT_GET, 'leaveGroup', FILTER_SANITIZE_NUMBER_INT )) {
	$leave = filter_input ( INPUT_GET, 'leaveGroup', FILTER_SANITIZE_NUMBER_INT );
	$lg = $db->prepare ( "DELETE FROM groupMembership WHERE userId = ? AND groupId = ?" );
	$lg->execute ( array (
			$myId,
			$leave
	) );
}

$isMember = 0;
$isBlocked = 0;
$isMem = $db->prepare ( "SELECT COUNT(*) FROM groupMembership WHERE groupId = ? AND userId = ?" );
$isMem->execute ( array (
		$groupId,
		$myId
) );
$isMemR = $isMem->fetch ();
if ($isMemR) {
	$isMember = $isMemR [0];
}
if ($isMember == 1) {
	$isMem2 = $db->prepare ( "SELECT blocked FROM groupMembership WHERE groupId = ? AND userId = ?" );
	$isMem2->execute ( array (
			$groupId,
			$myId
	) );
	$isMemR2 = $isMem2->fetch ();
	if ($isMemR2) {
		$isBlocked = $isMemR2 ['blocked'];
	}
}

$getG = $db->prepare ( "SELECT * FROM groups WHERE suspendDate = '0' AND closedDate = '0' AND id = ?" );
$getG->execute ( array (
		$groupId
) );
while ( $getGR = $getG->fetch () ) {
	if ($getGR) {
		$id = $getGR ['id'];
		$creatorId = $getGR ['creatorId'];
		$title = html_entity_decode ( $getGR ['title'] );
		$desc = nl2br ( html_entity_decode ( $getGR ['description'] ) );
		$logo = $getGR ['logoPic'];
		$members = 0;

		$blocked = 0;
		$check = $db->prepare ( "SELECT blocked FROM groupMembership WHERE userId = ? AND groupId = ?" );
		$check->execute ( array (
				$myId,
				$id
		) );
		$checkR = $check->fetch ();
		if ($checkR) {
			$blocked = $checkR ['blocked'];
		}

		if ($blocked == 0) {

			$mem = $db->prepare ( "SELECT COUNT(*) FROM groupMembership WHERE groupId = ?" );
			$mem->execute ( array (
					$groupId
			) );
			$memR = $mem->fetch ();
			if ($memR) {
				$members = $memR [0];
			}

			$l = ($logo == "x.png") ? "images/writeLogo.png" : "images/$creatorId/$logo";
			echo "<div style='' class='clearfix'>\n";
			echo "<div style='float:left;'><image src='$l' alt='' style='max-length:75px; max-width:75px;'></div>\n";
			echo "<div style='font-weight:bold; font-size:1.25em; text-align:left; padding:20px 20px 0px 20px;'><a href='index.php?page=group&groupId=$id' target='_self'>$title</a></div>\n";
			echo "<div style='font-size:.75em; text-align:left; padding:0px 20px 10px 20px; color:#555555;'>$members members</div>\n";
			echo "<div style='font-size:.75em; text-align:left; padding:0px 20px 20px 20px; color:#555555;'>Categories: ";
			$t = 1;
			$getC = $db->prepare ( "SELECT t1.catName FROM categories AS t1 INNER JOIN groupCategories AS t2 ON t1.id = t2.catId WHERE t2.groupId = ?" );
			$getC->execute ( array (
					$id
			) );
			while ( $getCR = $getC->fetch () ) {
				if ($getCR) {
					echo ($t == 1) ? "" : ", ";
					$c = html_entity_decode ( $getCR [0], ENT_QUOTES );
					echo $c;
					$t ++;
				}
			}
			echo "</div>\n";
			echo "$desc<br><br>\n";
			if ($myId >= 1) {
				if ($myId != $creatorId) {
					if ($isMember == 0 && $isBlocked == 0) {
						echo "<a href='index.php?page=allGroups&joinGroup=$id'>Join this group.</a>\n";
					} else {
						echo "<a href='index.php?page=allGroups&leaveGroup=$id'>Leave this group.</a>\n";
					}
				}
			} else {
				?>
			<a onclick="document.getElementById('id01').style.display='block'">Login / Register</a> to join this group.
			<?php
			}
		}
	}
}
?>
<div style='font-weight:bold; font-size:1em; text-align:left; padding:20px 0px;'>Group Projects</div>
<?php
$getW = $db->prepare ( "SELECT * FROM projects WHERE groupId = ? ORDER BY openDate DESC" );
$getW->execute ( array (
		$groupId
) );
while ( $getWR = $getW->fetch () ) {
	if ($getWR) {
		$pId = $getWR ['id'];
		$pOpenDate = $getWR ['openDate'];
		$pCloseDate = $getWR ['closeDate'];
		$pTitle = html_entity_decode ( $getWR ['title'], ENT_QUOTES );
		$pDescription = nl2br ( html_entity_decode ( $getWR ['description'], ENT_QUOTES ) );
		$pActive = ($pOpenDate <= $time && $pCloseDate >= $time) ? 1 : 0;
		echo "<div style='font-weight:bold;'><span style='cursor:pointer;' onclick='toggleview(\"project$pId\")'>$pTitle</span>\n";
		echo ($pActive == 1) ? " - <span style='color:red;'>Active</span>\n" : "";
		echo ($isMember == 1) ? " - <a href='index.php?page=project&projectId=$pId'>Go to the project</a>\n" : "";
		echo "</div>\n";
		echo "<div style='display:none' id='project$pId'><span style='font-size:.75em;'>" . showDate ( $pOpenDate ) . " - " . showDate ( $pCloseDate ) . "</span><br><br>$pDescription</div>\n";
	}
}

