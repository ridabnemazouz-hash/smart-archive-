<?php
// Simulate a logged in session for testing
session_start();
$_SESSION['user_id'] = 1;

$_POST['message'] = 'salam';
require_once 'api/ai_chat.php';
?>
