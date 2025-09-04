<?php
session_start();
session_destroy();

// Redirect to the landing page login
header("Location: login.php"); // If logout.php is in landingpage/, this works
exit();
?>
