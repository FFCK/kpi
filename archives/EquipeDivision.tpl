<html>
	<head>				  
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
	<title>{$title}</title>
	
	{literal}

		<script language="JavaScript" src="./js/formTools.js" type="text/javascript"></script>
		
		<script language="JavaScript" type="text/javascript">
			function validEquipeDivision()
			{
					var nomEquipe = document.forms['EquipeForm'].elements['nomEquipe'].value;
				
					if (nomEquipe.length == 0)
					{
						alert("Le Nom de l'Equipe est Vide ..., Ajout Impossible !");
						return false;
					}
					
					return true;
			}

			function Add()
			{
				if (!validEquipeDivision())
					return;
			
	 	    document.forms['EquipeForm'].elements['Cmd'].value = 'Add';
	      document.forms['EquipeForm'].elements['ParamCmd'].value = '';
	      document.forms['EquipeForm'].submit();
			}
			
	</script>
	{/literal}

	</head>	  
	
	 <body>
		{include file='header.tpl'}
		{include file='main_menu.tpl'} 
	
		<div class="main">
						
					<BR>Get Paramètres : {$Param}
					<BR>Post Cmd : {$Cmd}
					<BR>Post Param : {$ParamCmd}
					<BR>
						  
					<form method="POST" action="EquipeDivision.php" name="EquipeForm" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>
						
						<table class='tbl_DivisionEquipe'>
						
							<tr class='header'>
									<td></td>
									<td>N°</td>
									<td>Libelle</td>"
							</tr>
							
							{section name=i loop=$arrayEquipe} 
								<tr>
									<td><input type="checkbox" name="checkEquipe" value="{$arrayEquipe[i][0]}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									<td>{$arrayEquipe[i][0]}</td>
									<td>{$arrayEquipe[i][1]}</td>
									
									<td>
										<a href="EquipeDivision.php?Remove&{$arrayEquipe[i][0]}">
            					<img hspace="2" width="16" height="16" src="./img/b_drop.png" alt="Supprimer" title="Supprimer" border="0">
            			  </a>
            			</td>
            			
									<td>
										<a href="#" onclick="RemoveCheckbox('EquipeForm', '{$arrayEquipe[i][0]}');return false;"><img hspace="2" width="16" height="16" src="./img/b_drop.png" alt="Supprimer2" title="Supprimer2" border="0">
	           			  </a>
            			</td>
            			
									<td>
										<a href="#" onclick="RemoveCheckbox('EquipeForm', '{$arrayEquipe[i][0]}');return false;"> Par Ici
            			  </a>
            			</td>
  								
								</tr>

							{/section}
						
						</table>
						
						<label for="CodeEquipe">Code</label>
						<input type="text" name="codeEquipe" maxlength=5 id="codeEquipe" onKeyPress="return numbersonly(this, event)"/>
						
						<label for="nomEquipe">Nom</label>
						<input type="text" name="nomEquipe" maxlength=40 id="nomEquipe"/>
						
						<input type="submit" name="removeEquipe" value="Supprimer">
						<input type="button" onclick="Add();" name="addEquipe" value="Ajouter">
						<input type="button" onclick="RemoveCheckboxes('EquipeForm', 'checkEquipe');" name="delEquipe" value="Delete">
						
		        <a href="#" onclick="setCheckboxes('EquipeForm', 'checkEquipe', true);return false;">Tout cocher</a>
		        <a href="#" onclick="setCheckboxes('EquipeForm', 'checkEquipe', false);return false;">Tout décocher</a>
		        
		        <a href="#" onclick="RemoveCheckboxes('EquipeForm', 'checkEquipe');return false;">Tout supprimer</a>
		        <a href="#" onclick="Add();return false;">Ajout</a>
		        
		        <a href="Login.php">Login</a>
		        <a href="UnLogin.php">Delogin</a>
		        
					</form>			
					
					<BR>
					
		</div>	  	   

		{include file='footer.tpl'}
	 </body>
</html>
