<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Classements

class Schema extends MyPageSecure
{
    function Load()
    {
        $myBdd = new MyBdd();

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetSession('lang') == 'en') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        $codeCompet = utyGetSession('codeCompet', 'N1H');
        $codeCompet = utyGetPost('codeCompet', $codeCompet);
        $codeCompet = utyGetGet('Compet', $codeCompet);
        $this->m_tpl->assign('codeCompet', $codeCompet);

        $codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetPost('saisonTravail', $codeSaison);
        $codeSaison = utyGetGet('Saison', $codeSaison);
        $this->m_tpl->assign('Saison', $codeSaison);

        $idSelJournee = utyGetGet('J', -1);
        $this->m_tpl->assign('idSelJournee', $idSelJournee);

        $event = utyGetGet('event', '0');
        $this->m_tpl->assign('event', $event);
        if ($event > 0) {
            $eventTitle = $myBdd->GetEvenementLibelle($event);
            $this->m_tpl->assign('eventTitle', $eventTitle);
        }

        $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
        $this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
        $this->m_tpl->assign('navGroup', 1);

        if (isset($arrayNavGroup[0]['Code_ref'])) {
            $group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
            $this->m_tpl->assign('group', $group);
        }

        if ($event > 0 && $codeCompet == '*') {
            $codeCompet = $arrayNavGroup[0]['Code'];
            $this->m_tpl->assign('codeCompet', $codeCompet);
        }

