<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    redirectBasedOnRole();
} else {
    header('Location: login.php');
    exit();
}
?> 