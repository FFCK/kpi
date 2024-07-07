<?php
function getIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$data = file_get_contents('php://input');
file_put_contents('performance_log.txt', 
    date('Y-m-d H:i:s') 
    . " - "
    . getIp()
    . " - "
    . $data 
    . "\n", FILE_APPEND);
echo json_encode(['status' => 'success']);