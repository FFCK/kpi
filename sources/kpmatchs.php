<?php
include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion d'une Journee

class Matchs extends MyPage
{
    function Load()
    {
        $myBdd = new MyBdd();
        $codeCompetGroup = utyGetSession('codeCompetGroup', 'N1H');
        $codeCompetGroup = utyGetPost('Group', $codeCompetGroup);
        $codeCompetGroup = utyGetGet('Group', $codeCompetGroup);
        $this->m_tpl->assign('codeCompetGroup', $codeCompetGroup);
        if ((!isset($_SESSION['codeCompetGroup']) or $codeCompetGroup != $_SESSION['codeCompetGroup'])
            and utyGetGet('Compet', '*') == '*'
        ) {
            $_GET['J'] = '*';
            $_GET['Compet'] = '*';
        }
        $_SESSION['codeCompetGroup'] = $codeCompetGroup;

        $codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetPost('Saison', $codeSaison);
        $codeSaison = utyGetGet('Saison', $codeSaison);
        if ($codeSaison != $_SESSION['Saison'] and utyGetGet('Compet', '*') == '*') {
            $_GET['J'] = '*';
            $_GET['Compet'] = '*';
        }
        $this->m_tpl->assign('Saison', $codeSaison);
        $_SESSION['Saison'] = $codeSaison;


        $idSelJournee = utyGetSession('idSelJournee', '*');
        $idSelJournee = utyGetPost('J', $idSelJournee);
        $idSelJournee = utyGetGet('J', $idSelJournee);
        $_SESSION['idSelJournee'] = $idSelJournee;
        $this->m_tpl->assign('idSelJournee', $idSelJournee);

        $Round = utyGetGet('Round', '*');
        $this->m_tpl->assign('Round', $Round);

        $codeCompet = utyGetSession('idSelCompet', '*');
        $codeCompet = utyGetPost('Compet', $codeCompet);
        $codeCompet = utyGetGet('Compet', $codeCompet);
        $_SESSION['idSelCompet'] = $codeCompet;
        $this->m_tpl->assign('codeCompet', $codeCompet);

        $filtreJour = utyGetGet('filtreJour', '');
        $_SESSION['filtreJour'] = $filtreJour;
        $this->m_tpl->assign('filtreJour', $filtreJour);

        $next = utyGetSession('next', '');
        $next = utyGetPost('next', $next);
        $next = utyGetGet('next', $next);
        $_SESSION['next'] = $next;
        $this->m_tpl->assign('next', $next);

        $private = utyGetSession('private', false);
        $private = utyGetGet('private', $private);
        $_SESSION['private'] = $private;

        $event = utyGetSession('event', 0);
        $event = utyGetPost('event', $event);
        $event = utyGetGet('event', $event);
        $this->m_tpl->assign('event', $event);
        if (!isset($_SESSION['event']) || $event != $_SESSION['event']) {
            $codeCompet = '*';
            $_SESSION['idSelCompet'] = $codeCompet;
            $this->m_tpl->assign('codeCompet', $codeCompet);
            $idSelJournee = '*';
            $_SESSION['idSelJournee'] = $idSelJournee;
            $this->m_tpl->assign('idSelJournee', $idSelJournee);
        }
        $_SESSION['event'] = $event;

        $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
        $this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
        $this->m_tpl->assign('navGroup', 1);

        if ($codeCompet == '*' || count($arrayNavGroup) == 1) {
            $codeCompet2 = @$arrayNavGroup[0]['Code'] ?: '*';
            if (count($arrayNavGroup) == 1) {
                $codeCompet = $arrayNavGroup[0]['Code'];
                $_SESSION['idSelCompet'] = $codeCompet;
                $this->m_tpl->assign('codeCompet', $codeCompet);
            }
        } else {
            $codeCompet2 = $codeCompet;
        }
        $this->m_tpl->assign('codeCompet2', $codeCompet2);

        $recordCompetition = $myBdd->GetCompetition($codeCompet2, $codeSaison);
        $this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);

