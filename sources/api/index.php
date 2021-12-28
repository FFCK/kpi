<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('config/headers.php');
include_once('config/router.php');

$method = $_SERVER['REQUEST_METHOD'];
$url = $_GET['url']; // After htaccess url rewrite
$path = explode('/', trim($url, '/'));

set_response_headers();

routing($path);
