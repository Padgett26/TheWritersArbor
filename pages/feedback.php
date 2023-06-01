<?php
if ($myId >= 1) {
	$sent = 0;
	if (filter_input ( INPUT_POST, 'msgUp', FILTER_SANITIZE_NUMBER_INT ) == 1) {
		$subject = htmlentities ( filter_input ( INPUT_POST, 'msgUp', FILTER_SANITIZE_STRING ), ENT_QUOTES );
		$message = htmlentities ( filter_input ( INPUT_POST, 'message', FILTER_SANITIZE_STRING ), ENT_QUOTES );

		$msgUp = $db->prepare ( "INSERT INTO feedback VALUES(NULL,?,?,?,?,'0','0','0')" );
		$msgUp->execute ( array (
				$myId,
				$time,
				$subject,
				$message
		) );
		$sent = 1;
	}
	if ($sent == 1) {
		?>
        Thank you for your input. We will respond to your query in the best and quickest way possible.<br><br>
        <?php
		echo "Sent " . date ( "Y-m-d", $time ) . "<br><br>\n";
		echo "Subject:<br>$subject<br><br>\n";
		echo "Message:<br>$message<br><br>\n";
	} else {
		?>
Please let us know if you have any concerns, questions, suggestions, or whatever. We will respond to your query in the best and quickest way possible.<br><br>
<form action="index.php?page=feedback" method="post">
Subject:<br>
<input type="text" name="subject" value=""><br><br>
Message:<br>
<textarea name="message" style="width:97%; height:100px;"></textarea><br><br>
<input type="hidden" name="msgUp" value="1"><input type="submit" value=" Send ">
</form>
<?php
	}
} else {
	echo "<div style='text-align:center;'>Please <a href='index.php?page=login'>sign in or register</a> to use this page.</div>\n";
}