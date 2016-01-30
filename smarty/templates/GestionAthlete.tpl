		&nbsp;(<a href="javascript:history.back()">Retour</a>)
		<br>
		<iframe name="iframeRechercheLicenceIndi2" id="iframeRechercheLicenceIndi2" SRC="RechercheLicenceIndi2.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionAthlete.php" name="formAthlete" id="formAthlete" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}' />
				
				<div class='titrePage'>Statistiques athlète</div>
				<!--<center class='rouge'><i>Version BETA (Signaler les bugs)</i></center>-->
				<div class='blocTop'>
								<label>Recherche (nom, prénom ou licence)</label>
								<input type="text" name="choixJoueur" id="choixJoueur" size="30" />
								<input type="submit" name="maj" id="maj" value="Mise à jour" />
								&nbsp;&nbsp;&nbsp;&nbsp;
								<label for="comboarbitre1">Recherche avancée</label>
								<a href="#"  id='rechercheAthlete'><img width="16" src="../img/b_search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
								<br />
								<!--<label for="Athlete">Athlète sélectionné</label>-->
								<input type="hidden" size="5" name="Athlete" id="Athlete" value="{$Athlete}" />
								<!--<input type="text" size="30" name="Athlete_id" readonly id="Athlete_id" value="{$Athlete_id}" tabindex="12"/>-->
								
				</div>
				{if $Courreur.Matric != ''}
				<div class='blocMiddle'>
					<table class='tableau'>
						<tr>
							<th colspan=4>
								Saison:
                                    <select name="SaisonAthlete"  id="SaisonAthlete" onChange="submit()">
                                        {section name=i loop=$arraySaison} 
                                            <Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $SaisonAthlete}selected{/if}>{$arraySaison[i].Code}</Option>
                                        {/section}
                                    </select>
                                        &nbsp;&nbsp;
									<u>Licence n° {$Courreur.Matric}</u>&nbsp;&nbsp;
                                        <b>{$Courreur.Nom} {$Courreur.Prenom}</b> ({$Courreur.Sexe}) né(e) le {$Courreur.Naissance|date_format:"%d/%m/%Y"}
                                        <br>{if $Courreur.date_surclassement}<b class='vert'>Surclassé à la date du {$Courreur.date_surclassement}</b>{/if}
                                        <br>
							</th>
						</tr>
						<tr>
							<th>Club</th>
							<th>Pagaie couleur</th>
							<th>Certificats médicaux</th>
							<th>Arbitrage</th>
						</tr>
						<tr>
							<td>
								<b>{$Courreur.nomclub}</b><br>
								{$Courreur.nomcd}<br>
								{$Courreur.nomcr}<br>
								Dernière saison : <b>{$Courreur.Origine}</b>
							</td>
							<td>
								Eau vive : {$Courreur.Pagaie_EVI|replace:'PAGN':'Noire'|replace:'PAGV':'Verte'|replace:'PAGR':'Rouge'|replace:'PAGJ':'Jaune'|replace:'PAGBL':'Bleue'|replace:'PAGB':'Blanche'}<br>
								Mer : {$Courreur.Pagaie_MER|replace:'PAGN':'Noire'|replace:'PAGV':'Verte'|replace:'PAGR':'Rouge'|replace:'PAGJ':'Jaune'|replace:'PAGBL':'Bleue'|replace:'PAGB':'Blanche'}<br>
								Eau calme : <b>{$Courreur.Pagaie_ECA|replace:'PAGN':'Noire'|replace:'PAGV':'Verte'|replace:'PAGR':'Rouge'|replace:'PAGJ':'Jaune'|replace:'PAGBL':'Bleue'|replace:'PAGB':'Blanche'}</b><br>
							</td>
							<td>
								APS (Loisirs) : {$Courreur.Etat_certificat_APS}<br>
								CK (Compétition) : {$Courreur.Etat_certificat_CK}
							</td>
							<td>
								{$Arbitre.Arb}<br>
								Niveau : {$Arbitre.niveau}<br>
								Saison : {$Arbitre.saison}<br>
								Livret : {$Arbitre.Livret}<br>
							</td>
						</tr>
					</table>
					<table class='tableau'>
						<tr>
							<td valign=top>
								{if $Titulaire[0].Code_compet != ''}
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=5>Feuilles de présence</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Compét.</th>
											<th>Equipe</th>
											<th>n°</th>
											<th>Catégorie</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Titulaire}
											<tr>
												<td>{$Titulaire[i].Code_saison}</td>
												<td>{$Titulaire[i].Code_compet}</td>
												<td>{$Titulaire[i].Libelle}</td>
												<td>n°{$Titulaire[i].Num} {$Titulaire[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'X':'INACTIF'|replace:'-':''}</td>
												<td>({$Titulaire[i].Categ})</td>
											</tr>
										{/section}
									</tbody>
								</table>
								{/if}
								{if $Arbitrages[0].Code_competition != ''}
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=6>Arbitrages</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Date</th>
											<th>Compét.</th>
											<th>Match</th>
											<th>Prin</th>
											<th>Sec</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Arbitrages}
											<tr>
												<td>{$Arbitrages[i].Code_saison}</td>
												<td>{$Arbitrages[i].Date_match|date_format:"%d/%m"}</td>
												<td>{$Arbitrages[i].Code_competition}</td>
												<td>{$Arbitrages[i].Numero_ordre}
													{if $profile <= 3}
														<a href="FeuilleMatchMulti.php?listMatch={$Arbitrages[i].Identifiant}" target="_blank"><img width="10" src="../img/b_plus.png" alt="Feuille de match" title="Feuille de match" /></a>
													{/if}
												</td>
												{if $Arbitrages[i].ScoreOK == 'O'}
													<td>{$Arbitrages[i].Prin}</td>
													<td>{$Arbitrages[i].Sec}</td>
												{else}
													<td><i>{$Arbitrages[i].Prin}</i></td>
													<td><i>{$Arbitrages[i].Sec}</i></td>
												{/if}
											</tr>
										{/section}
									</tbody>
								</table>
								{/if}
							</td>
							<td>
								{if $Joueur[0].Code_competition != ''}
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=11>Matchs joués</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Date</th>
											<th>Compétition</th>
											<th>Match</th>
											<th>Equipes</th>
											<th>Score</th>
											<th>n°</th>
											<th>Buts</th>
											<th>Vert</th>
											<th>Jaune</th>
											<th>Rouge</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Joueur}
											<tr>
												<td>{$Joueur[i].Code_saison}</td>
												<td>{$Joueur[i].Date_match|date_format:"%d/%m"}</td>
												<td>{$Joueur[i].Code_competition}</td>
												<td>
													{$Joueur[i].Numero_ordre}
													{if $profile <= 3}
														<a href="FeuilleMatchMulti.php?listMatch={$Joueur[i].Identifiant}" target="_blank"><img width="10" src="../img/b_plus.png" alt="Feuille de match" title="Feuille de match" /></a>
													{/if}
												</td>
												{if $Joueur[i].ScoreOK == 'O'}
													{if $Joueur[i].Equipe == 'A'}
														<td><b>{$Joueur[i].eqA}</b> / {$Joueur[i].eqB}</td>
														<td>(<b>{$Joueur[i].ScoreA}</b>/{$Joueur[i].ScoreB})</td>
													{else}
														<td>{$Joueur[i].eqA} / <b>{$Joueur[i].eqB}</b></td>
														<td>({$Joueur[i].ScoreA}/<b>{$Joueur[i].ScoreB}</b>)</td>
													{/if}
													<td>n°{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'-':''}</td>
													{if $Joueur[i].But > 0}<td class='gris'>{$Joueur[i].But}</td>{else}<td></td>{/if}
													{if $Joueur[i].Vert > 0}<td class='vert'>{$Joueur[i].Vert}</td>{else}<td></td>{/if}
													{if $Joueur[i].Jaune > 0}<td class='jaune'>{$Joueur[i].Jaune}</td>{else}<td></td>{/if}
													{if $Joueur[i].Rouge > 0}<td class='rouge'>{$Joueur[i].Rouge}</td>{else}<td></td>{/if}
												{else}
													<td><i>{$Joueur[i].eqA} / {$Joueur[i].eqB}</i></td>
													<td>&nbsp;</td>
													<td><i>n°{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'-':''}</i></td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												{/if}
											</tr>
										{/section}
									</tbody>
								</table>
								{/if}
							</td>
						</tr>
					</table>
				</div>
				{/if}
				<div class='blocBottom'>
					

				</div>
			</form>
		</div>
		