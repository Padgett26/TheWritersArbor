<?php
function sendEmail($subject, $mess, $email, $name) {
	$headers [] = "MIME-Version: 1.0";
	$headers [] = "Content-Type: text/plain; charset=utf-8";
	$headers [] = "To: $name <$email>";
	$headers [] = "From: The Writers Arbor <admin@thewritersarbor.com>";
	mail ( $email, $subject, $mess, implode ( "\r\n", $headers ) );
}
function getPicType($imageType) {
	switch ($imageType) {
		case "image/gif" :
			$picExt = "gif";
			break;
		case "image/jpeg" :
			$picExt = "jpg";
			break;
		case "image/pjpeg" :
			$picExt = "jpg";
			break;
		case "image/png" :
			$picExt = "png";
			break;
		default :
			$picExt = "xxx";
			break;
	}
	return $picExt;
}
function processPic($imageName, $tmpFile, $f) {
	$folder = "images/$f";
	if (! is_dir ( "$folder" )) {
		mkdir ( "$folder", 0777, true );
	}

	$saveto = "$folder/$imageName";

	list ( $width, $height ) = (getimagesize ( $tmpFile ) != null) ? getimagesize ( $tmpFile ) : null;
	if ($width != null && $height != null) {
		$image = new Imagick ( $tmpFile );
		$image->thumbnailImage ( 800, 800, true );
		$image->writeImage ( $saveto );
	}
}
function processThumbPic($imageName, $tmpFile, $f) {
	$folder = "images/$f/thumbs";
	if (! is_dir ( "$folder" )) {
		mkdir ( "$folder", 0777, true );
	}

	$saveto = "$folder/$imageName";

	list ( $width, $height ) = (getimagesize ( $tmpFile ) != null) ? getimagesize ( $tmpFile ) : null;
	if ($width != null && $height != null) {
		$image = new Imagick ( $tmpFile );
		$image->thumbnailImage ( 150, 150, true );
		$image->writeImage ( $saveto );
	}
}
function sendPWResetEmail($toId, $name, $email, $salt) {
	$link = hash ( 'sha512', ($salt . $name . $email), FALSE );
	$mess = "$name,\n\n
        There has been a request on The Writes Arbor website for a password reset for this account.  If you initiated this request, click the link below, and you will be sent to a page where you will be able enter a new password. If you did not initiate this password reset request, simple ignore this email, and your password will not be changed.\n\n
        https://thewritersarbor.com/index.php?page=forgotpwd&id=$toId&ver=$link\n\n
        Thank you,\nAdmin\nThe Writers Arbor";
	$message = wordwrap ( $mess, 70 );
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= 'Content-Type: text/plain; charset=utf-8' . "\r\n";
	$headers .= "From: The Writers Arbor Admin <admin@thewritersarbor.com>" . "\r\n";
	mail ( $email, 'The Writers Arbor website password reset request', $message, $headers );
}
function make_links_clickable($text, $highlightColor) {
	return preg_replace ( '!(((f|ht)tp(s)?://)[-a-zA-Z()0-9@:%_+.~#?&;//=]+)!i', "<a href='$1' target='_blank' style='color:$highlightColor; text-decoration:underline;'>$1</a>", $text );
}
function money($amt) {
	settype ( $amt, "float" );
	$fmt = new NumberFormatter ( 'en_US', NumberFormatter::CURRENCY );
	return $fmt->formatCurrency ( $amt, "USD" );
}
function showDate($t) {
	return date ( "Y-m-d", $t );
}