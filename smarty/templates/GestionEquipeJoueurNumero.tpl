<html>
	<head>				  
	</head>	  
	
	<body>
			<form method="POST" action="GestionEquipeJoueurNumero.php" name="formNumero" enctype="multipart/form-data">
     
			<label for="numero">Numéro : </label>
			<input type="text" size="2" name="numero" value="{$numero}"/>
  
			<input type="submit" name="OkNumero" value="Valider">
			</form>
				<script type='text/javascript'>
				document.formNumero.numero.select();
				</script>
	</body>
</html>
