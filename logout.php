<?php
/**
 * Página de Logout
 */

require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: index.php');
exit;

