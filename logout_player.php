<?php
session_start();
session_unset();
session_destroy();

// Redirect back to the login page
header("Location: index.html");
exit();
?>
