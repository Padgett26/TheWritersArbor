<?php
include "cgi-bin/config.php";
include "cgi-bin/functions.php";
?>
<!DOCTYPE HTML>
<html>
<head>
<?php
include "include/head.php";
?>
</head>
<body style="position:relative; top:0px; left:0px; width:100%;">
<div style="text-align:center; margin:20px 0px 15px 0px;" class="clearfix"><image src="images/writeLogo.png" alt="" style="max-height:75px; max-width:75px; margin-right:20px; ">
<span class="header" style="margin-top:-20px;">The Writers Arbor</span></div>
<div style="text-align:center;">
<?php
include "include/menu.php";
?>
</div>
<div id="id01" class="modal">

		<form class="modal-content animate"
			action="index.php?page=home" method="post">

			<div class="container">
			<span class="psw">Log in, or <a
					href="index.php?page=register&t=register">register for access.</a></span>
				<label for="email"><b>Email</b></label> <input type="text"
					placeholder="Enter Email" name="email" required> <label for="pwd"><b>Password</b></label>
				<input type="password" placeholder="Enter Password" name="pwd"
					required>

				<button type="submit">Login</button>
			</div>

			<div class="container" style="background-color: #f1f1f1">
				<button type="button"
					onclick="document.getElementById('id01').style.display='none'"
					class="cancelbtn">Cancel</button>
				<span class="psw">Forgot <a
					href="index.php?page=register&t=forgotpwd">password?</a></span>
			</div>
			<input type="hidden" name="login" value="1">
		</form>
	</div>
<div style="padding:20px;">
<?php
include "pages/" . $page . ".php";
?>
</div>
<?php
include "../familyLinks.php";
?>
<p> This is the test</p>
</body>
</html>
