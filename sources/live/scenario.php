<?php
include_once('base.php');
include_once('create_cache_match.php');
include_once('page.php');

class Scenario extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Scenario Live</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="F.F.C.K.">
        <meta name="Description" content="KAYAK POLO - LIVE" />
        <meta name="Keywords" content="kayak polo, ffck" />
        <meta name="rating" content="general">
        <meta name="Robots" content="all">
        
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
        <!-- CSS styles -->
        <link href="./css/bootstrap.min.css" rel="stylesheet">
	
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
        </head>
    <?php
    }

	function Content_Command_Scenario($scenario)
	{
		echo "<table name='$scenario' id='$scenario'>";
        ?>		
			<thead>
			<tr>
				<th>N°</th>
				<th>Url</th>
				<th>Durée</th>
			</tr>
			</thead>
			<tbody>
			<?php for ($i=1; $i<=8; $i++) { ?>
				<tr>
					<td><?php echo $i;?></td>
					<td><input type="text" style="width:1024px" name="scenario_url<?php echo$i;?>" id="scenario_url<?php echo$i;?>"></td>
					<td><input type="text" style="width:64px"name="scenario_duree<?php echo$i;?>" id="scenario_duree<?php echo$i;?>"></td>
				</tr>
			<?php } ?>
			</tbody>
        </table>
        <?php 
	}


	function Content()
	{
        ?>
		<form method='GET' action='#' name='scenario_form' id='scenario_form' enctype='multipart/form-data'> 
            <article>
                <div class="row">
                    <div class='col-sm-2'>
                        <label>Channel</label>
                        <select id="scenario_channel" name="scenario_channel">
                            <?php for ($i=1; $i<=20; $i++) { ?>
                                <option value="<?= $i; ?>">
                                    <?= $i; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <br>
                    </div>
                    <div class='col-sm-10'>
                        <?php $this->Content_Command_Scenario('scenario'); ?>
                    </div>
                </div>
            </article>

            <button id='scenario_btn' type="button">Lancer le scenario</button>
            <button id="raz_btn" type="button">Reset</button>
            <br>
            <br>
            <label>Message :</label>
            <div id="tv_message"></div>
		</form>
		<?php
    }
	
    function Script()
    {
        parent::Script();
        ?>
		<script type="text/javascript" src="./js/voie.js" ></script>
        <script type="text/javascript" src="./js/scenario.js" ></script>
        <script type="text/javascript">$(document).ready(function(){ Init(); }); </script>	
        <?php
    }
}

new Scenario($_GET);
