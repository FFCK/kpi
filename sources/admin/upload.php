<?php
// $ds = DIRECTORY_SEPARATOR;  //1
// if($_POST['dest'])
// 	$dest = $ds.$_POST['dest'];
// if($_POST['titre'])
// 	$titre = $_POST['titre'];
// else
// 	$titre = $_FILES['file']['name'];
// $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
// $extension_upload = strtolower(  substr(  strrchr($titre, '.')  ,1)  );
// $storeFolder = '..'.$ds.'img'.$dest;   //2
// if (!empty($_FILES) && in_array($extension_upload,$extensions_valides)) {
// 	$tempFile = $_FILES['file']['tmp_name'];          //3             
// 	$targetPath = dirname( __FILE__ ) . $ds . $storeFolder . $ds;  //4
// 	$targetFile =  $targetPath. $titre;  //5
// 	move_uploaded_file($tempFile,$targetFile); //6
// }
// else
// 	echo 'Erreur !';
// ?>