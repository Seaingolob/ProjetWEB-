<?php
session_start();
echo "Session path: " . session_save_path();
$_SESSION['test'] = "Hello";
echo "<br>Session test: " . $_SESSION['test'];
?>