<?php
if ($myId >= 1) {
    if (filter_input(INPUT_POST, 'block', FILTER_SANITIZE_NUMBER_INT)) {
        $memId = filter_input(INPUT_POST, 'block', FILTER_SANITIZE_NUMBER_INT);
        $grId = filter_input(INPUT_POST, 'groupId', FILTER_SANITIZE_NUMBER_INT);
        $bl = $db->prepare(
                "UPDATE groupMembership SET blocked = ? WHERE userId = ? AND groupId = ?");
        $bl->execute(array(
                '1',
                $memId,
                $grId
        ));
    }
    if (filter_input(INPUT_POST, 'unblock', FILTER_SANITIZE_NUMBER_INT)) {
        $memId = filter_input(INPUT_POST, 'unblock', FILTER_SANITIZE_NUMBER_INT);
        $grId = filter_input(INPUT_POST, 'groupId', FILTER_SANITIZE_NUMBER_INT);
        $bl = $db->prepare(
                "UPDATE groupMembership SET blocked = ? WHERE userId = ? AND groupId = ?");
        $bl->execute(array(
                '0',
                $memId,
                $grId
        ));
    }
    if (filter_input(INPUT_POST, 'newOwner', FILTER_SANITIZE_NUMBER_INT) >= 1) {
        $newOwner = filter_input(INPUT_POST, 'newOwner',
                FILTER_SANITIZE_NUMBER_INT);
        $newOwnerGroup = filter_input(INPUT_POST, 'newOwnerGroup',
                FILTER_SANITIZE_NUMBER_INT);
        $oUp = $db->prepare("UPDATE groups SET creatorId = ? WHERE id = ?");
        $oUp->execute(array(
                $newOwner,
                $newOwnerGroup
        ));
        $recordChange = $db->prepare(
                "INSERT INTO groupOwnershipChanges VALUES(NULL,?,?,?,?,'0')");
        $recordChange->execute(
                array(
                        $myId,
                        $newOwner,
                        $time,
                        $newOwnerGroup
                ));
    }
    if (filter_input(INPUT_POST, 'invites', FILTER_SANITIZE_NUMBER_INT) == 1) {
        $gid = filter_input(INPUT_GET, 'gid', FILTER_SANITIZE_NUMBER_INT);
        for ($i = 1; $i <= 5; ++ $i) {
            ${"name" . $i} = filter_input(INPUT_POST, "name$i",
                    FILTER_SANITIZE_STRING);
        }
        for ($j = 1; $j <= 5; ++ $j) {
            ${"email" . $j} = filter_input(INPUT_POST, "email$j",
                    FILTER_SANITIZE_EMAIL);
        }
        for ($k = 1; $k <= 5; ++ $k) {
            if (${"name" . $k} != "" && ${"name" . $k} != " " &&
                    ${"email" . $k} != "" && ${"email" . $k} != " ") {
                $iCheck = $db->prepare(
                        "SELECT COUNT(*) FROM invites WHERE email = ? AND inviteGroup = ?");
                $iCheck->execute(array(
                        ${"email" . $k},
                        $gid
                ));
                $iCheckR = $iCheck->fetch();
                if ($iCheckR && $iCheckR[0] == 0) {
                    $iUp = $db->prepare(
                            "INSERT INTO invites VALUES(NULL, ?, ?, ?, ?, '0', '0')");
                    $iUp->execute(
                            array(
                                    ${"name" . $k},
                                    ${"email" . $k},
                                    $gid,
                                    $time
                            ));
                }
                $iGetId = $db->prepare(
                        "SELECT id FROM invites WHERE email = ? AND inviteGroup = ? LIMIT 1");
                $iGetId->execute(array(
                        ${"email" . $k},
                        $gid
                ));
                $iGetIdR = $iGetId->fetch();
                if ($iGetIdR) {
                    $u = $iGetIdR['id'];
                    $emailBody = nl2br(
                            filter_input(INPUT_POST, 'emailBody',
                                    FILTER_SANITIZE_STRING));
                    $emailBody .= "To join this group, please follow this link: https://thewritersarbor.com/index.php?page=invites&u=$u Thank you";
                    sendEmail(
                            "$MYNAME has invited you to a group on The Writers Arbor",
                            $emailBody, ${"email" . $k}, ${"name" . $k});
                }
            }
        }
    }
    if (filter_input(INPUT_POST, 'personalUp', FILTER_SANITIZE_NUMBER_INT) == 1) {
        $n = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $e = (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) ? filter_input(
                INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) : 'x';
        $pwd1 = filter_input(INPUT_POST, 'pwd1', FILTER_SANITIZE_STRING);
        $pwd2 = filter_input(INPUT_POST, 'pwd2', FILTER_SANITIZE_STRING);
        $groupNotification = (filter_input(INPUT_POST, 'groupNotification',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        $writingNotification = (filter_input(INPUT_POST, 'writingNotification',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        if ($e == 'x') {
            $errorMsg = "Was unable to validate your email address.";
        } else {
            if ($pwd1 != "" && $pwd1 != " " && $pwd1 === $pwd2) {
                $salt = mt_rand(100000, 999999);
                $hidepwd = hash('sha512', ($salt . $pwd1), FALSE);
                $stmt = $db->prepare(
                        "UPDATE users SET password = ?, salt = ? WHERE id = ?");
                $stmt->execute(array(
                        $hidepwd,
                        $salt,
                        $myId
                ));
            } else {
                $errorMsg = "There was either no password entered, or your passwords did not match. Your password was not changed.";
            }
            $updateP = $db->prepare(
                    "UPDATE users SET name = ?, email = ?, groupNotification = ?, writingNotification = ? WHERE id = ?");
            $updateP->execute(
                    array(
                            $n,
                            $e,
                            $groupNotification,
                            $writingNotification,
                            $myId
                    ));
        }
    }
    if (filter_input(INPUT_POST, 'groupUp', FILTER_SANITIZE_NUMBER_INT)) {
        $gId = filter_input(INPUT_POST, 'groupUp', FILTER_SANITIZE_NUMBER_INT);
        $gTitle = htmlentities(
                filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $gDesc = htmlentities(
                filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $gSuspend = (filter_input(INPUT_POST, 'suspend',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        $gUnsuspend = (filter_input(INPUT_POST, 'unsuspend',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        $gClose = (filter_input(INPUT_POST, 'close', FILTER_SANITIZE_NUMBER_INT) ==
                1) ? 1 : 0;
        $gUnclose = (filter_input(INPUT_POST, 'unclose',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        $gDelLogo = (filter_input(INPUT_POST, 'delLogo',
                FILTER_SANITIZE_NUMBER_INT) == 1) ? 1 : 0;
        $openGroup = filter_input(INPUT_POST, 'openGroup',
                FILTER_SANITIZE_NUMBER_INT);
        $sus = ($gSuspend == 1) ? $time : 0;
        $sus = ($gUnsuspend == 1) ? 0 : $sus;
        $close = ($gClose == 1) ? $time : 0;
        $close = ($gUnclose == 1) ? 0 : $close;
        $updateG = $db->prepare(
                "UPDATE groups SET suspendDate = ?, closedDate = ?, title = ?, description = ?, openGroup = ? WHERE id = ?");
        $updateG->execute(
                array(
                        $sus,
                        $close,
                        $gTitle,
                        $gDesc,
                        $openGroup,
                        $gId
                ));
        if ($gDelLogo == 1) {
            $delPic = $db->prepare("UPDATE groups SET logoPic = ? WHERE id = ?");
            $delPic->execute(array(
                    'x.png',
                    $gId
            ));
        } else {
            if (isset($_FILES['image']['tmp_name']) &&
                    $_FILES['image']['size'] >= 1000) {
                $image = $_FILES['image']["tmp_name"];
                list ($width, $height) = (getimagesize($image) != null) ? getimagesize(
                        $image) : null;
                if ($width != null && $height != null) {
                    $imageType = getPicType($_FILES['image']['type']);
                    $imageName = $time . "." . $imageType;
                    processPic("$domanin/images/$myId", $imageName, $image, 800,
                            150);
                    $p1stmt = $db->prepare(
                            "UPDATE groups SET logoPic = ? WHERE id = ?");
                    $p1stmt->execute(array(
                            $imageName,
                            $gId
                    ));
                }
            }
        }
        $delCat = $db->prepare("DELETE FROM groupCategories WHERE groupId = ?");
        $delCat->execute(array(
                $gId
        ));
        foreach ($_POST as $key => $val) {
            if (preg_match("/^cat([1-9][0-9]*)$/", $key, $match)) {
                if ($val == 1) {
                    $catId = $match[1];
                    $gm = $db->prepare(
                            "INSERT INTO groupCategories VALUES(NULL,?,?,?)");
                    $gm->execute(array(
                            $gId,
                            $catId,
                            '0'
                    ));
                }
            }
        }
        $catNew = (filter_input(INPUT_POST, 'catNew', FILTER_SANITIZE_NUMBER_INT) ==
                1) ? 1 : 0;
        if ($catNew == 1) {
            $catNewName = filter_input(INPUT_POST, 'catNewName',
                    FILTER_SANITIZE_STRING);
            if ($catNewName != "" && $catNewName != " ") {
                $cnn = htmlentities($catNewName, ENT_QUOTES);
                $cCheck = $db->prepare(
                        "SELECT COUNT(*),id FROM categories WHERE catName = ?");
                $cCheck->execute(array(
                        $cnn
                ));
                $cCheckR = $cCheck->fetch();
                $cc = ($cCheckR) ? $cCheckR[0] : 0;
                if ($cc == 0) {
                    $cUp = $db->prepare(
                            "INSERT INTO categories VALUES(NULL,?,?,?)");
                    $cUp->execute(array(
                            $cnn,
                            '0',
                            '0'
                    ));
                    $cGet = $db->prepare(
                            "SELECT id FROM categories WHERE catName = ?");
                    $cGet->execute(array(
                            $cnn
                    ));
                    $cGetR = $cGet->fetch();
                    if ($cGetR) {
                        $cId = $cGetR['id'];
                    }
                } else {
                    $cId = $cCheckR['id'];
                }
                $gcUpCheck = $db->prepare(
                        "SELECT COUNT(*) FROM groupCategories WHERE catId = ? AND groupId = ?");
                $gcUpCheck->execute(array(
                        $cId,
                        $gId
                ));
                $gcUCR = $gcUpCheck->fetch();
                if ($gcUCR) {
                    $gcCount = $gcUCR[0];
                    if ($gcCount == 0) {
                        $gcUp = $db->prepare(
                                "INSERT INTO groupCategories VALUES(NULL,?,?,?)");
                        $gcUp->execute(array(
                                $gId,
                                $cId,
                                '0'
                        ));
                    }
                }
            }
        }
    }
    if (filter_input(INPUT_POST, 'projectUp', FILTER_SANITIZE_NUMBER_INT)) {
        $projectId = filter_input(INPUT_POST, 'projectUp',
                FILTER_SANITIZE_NUMBER_INT);
        $projectTitle = htmlentities(
                filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $projectDesc = htmlentities(
                filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $projectOpen = filter_input(INPUT_POST, 'open', FILTER_SANITIZE_STRING);
        $projectClose = filter_input(INPUT_POST, 'close', FILTER_SANITIZE_STRING);
        $po = explode("-", $projectOpen);
        $pc = explode("-", $projectClose);
        $open = mktime(0, 0, 0, $po[1], $po[2], $po[0]);
        $close = mktime(0, 0, 0, $pc[1], $pc[2], $pc[0]);
        $updateP = $db->prepare(
                "UPDATE projects SET openDate = ?, closeDate = ?, title = ?, description = ? WHERE id = ?");
        $updateP->execute(
                array(
                        $open,
                        $close,
                        $projectTitle,
                        $projectDesc,
                        $projectId
                ));
    }
    if (filter_input(INPUT_POST, 'groupUp', FILTER_SANITIZE_STRING) == 'new') {
        $gTitle = htmlentities(
                filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $gDesc = htmlentities(
                filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $openGroup = filter_input(INPUT_POST, 'openGroup',
                FILTER_SANITIZE_NUMBER_INT);
        $createG = $db->prepare(
                "INSERT INTO groups VALUES(NULL,?,?,'0','0',?,?,'x.png','x.png',?,'0')");
        $createG->execute(array(
                $myId,
                $time,
                $gTitle,
                $gDesc,
                $openGroup
        ));
        $getG = $db->prepare(
                "SELECT id FROM groups WHERE creatorId = ? AND createDate = ?");
        $getG->execute(array(
                $myId,
                $time
        ));
        $getGR = $getG->fetch();
        if ($getGR) {
            $gId = $getGR['id'];
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
                        "UPDATE groups SET logoPic = ? WHERE id = ?");
                $p1stmt->execute(array(
                        $imageName,
                        $gId
                ));
            }
        }
        $join = $db->prepare("INSERT INTO groupMembership VALUES(NULL,?,?,'0')");
        $join->execute(array(
                $myId,
                $gId
        ));
        foreach ($_POST as $key => $val) {
            if (preg_match("/^cat([1-9][0-9]*)$/", $key, $match)) {
                if ($val == 1) {
                    $catId = $match[1];
                    $gm = $db->prepare(
                            "INSERT INTO groupCategories VALUES(NULL,?,?,?)");
                    $gm->execute(array(
                            $gId,
                            $catId,
                            '0'
                    ));
                }
            }
        }
        $catNew = (filter_input(INPUT_POST, 'catNew', FILTER_SANITIZE_NUMBER_INT) ==
                1) ? 1 : 0;
        if ($catNew == 1) {
            $catNewName = filter_input(INPUT_POST, 'catNewName',
                    FILTER_SANITIZE_STRING);
            if ($catNewName != "" && $catNewName != " ") {
                $cnn = htmlentities($catNewName, ENT_QUOTES);
                $cCheck = $db->prepare(
                        "SELECT COUNT(*),id FROM categories WHERE catName = ?");
                $cCheck->execute(array(
                        $cnn
                ));
                $cCheckR = $cCheck->fetch();
                $cc = ($cCheckR) ? $cCheckR[0] : 0;
                if ($cc == 0) {
                    $cUp = $db->prepare(
                            "INSERT INTO categories VALUES(NULL,?,?,?)");
                    $cUp->execute(array(
                            $cnn,
                            '0',
                            '0'
                    ));
                    $cGet = $db->prepare(
                            "SELECT id FROM categories WHERE catName = ?");
                    $cGet->execute(array(
                            $cnn
                    ));
                    $cGetR = $cGet->fetch();
                    if ($cGetR) {
                        $cId = $cGetR['id'];
                    }
                } else {
                    $cId = $cCheckR['id'];
                }
                $gcUpCheck = $db->prepare(
                        "SELECT COUNT(*) FROM groupCategories WHERE catId = ? AND groupId = ?");
                $gcUpCheck->execute(array(
                        $cId,
                        $gId
                ));
                $gcUCR = $gcUpCheck->fetch();
                if ($gcUCR) {
                    $gcCount = $gcUCR[0];
                    if ($gcCount == 0) {
                        $gcUp = $db->prepare(
                                "INSERT INTO groupCategories VALUES(NULL,?,?,?)");
                        $gcUp->execute(array(
                                $gId,
                                $cId
                        ));
                    }
                }
            }
        }
    }
    if (filter_input(INPUT_POST, 'projectUp', FILTER_SANITIZE_STRING) == 'new') {
        $groupId = filter_input(INPUT_POST, 'groupId',
                FILTER_SANITIZE_NUMBER_INT);
        $projectTitle = htmlentities(
                filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $projectDesc = htmlentities(
                filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING),
                ENT_QUOTES);
        $projectOpen = filter_input(INPUT_POST, 'open', FILTER_SANITIZE_STRING);
        $projectClose = filter_input(INPUT_POST, 'close', FILTER_SANITIZE_STRING);
        $po = explode("-", $projectOpen);
        $pc = explode("-", $projectClose);
        $open = mktime(0, 0, 0, $po[1], $po[2], $po[0]);
        $close = mktime(0, 0, 0, $pc[1], $pc[2], $pc[0]);
        $updateP = $db->prepare(
                "INSERT INTO projects VALUES(NULL,?,?,?,?,?,'1','0','0')");
        $updateP->execute(
                array(
                        $open,
                        $close,
                        $groupId,
                        $projectTitle,
                        $projectDesc
                ));
    }

    $getUser = $db->prepare(
            "SELECT name, email, groupNotification, writingNotification FROM users WHERE id = ?");
    $getUser->execute(array(
            $myId
    ));
    $getUserR = $getUser->fetch();
    if ($getUserR) {
        $name = $getUserR['name'];
        $email = $getUserR['email'];
        $groupNotification = $getUserR['groupNotification'];
        $writingNotification = $getUserR['writingNotification'];
    } else {
        $name = "";
        $email = "";
    }

    echo "<div style='text-align:left; font-weight:bold; font-size:1.25em; padding-top:20px;'>Personal Information</div>\n";
    echo "<form action='index.php?page=settings' method='post'>\n";
    echo "<label for='name'>Name</label>\n";
    echo "<input type='text' name='name' value='$name' required>\n";
    echo "<label for='email'>Email</label>\n";
    echo "<input type='email' name='email' value='$email' required>\n";
    echo "<label for='pwd1'>Change Password - Enter new password twice</label>\n";
    echo "<input type='password' name='pwd1' value=''>\n";
    echo "<input type='password' name='pwd2' value=''><br><br>\n";
    echo "Notifications:<br>\n";
    echo "Would you like to receive an email notification when there is a new submission to one of the projects you are a member of? ";
    echo " <span style='font-weight:bold;'>YES <input type='radio' name='groupNotification' value='1'";
    echo ($groupNotification == 1) ? " checked>" : ">";
    echo " || NO <input type='radio' name='groupNotification' value='0'";
    echo ($groupNotification == 0) ? " checked>" : ">";
    echo "</span><br>\nWould you like to receive an email notification when there is a new note on one of your writings? ";
    echo " <span style='font-weight:bold;'>YES <input type='radio' name='writingNotification' value='1'";
    echo ($writingNotification == 1) ? " checked>" : ">";
    echo " || NO <input type='radio' name='writingNotification' value='0'";
    echo ($writingNotification == 0) ? " checked>" : ">";
    echo "</span><br><br>\n";
    echo "<input type='submit' value=' Update Personal Info '><input type='hidden' name='personalUp' value='1'>\n";
    echo "</form>\n";
    echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
    echo "<div style='text-align:left; font-weight:bold; font-size:1.25em;'>Create a group</div>\n";
    echo "<div style='text-align:left; font-weight:bold; font-size:1em; padding-top:20px; cursor:pointer;' onclick='toggleview(\"groupNew\")'>New Group</div>\n";
    echo "<div id='groupNew' style='display:none; margin:-left:20px;'>\n";
    echo "<form action='index.php?page=settings' method='post' enctype='multipart/form-data'>\n";
    echo "<label for='title'>Group Title</label>\n";
    echo "<input type='text' name='title' value='' required>\n";
    echo "<label for='desc'>Group Description</label>\n";
    echo "<textarea name='desc'></textarea><br><br>\n";
    echo "Logo Pic:<br>\n";
    echo "Upload a new logo pic:<br>\n";
    echo "<input type='file' name='image'>\n";
    echo "<div style='margin:20px 0px; font-weight:bold; cursor:pointer;' onclick='toggleview(\"catsNew\")'>Categories</div>\n";
    echo "<div id='catsNew' style='display:block; padding-bottom:20px;'>\n";
    echo "<table cellspacing='0px'><tr>\n";
    $t = 1;
    foreach ($CATEGORIES as $k => $v) {
        if ($v != 0) {
            echo "<td style='border:1px solid black; padding:5px;'><input type='checkbox' name='cat$k' value='1'> $v</td>\n";
            if ($t % 3 == 0) {
                echo "</tr><tr>\n";
            }
            $t ++;
        }
    }
    echo "</tr><tr>\n";
    echo "<td style='border:1px solid black; padding:5px;'><input type='checkbox' name='catNew' value='1'>&nbsp;<input type='text' name='catNewName' value='' placeholder='New category name'></td>\n";
    echo "<td style='border:1px solid black; padding:5px;' colspan='2'>&nbsp;</td>\n";
    echo "</tr></table>\n";
    echo "</div>\n";
    echo "<div style='text-align:left; font-weight:bold; font-size:1;'><input type='radio' name='openGroup' value='1' checked> Open Group</div>\n";
    echo "<div style='text-align:left; font-size:1;'>An open group will be displayed in the groups and projects lists, and anyone can click a link to join your group.</div>\n";
    echo "<div style='text-align:left; font-weight:bold; font-size:1;'><input type='radio' name='openGroup' value='2'> Closed Group</div>\n";
    echo "<div style='text-align:left; font-size:1; margin-bottom:20px;'>A closed group will not be displayed in the groups and projects lists, members can only join through an invitation from the group creator.</div>\n";
    echo "<input type='submit' value=' Create Group '><input type='hidden' name='groupUp' value='new'>\n";
    echo "</form></div>\n";
    echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
    echo "<div style='text-align:left; font-weight:bold; font-size:1.25em;'>Groups I have created</div>\n";
    $getG = $db->prepare(
            "SELECT * FROM groups WHERE creatorId = ? ORDER BY closedDate");
    $getG->execute(array(
            $myId
    ));
    while ($getGR = $getG->fetch()) {
        if ($getGR) {
            $id = $getGR['id'];
            $created = $getGR['createDate'];
            $suspended = $getGR['suspendDate'];
            $closed = $getGR['closedDate'];
            $title = html_entity_decode($getGR['title'], ENT_QUOTES);
            $desc = html_entity_decode($getGR['description'], ENT_QUOTES);
            $logo = $getGR['logoPic'];
            $openGroup = $getGR['openGroup'];

            echo "<div style='text-align:left; font-weight:bold; font-size:1em; padding-top:30px; cursor:pointer;' onclick='toggleview(\"group$id\")'>$title</div>\n";
            echo "<div id='group$id' style='display:none; margin-left:20px;'>\n";
            echo "<div style='font-weight:bold; padding-top:10px; cursor:pointer;' onclick='toggleview(\"owner$id\")'>Change group ownership</div>\n";
            echo "<div style='display:none; margin-left:20px;' id='owner$id'>\n";
            echo "<form action='index.php?page=settings' method='post'>\n";
            echo "If you wish hand the group off to another member of the group, select their name, and click 'Change Owner'.<br>\n";
            echo "You will no longer have the ability to administer the group, but you will remain a member.<br><br>\n";
            echo "<select name='newOwner' size='1'>\n";
            $get = $db->prepare(
                    "SELECT userId FROM groupMembership WHERE groupId = ? AND blocked = ?");
            $get->execute(array(
                    $id,
                    '0'
            ));
            while ($getR = $get->fetch()) {
                if ($getR) {
                    $userId = $getR['userId'];
                    $getN = $db->prepare("SELECT name FROM users WHERE id = ?");
                    $getN->execute(array(
                            $userId
                    ));
                    $getNR = $getN->fetch();
                    if ($getNR) {
                        $userName = html_entity_decode($getNR['name'],
                                ENT_QUOTES);
                        echo "<option value='$userId'";
                        echo ($userId == $myId) ? " selected" : "";
                        echo ">$userName</option>\n";
                    }
                }
            }
            echo "</select><br><input type='submit' value=' Change Owner '><input type='hidden' name='newOwnerGroup' value='$id'></form></div>\n";
            echo "<div style='padding-top:30px;'>\n";
            echo "<form action='index.php?page=settings' method='post' enctype='multipart/form-data'>\n";
            echo "<label for='title'>Group Title</label>\n";
            echo "<input type='text' name='title' value='$title' required>\n";
            echo "<label for='desc'>Group Description</label>\n";
            echo "<textarea name='desc'>$desc</textarea><br><br>\n";
            echo "Logo Pic:<br>\n";
            if ($logo != 'x.png') {
                echo "<img src='images/$myId/$logo' alt='' style='padding:5px;'><br>\n";
            }
            echo "Upload a new logo pic:<br>\n";
            echo "<input type='file' name='image'><br>\n";
            echo "Or, delete existing logo pic: <input type='checkbox' name='delLogo' value='1'>\n";
            echo "<div style='margin:20px 0px; font-weight:bold; cursor:pointer;' onclick='toggleview(\"cats$id\")'>Categories</div>\n";
            echo "<div id='cats$id' style='display:none; padding-bottom:20px;'>\n";
            echo "<table cellspacing='0px'><tr>\n";
            $t = 1;
            foreach ($CATEGORIES as $k => $v) {
                if ($v != 0) {
                    echo "<td style='border:1px solid black; padding:5px;'><input type='checkbox' name='cat$k' value='1'";
                    $getGC = $db->prepare(
                            "SELECT COUNT(*) FROM groupCategories WHERE groupId = ? AND catId = ?");
                    $getGC->execute(array(
                            $id,
                            $k
                    ));
                    $getGCR = $getGC->fetch();
                    if ($getGCR) {
                        $c = $getGCR[0];
                        if ($c >= 1) {
                            echo " checked";
                        }
                    }
                    echo "> $v</td>\n";
                    if ($t % 3 == 0) {
                        echo "</tr><tr>\n";
                    }
                    $t ++;
                }
            }
            echo "</tr><tr>\n";
            echo "<td style='border:1px solid black; padding:5px;'><input type='checkbox' name='catNew' value='1'>&nbsp;<input type='text' name='catNewName' value='' placeholder='New category name'></td>\n";
            echo "<td style='border:1px solid black; padding:5px;' colspan='2'>&nbsp;</td>\n";
            echo "</tr></table>\n";
            echo "</div>\n";
            echo "<div style='text-align:left; font-weight:bold; font-size:1;'><input type='radio' name='openGroup' value='1'";
            echo ($openGroup == 1) ? " checked" : "";
            echo "> Open Group</div>\n";
            echo "<div style='text-align:left; font-size:1;'>An open group will be displayed in the groups and projects lists, and anyone can click a link to join your group.</div>\n";
            echo "<div style='text-align:left; font-weight:bold; font-size:1;'><input type='radio' name='openGroup' value='2'";
            echo ($openGroup == 2) ? " checked" : "";
            echo "> Closed Group</div>\n";
            echo "<div style='text-align:left; font-size:1; margin-bottom:20px;'>A closed group will not be displayed in the groups and projects lists, members can only join through an invitation from the group creator.</div>\n";
            echo "Created date: " . showDate($created) . "<br>\n";
            echo "Suspended date: \n";
            if ($suspended == 0) {
                echo "Suspend - <input type='checkbox' name='suspend' value='1'>\n";
                echo "<input type='hidden' name='unsuspend' value='0'>\n";
            } else {
                echo "<input type='hidden' name='suspend' value='0'>\n";
                echo showDate($suspended) .
                        " Unsuspend - <input type='checkbox' name='unsuspend' value='1'>\n";
            }
            echo "<br>\n";
            echo "Closed date: \n";
            if ($closed == 0) {
                echo "Close - <input type='checkbox' name='close' value='1'>\n";
                echo "<input type='hidden' name='unclose' value='0'>\n";
            } else {
                echo "<input type='hidden' name='close' value='0'>\n";
                echo showDate($closed) .
                        " Unclose - <input type='checkbox' name='unclose' value='1'>\n";
            }
            echo "<br>\n";
            echo "<input type='submit' value=' Update Group Info '><input type='hidden' name='groupUp' value='$id'>\n";
            echo "</form></div>\n";
            echo "<div style='text-align:left; font-weight:bold; cursor:pointer; margin-top:20px;' onclick='toggleview(\"members$id\")'>Members</div>\n";
            echo "<div id='members$id' style='display:none; margin:-left:20px;'>\n";
            echo "<div style='margin:20px 0px;'>\n";
            echo "<span style='font-weight:bold'>Invite new members to this group:</span>\n";
            echo "<div style='margin:20px 0px;'><span style='font-weight:bold'>Invites already sent for this group:</span><br>\n";
            $gi = $db->prepare(
                    "SELECT * FROM invites WHERE inviteGroup = ? ORDER BY inviteDate");
            $gi->execute(array(
                    $id
            ));
            while ($giR = $gi->fetch()) {
                if ($giR) {
                    $giName = $giR['name'];
                    $giEmail = $giR['email'];
                    $giDate = showDate($giR['inviteDate']);
                    echo "$giName - $giEmail - $giDate<br>";
                }
            }
            echo "</div><form action='index.php?page=settings&gid=$id' method='post'>\n";
            echo "<table cellspacing='0px' style='border:1px solid black;'>\n";
            echo "<tr><td style='border:1px solid black;'>Name</td><td style='border:1px solid black;'>Email</td></tr>\n";
            for ($i = 1; $i <= 5; ++ $i) {
                echo "<tr><td style='border:1px solid black;'><input type='text' name='name$i' value=''></td><td style='border:1px solid black;'><input type='email' name='email$i' value=''></td></tr>\n";
            }
            echo "<tr><td style='border:1px solid black;' colspan='2'>Text to include in the email:<br><textarea name='emailBody'></textarea></td></tr>\n";
            echo "<tr><td style='border:1px solid black;' colspan='2'><input type='submit' value=' Send Invites '><input type='hidden' name='invites' value='1'></td></tr>\n";
            echo "</table></form></div>\n";
            echo "<table cellspacing='0px'>\n";
            echo "<tr><td style='text-align:center; font-weight:bold; width:50%;'>Active Members</td><td style='text-align:center; font-weight:bold; width:50%;'>Blocked Members</td></tr>\n";
            echo "<tr><td style='text-align:left; width:50%;'>\n";
            $am1 = $db->prepare(
                    "SELECT userId FROM groupMembership WHERE groupId = ? AND blocked = ?");
            $am1->execute(array(
                    $id,
                    '0'
            ));
            while ($am1R = $am1->fetch()) {
                if ($am1R) {
                    $mUserId = $am1R['userId'];
                    if ($myId == $mUserId) {
                        echo "<div style=''>$MYNAME</div>\n";
                    } else {
                        $gu = $db->prepare(
                                "SELECT name FROM users WHERE id = ?");
                        $gu->execute(array(
                                $mUserId
                        ));
                        $guR = $gu->fetch();
                        if ($guR) {
                            $mUserName = $guR['name'];
                        }
                        echo "<div style=''><form action='index.php?page=settings' method='post'>$mUserName - <input type='submit' value=' Block this member '><input type='hidden' name='block' value='$mUserId'><input type='hidden' name='groupId' value='$id'></form></div>\n";
                    }
                }
            }
            echo "</td><td style='text-align:left; width:50%;'>\n";
            $am2 = $db->prepare(
                    "SELECT id, userId FROM groupMembership WHERE groupId = ? AND blocked = ?");
            $am2->execute(array(
                    $id,
                    '1'
            ));
            while ($am2R = $am2->fetch()) {
                if ($am2R) {
                    $mUserId = $am2R['userId'];
                    $gu = $db->prepare("SELECT name FROM users WHERE id = ?");
                    $gu->execute(array(
                            $mUserId
                    ));
                    $guR = $gu->fetch();
                    if ($guR) {
                        $mUserName = $guR['name'];
                    }
                    echo "<div style=''><form action='index.php?page=settings' method='post'>$mUserName - <input type='submit' value=' Unblock this member '><input type='hidden' name='unblock' value='$mUserId'><input type='hidden' name='groupId' value='$id'></form></div>\n";
                }
            }
            echo "</td></tr></table></div>\n";
            echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
            echo "<div style='text-align:left; font-weight:bold; font-size:1em; padding-top:20px;'>Group Projects</div>\n";
            echo "<div style='text-align:left; font-size:1em; padding-top:10px;'>New Project</div>\n";
            echo "<form action='index.php?page=settings' method='post'>\n";
            echo "<label for='title'>Title</label>\n";
            echo "<input type='text' name='title' value=''><br>\n";
            echo "<label for='desc'>Description</label>\n";
            echo "<textarea name='desc'></textarea><br>\n";
            echo "<label for='open'>Open Date</label>\n";
            echo "<input type='date' name='open' value='" . showDate($time) .
                    "'><br>\n";
            echo "<label for='close'>Close Date</label>\n";
            echo "<input type='date' name='close' value='" .
                    showDate($time + 1209600) . "'><br>\n";
            echo "<input type='submit' value=' Create Project '><input type='hidden' name='projectUp' value='new'><input type='hidden' name='groupId' value='$id'></form>\n";
            echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
            $getP = $db->prepare(
                    "SELECT * FROM projects WHERE groupId = ? ORDER BY openDate DESC");
            $getP->execute(array(
                    $id
            ));
            while ($getPR = $getP->fetch()) {
                if ($getPR) {
                    $pId = $getPR['id'];
                    $pOpenDate = $getPR['openDate'];
                    $pCloseDate = $getPR['closeDate'];
                    $pTitle = html_entity_decode($getPR['title'], ENT_QUOTES);
                    $pDesc = html_entity_decode($getPR['description'],
                            ENT_QUOTES);
                    echo "<div style='text-align:left; font-size:1em; padding-top:10px;'><a href='index.php?page=project&projectId=$pId'>$pTitle</a></div>\n";
                    echo "<form action='index.php?page=settings' method='post'>\n";
                    echo "<label for='title'>Title</label>\n";
                    echo "<input type='text' name='title' value='$pTitle'><br>\n";
                    echo "<label for='desc'>Description</label>\n";
                    echo "<textarea name='desc'>$pDesc</textarea><br>\n";
                    echo "<label for='open'>Open Date</label>\n";
                    echo "<input type='date' name='open' value='" .
                            showDate($pOpenDate) . "'><br>\n";
                    echo "<label for='close'>Close Date</label>\n";
                    echo "<input type='date' name='close' value='" .
                            showDate($pCloseDate) . "'><br>\n";
                    echo "<input type='submit' value=' Update Project '><input type='hidden' name='projectUp' value='$pId'></form>\n";
                    echo "<div style='text-align:center; margin:20px 0px;''><hr style='width:50%; color:#11bd4a;'></div>\n";
                }
            }
            echo "</div>\n";
        }
    }
}
?>