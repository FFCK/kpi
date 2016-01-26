		<div class="main">
					
					<form method="POST" action="GestionMatch.php" name="formMatch" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>

						<div class='blocLeftGestionEquipe'>
	        
						<label for="scoreA">{$nomEquipeA}</label>
						<input type="text" name="scoreA" value="{$scoreA}"/>

						<Br>
						
						<label for="scoreB">{$nomEquipeB}</label>
						<input type="text" name="scoreB" value="{$scoreB}"/>
						
						<Br>
			    
						<input type="submit" name="OkScore" value="Valider le Score">
					
		        </div>
						
					</form>			
					
		</div>	  	   
