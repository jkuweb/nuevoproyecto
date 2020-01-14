<?php
require_once 'config.php';
require_once 'core/helper.php';

array_walk_recursive($_POST, "Helper::sanear");
ini_set("include_path", $_SERVER['DOCUMENT_ROOT']);
session_start();
?>
