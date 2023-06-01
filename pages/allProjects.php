<div
	style="font-weight: bold; font-size: 1.5em; text-align: center; padding: 40px 0px;">My
	Active Projects</div>
<?php
$check = $db->prepare("SELECT groupId, blocked FROM groupMembership WHERE userId = ?");
$check->execute(array(
    $myId
));
while ($checkR = $check->fetch()) {
    $g = $checkR['groupId'];
    $b = $checkR['blocked'];
    if ($b == 0) {
        $p = $db->prepare("SELECT * FROM projects WHERE groupId = ? AND openDate <= ? AND closeDate >= ? ORDER BY openDate");
        $p->execute(array(
            $g,
            $time,
            $time
        ));
        while ($pR = $p->fetch()) {
            if ($pR) {
                $id = $pR['id'];
                $openDate = $pR['openDate'];
                $closeDate = $pR['closeDate'];
                $title = html_entity_decode($pR['title']);
                $description = nl2br(html_entity_decode($pR['description']));

                echo "<div style='text-align:left; padding-bottom:20px;'><span style='font-weight:bold; font-size:1.25em;'><a href='index.php?page=project&projectId=$id' target='_self'>$title</a></span><br>\n";
                echo "<span style='color:#555555; font-size:.75em;'>" . showDate($openDate) . " - " . showDate($closeDate) . "</span></div>\n";
                echo "<div style=''>$description</div>\n";
                echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
            }
        }
    }
}
?>
<div
	style="font-weight: bold; font-size: 1.5em; text-align: center; padding: 40px 0px;">Past
	Group Projects</div>
<?php
$check = $db->prepare("SELECT groupId, blocked FROM groupMembership WHERE userId = ?");
$check->execute(array(
    $myId
));
while ($checkR = $check->fetch()) {
    $g = $checkR['groupId'];
    $b = $checkR['blocked'];
    if ($b == 0) {
        $p = $db->prepare("SELECT * FROM projects WHERE groupId = ? AND closeDate <= ? ORDER BY openDate");
        $p->execute(array(
            $g,
            $time
        ));
        while ($pR = $p->fetch()) {
            if ($pR) {
                $id = $pR['id'];
                $openDate = $pR['openDate'];
                $closeDate = $pR['closeDate'];
                $title = html_entity_decode($pR['title']);
                $description = nl2br(html_entity_decode($pR['description']));

                echo "<div style='text-align:left; padding-bottom:20px;'><span style='font-weight:bold; font-size:1.25em;'><a href='index.php?page=project&projectId=$id' target='_self'>$title</a></span><br>\n";
                echo "<span style='color:#555555; font-size:.75em;'>" . showDate($openDate) . " - " . showDate($closeDate) . "</span></div>\n";
                echo "<div style=''>$description</div>\n";
                echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
            }
        }
    }
}
?>
</table>