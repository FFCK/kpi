<?php
if(!isset($_SESSION)) {
	session_start(); 
}

$_SESSION['mirror'] = '1';
require('./Classement.php');
