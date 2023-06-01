<?php
if (filter_input ( INPUT_GET, 't', FILTER_SANITIZE_STRING )) {
	$t = filter_input ( INPUT_GET, 't', FILTER_SANITIZE_STRING );
} else {
	$t = "x";
}
switch ($t) {
	case "forgotpwd" :
		$action = "forgot";
		break;
	default :
		$action = "register";
}

$msg = "";
$showReset = 0;
$errorMsg = "";
if (filter_input ( INPUT_POST, 'upPwd', FILTER_SANITIZE_NUMBER_INT )) {
	$upId = filter_input ( INPUT_POST, 'upPwd', FILTER_SANITIZE_NUMBER_INT );
	$upSalt = filter_input ( INPUT_POST, 'upS', FILTER_SANITIZE_NUMBER_INT );
	$pwd1 = filter_input ( INPUT_POST, 'pwd1', FILTER_SANITIZE_STRING );
	$pwd2 = filter_input ( INPUT_POST, 'pwd2', FILTER_SANITIZE_STRING );
	$getS = $db->prepare ( "SELECT salt FROM users WHERE id = ?" );
	$getS->execute ( array (
			$upId
	) );
	$getSR = $getS->fetch ();
	if ($getSR) {
		$s = $getSR ['salt'];
		if ($s === $upSalt) {
			if ($pwd1 != "" && $pwd1 != " " && $pwd1 === $pwd2) {
				$salt = mt_rand ( 100000, 999999 );
				$hidepwd = hash ( 'sha512', ($salt . $pwd1), FALSE );
				$stmt = $db->prepare ( "UPDATE users SET password = ?, salt = ? WHERE id = ?" );
				$stmt->execute ( array (
						$hidepwd,
						$salt,
						$upId
				) );
			} else {
				$errorMsg = "There was either no password entered, or your passwords did not match.";
				$showReset = 1;
			}
		}
	}
}
if (filter_input ( INPUT_GET, 'ver', FILTER_SANITIZE_STRING )) {
	$id = filter_input ( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
	$ver = filter_input ( INPUT_GET, 'ver', FILTER_SANITIZE_STRING );
	$get = $db->prepare ( "SELECT name, email, salt FROM users WHERE id = ?" );
	$get->execute ( array (
			$id
	) );
	$getR = $get->fetch ();
	if ($getR) {
		$name = $getUR ['name'];
		$email = $getUR ['email'];
		$salt = $getUR ['salt'];
		$link = hash ( 'sha512', ($salt . $name . $email), FALSE );
		if ($ver === $link) {
			$showReset = 1;
		}
	}
}
if (filter_input ( INPUT_POST, 'fEmail', FILTER_SANITIZE_EMAIL )) {
	$fEmail = filter_input ( INPUT_POST, 'fEmail', FILTER_SANITIZE_EMAIL );
	$getU = $db->prepare ( "SELECT id, name, salt FROM users WHERE email = ?" );
	$getU->execute ( array (
			$fEmail
	) );
	$getUR = $getU->fetch ();
	if ($getUR) {
		$toId = $getUR ['id'];
		$name = $getUR ['name'];
		$salt = $getUR ['salt'];
		sendPWResetEmail ( $toId, $name, $fEmail, $salt );
		$msg = "Email sent with a link to reset your password.";
	} else {
		$msg = "Email not found.";
	}
}
$showRegister = 1;
if (filter_input ( INPUT_POST, 'newUser', FILTER_SANITIZE_NUMBER_INT ) == 1) {
	$upName = htmlentities ( filter_input ( INPUT_POST, 'name', FILTER_SANITIZE_STRING ), ENT_QUOTES );
	$upEmail = (filter_input ( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL )) ? filter_input ( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL ) : 0;
	$pwd1 = filter_input ( INPUT_POST, 'pwd1', FILTER_SANITIZE_STRING );
	$pwd2 = filter_input ( INPUT_POST, 'pwd2', FILTER_SANITIZE_STRING );
	$getS = $db->prepare ( "SELECT COUNT(*) FROM users WHERE email = ?" );
	$getS->execute ( array (
			$upEmail
	) );
	$getSR = $getS->fetch ();
	if ($getSR) {
		$s = $getSR [0];
		if ($s == 0) {
			if ($pwd1 != "" && $pwd1 != " " && $pwd1 === $pwd2) {
				$salt = mt_rand ( 100000, 999999 );
				$hidepwd = hash ( 'sha512', ($salt . $pwd1), FALSE );
				$stmt = $db->prepare ( "INSERT INTO users VALUES(NULL, ?, ?, ?, ?, ?, '0', '0', '0', '0')" );
				$stmt->execute ( array (
						$upName,
						$upEmail,
						$hidepwd,
						$salt,
						$time
				) );
				$errorMsg = "You have been registered for this site. Please log in with your email address and password.";
				$showRegister = 0;
			} else {
				$errorMsg = "There was either no password entered, or your passwords did not match.";
				$showRegister = 1;
			}
		} else {
			$errorMsg = "Looks like your email address has already been registered. Please use the password reset feature to access the site.";
			$showRegister = 0;
		}
	}
}
if ($action == 'forgot') {
	?>
<div style='margin:10px; border:1px solid #000000; padding:20px;'>
<?php
	if ($showReset == 1) {
		?>
	<div style="text-align: center; padding: 50px 0px; font-weight: bold;">
	<span style='font-size:1.25em;'>Reset Password</span>
	<?php
		echo $errorMsg;
		?>
	<form action="index.php?page=register&t=forgotpwd" method="post">
	<label for="pwd1">Password</label>
	<input type="password" name="pwd1" value="">
	<label for="pwd2">Password again</label>
	<input type="password" name="pwd2" value="">
	<input type="submit" value=" Update Password ">
	<input type="hidden" name="upPwd" value="<?php
		echo $id;
		?>">
	<input type="hidden" name="upS" value="<?php
		echo $salt;
		?>">
	</form>
	</div>
	<?php
	} else {
		if ($msg = "") {
			?>
<div style="text-align: center; padding: 50px 0px; font-weight: bold;">
	<form action="index.php?page=register&t=forgotpwd" method="post">
		<label for="fEmail">Enter Email</label> <input type="email"
			name="fEmail" value=""> <input type="submit" value=" send ">
	</form>
</div>
<?php
		} else {
			echo "<div style='text-align:center; padding:50px 0px; font-weight:bold;'>$msg</div>";
		}
	}
	?>
</div>
<?php
} else {
	?>
	<div style='margin:10px; border:1px solid #000000; padding:20px; text-align: center; padding: 50px 0px; font-weight: bold;'>
	<span style='font-size:1.25em;'>Register</span><br><br>
	<?php
	echo $errorMsg;
	if ($showRegister == 1) {
		?>
	<form action="index.php?page=register&t=register" method="post">
	<label for="name">Name</label>
	<input type="text" name="name" value="" required>
	<label for="email">Email</label>
	<input type="email" name="email" value="" required>
	<label for="pwd1">Password</label>
	<input type="password" name="pwd1" value="" required>
	<label for="pwd2">Password again</label>
	<input type="password" name="pwd2" value="" required>
	<input type="submit" value=" Register ">
	<input type="hidden" name="newUser" value="1">
	</form>
	<?php
	}
	?>
	</div>
	<?php
}
?>