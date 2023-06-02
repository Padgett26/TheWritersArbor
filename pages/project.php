<?php
if ($myId >= 1) {
    $p = 0;
    if (filter_input(INPUT_POST, 'writingUp', FILTER_SANITIZE_NUMBER_INT) >= 1) {
        $p = filter_input(INPUT_POST, 'writingUp', FILTER_SANITIZE_NUMBER_INT);
        $id = filter_input(INPUT_POST, 'writingId', FILTER_SANITIZE_NUMBER_INT);
        $title = htmlentities(
                filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $writing = htmlspecialchars(
                filter_input(INPUT_POST, 'writing', FILTER_UNSAFE_RAW));
        $delPic = (filter_input(INPUT_POST, 'delPic', FILTER_SANITIZE_NUMBER_INT) ==
                1) ? 1 : 0;
        if ($id == 0) {
            $wUpNew = $db->prepare(
                    "INSERT INTO writings VALUES(NULL, ?, ?, ?, ?, ?, ?, '0', '0', '0')");
            $wUpNew->execute(
                    array(
                            $myId,
                            $p,
                            $time,
                            $time,
                            $title,
                            $writing
                    ));
            $getNew = $db->prepare(
                    "SELECT id FROM writings WHERE userId = ? AND projectId = ? AND submissionDate = ? ORDER BY id DESC LIMIT 1");
            $getNew->execute(array(
                    $myId,
                    $p,
                    $time
            ));
            $getNewR = $getNew->fetch();
            if ($getNewR) {
                $id = $getNewR['id'];
            }
            $setP = $db->prepare(
                    "INSERT INTO images VALUES(NULL, ?, ?, '0', '0')");
            $setP->execute(array(
                    $id,
                    'x.png'
            ));

            // Send email notification to member of the group, who want them,
            // about a new submission
            $pn = $db->prepare("SELECT title FROM projects WHERE id = ?");
            $pn->execute(array(
                    $p
            ));
            $pnR = $pn->fetch();
            if ($pnR) {
                $projectName = $pnR['title'];
            }
            $n1 = $db->prepare("SELECT groupId FROM projects WHERE id = ?");
            $n1->execute(array(
                    $p
            ));
            $n1R = $n1->fetch();
            if ($n1R) {
                $g = $n1R['groupId'];
                $n2 = $db->prepare(
                        "SELECT userId FROM groupMembership WHERE groupId = ?");
                $n2->execute(array(
                        $g
                ));
                while ($n2R = $n2->fetch()) {
                    if ($n2R) {
                        $u = $n2R['userId'];
                        $n3 = $db->prepare(
                                "SELECT name, email, groupNotification FROM users WHERE id = ?");
                        $n3->execute(array(
                                $u
                        ));
                        $n3R = $n3->fetch();
                        if ($n3R) {
                            $notifyName = $n3R['name'];
                            $notifyEmail = $n3R['email'];
                            $notify = $n3R['groupNotification'];
                            if ($notify == 1) {
                                $subject = "New writing submission on The Writers Arbor - Project: $projectName";
                                $mess = "There has been a new writing submission in the $projectName project.<br><br>\n";
                                $mess .= "You can follow this link to view the writing:<br>\n";
                                $mess .= "<a href='https://thewritersarbor.com/index.php?page=projects&projectId=$p'>https://thewritersarbor.com/index.php?page=projects&projectId=$p</a><br><br>\n";
                                $mess .= "Thank you,<br>The Writers Arbor Admin";
                                sendEmail($subject, $mess, $notifyEmail,
                                        $notifyName);
                            }
                        }
                    }
                }
            }
        } else {
            $update = $db->prepare(
                    "UPDATE writings SET lastEditDate = ?, title = ?, writing = ? WHERE id = ?");
            $update->execute(array(
                    $time,
                    $title,
                    $writing,
                    $id
            ));
        }
        if (isset($_FILES['image']['tmp_name']) &&
                $_FILES['image']['size'] >= 1000) {
            $image = $_FILES['image']["tmp_name"];
            list ($width, $height) = (getimagesize($image) != null) ? getimagesize(
                    $image) : null;
            if ($width != null && $height != null) {
                $imageType = getPicType($_FILES['image']['type']);
                $imageName = $time . "." . $imageType;
                processPic("$domanin/images/$myId", $imageName, $image, 800, 150);
                $p1stmt = $db->prepare(
                        "UPDATE images SET imageName = ? WHERE writingsId = ?");
                $p1stmt->execute(array(
                        $imageName,
                        $id
                ));
            }
        }
        if ($delPic == 1) {
            $pstmt = $db->prepare(
                    "UPDATE images SET imageName = ? WHERE writingsId = ?");
            $pstmt->execute(array(
                    'x.png',
                    $id
            ));
        }
    }
    if (filter_input(INPUT_POST, 'noteUp', FILTER_SANITIZE_NUMBER_INT) >= 1) {
        $w = filter_input(INPUT_POST, 'noteUp', FILTER_SANITIZE_NUMBER_INT);
        $note = htmlentities(
                filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $nUp = $db->prepare(
                "INSERT INTO notes VALUES(NULL, ?, ?, ?, ?, '0', '0')");
        $nUp->execute(array(
                $myId,
                $w,
                $note,
                $time
        ));

        // Send email notification to member of the group, who want them, about
        // a new submission
        $wn = $db->prepare(
                "SELECT userId, title, projectId FROM writings WHERE id = ?");
        $wn->execute(array(
                $w
        ));
        $wnR = $wn->fetch();
        if ($wnR) {
            $writingId = $wnR['userId'];
            $writingName = $wnR['title'];
            $writingProject = $wnR['projectId'];
            $n3 = $db->prepare(
                    "SELECT name, email, writingNotification FROM users WHERE id = ?");
            $n3->execute(array(
                    $writingId
            ));
            $n3R = $n3->fetch();
            if ($n3R) {
                $notifyName = $n3R['name'];
                $notifyEmail = $n3R['email'];
                $notify = $n3R['writingNotification'];
                if ($notify == 1) {
                    $subject = "New note on The Writers Arbor - your writing: $writingName";
                    $mess = "There has been a new note on your $writingName writing.<br><br>\n";
                    $mess .= "You can follow this link to view the note:<br>\n";
                    $mess .= "<a href='https://thewritersarbor.com/index.php?page=projects&projectId=$writingProject'>https://thewritersarbor.com/index.php?page=projects&projectId=$writingProject</a><br><br>\n";
                    $mess .= "Thank you,<br>The Writers Arbor Admin";
                    sendEmail($subject, $mess, $notifyEmail, $notifyName);
                }
            }
        }
    }
    if (filter_input(INPUT_GET, 'projectId', FILTER_SANITIZE_NUMBER_INT) >= 1) {
        $p = filter_input(INPUT_GET, 'projectId', FILTER_SANITIZE_NUMBER_INT);
    }
    if ($p >= 1) {
        $getP = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $getP->execute(array(
                $p
        ));
        $getPR = $getP->fetch();
        if ($getPR) {
            $openDate = $getPR['openDate'];
            $closeDate = $getPR['closeDate'];
            $title = html_entity_decode($getPR['title'], ENT_QUOTES);
            $description = nl2br(
                    html_entity_decode($getPR['description'], ENT_QUOTES));

            echo "<div style='font-weight:bold; font-size:1.25em; text-align:center; padding:30px 0px;'>$title<br>\n";
            echo "<span style='color:#555555; font-size:.75em;'>" .
                    showDate($openDate) . " - " . showDate($closeDate) .
                    "</span></div>\n";
            echo "<div style=''>$description</div>\n";
            echo "<div style='padding:30px;'>\n";
            echo "<div style='padding:10px; cursor:pointer; font-weight:bold;' onclick='toggleview(\"writingMySub\")'>My submission</div>\n";
            echo "<div id='writingMySub' style='display:none; padding:10px;'>\n";
            $in = "x.png";
            $getW = $db->prepare(
                    "SELECT * FROM writings WHERE userId = ? AND projectId = ? LIMIT 1");
            $getW->execute(array(
                    $myId,
                    $p
            ));
            $getWR = $getW->fetch();
            if ($getWR) {
                $wId = $getWR['id'];
                $wSubmissionDate = showDate($getWR['submissionDate']);
                $wLastEditDate = showDate($getWR['lastEditDate']);
                $wTitle = html_entity_decode($getWR['title'], ENT_QUOTES);
                $wWriting = html_entity_decode($getWR['writing'], ENT_QUOTES);

                $getI = $db->prepare(
                        "SELECT * FROM images WHERE writingsId = ? LIMIT 1");
                $getI->execute(array(
                        $wId
                ));
                $getIR = $getI->fetch();
                if ($getIR) {
                    $i = $getIR['imageName'];
                    $in = ($i == 'x.png') ? "images/x.png" : "images/$myId/thumbs/$i";
                } else {
                    $in = "images/x.png";
                }
            } else {
                $wId = 0;
                $wSubmissionDate = 'New';
                $wLastEditDate = 'New';
                $wTitle = "";
                $wWriting = "";
            }
            echo "<form action='index.php?page=project&projectId=$p' method='post' enctype='multipart/form-data'>\n";
            echo "<div style='font-size:.75em; padding-top:10px;'>Submission Date: $wSubmissionDate</div>\n";
            echo "<div style='font-size:.75em; padding-bottom:10px;'>Last Edit Date: $wLastEditDate</div>\n";
            echo "<div style=''>Title:<br><input type='text' name='title' value='$wTitle'></div>\n";
            ?>
			Writing:<br>
			Text align:&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="textAlignSelection('left')">left</span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="textAlignSelection('center')">center</span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="textAlignSelection('right')">right</span>&nbsp;&nbsp;
			<span style="cursor:pointer; font-size:2em; font-weight:bold;" onclick="ModifySelection('h1')">h1</span>&nbsp;&nbsp;
			<span style="cursor:pointer; font-size:1.5em; font-weight:bold;" onclick="ModifySelection('h2')">h2</span>&nbsp;&nbsp;
			<span style="cursor:pointer; font-size:1.17em; font-weight:bold;" onclick="ModifySelection('h3')">h3</span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="ModifySelection('b')"><b>bold</b></span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="ModifySelection('i')"><i>italics</i></span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="ModifySelection('blockquote')">blockquote</span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="ModifySelection('del')"><del>deleted text</del></span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="ModifySelection('ins')"><ins>replacement text</ins></span>&nbsp;&nbsp;
			<span style="cursor:pointer;" onclick="languageSelection('hebrew')">Hebrew</span><br>
			<span style='font-size:.75em;'>Your formatting tag will be placed where your cursor is in the text.<br>
			Place the text you want to be formatted between the tags.<br>
			Or highlight text that you want to be eclosed inclosed in the tag.<br>
			Language tags will convert everything inside of the tag, including other tags.<br>
			So, if you want to change the size of the characters in a converted langauge, put the sizing tags outside of the langauge tags.<br>
			Hebrew is read right-to-left, and any sequence of letters in hebrew tags will be printed rtf.<br>
			The tags will not show up in the version of your text that other see.</span>
			<br><br>
			<?php
            echo "<div style=''><textarea name='writing' id='textField'>$wWriting</textarea></div>\n";
            echo "<div style=''>Photo:<br><img src='$in' alt='' style=''><br>\n";
            echo "Upload a new pic:<br><input type='file' name='image'><br><br>\n";
            echo "Delete uploaded pic: <input type='checkbox' name='delPic' value='1'></div>\n";
            echo "<div style=''><input type='submit' value=' Upload Writing '><input type='hidden' name='writingUp' value='$p'><input type='hidden' name='writingId' value='$wId'></div>\n";
            echo "</form>\n";
            echo "<div style='padding-top:20px;'>\n";
            if ($wId != 0) {
                echo "Notes:<br>\n";
                echo "<form action='index.php?page=project&projectId=$p' method='post'>\n";
                echo "<label for='note'>New note:</label>\n";
                echo "<textarea name='note'></textarea><br><br>\n";
                echo "<input type='submit' value=' Submit Note '><input type='hidden' name='noteUp' value='$wId'>\n";
                echo "</form>\n";
                $notes = $db->prepare(
                        "SELECT * FROM notes WHERE writingsId = ? ORDER BY noteDate DESC");
                $notes->execute(array(
                        $wId
                ));
                while ($notesR = $notes->fetch()) {
                    if ($notesR) {
                        $u = $notesR['userId'];
                        $n = nl2br(
                                html_entity_decode($notesR['note'], ENT_QUOTES));
                        $d = $notesR['noteDate'];
                        $getU = $db->prepare(
                                "SELECT name FROM users WHERE id = ?");
                        $getU->execute(array(
                                $u
                        ));
                        $getUR = $getU->fetch();
                        if ($getUR) {
                            $un = $getUR['name'];
                        }
                        echo "<div style='padding:20px 0px;'>" . showDate($d) .
                                " - $un<br>$n</div>\n";
                    }
                }
            }
            echo "</div></div></div>\n";

            echo "<div style='padding:30px;'>\n";
            $in = "x.png";
            $getW = $db->prepare(
                    "SELECT * FROM writings WHERE projectId = ? ORDER BY RAND()");
            $getW->execute(array(
                    $p
            ));
            while ($getWR = $getW->fetch()) {
                if ($getWR) {
                    $wId = $getWR['id'];
                    $uId = $getWR['userId'];
                    $wSubmissionDate = $getWR['submissionDate'];
                    $wLastEditDate = $getWR['lastEditDate'];
                    $wTitle = html_entity_decode($getWR['title'], ENT_QUOTES);
                    $wWriting = nl2br(
                            html_entity_decode($getWR['writing'], ENT_QUOTES));
                    preg_match_all("/<hebrew>([a-z\s]+)<\/hebrew>/", $wWriting,
                            $match, PREG_SET_ORDER);
                    foreach ($match as $val) {
                        $letters = str_split($val[1]);
                        $replacement = "<span style='font-weight:bold; font-size:1.25em;'>";
                        foreach ($letters as $v) {
                            $replacement .= (array_key_exists($v, $HEBREW)) ? $HEBREW[$v] : "";
                            $replacement .= ($v == " ") ? " " : "";
                        }
                        $replacement .= "</span>";
                        $wWriting = str_replace($val[0], $replacement, $wWriting);
                    }
                    $getU = $db->prepare("SELECT name FROM users WHERE id = ?");
                    $getU->execute(array(
                            $uId
                    ));
                    $getUR = $getU->fetch();
                    if ($getUR) {
                        $name = $getUR['name'];
                    }
                    $getI = $db->prepare(
                            "SELECT * FROM images WHERE writingsId = ? LIMIT 1");
                    $getI->execute(array(
                            $wId
                    ));
                    $getIR = $getI->fetch();
                    if ($getIR) {
                        $i = $getIR['imageName'];
                        $in = ($i == 'x.png') ? "images/x.png" : "images/$uId/$i";
                    } else {
                        $in = "images/x.png";
                    }
                    list ($width, $height) = (getimagesize($in) != null) ? getimagesize(
                            $in) : null;
                    $vertical = ($height >= $width) ? '60%' : '100%';
                    echo "<div style='padding:10px; cursor:pointer; font-weight:bold;' onclick='toggleview(\"writing$uId\")'>$name</div>\n";
                    echo "<div id='writing$uId' style='display:none; padding:10px;'>\n";
                    echo "<div style='font-size:.75em; padding-top:10px;'>Submission Date: " .
                            showDate($wSubmissionDate) . "</div>\n";
                    echo "<div style='font-size:.75em; padding-bottom:10px;'>Last Edit Date: " .
                            showDate($wLastEditDate) . "</div>\n";
                    echo "<div style='text-align:center;'><img src='$in' alt='' style='max-width:$vertical; max-height:300px; padding:10px;'></div>\n";
                    echo "<div style='text-align:center; font-weight:bold; font-size:1.25em;'>$wTitle</div>\n";
                    echo "<div style='padding-top:20px;'>$wWriting</div>\n";
                    echo "<div style='padding-top:20px;'>\n";
                    echo "Notes:<br>\n";
                    echo "<form action='index.php?page=project&projectId=$p' method='post'>\n";
                    echo "<label for='note'>New note:</label>\n";
                    echo "<textarea name='note'></textarea><br><br>\n";
                    echo "<input type='submit' value=' Submit Note '><input type='hidden' name='noteUp' value='$wId'>\n";
                    echo "</form>\n";
                    $notes = $db->prepare(
                            "SELECT * FROM notes WHERE writingsId = ? ORDER BY noteDate DESC");
                    $notes->execute(array(
                            $wId
                    ));
                    while ($notesR = $notes->fetch()) {
                        if ($notesR) {
                            $u = $notesR['userId'];
                            $n = nl2br(
                                    html_entity_decode($notesR['note'],
                                            ENT_QUOTES));
                            $d = $notesR['noteDate'];
                            $getU = $db->prepare(
                                    "SELECT name FROM users WHERE id = ?");
                            $getU->execute(array(
                                    $u
                            ));
                            $getUR = $getU->fetch();
                            if ($getUR) {
                                $un = $getUR['name'];
                            }
                            echo "<div style='padding:20px 0px;'><span style='font-weight:bold;'>" .
                                    showDate($d) . " - $un</span><br>$n</div>\n";
                        }
                    }
                    echo "</div></div>\n";
                }
            }
            echo "</div>\n";
        }
    } else {
        include "pages/allProjects.php";
    }
} else {
    echo "Please log in to see your writing projects.";
}