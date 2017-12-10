		&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
		<br>
		<div class="main">
				
				<div class='titrePage'>Liste des matchs</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr>
									<th>Date<br>heure</th>
									<th>Num</th>
									{section name=j loop=$arrayEquipes}
										<th>{$arrayEquipes[j]}</th>
									{/section}
								</tr>
							</thead>
							<tbody>
								{section name=i loop=$arrayMatchs}
									<tr class='{cycle values="impair,pair"}'>
										<td>{$arrayMatchs[i].Date_match}<br>{$arrayMatchs[i].Heure_match}</td>
										<td>{$arrayMatchs[i].Numero_ordre}</td>
										{section name=j loop=$arrayEquipes}
											<td>{if $arrayEquipes[j] == $arrayMatchs[i].eqA}<span class='bouton'>A</span>{elseif 
												$arrayEquipes[j] == $arrayMatchs[i].eqB}<span class='bouton'>B</span>{elseif 
												$arrayEquipes[j] == $arrayMatchs[i].Prin}Principal{elseif 
												$arrayEquipes[j] == $arrayMatchs[i].Sec}Secondaire{/if}</td>
										{/section}
									</tr>
								{/section}
							</tbody>
						</table>
						<br>
					</div>
					{assign var='nbmatch' value=$smarty.section.i.iteration-1}
					{if $nbmatch > 0}Nb matchs : {$nbmatch}{/if}
				</div>
			</form>
		</div>
		