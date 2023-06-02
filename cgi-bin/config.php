<?php
session_start();

include "../globalFunctions.php";

$db = db_twa();

$debugging = 0; // 1 for debug info showing, 0 for not showing

date_default_timezone_set('America/Chicago');
$time = time();
$domain = "thewritersarbor.com";

// *** Log out ***

if (filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_STRING) == 'yep') {
    destroySession();
    setcookie("staySignedIn", '', $time - 1209600, "/", $domain, 0);
}

// *** Sign in ***
$loginErr = "x";
if (filter_input(INPUT_POST, 'login', FILTER_SANITIZE_NUMBER_INT) == "1") {
    $email = (filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) ? strtolower(
            filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) : '0';
    $login1stmt = $db->prepare("SELECT id,salt FROM users WHERE email = ?");
    $login1stmt->execute(array(
            $email
    ));
    $login1row = $login1stmt->fetch();
    $salt = ($login1row) ? $login1row['salt'] : 000000;
    $checkId = (isset($login1row['id']) && $login1row['id'] > 0) ? $login1row['id'] : '0';
    $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING);
    $hidepwd = hash('sha512', ($salt . $pwd), FALSE);
    $login2stmt = $db->prepare(
            "SELECT id, name FROM users WHERE email = ? AND password = ?");
    $login2stmt->execute(array(
            $email,
            $hidepwd
    ));
    $login2row = $login2stmt->fetch();
    if ($login2row) {
        if ($login2row['id']) {
            $x = $login2row['id'];
            $_SESSION['myId'] = $x;
            setcookie("staySignedIn", $x, $time + 1209600, "/", $domain, 0); // set
                                                                             // for
                                                                             // 14
                                                                             // days
        } else {
            $loginErr = "Your email / password combination isn't correct.";
        }
    }
}

// *** User settings ***
$myId = (isset($_SESSION['myId']) && ($_SESSION['myId'] >= '1')) ? $_SESSION['myId'] : '0'; // are
                                                                                            // they
                                                                                            // logged
                                                                                            // in
if ($myId == '0' &&
        (empty(filter_input(INPUT_GET, 'logout', FILTER_SANITIZE_STRING)))) {
    $myId = (filter_input(INPUT_COOKIE, 'staySignedIn',
            FILTER_SANITIZE_NUMBER_INT) >= '1') ? filter_input(INPUT_COOKIE,
            'staySignedIn', FILTER_SANITIZE_NUMBER_INT) : '0'; // are they
                                                               // logged in
}

$checkId = $db->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
$checkId->execute(array(
        $myId
));
$checkIdR = $checkId->fetch();
$idCount = $checkIdR[0];
if ($idCount == 0) {
    destroySession();
    setcookie("staySignedIn", '', $time - 1209600, "/", $domain, 0);
    $myId = 0;
}

if ($myId != 0) {
    $lastUpdate = $db->prepare("UPDATE users SET lastLogin = ? WHERE id = ?");
    $lastUpdate->execute(array(
            $time,
            $myId
    ));
}

// *** page settings ***
$page = (filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING)) ? filter_input(
        INPUT_GET, 'page', FILTER_SANITIZE_STRING) : "home";
if (! file_exists("pages/" . $page . ".php")) {
    $page = "home";
}

$groupId = 0;
if ($page == "group" &&
        filter_input(INPUT_GET, 'groupId', FILTER_SANITIZE_NUMBER_INT) >= 1) {
    $groupId = filter_input(INPUT_GET, 'groupId', FILTER_SANITIZE_NUMBER_INT);
}

$WEEKDAYS = array(
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
);
$MONTHS = array(
        1 => "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
);

// Set up MY information
$myInfo = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$myInfo->execute(array(
        $myId
));
$myInfoR = $myInfo->fetch();
if ($myInfoR) {
    $MYNAME = html_entity_decode($myInfoR['name']);
    $MYEMAIL = $myInfoR['email'];
}

// Set up group categories
$CATEGORIES = array();
$getCats = $db->prepare("SELECT * FROM categories");
$getCats->execute();
while ($getCatsR = $getCats->fetch()) {
    if ($getCatsR) {
        $cat = html_entity_decode($getCatsR['catName'], ENT_QUOTES);
        $catId = $getCatsR['id'];
        $CATEGORIES[$catId] = $cat;
    }
}

$HEBREW = array(
        "b" => "&#1489;",
        "a" => "&#1488;",
        "h" => "&#1492;",
        "d" => "&#1491;",
        "g" => "&#1490;",
        "c" => "&#1495;",
        "z" => "&#1494;",
        "v" => "&#1493;",
        "y" => "&#1497;",
        "e" => "&#1496;",
        "l" => "&#1500;",
        "k" => "&#1499;",
        "n" => "&#1504;",
        "m" => "&#1502;",
        "i" => "&#1506;",
        "s" => "&#1505;",
        "p" => "&#1508;",
        "q" => "&#1511;",
        "x" => "&#1510;",
        "r" => "&#1512;",
        "t" => "&#1514;",
        "w" => "&#1513;"
);