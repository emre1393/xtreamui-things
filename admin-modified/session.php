<?php
$rSessionTimeout = 60;      // Session Timeout in Minutes

session_start();
if (isset($_SESSION['hash']) && isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > ($rSessionTimeout*60)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION["last_activity"] = time();

if (!isset($_SESSION['hash'])) { header("Location: ./login.php?referrer=".$_SERVER['REQUEST_URI']); exit; }
?>