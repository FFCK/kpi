 	 	<span class="repere">&nbsp;(<a href="Palmares.php">{#Retour#}</a>)</span>
	
		<div class="main">
			<form method="POST" action="Palmares.php" name="formPalmares" id="formPalmares" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre'>
					<br>
					<label for="choixEquipe" class='maxWith'><i>{#Chercher_equipe#} : </i></label>
					<input width=350 type="text" name="choixEquipe" id="choixEquipe" />
					<INPUT TYPE="SUBMIT" VALUE="{#Palmares#}">
					<br>
					<br>
					<div class='titrePage'>{#Palmares#}</div>
					<table class='tableau tableauPublic'>
						<thead>
							<tr>
								<th>{#Equipe#} : {$Equipe}</th>
							</tr>
						</thead>
					</table>
					<br>
					<table class='tableau tableauPublic'>
						<thead>
							<tr>
								<th>{#Saison#}</th>
								<th>{#Competition#}</th>
								<th colspan=2 width=20>{#Classement#}</th>
							</tr>
						</thead>
						<tbody>
						{assign var='idSaison' value=$arrayPalmares[0].Saisons}
						{section name=i loop=$arrayPalmares}
							{if $arrayPalmares[i].Saisons != $idSaison}
								<tr class='pair2'><td colspan=4></td></tr>
							{/if}
							<tr class='impair2'>
								{if $arrayPalmares[i].Code_tour == 10}
									<td>{if $arrayPalmares[i].Saisons != $idSaison or $arrayPalmares[i].Saisons == $arrayPalmares[0].Saisons}{$arrayPalmares[i].Saisons}{/if}</td>
									<td class="cliquableCompet">
										<a href='Classements.php?Compet={$arrayPalmares[i].Code}&Group={$arrayPalmares[i].Code_ref}&Saison={$arrayPalmares[i].Saisons}' title='{#Classement#}'>{$arrayPalmares[i].Competitions}</a>
									</td>
									<td>{$arrayPalmares[i].Classt}</td>
									<td>
										{if $arrayPalmares[i].Classt <= 3}
											<img width="16" src="img/medal{$arrayPalmares[i].Classt}.gif" alt="{$arrayPalmares[i].Classt}" title="{$arrayPalmares[i].Classt}" />
										{/if}
									</td>
								{else}
									<td>{if $arrayPalmares[i].Saisons != $idSaison or $arrayPalmares[i].Saisons == $arrayPalmares[0].Saisons}{$arrayPalmares[i].Saisons}{/if}</td>
									<td class='cliquableCompet grispetit'>
										<a href='Classements.php?Compet={$arrayPalmares[i].Code}&Group={$arrayPalmares[i].Code_ref}&Saison={$arrayPalmares[i].Saisons}' title='{#Classement#}'><i>{$arrayPalmares[i].Competitions}</i></a>
									</td>
									<td class='grispetit'><i>({$arrayPalmares[i].Classt})</i></td>
									<td>
										&nbsp;
									</td>
								{/if}
							</tr>
							{assign var='idSaison' value=$arrayPalmares[i].Saisons}
						{/section}
						</tbody>
					</table>

				</div>
						
			</form>			
					
		</div>	  	   