        $this->m_tpl->assign('Css', utyGetGet('Css', ''));

        //Logos
        if ($codeCompet !== -1 && $codeCompet !== '*') {
            $this->m_tpl->assign('visuels', utyGetVisuels($recordCompetition));
        }

        // Chargement des Saisons ...
        $sql  = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin 
            FROM kp_saison 
            WHERE Code > '1900' 
            ORDER BY Code DESC";
        $arraySaison = array();
        $result = $myBdd->pdo->prepare($sql);
        $result->execute();
        while ($row = $result->fetch()) {
            array_push($arraySaison, array(
                'Code' => $row['Code'], 'Etat' => $row['Etat'],
                'Nat_debut' => utyDateUsToFr($row['Nat_debut']),
                'Nat_fin' => utyDateUsToFr($row['Nat_fin']),
                'Inter_debut' => utyDateUsToFr($row['Inter_debut']),
                'Inter_fin' => utyDateUsToFr($row['Inter_fin'])
            ));
        }

        $this->m_tpl->assign('arraySaison', $arraySaison);

        // Chargement des Evénements
        $arrayEvents = $myBdd->GetEvents(true, false);
        $this->m_tpl->assign('arrayEvents', $arrayEvents);
        if ($event > 0) {
            foreach ($arrayEvents as $key => $value) {
                if ($value['Id'] == $event) {
                    $eventTitle = $value['Libelle'];
                    $this->m_tpl->assign('eventTitle', $eventTitle);
                }
            }
        }

        // Chargement des Groupes
        $getGroups = $myBdd->GetGroups('public', $codeCompetGroup);
        $this->m_tpl->assign('arrayCompetitionGroupe', $getGroups);

