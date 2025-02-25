<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion Athlete

class GestionAthlete extends MyPageSecure
{
    function Load()
    {
        $myBdd = new MyBdd();

        $Athlete = utyGetSession('Athlete', '');
        $Athlete = utyGetPost('Athlete', $Athlete);
        $Athlete = utyGetGet('Athlete', $Athlete);
        $this->m_tpl->assign('Athlete', $Athlete);

        $codeSaison = $myBdd->GetActiveSaison();
        $SaisonAthlete = utyGetSession('SaisonAthlete', $codeSaison);
        $SaisonAthlete = utyGetPost('SaisonAthlete', $SaisonAthlete);
        $this->m_tpl->assign('SaisonAthlete', $SaisonAthlete);

        // Saisons	
        $arraySaison = array();
        $sql = "SELECT Code 
            FROM kp_saison 
            WHERE Code > '1900' 
            ORDER BY Code DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute();
        while ($row = $result->fetch()) {
            array_push($arraySaison, $row);
        }
        $this->m_tpl->assign('arraySaison', $arraySaison);

        // Chargement des Informations relatives à l'athlète
        if ($Athlete != '') {
            // Données générales
            $sql = "SELECT c.*, cl.Libelle nomclub, dep.Libelle nomcd, reg.Libelle nomcr, 
                s.Date date_surclassement 
                FROM kp_club cl, kp_cd dep, kp_cr reg, kp_licence c 
                LEFT OUTER JOIN kp_surclassement s 
                    ON (c.Matric = s.Matric AND s.Saison = ?) 
                WHERE c.Numero_club = cl.Code 
                AND c.Numero_comite_dept = dep.Code 
                AND c.Numero_comite_reg = reg.Code 
                AND c.Matric = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($SaisonAthlete, $Athlete));
            if ($result->rowCount() != 1) {
                return;
            }
            $row = $result->fetch();
            $row['Nom'] = mb_strtoupper($row['Nom']);
            $row['Prenom'] = mb_convert_case($row['Prenom'], MB_CASE_TITLE, "UTF-8");
            if ($_SESSION['lang'] == 'fr') {
                $row['date_surclassement'] = utyDateUsToFr($row['date_surclassement']);
            }
            $this->m_tpl->assign('Courreur', $row);
            $this->m_tpl->assign('Athlete_id', $row['Nom'] . ' ' . $row['Prenom']);
            // Arbitre
            $sql = "SELECT * FROM kp_arbitre 
                WHERE Matric = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Athlete));
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                switch ($row['arbitre']) {
                    case 'Int':
                        // $row['arbitre'] = 'INTERNATIONAL';
                        break;
                    case 'Nat':
                        // $row['arbitre'] = 'NATIONAL';
                        break;
                    case 'Reg':
                        // $row['arbitre'] = 'REGIONAL';
                        break;
                    case 'OTM':
                        $_SESSION['lang'] == 'en' ? $row['arbitre'] = 'Game official' : $row['arbitre'] = 'Officiel table de marque';
                        break;
                    case 'JO':
                        $_SESSION['lang'] == 'en' ? $row['arbitre'] = 'Young official' : $row['arbitre'] = 'Jeune officiel';
                        break;
                    default:
                        // $row['arbitre'] = '-';
                        break;
                }
                $this->m_tpl->assign('Arbitre', $row);
            }
            // Titulaire
            $Titulaire = array();
            $sql = "SELECT cej.*, ce.*, cej.Numero Num 
                FROM kp_competition_equipe_joueur cej, kp_competition_equipe ce 
                WHERE cej.Matric = ? 
                AND cej.Id_equipe = ce.Id 
                AND ce.Code_compet != 'POOL' 
                AND ce.Code_saison = ? 
                ORDER BY ce.Code_compet ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Athlete, $SaisonAthlete));
            while ($row = $result->fetch()) {
                array_push($Titulaire, $row);
            }
            $this->m_tpl->assign('Titulaire', $Titulaire);

            // Arbitrages
            $Arbitrages = array();
            $sql = "SELECT m.*, j.*, m.id Identifiant, 
                IF(m.Matric_arbitre_principal = :Athlete1,'Prin','') Prin, 
                IF(m.Matric_arbitre_secondaire = :Athlete2,'Sec','') Sec 
                FROM kp_match m, kp_journee j 
                WHERE (m.Matric_arbitre_principal = :Athlete3 
                    OR m.Matric_arbitre_secondaire = :Athlete4) 
                AND m.Id_journee = j.Id 
                AND j.Code_saison = :SaisonAthlete 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute([
                ':Athlete1' => $Athlete,
                ':Athlete2' => $Athlete,
                ':Athlete3' => $Athlete,
                ':Athlete4' => $Athlete,
                ':SaisonAthlete' => $SaisonAthlete
            ]);
            while ($row = $result->fetch()) {
                if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($Arbitrages, $row);
            }
            $this->m_tpl->assign('Arbitrages', $Arbitrages);

            // Table de marque
            $OTM = array();
            $sql = "SELECT m.*, j.*, m.id Identifiant, 
                IF(m.Secretaire LIKE :Athlete1,'Sec','') Sec, 
                IF(m.Chronometre LIKE :Athlete2,'Chrono','') Chrono, 
                IF(m.Timeshoot LIKE :Athlete3,'TS','') TS, 
                IF(m.Ligne1 LIKE :Athlete4 OR m.Ligne2 LIKE :Athlete5,'Ligne','') Ligne 
                FROM kp_match m, kp_journee j 
                WHERE (m.Secretaire LIKE :Athlete6 
                    OR m.Chronometre LIKE :Athlete7 
                    OR m.Timeshoot LIKE :Athlete8 
                    OR m.Ligne1 LIKE :Athlete9
                    OR m.Ligne2 LIKE :Athlete10 ) 
                AND m.Id_journee = j.Id 
                AND j.Code_saison = :SaisonAthlete 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute([
                ':Athlete1' => '%(' . $Athlete . ')%',
                ':Athlete2' => '%(' . $Athlete . ')%',
                ':Athlete3' => '%(' . $Athlete . ')%',
                ':Athlete4' => '%(' . $Athlete . ')%',
                ':Athlete5' => '%(' . $Athlete . ')%',
                ':Athlete6' => '%(' . $Athlete . ')%',
                ':Athlete7' => '%(' . $Athlete . ')%',
                ':Athlete8' => '%(' . $Athlete . ')%',
                ':Athlete9' => '%(' . $Athlete . ')%',
                ':Athlete10' => '%(' . $Athlete . ')%',
                ':SaisonAthlete' => $SaisonAthlete
            ]);
            while ($row = $result->fetch()) {
                if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($OTM, $row);
            }
            $this->m_tpl->assign('OTM', $OTM);

            // Joueur
            $Joueur = array();
            $sql = "SELECT mj.*, m.*, m.id Identifiant, j.*, mj.Numero Num, ceA.Libelle eqA, 
                ceB.Libelle eqB, 
                SUM(IF(b.Id_evt_match='B',1,0)) But, 
                SUM(IF(b.Id_evt_match='V',1,0)) Vert, 
                SUM(IF(b.Id_evt_match='J',1,0)) Jaune, 
                SUM(IF(b.Id_evt_match='R',1,0)) Rouge, 
                SUM(IF(b.Id_evt_match='D',1,0)) Rouge_definitif, 
                SUM(IF(b.Id_evt_match='T',1,0)) Tir, 
                SUM(IF(b.Id_evt_match='A',1,0)) Arret 
                FROM kp_match m, kp_journee j, kp_competition_equipe ceA, 
                    kp_competition_equipe ceB, kp_match_joueur mj 
                LEFT OUTER JOIN kp_match_detail b 
                    ON (mj.Matric = b.Competiteur AND mj.Id_match = b.Id_match) 
                WHERE mj.Matric = :Athlete 
                AND j.Code_saison = :SaisonAthlete 
                AND mj.Id_match = m.Id 
                AND m.Id_journee = j.Id 
                AND m.Id_equipeA = ceA.Id 
                AND m.Id_equipeB = ceB.Id 
                AND mj.Capitaine != 'X'
                GROUP BY m.Id 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(':Athlete' => $Athlete, ':SaisonAthlete' => $SaisonAthlete));
            while ($row = $result->fetch()) {
                if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($Joueur, $row);
            }
            $this->m_tpl->assign('Joueur', $Joueur);
        }
    }

    function Update()
    {
        $myBdd = new MyBdd();
        $update_matric = utyGetPost('update_matric');
        if ($update_matric < 2000000) {
            return 'Modification interdite !';
        }
        $update_nom = strtoupper(trim(utyGetPost('update_nom')));
        $update_prenom = strtoupper(trim(utyGetPost('update_prenom')));
        $update_sexe = trim(utyGetPost('update_sexe'));
        $update_naissance = utyDateFrToUs(trim(utyGetPost('update_naissance')));
        $update_saison = trim(utyGetPost('update_saison'));
        $update_icf = (int) trim(utyGetPost('update_icf'));
        $update_arb = trim(utyGetPost('update_arb'));
        $update_niveau = trim(utyGetPost('update_niveau'));
        $update_club = trim(utyGetPost('update_club'));
        $update_cd = trim(utyGetPost('update_cd'));
        $update_cr = trim(utyGetPost('update_cr'));


        try {
            $myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $myBdd->pdo->beginTransaction();

            $sql = "UPDATE kp_licence 
                SET Origine = ?, Nom = ?, 
                Prenom = ?, Sexe = ?, 
                Naissance = ? ";
            $arrayQuery = array(
                $update_saison, $update_nom, $update_prenom,
                $update_sexe, $update_naissance
            );

            if ($update_icf > 0) {
                $sql .= ", Reserve = ? ";
                $arrayQuery = array_merge($arrayQuery, [$update_icf]);
            } else {
                $sql .= ", Reserve = NULL ";
            }
            if ($update_club != '') {
                $sql .= ", Numero_club = ?, 
                Numero_comite_dept = ?, 
                Numero_comite_reg = ? ";
                $arrayQuery = array_merge($arrayQuery, [$update_club], [$update_cd], [$update_cr]);
            }
            $sql .= "WHERE Matric = ? ";
            $arrayQuery = array_merge($arrayQuery, [$update_matric]);
            $result = $myBdd->pdo->prepare($sql);
            $result->execute($arrayQuery);

            $sql = "UPDATE kp_competition_equipe_joueur 
                SET Nom = ?, Prenom = ?, 
                Sexe = ? 
                WHERE Matric = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($update_nom, $update_prenom, $update_sexe, $update_matric));

            $sql = "REPLACE INTO kp_arbitre VALUES (?, ";
            switch ($update_arb) {
                case 'Reg':
                    $sql .= "'O','N','N','N','Reg','',?,?) ";
                    break;
                case 'IR':
                    $sql .= "'N','O','N','N','IR','',?,?) ";
                    break;
                case 'Nat':
                    $sql .= "'N','N','O','N','Nat','',?,?) ";
                    break;
                case 'Int':
                    $sql .= "'N','N','O','O','Int','',?,?) ";
                    break;
                case 'OTM':
                    $sql .= "'N','N','O','N','OTM','',?,?) ";
                    break;
                case 'JO':
                    $sql .= "'N','N','O','N','JO','',?,?) ";
                    break;
                default:
                    $sql .= "'N','N','N','N','','',?,?) ";
                    $update_niveau = '';
                    $update_saison = '';
                    break;
            }

            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($update_matric, $update_niveau, $update_saison));

            $myBdd->pdo->commit();
        } catch (Exception $e) {
            $myBdd->pdo->rollBack();
            utySendMail("[KPI] Erreur SQL", 'Modification Joueur' . '\r\n' . $e->getMessage());

            return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
        }

        return "Modification effectuée !";
    }


    function __construct()
    {
        parent::__construct(7);

        $alertMessage = '';

        $Cmd = utyGetPost('Cmd', '');
        $ParamCmd = utyGetPost('ParamCmd', '');

        if (strlen($Cmd) > 0) {
            if ($Cmd == 'Update') {
                ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($alertMessage == '') {
                header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
                exit;
            }
        }

        $this->SetTemplate("Statistiques_athlete", "Athletes", false);
        $this->Load();
        $this->m_tpl->assign('AlertMessage', $alertMessage);

        $this->DisplayTemplate('GestionAthlete');
    }
}

$page = new GestionAthlete();
