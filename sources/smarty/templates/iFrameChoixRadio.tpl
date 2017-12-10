<html>
	<head>				  
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
	<link rel="stylesheet" type="text/css" href="./css/iframeUpdate.css" />
	<title>{$title}</title>
	
	{literal}
		<script language="JavaScript" src="./js/prototype.js" type="text/javascript"></script>
		<script language="JavaScript" src="./js/formTools.js" type="text/javascript"></script>
		<script language="JavaScript" src="./js/GestionEquipeJoueur.js" type="text/javascript"></script>
	{/literal}

	</head>	  
	
	<body>
	
		<div class="main">
					
			<form method="POST" action="iFrameChoixRadio.php" name="formframeChoixRadio" id="formframeChoixRadio" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='columnName' Value='{$columnName}'/>
				<input type='hidden' name='key' Value='{$key}'/>
	
				<fieldset>
				<legend>Choix</legend>	
				{section name=i loop=$arrayChoixRadio} 
					{$arrayChoixRadio[i].Libelle}<input type="radio" name="radio_choix" value="{$arrayChoixRadio[i].Code}"/ {$arrayChoixRadio[i].Checked}>
				{/section}
				</fieldset>
							
				<input type="button" onclick="SaveFrameChoixRadio();" value="Valider">
				
			</form>			
		</div>	  	   
	</body>
</html>
