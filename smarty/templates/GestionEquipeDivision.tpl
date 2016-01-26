	{literal}
		<script language="JavaScript" type="text/javascript">

			function changeCompetition()
			{
				changeCombo('formEquipeDivision','comboCompetition', 'idCompetition', true);
			}
			
			function changeDivision()
			{
				changeCombo('formEquipeDivision','comboDivision', 'idDivision', false);
	 	    changeCompetition();
			}
		</script>
	{/literal}

		<div class="main">
					
					Debug : {$Debug}
						  
					<form method="POST" action="GestionEquipeDivision.php" name="formEquipeDivision" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>
						<input type='hidden' name='idCompetition' Value='{$idCompetition}'/>
						<input type='hidden' name='idDivision' Value='{$idDivision}'/>

						<label for="comboCompetition">Compétition</label>
						<select name="comboCompetition" onChange="changeCombo('formEquipeDivision','comboCompetition', 'idCompetition');">
						changeCombo
							{section name=i loop=$arrayCompetition} 
				      <Option Value="{$arrayCompetition[i][0]}" {$arrayCompetition[i][2]}>{$arrayCompetition[i][1]}</Option>
							{/section}
				    </select>
						<br>
												
						<label for="comboDivision">Division</label>
						<select name="comboDivision" onChange="changeDivision();">
							{section name=i loop=$arrayDivision} 
				      <Option Value="{$arrayDivision[i][0]}" {$arrayDivision[i][2]}>{$arrayDivision[i][1]}</Option>
							{/section}
				    </select>
						<br>
						
						<table class='tbl_GestionEquipeDivision'>
							<tr class='header'>
									<td></td>
									<td>N°</td>
									<td>Libelle</td>
									<td>N°Club</td>
									<td></td>
							</tr>
							
							{section name=i loop=$arrayEquipeDivision} 
								<tr>
									<td><input type="checkbox" name="checkEquipeDivision" value="{$arrayEquipeDivision[i][0]}" id="checkDelete{$smarty.section.i.iteration}" /></td>
									
									<td>{$arrayEquipeDivision[i][0]}</td>
									<td>{$arrayEquipeDivision[i][1]}</td>
									<td>{$arrayEquipeDivision[i][2]}</td>
          			
									<td>
										<a href="#" onclick="RemoveCheckbox('formEquipeDivision', '{$arrayEquipeDivision[i][0]}');return false;"><img hspace="2" width="16" height="16" src="./img/b_drop.png" alt="Supprimer" title="Supprimer" border="0">
	           			  </a>
            			</td>
								</tr>
							{/section}
						
						</table>
						
						<table class='tbl_GestionEquipeDivisionDispo'>
						
							<tr class='header'>
									<td></td>
									<td>N°</td>
									<td>Libelle</td>
									<td>N°Club</td>
									<td></td>
							</tr>
							
							{section name=i loop=$arrayEquipeDivisionDispo} 
								<tr>
									<td><input type="checkbox" name="checkEquipeDivisionDispo" value="{$arrayEquipeDivisionDispo[i][0]}" id="checkAdd{$smarty.section.i.iteration}" /></td>
									
									<td>{$arrayEquipeDivisionDispo[i][0]}</td>
									<td>{$arrayEquipeDivisionDispo[i][1]}</td>
									<td>{$arrayEquipeDivisionDispo[i][2]}</td>
          			
									<td>
										<a href="#" onclick="AddCheckbox('formEquipeDivision', '{$arrayEquipeDivisionDispo[i][0]}');return false;"><img hspace="2" width="16" height="16" src="./img/b_insrow.png" alt="Ajouter" title="Ajouter" border="0">
	           			  </a>
            			</td>
								</tr>

							{/section}
						
						</table>
	
						<br>					
		        <a href="#" onclick="setCheckboxes('formEquipeDivision', 'checkEquipeDivision', true);return false;">Tout cocher</a>
		        <a href="#" onclick="setCheckboxes('formEquipeDivision', 'checkEquipeDivision', false);return false;">Tout décocher</a>
						<input type="button" onclick="RemoveCheckboxes('formEquipeDivision', 'checkEquipeDivision');" name="delEquipeDivision" value="Supprimer">
						<br>					
						
						<br>					
		        <a href="#" onclick="setCheckboxes('formEquipeDivision', 'checkEquipeDivisionDispo', true);return false;">Tout cocher</a>
		        <a href="#" onclick="setCheckboxes('formEquipeDivision', 'checkEquipeDivisionDispo', false);return false;">Tout décocher</a>
						<input type="button" onclick="AddCheckboxes('formEquipeDivision', 'checkEquipeDivisionDispo');" name="addEquipeDivisionDispo" value="Ajouter">
						<br>					

				        
					</form>			
					
		</div>	  	   

