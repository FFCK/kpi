<?php
include_once('../commun/MyTools.php');
session_start();
switch(utyGetPost('masquer', false)) {
    case 1:
        $_SESSION['masquer'] = 1;
        break;
    case 0:
        $_SESSION['masquer'] = 0;
        break;
}
return;