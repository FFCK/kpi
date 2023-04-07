<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();

$equipe = (int) utyGetPost('equipe', 0);

if (
  utyGetSession('Profile', false) > 2 ||
  utyGetSession('AuthModif', false) !== 'O' ||
  $equipe <= 0
) {
  return_401();
}

$sql = "SELECT Numero, Code_club
  FROM kp_competition_equipe 
  WHERE Id = ? ";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute(array($equipe));
$row = $stmt->fetch();
$club = $row['Code_club'];
$numero = $row['Numero'];

$logo = utyGetPost('logo');
if (strlen($logo) <= 4) {
  $logo = utySearchLogoFile($club);
}

$color1 = utyGetPost('color1', null);
$color2 = utyGetPost('color2', $color1);
$colortext = utyGetPost('colortext', 'black');
$colorChangeNext = filter_var(utyGetPost('colorChangeNext', false), FILTER_VALIDATE_BOOLEAN);
$colorChangeLast = filter_var(utyGetPost('colorChangeLast', false), FILTER_VALIDATE_BOOLEAN);
$colorChangeClub = filter_var(utyGetPost('colorChangeClub', false), FILTER_VALIDATE_BOOLEAN);

if ($color1 && $color2) {
  $sql = "UPDATE kp_competition_equipe
    SET logo = :logo,
      color1 = :color1, 
      color2 = :color2,
      colortext = :colortext
    WHERE Id = :equipe ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute(array(
    ':logo' => $logo,
    ':color1' => $color1,
    ':color2' => $color2,
    ':colortext' => $colortext,
    ':equipe' => $equipe,
  ));

  if ($colorChangeNext) {
    $sql = "UPDATE kp_equipe e
      LEFT JOIN kp_competition_equipe ce ON e.Numero = ce.Numero
      SET e.color1 = :color1, 
        e.color2 = :color2,
        e.colortext = :colortext
      WHERE ce.Numero = :numero ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute(array(
      ':color1' => $color1,
      ':color2' => $color2,
      ':colortext' => $colortext,
      ':numero' => $numero,
    ));
  }

  if ($colorChangeLast) {
    $sql = "UPDATE kp_competition_equipe
      SET color1 = :color1, 
        color2 = :color2,
        colortext = :colortext
      WHERE Numero = :numero ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute(array(
      ':color1' => $color1,
      ':color2' => $color2,
      ':colortext' => $colortext,
      ':numero' => $numero,
    ));
  }

  if ($colorChangeClub) {
    $sql = "UPDATE kp_competition_equipe
      SET color1 = :color1, 
        color2 = :color2,
        colortext = :colortext
      WHERE Code_club = :club ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute(array(
      ':color1' => $color1,
      ':color2' => $color2,
      ':colortext' => $colortext,
      ':club' => $club,
    ));
    $sql = "UPDATE kp_equipe
      SET color1 = :color1, 
        color2 = :color2,
        colortext = :colortext
      WHERE Code_club = :club ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute(array(
      ':color1' => $color1,
      ':color2' => $color2,
      ':colortext' => $colortext,
      ':club' => $club,
    ));
  }
} else {
  $sql = "UPDATE kp_competition_equipe
    SET logo = :logo,
    WHERE Id = :equipe ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute(array(
    ':logo' => $logo,
    ':equipe' => $equipe,
  ));
}

return_200();
