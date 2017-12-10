<?php
include_once('../commun/MyConfig.php');
include_once('replace_evenement.php');

$jsondata = '';
if (isset($_POST['json_data']))
	$jsondata = stripcslashes($_POST['json_data']);
?>

<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>Validation Import/Export</title> 

		<style type="text/css" media="screen">
/*			
		body{
			border:5px solid red;
		}
*/		
		#urlOrigine
		{
			width:70%;
		}
		</style>
		
		<!-- jQuery includes -->
		<script type="text/javascript"  src="../js/jquery-1.4.2.min.js"></script>	<!-- Use .min for production -->
		<script type="text/javascript" src="./test.js"></script>		
		
	</head>
	<body>
	
		<form method="POST" action="test.php" name="testForm" enctype="multipart/form-data">
		<input type='hidden' name='json_data' id='json_data' Value=''/>

		<ol>
			<li>
			<label for="lstEvent">Liste des Ev&eacute;nements</label>
			<input type="text" name="lstEvent" maxlength=20 id="lstEvent"/>
			</li>
		
			<?php
			if (PRODUCTION)
			{
				echo "<li>";
				echo "<label for=\"user\">Utilisateur</label>";
				echo "<input type=\"text\" name=\"user\" maxlength=20 id=\"user\"/>";
				echo "</li>";

				echo "<li>";
				echo "<label for=\"pwd\">Mot de passe</label>";
				echo "<input type=\"text\" name=\"pwd\" maxlength=20 id=\"pwd\"/>";
				echo "</li>";

				echo "<li>";
				echo "<input type=\"button\" name=\"btnImportServer\" id=\"btnImportServer\" value=\"Importer (Wamp vers kayak-polo.info)\">";
				echo "</li>";
			}
			else
			{
				echo "<li>";
				echo "<label for=\"urlOrigine\">Url Origine</label>";
				echo "<input type=\"text\" name=\"urlOrigine\" maxlength=80 id=\"urlOrigine\"/>";
				echo "</li>";
				echo "<br>";
				echo "<li>";
				echo "<input type=\"button\" name=\"btnImport\" id=\"btnImport\" value=\"Importer Methode 1(de kayak-polo.info vers Wamp)\">";
				echo "</li>";
				echo "<br>";
				echo "<li>";
				echo "<input type=\"button\" name=\"btnImport2\" id=\"btnImport2\" value=\"Importer Methode 2(de kayak-polo.info vers Wamp)\">";
				echo "</li>";
			}
			?>
			
			<li>
			<div id="result">
			</div>
			</li>
			
		</ol>
		</form>		
		
		<?php
		if (strlen($jsondata) > 0)
		{
			$jsondata = str_replace("\\\"", "\"", $jsondata);
			if (strstr($_SERVER['DOCUMENT_ROOT'],'wamp') == false)
				echo "*** IMPORT SERVEUR POLOWEB5 *** <br>";
			else
				echo "*** IMPORT WAMP LOCAL **** <br>";
//			echo 'JSON DATA :<br>'.$jsondata.'<br>';
			
			Replace_Evenement($jsondata);
		}
		?>
	</body>

	<script type="text/javascript">

	/* Execute JS once the DOM has loaded */
	$(document).ready(function(){
		Init();
//		localJSON();
	});

	</script>
</html>



