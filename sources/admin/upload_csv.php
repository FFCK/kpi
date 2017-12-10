<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();
$sql = utyGetSession('sql_query');

// Export to CSV
if(utyGetGet('action') == 'export') {
    $myBdd = new MyBdd();

    $result = $myBdd->Query($sql);
    $columns = $myBdd->NumFields($result);
    $out = '';
    $headers = array();
    
    for ($i = 0; $i < $columns; $i++) {     
        $headers[] = $myBdd->FieldName($result , $i); 
    } 
    $fp = fopen('php://output', 'w'); 
    if ($fp && $result) {     
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export.csv"');
        header('Pragma: no-cache');    
        header('Expires: 0');
        fputcsv($fp, $headers); 
        while ($row = $myBdd->FetchRow($result)) {
           fputcsv($fp, array_values($row)); 
        }
        die; 
    }     
}