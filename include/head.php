<title>The Writes Arbor</title>
<link rel="icon" href="images/icon.png" type="image/png">
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<meta name="keywords" content="writing group, book club, writing, writers group">
<meta name="description" content="he Writers Arbor is a place where you can create your own specialized writing group or book club. Members can enter their stories or reviews and all members can comment.">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<script src="include/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tangerine:wght@700&display=swap">
<link rel="stylesheet" href="css/lightbox.min.css">

<script type="text/javascript">
function toggleview(itm) {
	var itmx = document.getElementById(itm);
	if (itmx.style.display === "none") {
		itmx.style.display = "block";
	} else {
		itmx.style.display = "none";
	}
}

function ModifySelection(tag) {
    var textarea = document.getElementById("textField");
    if ('selectionStart' in textarea) {
        // check whether some text is selected in the textarea
        if (textarea.selectionStart !== textarea.selectionEnd) {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<" + tag + ">" + textarea.value.substring(textarea.selectionStart, textarea.selectionEnd) + "</" + tag + ">" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        } else {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<" + tag + ">" + "</" + tag + ">" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        }
    }
}

function textAlignSelection(tag) {
    var textarea = document.getElementById("textField");
    if ('selectionStart' in textarea) {
        // check whether some text is selected in the textarea
        if (textarea.selectionStart !== textarea.selectionEnd) {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<div style='text-align:" + tag + ";'>" + textarea.value.substring(textarea.selectionStart, textarea.selectionEnd) + "</div>" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        } else {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<div style='text-align:" + tag + ";'>" + "</div>" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        }
    }
}

function languageSelection(tag) {
    var textarea = document.getElementById("textField");
    if ('selectionStart' in textarea) {
        // check whether some text is selected in the textarea
        if (textarea.selectionStart !== textarea.selectionEnd) {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<hebrew>" + textarea.value.substring(textarea.selectionStart, textarea.selectionEnd) + "</hebrew>" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        } else {
            var newText = textarea.value.substring(0, textarea.selectionStart) + "<hebrew>" + "</hebrew>" + textarea.value.substring(textarea.selectionEnd);
            textarea.value = newText;
        }
    }
}

function myFunction() {
  var x = document.getElementById("myTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}
</script>
<style>
body {
	margin: 0;
	font-family: Arial, Helvetica, sans-serif;
}

a {
	color:#555555;
	text-decoration:none;
}

a:hover {
	color:#aaaaaa;
	text-decoration:none;
}

.feedback {
	padding: 10px;
	width: 100%;
	display: inline-block;
	border: 1px solid #ccc;
	box-sizing: border-box;
}

form {
	border: 3px solid #f1f1f1;
	padding: 10px;
}

/* Full-width input fields */
input[type=text], input[type=password], input[type=email] {
	width: 100%;
	padding: 12px 20px;
	margin: 8px 0;
	display: inline-block;
	border: 1px solid #ccc;
	box-sizing: border-box;
}

textarea {
	width: 100%;
	height: 100px;
	padding: 12px 20px;
	margin: 8px 0;
	display: inline-block;
	border: 1px solid #ccc;
	box-sizing: border-box;
}

td {
	padding: 10px;
}

/* Set a style for all buttons */
button {
	background-color: #04AA6D;
	color: white;
	padding: 14px 20px;
	margin: 8px 0;
	border: none;
	cursor: pointer;
	width: 100%;
}

button:hover {
	opacity: 0.8;
}

/* Extra styles for the cancel button */
.cancelbtn {
	width: auto;
	padding: 10px 18px;
	background-color: #f44336;
}

.container {
	padding: 16px;
}

span.psw {
	float: right;
	padding-top: 16px;
}

/* The Modal (background) */
.modal {
	display: none; /* Hidden by default */
	position: fixed; /* Stay in place */
	z-index: 1; /* Sit on top */
	left: 0;
	top: 0;
	width: 100%; /* Full width */
	height: 100%; /* Full height */
	overflow: auto; /* Enable scroll if needed */
	background-color: rgb(0, 0, 0); /* Fallback color */
	background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
	padding-top: 60px;
}

/* Modal Content/Box */
.modal-content {
	background-color: #fefefe;
	margin: 5% auto 15% auto;
	/* 5% from the top, 15% from the bottom and centered */
	border: 1px solid #888;
	width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button (x) */
.close {
	position: absolute;
	right: 25px;
	top: 0;
	color: #000;
	font-size: 35px;
	font-weight: bold;
}

.close:hover, .close:focus {
	color: red;
	cursor: pointer;
}

/* Add Zoom Animation */
.animate {
	-webkit-animation: animatezoom 0.6s;
	animation: animatezoom 0.6s
}

@
-webkit-keyframes animatezoom {
	from {-webkit-transform: scale(0)
}

to {
	-webkit-transform: scale(1)
}

}

@
keyframes animatezoom {
	from {transform: scale(0)
}

to {
	transform: scale(1)
}

}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 300px) {
	span.psw {
		display: block;
		float: none;
	}
	.cancelbtn {
		width: 100%;
	}
}

/* Start of topnav */
.topnav {
	overflow: hidden;
	background-color: #ffffff;
}

.topnav a {
	float: left;
	display: block;
	color: #000000;
	text-align: center;
	padding: 14px 16px;
	text-decoration: none;
	font-size: 17px;
}

.topnav a:hover {
	background-color: #ddd;
	color: black;
}

.topnav a.active {
	background-color: #ffffff;
	color: #000000;
}

.topnav .icon {
	display: none;
}

@media screen and (max-width: 600px) {
	.topnav a:not(:first-child) {
		display: none;
	}
	.topnav a.icon {
		float: right;
		display: block;
	}
}

@media screen and (max-width: 600px) {
	.topnav.responsive {
		position: relative;
	}
	.topnav.responsive .icon {
		position: absolute;
		right: 0;
		top: 0;
	}
	.topnav.responsive a {
		float: none;
		display: block;
		text-align: left;
	}
}
/* End of topnav */

.clearfix::after {
  content: "";
  clear: both;
  display: table;
}

.header {
	font-family: 'Tangerine', cursive;
	font-weight:bold;
	font-size:4em;
}
</style>