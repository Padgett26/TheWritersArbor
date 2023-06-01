<div style="text-align:center; padding:0px;"><hr style="width:100%; color:#11bd4a;"></div>
<div class="topnav" id="myTopnav">
    <?php
				echo "<a href='index.php?page=home'>Home</a>\n";
				echo "<a href='index.php?page=allGroups'>View Groups</a>\n";
				if ($myId >= 1) {
					echo "<a href='index.php?page=allProjects'>Active Projects</a>\n";
					echo "<a href='index.php?page=feedback'>Contact Us</a>\n";
					echo "<a href='index.php?page=donate' class='active'>Donate</a>\n";
					echo "<a href='index.php?page=settings'>Settings</a>\n";
					echo "<a href='index.php?page=home&logout=yep'>Logout</a>\n";
				} else {
					?>
		<a onclick="document.getElementById('id01').style.display='block'" style="cursor:pointer;">Login / Register</a>
		<?php
				}
				?>
	<a href="javascript:void(0);" class="icon" onclick="myFunction()"> <i class="fa fa-bars"></i></a>
</div>
<div style="text-align:center; padding:0px;"><hr style="width:100%; color:#11bd4a;"></div>