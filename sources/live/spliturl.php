<?php
//include_once('base.php');
//include_once('create_cache_match.php');
include_once('page.php');

class Spliturl extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Split Url</title>
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

	function Content_Command_Spliturl($split)
	{
		echo "<table name='$split' id='$split'>";
        ?>		
			<thead>
			<tr>
				<th>N°</th>
				<th>Url</th>
			</tr>
			</thead>
			<tbody>
			<?php for ($i=1; $i<=4; $i++) { ?>
				<tr>
					<td><?= $i;?></td>
					<td><input type="text" style="width:1024px" name="split_url<?= $i;?>" id="split_url<?= $i;?>"></td>
				</tr>
			<?php } ?>
			</tbody>
        </table>
        <?php 
	}


	function Content()
	{
        ?>
        <div class="container">
            <form method='GET' action='#' name='spliturl_form' id='scenario_form' enctype='multipart/form-data'> 
                <article>
                    <div class="row">
                        <div class='col-sm-12'>
                            <?php $this->Content_Command_Spliturl('split'); ?>
                        </div>
                    </div>
                </article>

                <br>
                <button id='split_btn' type="button">Lancer le split</button>
                <br>
                <br>
                <div id="tv_message"></div>
            </form>
        </div>
		<?php
    }
	
    function Script()
    {
        parent::Script();
        ?>
		<script type="text/javascript" src="./js/voie.js" ></script>
        <script type="text/javascript" src="./js/spliturl.js" ></script>
        <script type="text/javascript">$(document).ready(function(){ Init(); }); </script>	
        <?php
    }
}

new Spliturl($_GET);