        // Chargement des Compétitions ...
        $arrayCompetition = array();
        if (!$private) {
            $publi = " AND c.Publication = 'O' ";
        } else {
            $publi = "";
        }
        if ($event > 0) {
            $sql  = "SELECT c.* 
                FROM `kp_competition` c, `kp_journee` j, `kp_evenement_journee` ej 
                WHERE ej.Id_journee = j.Id 
                AND j.Code_competition = c.Code 
                AND j.Code_saison = c.Code_saison 
                AND ej.Id_evenement = ? 
                $publi 
                GROUP BY c.Code 
                ORDER BY c.Code_niveau, COALESCE(c.Code_ref, 'z'), 
                    c.GroupOrder, c.Code_tour, c.Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($event));
        } else {
            $sql  = "SELECT c.* 
                FROM kp_competition c 
                WHERE c.Code_saison = ? 
                $publi 
                AND c.Code_ref = ? 
                ORDER BY c.Code_niveau, COALESCE(c.Code_ref, 'z'), c.GroupOrder, c.Code_tour, c.Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeSaison, $codeCompetGroup));
        }
        $nbCompet = $result->rowCount();
        $listCompet = [];
        while ($row = $result->fetch()) {
            array_push($arrayCompetition, $row);
            if ($codeCompet == '*' || $codeCompet == $row["Code"]) {
                $listCompet[] = $row["Code"];
            }
        }
        if ($listCompet == []) { // compet != * mais pas trouvée dans la liste
            $codeCompet = '*';
            $_SESSION['idSelCompet'] = $codeCompet;
            $this->m_tpl->assign('codeCompet', $codeCompet);
            $codeCompet2 = @$arrayNavGroup[0]['Code'] ?: $codeCompet;
            $codeCompetGroup = '*';
            $this->m_tpl->assign('codeCompetGroup', $codeCompetGroup);
            $_SESSION['codeCompetGroup'] = $codeCompetGroup;
        }
        $this->m_tpl->assign('arrayCompetition', $arrayCompetition);
        $this->m_tpl->assign('nbCompet', $nbCompet);

        // Chargement des journées
        if (!$private) {
            $publi = " AND j.Publication = 'O' ";
        } else {
            $publi = "";
        }
        $arrayJournees = array();

        if (count($listCompet) > 0) {
            $in  = str_repeat('?,', count($listCompet) - 1) . '?';
            $sql  = "SELECT j.Id, j.Code_competition, j.Phase, j.Niveau, j.Libelle, 
                j.Lieu, j.Date_debut 
                FROM kp_journee j, kp_competition c 
                WHERE j.Code_competition IN ($in) 
                AND j.Code_saison = ? 
                AND j.Code_competition = c.Code 
                AND j.Code_saison = c.Code_saison 
                $publi 
                ORDER BY j.Code_competition, j.Date_debut, j.Lieu ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array_merge($listCompet, [$codeSaison]));
            $arrayListJournees = array();
            $selJournee = false;
            while ($row = $result->fetch()) {
                array_push($arrayListJournees, array(
                    'Id' => $row['Id'], 'Code_competition' => $row['Code_competition'],
                    'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'],
                    'Libelle' => $row['Libelle'], 'Lieu' => $row['Lieu'],
                    'Date_debut' => utyDateUsToFr($row['Date_debut']),
                    'Date_debut_en' => $row['Date_debut']
                ));
                if ($row['Id'] == $idSelJournee) {
                    $selJournee = true;
                }
            }
            $this->m_tpl->assign('arrayListJournees', $arrayListJournees);


            // Chargement des Informations relatives aux Journées ...
            if ($idSelJournee != '*' && $selJournee) {
                $sql  = "SELECT j.*, c.* 
                    FROM kp_journee j, kp_competition c 
                    WHERE j.Id = ? 
                    $publi ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($idSelJournee));
            } elseif ($event > 0) {
                $sql  = "SELECT j.Id, j.Code_competition, j.Phase, j.Niveau, j.Libelle, 
                    j.Lieu, j.Date_debut 
                    FROM kp_journee j, kp_evenement_journee ej 
                    WHERE ej.Id_evenement = ? 
                    AND j.Id = ej.Id_journee 
                    AND j.Code_competition IN ($in) 
                    $publi
                    ORDER BY j.Code_competition, j.Date_debut, j.Lieu ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge([$event], $listCompet));
            } else {
                $sql  = "SELECT j.Id, j.Code_competition, j.Phase, j.Niveau, j.Libelle, 
                    j.Lieu, j.Date_debut 
                    FROM kp_journee j, kp_competition c 
                    WHERE j.Code_competition In ($in) 
                    AND j.Code_saison = ? 
                    AND j.Code_competition = c.Code 
                    AND j.Code_saison = c.Code_saison 
                    $publi 
                    ORDER BY j.Code_competition, j.Date_debut, j.Lieu ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($listCompet, [$codeSaison]));
            }
            $lstJournee = '';
            while ($row = $result->fetch()) {
                array_push($arrayJournees, array(
                    'Id' => $row['Id'],
                    'Code_competition' => $row['Code_competition'],
                    'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'],
                    'Libelle' => $row['Libelle'], 'Lieu' => $row['Lieu'],
                    'Date_debut' => utyDateUsToFr($row['Date_debut']),
                ));
                if ($lstJournee) {
                    $lstJournee .= ',';
                }
                $lstJournee .= $row['Id'];
                $arrayInJournees[] = $row['Id'];
            }
            $_SESSION['lstJournee'] = $lstJournee;
        }
        if (@$lstJournee != '') {
            $selected = '';
            // Ordre des Matchs
            //$orderMatchs = utyGetSession('orderMatchs', 'Order By a.Date_match, a.Heure_match, a.Terrain');
            $orderMatchs = utyGetPost('orderMatchs', 'ORDER BY m.Date_match, m.Heure_match, m.Terrain');
            //$_SESSION['orderMatchs'] = $orderMatchs;

            $arrayOrderMatchs = array();

            //array_push($arrayOrderMatchs, array( 'Key' => 'Order By d.Date_debut, d.Niveau, d.Phase, d.Lieu, a.Id_journee, a.Date_match, a.Heure_match, a.Terrain', 'Value' => 'Par_Journee', 'Selected' => $selected ));
            array_push($arrayOrderMatchs, array('Key' => 'ORDER BY m.Date_match, m.Heure_match, m.Terrain', 'Value' => 'Par_Date_Heure_et_Terrain', 'Selected' => $selected));
            array_push($arrayOrderMatchs, array('Key' => 'ORDER BY m.Numero_ordre, m.Date_match, m.Heure_match, m.Terrain', 'Value' => 'Par_Numero'));
            array_push($arrayOrderMatchs, array('Key' => 'ORDER BY j.Code_competition, m.Date_match, m.Heure_match, m.Terrain', 'Value' => 'Par_Competition_et_Date'));
            array_push($arrayOrderMatchs, array('Key' => 'ORDER BY m.Terrain, m.Date_match, m.Heure_match', 'Value' => 'Par_Terrain_et_Date'));

            $this->m_tpl->assign('orderMatchs', $orderMatchs);
            $this->m_tpl->assign('arrayOrderMatchs', $arrayOrderMatchs);

            $orderMatchsKey1 = utyKeyOrder($orderMatchs, 0);
            $this->m_tpl->assign('orderMatchsKey1', $orderMatchsKey1);

            // Chargement des Matchs des journées ...
            $in  = str_repeat('?,', count($arrayInJournees) - 1) . '?';
            $sql  = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match, 
                    m.Libelle, m.Terrain, m.Publication, m.Validation, m.Statut, m.Periode, 
                    m.ScoreDetailA, m.ScoreDetailB, cea.Libelle EquipeA, ceb.Libelle EquipeB, 
                    cea.Numero NumA, ceb.Numero NumB, cea.Code_club clubA, ceb.Code_club clubB, 
                    m.Terrain, m.ScoreA, m.ScoreB, m.CoeffA, m.CoeffB, m.Arbitre_principal, 
                    m.Arbitre_secondaire, m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, 
                    j.Code_competition, j.Phase, j.Niveau, j.Lieu, j.Libelle LibelleJournee, 
                    j.Date_debut, c.Soustitre2, lcp.Nom Nom_arb_prin, lcp.Prenom Prenom_arb_prin, 
                    lcs.Nom Nom_arb_sec, lcs.Prenom Prenom_arb_sec 
                    FROM kp_match m 
                    LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id) 
                    LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id) 
                    LEFT OUTER JOIN kp_licence lcp ON (m.Matric_arbitre_principal = lcp.Matric) 
                    LEFT OUTER JOIN kp_licence lcs ON (m.Matric_arbitre_secondaire = lcs.Matric) 
                    INNER JOIN kp_journee j ON (m.Id_journee = j.Id) 
                    INNER JOIN kp_competition c ON (j.Code_competition = c.Code 
                        AND j.Code_saison = c.Code_saison) 
                    WHERE m.Id_journee IN ($in) ";
            if (!$private) {
                $sql .= "AND m.Publication='O' ";
            }
            if ($next == 'next') {
                $sql .= "AND (m.Date_match > '" . date('Y-m-d') . "' "
                    // -35 minutes & ajuster selon fuseau horaire
                    . "OR (m.Date_match = '" . date('Y-m-d') . "' AND m.Heure_match >= '" . date('H:i:s', strtotime(DECALAGE_MINUTES)) . "')) ";
            }
            if ($filtreJour != '') {
                $sql .= "AND m.Date_match = ? ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute($arrayInJournees, [$filtreJour]);
            } else {
                $result = $myBdd->pdo->prepare($sql);
                $result->execute($arrayInJournees);
            }

            $sql .= $orderMatchs;

            $dateDebut = '';
            $dateFin = '';
            $i = 0;
            $listMatch = '';
            $arrayMatchs = array();
            $PhaseLibelle = 0;
            while ($row = $result->fetch()) {
                if ($row['Soustitre2'] != '') {
                    $row['Categorie'] = $row['Soustitre2'];
                } else {
                    $row['Categorie'] = $row['Code_competition'];
                }

                if ($row['Libelle'] != '' && strpbrk($row['Libelle'], '[')) {
                    $libelle = explode(']', $row['Libelle']);
                    if ($_SESSION['lang'] == 'EN') {
                        $EquipesAffectAuto = utyEquipesAffectAuto($row['Libelle']);
                    } else {
                        $EquipesAffectAuto = utyEquipesAffectAutoFR($row['Libelle']);
                    }

                    if ($row['EquipeA'] == '' && isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '') {
                        $row['EquipeA'] = $EquipesAffectAuto[0];
                    }
                    if ($row['EquipeB'] == '' && isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '') {
                        $row['EquipeB'] = $EquipesAffectAuto[1];
                    }
                    if (count($libelle) > 1) {
                        $row['Libelle'] = $libelle[1];
                    }
                }
                if ($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1') {
                    $row['Arbitre_principal'] = utyArbSansNiveau(
                        utyInitialesPrenomArbitre(
                            $row['Arbitre_principal'],
                            $row['Nom_arb_prin'],
                            $row['Prenom_arb_prin'],
                            $row['Matric_arbitre_principal']
                        )
                    );
                } elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '') {
                    $row['Arbitre_principal'] = $EquipesAffectAuto[2];
                }
                if ($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1') {
                    $row['Arbitre_secondaire'] = utyArbSansNiveau(
                        utyInitialesPrenomArbitre(
                            $row['Arbitre_secondaire'],
                            $row['Nom_arb_sec'],
                            $row['Prenom_arb_sec'],
                            $row['Matric_arbitre_secondaire']
                        )
                    );
                } elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '') {
                    $row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
                }
                $Validation = 'O';
                if ($row['Validation'] != 'O') {
                    $Validation = 'N';
                }

                $MatchAutorisation = 'O';
                if (!utyIsAutorisationJournee($row['Id_journee'])) {
                    $MatchAutorisation = 'N';
                }

                if ($row['Date_match'] > date("Y-m-d")) {
                    $past = 'past';
                } else {
                    $past = '';
                }

                //Logos
                $logoA = '';
                $clubA = $row['clubA'];
                if (is_file('img/KIP/logo/' . $clubA . '-logo.png')) {
                    $logoA = 'img/KIP/logo/' . $clubA . '-logo.png';
                } elseif (is_file('img/Nations/' . substr($clubA, 0, 3) . '.png')) {
                    $clubA = substr($clubA, 0, 3);
                    $logoA = 'img/Nations/' . $clubA . '.png';
                } else {
                    $logoA = 'img/KIP/logo/empty-logo.png';
                }
                $logoB = '';
                $clubB = $row['clubB'];
                if (is_file('img/KIP/logo/' . $clubB . '-logo.png')) {
                    $logoB = 'img/KIP/logo/' . $clubB . '-logo.png';
                } elseif (is_file('img/Nations/' . substr($clubB, 0, 3) . '.png')) {
                    $clubB = substr($clubB, 0, 3);
                    $logoB = 'img/Nations/' . $clubB . '.png';
                } else {
                    $logoB = 'img/KIP/logo/empty-logo.png';
                }

                array_push($arrayMatchs, array(
                    'Id' => $row['Id'], 'Id_journee' => $row['Id_journee'], 'Numero_ordre' => $row['Numero_ordre'],
                    'Date_match' => utyDateUsToFr($row['Date_match']), 'Date_EN' => $row['Date_match'], 'Heure_match' => $row['Heure_match'],
                    'Libelle' => $row['Libelle'], 'Terrain' => $row['Terrain'],
                    'EquipeA' => $row['EquipeA'], 'EquipeB' => $row['EquipeB'],
                    'NumA' => $row['NumA'], 'NumB' => $row['NumB'],
                    'ScoreA' => $row['ScoreA'], 'ScoreB' => $row['ScoreB'],
                    'ScoreDetailA' => $row['ScoreDetailA'], 'ScoreDetailB' => $row['ScoreDetailB'],
                    'Statut' => $row['Statut'], 'Periode' => $row['Periode'],
                    'CoeffA' => $row['CoeffA'], 'CoeffB' => $row['CoeffB'],
                    'Arbitre_principal' => $row['Arbitre_principal'],
                    'Arbitre_secondaire' => $row['Arbitre_secondaire'],
                    'Matric_arbitre_principal' => $row['Matric_arbitre_principal'],
                    'Matric_arbitre_secondaire' => $row['Matric_arbitre_secondaire'],
                    'Code_competition' => $row['Code_competition'],
                    'Phase' => $row['Phase'],
                    'Niveau' => $row['Niveau'],
                    'Lieu' => $row['Lieu'],
                    'LibelleJournee' => $row['LibelleJournee'],
                    'Categorie' => $row['Categorie'],
                    'MatchAutorisation' => $MatchAutorisation,
                    //							'Publication' => $Publication,
                    'Validation' => $Validation,
                    'past' => $past,
                    'clubA' => $clubA,
                    'clubB' => $clubB,
                    'logoA' => $logoA,
                    'logoB' => $logoB
                ));

                if ($i != 0) {
                    $listMatch .= ',';
                }
                $listMatch .= $row['Id'];

                if ($row['Phase'] != '' || $row['Libelle'] != '') {
                    $PhaseLibelle = 1;
                }

                if ($i == 0) {
                    $dateDebut = utyDateUsToFr($row['Date_match']);
                    $dateFin = utyDateUsToFr($row['Date_match']);
                } else {
                    if (utyDateCmpFr($dateDebut, utyDateUsToFr($row['Date_match'])) > 0) {
                        $dateDebut = utyDateUsToFr($row['Date_match']);
                    }

                    if (utyDateCmpFr($dateFin, utyDateUsToFr($row['Date_match'])) < 0) {
                        $dateFin = utyDateUsToFr($row['Date_match']);
                    }
                }
            }
            $this->m_tpl->assign('listMatch', $listMatch);
            $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
            $this->m_tpl->assign('PhaseLibelle', $PhaseLibelle);

            $i++;
        }

        $this->m_tpl->assign('arrayJournees', $arrayJournees);
        $this->m_tpl->assign('page', 'Matchs');
    }


    function __construct()
    {
        parent::__construct();


        $this->SetTemplate("Matchs", "Matchs", true);
        $this->Load();

        $this->m_tpl->assign('idMatch', utyGetSession('idMatch', 0));
        $this->m_tpl->assign('idJournee', utyGetSession('idJournee', 0));

        $this->m_tpl->assign('Intervalle_match', utyGetSession('Intervalle_match', '40'));
        $this->m_tpl->assign('Num_match', utyGetSession('Num_match', ''));
        $this->m_tpl->assign('Date_match', utyGetSession('Date_match', ''));
        $this->m_tpl->assign('Heure_match', utyGetSession('Heure_match', ''));
        $this->m_tpl->assign('Libelle', utyGetSession('Libelle', ''));
        $this->m_tpl->assign('Terrain', utyGetSession('Terrain', ''));
        $this->m_tpl->assign('arbitre1', utyGetSession('arbitre1', ''));
        $this->m_tpl->assign('arbitre2', utyGetSession('arbitre2', ''));
        $this->m_tpl->assign('arbitre1_matric', utyGetSession('arbitre1_matric', ''));
        $this->m_tpl->assign('arbitre2_matric', utyGetSession('arbitre2_matric', ''));
        $this->m_tpl->assign('coeffA', utyGetSession('coeffA', 1));
        $this->m_tpl->assign('coeffB', utyGetSession('coeffB', 1));

        $this->DisplayTemplateNew('kpmatchs');
    }
}

$page = new Matchs();
