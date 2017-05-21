		&nbsp;(<a href="javascript:history.back()">Retour</a>)
		<br>
		<iframe name="iframeRechercheLicenceIndi2" id="iframeRechercheLicenceIndi2" SRC="RechercheLicenceIndi2.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionAthlete.php" name="formAthlete" id="formAthlete" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}' />
				
				<div class='titrePage'>Statistiques athlète</div>
				<div class='blocTop'>
								<label>Recherche (nom, prénom ou licence)</label>
								<input type="text" name="choixJoueur" id="choixJoueur" size="30" />
								<input type="submit" name="maj" id="maj" value="Mise à jour" />
                                {if $profile <= 6}
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <label>Recherche avancée</label>
                                    <a href="#" id="rechercheAthlete"><img height="18" src="../img/glyphicons-28-search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
								{/if}
                                <br />
								<input type="hidden" name="Athlete" id="Athlete" value="{$Athlete}" />
								
				</div>
                {if $profile <= 2}
                    <div class="blocTop">
                        <b>Fusionner des licenciés : </b>
                        <label for="FusionSource">Source (sera supprimé)</label>
                        <input type="hidden" name="numFusionSource" id="numFusionSource">
                        <input type="text" name="FusionSource" size=40 id="FusionSource">
                        <label for="FusionCible">Cible (sera conservé)</label>
                        <input type="hidden" name="numFusionCible" id="numFusionCible">
                        <input type="text" name="FusionCible" size=40 id="FusionCible">
                        <input type="button" name="FusionJoueurs" id="FusionJoueurs" value="Fusionner">
                    </div>

                {/if}
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
                                        <b>{$Courreur.Nom} {$Courreur.Prenom}</b> ({$Courreur.Sexe}) 
                                        né(e) le {$Courreur.Naissance|replace:'0000-00-00':''|date_format:"%d/%m/%Y"}
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
                    {if $profile <= 2 && $Courreur.Matric > 2000000}
                        <div class='blocTop'>
                            <b>Modification : </b>
                            <input type="hidden" name="update_matric" id="update_matric" value="{$Courreur.Matric}">
                            Nom<input type="text" name="update_nom" id="update_nom" value="{$Courreur.Nom}">
                            Prénom<input type="text" name="update_prenom" id="update_prenom" value="{$Courreur.Prenom}">
                            Sexe<select id="update_sexe" name="update_sexe">
                                <option value="M" {if $Courreur.Sexe == 'M'}selected{/if}>M</option>
                                <option value="F" {if $Courreur.Sexe == 'F'}selected{/if}>F</option>
                            </select>
                            Naissance<input type="text" name="update_naissance" id="update_naissance" size="10" 
                                            maxlength="10" minlength="10"  onfocus="displayCalendar(document.forms[0].update_naissance,'dd/mm/yyyy',this)"
                                            value="{$Courreur.Naissance|replace:'0000-00-00':''|date_format:"%d/%m/%Y"}">
                            Saison<input type="text" name="update_saison" id="update_saison" size="4" maxlength="4" minlength="4" value="{$Courreur.Origine}">
                            <input type="submit" id="update_button" value="Modifier">
                        </div>
                    {/if}
                    
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
											<th colspan=13>Matchs joués</th>
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
                                            <th>Tirs</th>
                                            <th>Arrêts</th>
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
													{if $Joueur[i].Tir > 0}<td class='gris'>{$Joueur[i].Tir}</td>{else}<td></td>{/if}
													{if $Joueur[i].Arret > 0}<td class='gris'>{$Joueur[i].Arret}</td>{else}<td></td>{/if}
												{else}
													<td><i>{$Joueur[i].eqA} / {$Joueur[i].eqB}</i></td>
													<td>&nbsp;</td>
													<td><i>n°{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'-':''}</i></td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
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
		