        $Round = utyGetGet('Round', '*');
        $this->m_tpl->assign('Round', $Round);
        $Round = str_replace('*', '%', $Round);

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);

        //Logos
        if ($codeCompet != -1) {
            $this->m_tpl->assign('visuels', utyGetVisuels($recordCompetition));
        }

        // Chargement des Equipes ...
        $arrayEquipe = array();
        $arrayEquipe_journee = array();
        $arrayEquipe_journee_publi = array();
        $arrayEquipe_publi = array();
        $arrayJournee = array();
        $arrayMatchs = array();

        // Par défaut type Championnat et compétition non internationale...
        $typeClt = $recordCompetition['Code_typeclt'];

        $journee = 0;

        if (strlen($codeCompet) > 0) {
            // Classement public				
            $sql = "SELECT ce.*, c.Code_comite_dep 
                FROM kp_competition_equipe ce, kp_club c 
                WHERE ce.Code_compet = ? 
                AND ce.Code_saison = ? 
                AND ce.Code_club = c.Code ";
            if ($typeClt == 'CP') {
                $sql .= "ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";
            } else {
                $sql .= "ORDER BY Clt_publi Asc, Diff_publi Desc ";
            }
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeCompet, $codeSaison));
            while ($row = $result->fetch()) {
                //Logos
                $logo = '';
                $club = $row['Code_club'];
                if (is_file('img/KIP/logo/' . $club . '-logo.png')) {
                    $logo = 'img/KIP/logo/' . $club . '-logo.png';
                } elseif (is_file('img/Nations/' . substr($club, 0, 3) . '.png')) {
                    $club = substr($club, 0, 3);
                    $logo = 'img/Nations/' . $club . '.png';
                }
                if (strlen($row['Code_comite_dep']) > 3) {
                    $row['Code_comite_dep'] = 'FRA';
                }
                array_push($arrayEquipe_publi, array(
                    'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'],
                    'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
                    'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'],
                    'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'],
                    'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'],
                    'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
                    'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'],
                    'logo' => $logo, 'club' => $club
                ));
                if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0)) {
                    $recordCompetition['Qualifies']    = 0;
                    $recordCompetition['Elimines'] = 0;
                }
            }
            $this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);

            // Journées
            $etapes = 0;
            if ($event > 0) {
                $sql = "SELECT j.Id Id_journee, j.Phase, j.Etape, j.Nbequipes, j.Niveau, 
                    j.Type, j.Date_debut, j.Date_fin, j.Lieu, j.Departement 
                    FROM kp_journee j, kp_evenement_journee ej 
                    WHERE ej.Id_journee = j.Id 
                    AND ej.Id_evenement = ? 
                    AND j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ? 
                    AND j.Phase != 'Break'
                    AND j.Phase != 'Pause'
                    ORDER BY j.Etape, j.Niveau DESC, j.Date_debut DESC, j.Phase ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($event, $codeCompet, $codeSaison, $Round));
            } else {
                $arrayQuery = [];
                $sql = "SELECT j.Id Id_journee, j.Phase, j.Etape, j.Nbequipes, j.Niveau, 
                    j.Type, j.Date_debut, j.Date_fin, j.Lieu, j.Departement 
                    FROM kp_journee j 
                    WHERE j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ?
                    AND j.Phase != 'Break'
                    AND j.Phase != 'Pause' ";
                if ($idSelJournee > 0) {
                    $sql .= "AND j.Id = ? ";
                    $arrayQuery = [$idSelJournee];
                }
                $sql .= "ORDER BY j.Etape, j.Niveau DESC, j.Date_debut DESC, j.Phase ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge([$codeCompet], [$codeSaison], [$Round], $arrayQuery));
            }
            while ($row = $result->fetch()) {
                $arrayJournees[$row['Id_journee']] = $row;
                $arrayJournees[$row['Id_journee']]['Actif'] = 0;
                $arrayListJournees[] = $row['Id_journee'];
                if ($row['Etape'] > $etapes) {
                    $etapes = $row['Etape'];
                }
            }

            // Classement public par journée/phase
            if ($event > 0) {
                $sql = "SELECT ce.Id, ce.Numero, ce.Libelle, ce.Code_club, cej.Id_journee, 
                    cej.Clt_publi, cej.Pts_publi, cej.J_publi, cej.G_publi, cej.N_publi, 
                    cej.P_publi, cej.F_publi, cej.Plus_publi, cej.Moins_publi, cej.Diff_publi, 
                    cej.PtsNiveau_publi, cej.CltNiveau_publi, j.Phase, j.Etape, j.Nbequipes, 
                    j.Niveau, j.Type, c.Code_comite_dep, j.Date_debut, j.Date_fin, j.Lieu, 
                    j.Departement 
                    FROM kp_competition_equipe ce, kp_competition_equipe_journee cej, 
                    kp_journee j, kp_evenement_journee ej, kp_club c 
                    WHERE ej.Id_journee = j.Id 
                    AND ce.Id = cej.Id 
                    AND cej.Id_journee = j.Id 
                    AND ce.Code_club = c.Code 
                    AND ej.Id_evenement = ? 
                    AND j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ? 
                    ORDER BY j.Etape, j.Niveau DESC, j.Date_debut DESC, j.Phase, 
                    cej.Clt_publi ASC, cej.Diff_publi DESC, cej.Plus_publi ASC ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($event, $codeCompet, $codeSaison, $Round));
            } else {
                $arrayQuery = [];
                $sql = "SELECT ce.Id, ce.Numero, ce.Libelle, ce.Code_club, cej.Id_journee, 
                    cej.Clt_publi, cej.Pts_publi, cej.J_publi, cej.G_publi, cej.N_publi, 
                    cej.P_publi, cej.F_publi, cej.Plus_publi, cej.Moins_publi, cej.Diff_publi, 
                    cej.PtsNiveau_publi, cej.CltNiveau_publi, j.Phase, j.Etape, j.Nbequipes, 
                    j.Niveau, j.Type, c.Code_comite_dep, j.Date_debut, j.Date_fin, j.Lieu, 
                    j.Departement 
                    FROM kp_competition_equipe ce, kp_competition_equipe_journee cej, 
                    kp_journee j, kp_club c 
                    WHERE ce.Id = cej.Id 
                    AND cej.Id_journee = j.Id 
                    AND ce.Code_club = c.Code 
                    AND j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ? ";
                if ($idSelJournee > 0) {
                    $sql .= "AND j.Id = $idSelJournee ";
                    $arrayQuery = [$idSelJournee];
                }
                $sql .= "ORDER BY j.Etape, j.Niveau DESC, j.Date_debut DESC, j.Phase, 
                    cej.Clt_publi ASC, cej.Diff_publi DESC, cej.Plus_publi ASC ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge([$codeCompet], [$codeSaison], [$Round], $arrayQuery));
            }
            while ($row = $result->fetch()) {
                $arrayJournees[$row['Id_journee']]['Actif'] = 1;
                if (strlen($row['Code_comite_dep']) > 3) {
                    $row['Code_comite_dep'] = 'FRA';
                }
                if ($journee != $row['Id_journee']) {
                    $arrayJournee[] = array(
                        'Id_journee' => $row['Id_journee'], 'Phase' => $row['Phase'], 'Etape' => $row['Etape'],
                        'Nbequipes' => $row['Nbequipes'], 'Niveau' => $row['Niveau'], 'Type' => $row['Type'],
                        'Date_debut' => $row['Date_debut'], 'Date_fin' => $row['Date_fin'],
                        'Lieu' => $row['Lieu'], 'Departement' => $row['Departement']
                    );
                    $journee = $row['Id_journee'];
                }
                $arrayEquipe_journee_publi[$journee][] = array(
                    'Id' => $row['Id'], 'Numero' => $row['Numero'],
                    'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Id_journee' => $row['Id_journee'],
                    'Phase' => $row['Phase'], 'Etape' => $row['Etape'], 'Nbequipes' => $row['Nbequipes'], 'Niveau' => $row['Niveau'],
                    'Type' => $row['Type'], 'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'],
                    'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'],
                    'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'],
                    'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
                    'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'],
                    'Code_comite_dep' => $row['Code_comite_dep']
                );
            }

            // Matchs publics par journée / phase
            if ($event > 0) {
                $sql = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match, 
                    m.Libelle, m.Terrain, m.Publication, m.Validation, m.Statut, m.Periode, 
                    m.ScoreDetailA, m.ScoreDetailB, m.Id_equipeA, m.Id_equipeB, ce1.Libelle EquipeA, 
                    ce2.Libelle EquipeB, ce1.Numero NumA, ce2.Numero NumB, m.Terrain, m.ScoreA, 
                    m.ScoreB, m.CoeffA, m.CoeffB, m.Arbitre_principal, m.Arbitre_secondaire, 
                    m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, j.Code_competition, 
                    j.Phase, j.Niveau, j.Lieu, j.Libelle LibelleJournee, j.Date_debut 
                    FROM kp_journee j, kp_evenement_journee ej, kp_match m 
                    LEFT OUTER JOIN kp_competition_equipe ce1 ON (m.Id_equipeA = ce1.Id) 
                    LEFT OUTER JOIN kp_competition_equipe ce2 ON (m.Id_equipeB = ce2.Id) 
                    WHERE ej.Id_journee = j.Id 
                    AND m.Id_journee = j.Id 
                    AND ej.Id_evenement = ? 
                    AND j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ? 
                    ORDER BY j.Etape, j.Niveau DESC, j.Id ASC ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($event, $codeCompet, $codeSaison, $Round));
            } else {
                $sql = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match, 
                    m.Libelle, m.Terrain, m.Publication, m.Validation, m.Statut, m.Periode, 
                    m.ScoreDetailA, m.ScoreDetailB, m.Id_equipeA, m.Id_equipeB, ce1.Libelle EquipeA, 
                    ce2.Libelle EquipeB, ce1.Numero NumA, ce2.Numero NumB, m.Terrain, m.ScoreA, 
                    m.ScoreB, m.CoeffA, m.CoeffB, m.Arbitre_principal, m.Arbitre_secondaire, 
                    m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, j.Code_competition, 
                    j.Phase, j.Niveau, j.Lieu, j.Libelle LibelleJournee, j.Date_debut 
                    FROM kp_journee j, kp_match m 
                    LEFT OUTER JOIN kp_competition_equipe ce1 ON (m.Id_equipeA = ce1.Id) 
                    LEFT OUTER JOIN kp_competition_equipe ce2 ON (m.Id_equipeB = ce2.Id) 
                    WHERE m.Id_journee = j.Id 
                    AND j.Code_competition = ? 
                    AND j.Code_saison = ? 
                    AND j.Etape LIKE ? 
                    ORDER BY j.Etape, j.Niveau DESC, j.Id ASC ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($codeCompet, $codeSaison, $Round));
            }
            while ($row = $result->fetch()) {
                $journee = $row['Id_journee'];
                if ($row['Validation'] != 'O') {
                    $row['ScoreA'] = '';
                    $row['ScoreB'] = '';
                }
                if ($row['Id_equipeA'] <= 1 || $row['Id_equipeB'] <= 1) {
                    if ($_SESSION['lang'] == 'en') {
                        $intitule = utyEquipesAffectAuto($row['Libelle']);
                    } else {
                        $intitule = utyEquipesAffectAutoFR($row['Libelle']);
                    }
                }
                if (isset($intitule[0]) && $row['Id_equipeA'] <= 1) {
                    $row['EquipeA'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[0]));
                }
                if (isset($intitule[1]) && $row['Id_equipeB'] <= 1) {
                    $row['EquipeB'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[1]));
                }
                $arrayMatchs[$journee][] = $row;
            }

            // Equipes par poules
            $sql = "SELECT j.Id, m.Id_equipeA, m.Id_equipeB, m.Libelle, ce1.Libelle EquipeA, 
                ce2.Libelle EquipeB, ce1.Numero NumA, ce2.Numero NumB, 
                ce1.Tirage TirageA, ce2.Tirage TirageB 
                FROM kp_journee j, kp_match m 
                LEFT OUTER JOIN kp_competition_equipe ce1 ON (m.Id_equipeA = ce1.Id) 
                LEFT OUTER JOIN kp_competition_equipe ce2 ON (m.Id_equipeB = ce2.Id) 
                WHERE m.Id_journee = j.Id 
                AND j.Type = 'C' 
                AND j.Code_competition = ? 
                AND j.Code_saison = ? 
                AND j.Etape LIKE ? 
                ORDER BY j.Etape, j.Niveau DESC, j.Id ASC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeCompet, $codeSaison, $Round));
            while ($row = $result->fetch()) {
                $journee = $row['Id'];
                if ($row['Id_equipeA'] <= 1 || $row['Id_equipeB'] <= 1) {
                    if ($_SESSION['lang'] == 'en') {
                        $intitule = utyEquipesAffectAuto($row['Libelle']);
                    } else {
                        $intitule = utyEquipesAffectAutoFR($row['Libelle']);
                    }
                }
                if ($row['Id_equipeA'] > 1) {
                    $arrayEquipes[$journee][$row['EquipeA']] = array(
                        'Tirage' => $row['TirageA'], 'Id' => $row['Id_equipeA'],
                        'Libelle' => $row['EquipeA'], 'Num' => $row['NumA']
                    );
                } elseif (isset($intitule[0])) {
                    $row['EquipeA'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[0]));
                    $arrayEquipes[$journee][$row['EquipeA']] = array(
                        'Tirage' => $row['TirageA'], 'Id' => $row['Id_equipeA'],
                        'Libelle' => $row['EquipeA'], 'Num' => $row['NumA']
                    );
                }
                if ($row['Id_equipeB'] > 1) {
                    $arrayEquipes[$journee][$row['EquipeB']] = array(
                        'Tirage' => $row['TirageB'], 'Id' => $row['Id_equipeB'],
                        'Libelle' => $row['EquipeB'], 'Num' => $row['NumB']
                    );
                } elseif (isset($intitule[1])) {
                    $row['EquipeB'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[1]));
                    $arrayEquipes[$journee][$row['EquipeB']] = array(
                        'Tirage' => $row['TirageB'], 'Id' => $row['Id_equipeB'],
                        'Libelle' => $row['EquipeB'], 'Num' => $row['NumB']
                    );
                }
            }
        }

        $this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
        $this->m_tpl->assign('arrayJournees', $arrayJournees);
        $this->m_tpl->assign('arrayJournee', $arrayJournee);
        $this->m_tpl->assign('arrayListJournees', $arrayListJournees);
        $this->m_tpl->assign('arrayEquipes', $arrayEquipes);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);
        $this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
        $this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);
        $this->m_tpl->assign('etapes', $etapes);
        if ($etapes > 0) {
            $this->m_tpl->assign('largeur', round(12 / $etapes));
        } else {
            $this->m_tpl->assign('largeur', 12);
        }
        $this->m_tpl->assign('page', 'chart');
        $this->m_tpl->assign('skipheader', true);
        $this->m_tpl->assign('skipfooter', true);

        // Combo "CHPT" - "CP"		
        $arrayOrderCompetition = array();
        if ('CHPT' == $typeClt) {
            array_push($arrayOrderCompetition, array('CHPT', 'Championnat', 'SELECTED'));
        } else {
            array_push($arrayOrderCompetition, array('CHPT', 'Championnat', ''));
        }

        if ('CP' == $typeClt) {
            array_push($arrayOrderCompetition, array('CP', 'Coupe', 'SELECTED'));
        } else {
            array_push($arrayOrderCompetition, array('CP', 'Coupe', ''));
        }
        $this->m_tpl->assign('arrayOrderCompetition', $arrayOrderCompetition);
    }

    // GestionClassement 		
    function __construct()
    {
        MyPageSecure::MyPageSecure(10);

        $this->SetTemplate("Schema", "Journees_phases", false);
        $this->Load();
        //		$this->m_tpl->assign('AlertMessage', $alertMessage);
        $this->DisplayTemplateNew('GestionSchema');
    }
}

$page = new Schema();
