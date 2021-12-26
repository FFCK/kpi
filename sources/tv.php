<?php
//include_once('base.php');
include_once('commun/MyParams.php');
include_once('commun/MyTools.php');
include_once('commun/MyBdd.php');

// include_once('live/page.php');

class TV
{
    var $m_arrayParams;        // Tableau des Paramètres

    // Constructeur ...
    function __construct(&$arrayParams)
    {
        $this->m_arrayParams = &$arrayParams;
        $this->Display();
    }


    function Header()
    {
    }
    function Footer()
    {
    }
    function Menu()
    {
    }

    function Head()
    {
?>

        <head>
            <title>KPI TV ()</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="author" content="F.F.C.K.">
            <meta name="Description" content="KAYAK POLO - LIVE" />
            <meta name="Keywords" content="kayak polo, ffck" />
            <meta name="rating" content="general">
            <meta name="Robots" content="all">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- CSS styles -->
            <link href="live/css/bootstrap.min.css" rel="stylesheet">
            <link href="live/css/tv2.css?v=<?= NUM_VERSION ?>" rel="stylesheet">

        </head>
    <?php
    }

    function Display()
    {
        $this->Html();
        $this->Head();
        echo "<body class='black'>\n";
        $this->Body();
        echo "</body>\n";
        echo "</html>";
    }

    // Tag HTML ...
    function Html()
    {
    ?>
        <!DOCTYPE html>
        <html lang="fr">
    <?php
    }

    // BODY ...
    function Body()
    {
        $this->Header();
        $this->Menu();
        $this->Content();
        $this->Footer();
        $this->Script();
    }


    function GetParamInt($key, $defaultValue = -1)
    {
        if (isset($this->m_arrayParams[$key]))
            return (int) $this->m_arrayParams[$key];
        else
            return $defaultValue;
    }


    function Content()
    {
        $voie = $this->GetParamInt('voie', 0);
        echo '
            <div class="container-fluid nuage">
                <div class="voie">
                    <button type="button" class="btn btn-light btn-lg">' . $voie . '</button>
                </div>
            </div>';
    }



    function Script()
    {
        // parent::Script();
        $voie = $this->GetParamInt('voie', 0);
        $intervalle = $this->GetParamInt('intervalle', 2000);

    ?>
        <script src="js/jquery-3.5.1.min.js"></script>
        <script type="text/javascript" src="js/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/axios/axios.min.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript" src="js/voie.js?v={$NUM_VERSION}"></script>
        <script type="text/javascript">
            SetVoie(<?= $voie ?>, <?= $intervalle ?>);
        </script>
<?php
    }
}

new TV($_GET);
