<?php
session_start();
switch($_POST['masquer']) {
    case 1:
        $_SESSION['masquer'] = 1;
        break;
    case 0:
        $_SESSION['masquer'] = 0;
        break;
}
return;