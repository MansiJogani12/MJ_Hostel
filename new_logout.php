<?php
session_start();
session_destroy();
header('Location: new_login.html?message=' . urlencode('You have been logged out successfully'));
exit;
?>