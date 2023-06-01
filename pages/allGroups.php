<div
	style="font-weight: bold; font-size: 1.5em; text-align: center; padding: 30px 0px;">Active
	Groups</div>
<?php
if (filter_input(INPUT_GET, 'joinGroup', FILTER_SANITIZE_NUMBER_INT)) {
    $join = filter_input(INPUT_GET, 'joinGroup', FILTER_SANITIZE_NUMBER_INT);
    $jg = $db->prepare("INSERT INTO groupMembership VALUES(NULL,?,?,'0')");
    $jg->execute(array(
        $myId,
        $join
    ));
}
if (filter_input(INPUT_GET, 'leaveGroup', FILTER_SANITIZE_NUMBER_INT)) {
    $leave = filter_input(INPUT_GET, 'leaveGroup', FILTER_SANITIZE_NUMBER_INT);
    $lg = $db->prepare("DELETE FROM groupMembership WHERE userId = ? AND groupId = ?");
    $lg->execute(array(
        $myId,
        $leave
    ));
}
$sortby = 0;
if (filter_input(INPUT_POST, 'sortGroups', FILTER_SANITIZE_NUMBER_INT)) {
    $sortby = filter_input(INPUT_POST, 'sortGroups', FILTER_SANITIZE_NUMBER_INT);
}
switch ($sortby) {
    case 0:
        $ob = "SELECT id, creatorId, title, description, logoPic, openGroup FROM groups WHERE suspendDate = '0' AND closedDate = '0' ORDER BY RAND()";
        break;
    case 1:
        $ob = "SELECT id, creatorId, title, description, logoPic, openGroup FROM groups WHERE suspendDate = '0' AND closedDate = '0' ORDER BY title";
        break;
    default:
        $ob = "SELECT t1.id, t1.creatorId, t1.title, t1.description, t1.logoPic, t1.openGroup FROM groups AS t1 INNER JOIN groupCategories AS t2 ON t1.id = t2.groupId WHERE t2.catId='$sortby' AND t1.suspendDate = '0' AND t1.closedDate = '0'";
}
echo "<div style='font-weight:bold; font-size:1em; text-align:center; padding-bottom:30px;'><form action='index.php?page=allGroups' method='post'>Sort By: <select name='sortGroups' size='1'>\n";
echo "<option value='0'";
echo ($sortby == 0) ? " selected" : "";
echo ">Random</option>\n";
echo "<option value='1'";
echo ($sortby == 1) ? " selected" : "";
echo ">Alphabetical</option>\n";
foreach ($CATEGORIES as $k => $v) {
    echo "<option value='$k'";
    echo ($sortby == $k) ? " selected" : "";
    echo ">$v</option>\n";
}
echo "</select> <input type='submit' value=' Sort '></form></div>\n";
$getG = $db->prepare($ob);
$getG->execute();
while ($getGR = $getG->fetch()) {
    if ($getGR) {
        $id = $getGR[0];
        $creatorId = $getGR[1];
        $title = html_entity_decode($getGR[2]);
        $desc = nl2br(html_entity_decode($getGR[3]));
        $logo = $getGR[4];
        $openGroup = $getGR[5];
        $members = 0;

        $blocked = 0;
        $check = $db->prepare("SELECT blocked FROM groupMembership WHERE userId = ? AND groupId = ?");
        $check->execute(array(
            $myId,
            $id
        ));
        $checkR = $check->fetch();
        if ($checkR) {
            $blocked = $checkR['blocked'];
        }

        if (($blocked == 0 && $myId >= 1) || ($blocked == 0 && $openGroup == 1)) {

            $mem = $db->prepare("SELECT COUNT(*) FROM groupMembership WHERE groupId = ?");
            $mem->execute(array(
                $id
            ));
            $memR = $mem->fetch();
            if ($memR) {
                $members = $memR[0];
            }

            $l = ($logo == "x.png") ? "images/writeLogo.png" : "images/$creatorId/$logo";
            echo "<div style='' class='clearfix'><image src='$l' alt='' style='max-length:75px; max-width:75px; margin:10px; float:left;'>\n";
            echo "<div style='font-weight:bold; font-size:1.25em; text-align:left; padding:20px 20px 0px 20px;'><a href='index.php?page=group&groupId=$id' target='_self'>$title</a></div>\n";
            echo "<div style='font-size:.75em; text-align:left; padding-bottom:10px; color:#555555;'>$members members</div>\n";
            echo "$desc</div>\n";
            if ($myId >= 1) {
                $isMember = 0;
                $isMem = $db->prepare("SELECT COUNT(*) FROM groupMembership WHERE groupId = ? AND userId = ?");
                $isMem->execute(array(
                    $id,
                    $myId
                ));
                $isMemR = $isMem->fetch();
                if ($isMemR) {
                    $isMember = $isMemR[0];
                }
                $checkC = $db->prepare("SELECT creatorId FROM groups WHERE id = ?");
                $checkC->execute(array(
                    $id
                ));
                $checkCR = $checkC->fetch();
                if ($checkCR) {
                    $creator = $checkCR['creatorId'];
                } else {
                    $creator = 0;
                }
                if ($myId != $creator) {
                    if ($isMember == 0) {
                        echo "<a href='index.php?page=allGroups&joinGroup=$id'>Join this group.</a>\n";
                    } else {
                        echo "<a href='index.php?page=allGroups&leaveGroup=$id'>Leave this group.</a>\n";
                    }
                }
            } else {
                ?>
<a onclick="document.getElementById('id01').style.display='block'">Login
	/ Register</a>
to join this group.
			<?php
            }
            echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
        }
	}
}