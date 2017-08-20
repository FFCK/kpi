 	 	&nbsp;(<a href="GestionCompetition.php">Retour</a>)

		<div class="main">
			<form method="POST" action="GestionDoc.php" name="formDoc" id="formDoc" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='laSaison' id='laSaison' Value='{$sessionSaison}'/>
				<input type='hidden' name='laCompet' id='laCompet' Value='{$codeCompet}'/>

				<div class='blocLeft'>
					<div class='titrePage'>Documents {$detailsCompet.Libelle}</div>
					<label for="saisonTravail">Saison :</label>
					<select name="saisonTravail" onChange="submit()">
						{section name=i loop=$arraySaison} 
							<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{$arraySaison[i].Code}{if $arraySaison[i].Code eq $sessionSaison} (Travail){/if}</Option>
						{/section}
					</select>
					<br />
					<label for="codeCompet">Compétition :</label>
					<select name="codeCompet" onChange="submit();">
						{section name=i loop=$arrayCompetition} 
							<Option Value="{$arrayCompetition[i][0]}" {$arrayCompetition[i][2]}>{$arrayCompetition[i][1]}</Option>
						{/section}
					</select>

					<div class='blocTable table2'>
						<table class='tableauJQ tableauClassement tableau'>
							<thead>
								<tr>
									<th>Catégorie</th>
									<th>DOCUMENTS</th>
									<th>Admin<br>(provisoire)</th>
									<th>Public</th>
								</tr>
							</thead>
							<tbody>
									<tr class='{cycle values="impair,pair"}'>
										<td>Equipes</td>
										<td>Liste Equipes par poule</td>
										<td><a href="FeuilleGroups.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Equipes</td>
										<td>Feuilles de présence FR</td>
										<td><a href="FeuillePresence.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Equipes</td>
										<td>Feuilles de présence EN</td>
										<td><a href="FeuillePresenceEN.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Matchs</td>
										<td>Liste FR</td>
										<td><a href="FeuilleListeMatchs.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfListeMatchs.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Matchs</td>
										<td>Liste EN</td>
										<td><a href="FeuilleListeMatchsEN.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfListeMatchsEN.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Matchs</td>
										<td>Liste OpenOffice</td>
										<td><a href="tableau_tbs.php" Target="_blank"><img height="22" src="../img/ods.png" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Matchs</td>
										<td>Feuilles de match</td>
										<td><a href="FeuilleMatchMulti.php?listMatch={$listMatchs}" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfMatchMulti.php?listMatch={$listMatchs}" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
								{if $detailsCompet.Code_typeclt == 'CHPT'}
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Classement Général</td>
										<td><a href="FeuilleCltChpt.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltChpt.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Détail par équipe</td>
										<td><a href="FeuilleCltChptDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltChptDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Détail par journée</td>
										<td><a href="FeuilleCltNiveauJournee.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltNiveauJournee.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									{if $user == '42054'}
										<tr>
											<th>&nbsp;</th>
											<th><i>Classement Coupe</i></th>
											<th>&nbsp;</th>
											<th>&nbsp;</th>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Classement Général</td>
											<td><a href="FeuilleCltNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Détail par phase</td>
											<td><a href="FeuilleCltNiveauPhase.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltNiveauPhase.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<!--<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Détail par niveau</td>
											<td><a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltNiveauNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>-->
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Détail par équipe</td>
											<td><a href="FeuilleCltNiveauDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltNiveauDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
									{/if}
								{else}
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Classement Général</td>
										<td><a href="FeuilleCltNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Détail par phase</td>
										<td><a href="FeuilleCltNiveauPhase.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltNiveauPhase.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<!--<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Détail par niveau</td>
										<td><a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltNiveauNiveau.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>-->
									<tr class='{cycle values="impair,pair"}'>
										<td>Classements</td>
										<td>Détail par équipe</td>
										<td><a href="FeuilleCltNiveauDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="../PdfCltNiveauDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									{if $user == '42054'}
										<tr>
											<th>&nbsp;</th>
											<th><i>Classement Championnat</i></th>
											<th>&nbsp;</th>
											<th>&nbsp;</th>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Classement Général</td>
											<td><a href="FeuilleCltChpt.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltChpt.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Détail par équipe</td>
											<td><a href="FeuilleCltChptDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltChptDetail.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Classements</td>
											<td>Détail par journée</td>
											<td><a href="FeuilleCltNiveauJournee.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a href="../PdfCltNiveauJournee.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
									{/if}
									<tr class='{cycle values="impair,pair"}'>
										<td>Liens</td>
										<td>Liens accès direct</td>
										<td><a href="../PdfQrCodes.php?S={$sessionSaison}&Compet={$codeCompet}" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Live</td>
										<td>En direct des terrains</td>
										<td><a href="../DirectPitchs.php?saison={$sessionSaison}&idCompet={$codeCompet}" Target="_blank"><img height="22" src="../img/web.png" /></a></td>
										<td></td>
									</tr>
								{/if}
									{if $user == '42054'}
										<thead>
											<tr>
												<th>&nbsp;</th>
												<th>Evénement</th>
												<th>Admin</th>
												<th>Public</th>
											</tr>
										</thead>
										<tr class='{cycle values="impair,pair"}'>
											<td>Evénement</td>
											<td>
												<select name="evenement" id="evenement">
													{section name=i loop=$arrayEvenement} 
														<Option Value="{$arrayEvenement[i].Id}" {$arrayEvenement[i].Selection}>{$arrayEvenement[i].Libelle}</Option>
													{/section}
												</select>
											</td>
											<td></td>
											<td></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Matchs</td>
											<td>Matchs de l'événement</td>
											<td><a id="linkEvt1" href="FeuilleListeMatchs.php?idEvenement=" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a id="linkEvt2" href="../PdfListeMatchs.php?idEvenement=" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Matchs</td>
											<td>Matchs de l'événement EN</td>
											<td><a id="linkEvt3" href="FeuilleListeMatchsEN.php?idEvenement=" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td><a id="linkEvt4" href="../PdfListeMatchsEN.php?idEvenement=" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Liens</td>
											<td>Liens accès direct</td>
											<td><a id="linkEvt5" href="../PdfQrCodes.php?" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
											<td></td>
										</tr>
										<tr class='{cycle values="impair,pair"}'>
											<td>Live</td>
											<td>En direct des terrains</td>
											<td><a id="linkEvt6" href="../DirectPitchs.php" Target="_blank"><img height="22" src="../img/web.png" /></a></td>
											<td></td>
										</tr>
									{/if}
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>STATISTIQUES</th>
									<th>FR</th>
									<th>EN</th>
								</tr>
							</thead>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Meilleurs buteurs</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Buteurs" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Buteurs" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Meilleure attaque</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Attaque" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Attaque" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Meilleure défense</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Defense" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Defense" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Cartons</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Cartons" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Cartons" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Cartons par équipe</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CartonsEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CartonsEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Classement disciplinaire individuel</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Fairplay" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Fairplay" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Classement disciplinaire par équipe</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=FairplayEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=FairplayEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Arbitrages individuels</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Arbitrage" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=Arbitrage" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Arbitrages par équipe</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=ArbitrageEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=ArbitrageEquipe" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<!--<tr class='{cycle values="impair,pair"}'>
										<td>Stats</td>
										<td>Joueurs catégorie <select id="Cat" name="Cat">
												<option>Sélectionner</option>
												<Option Value="MIN">Minime</Option>
												<Option Value="CAD">Cadet</Option>
												<Option Value="JUN">Junior</Option>
												<Option Value="SEN">Senior</Option>
												<Option Value="V1">Vétéran 1</Option>
												<Option Value="V2">Vétéran 2</Option>
												<Option Value="V3">Vétéran 3</Option>
											</select> (matchs joués)</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees4" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees4" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>-->
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>CONTROLE IRREGULARITES</th>
									<th>FR</th>
									<th>EN</th>
								</tr>
							</thead>
									<tr class='{cycle values="impair,pair"}'>
										<td>Contrôle</td>
										<td>Feuilles de présence par catégorie</td>
										<td><a href="FeuillePresenceCat.php" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Contrôle</td>
										<td>Matchs joués par club</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Contrôle</td>
										<td>Matchs joués par équipe</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees2" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees2" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Contrôle</td>
										<td>Irrégularités à contrôler (matchs joués)</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees3" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets={$detailsCompet.Code}&nbLignes=30&Stat=CJouees3" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td class='drag'>Contrôle</td>
										<td>Matchs joués Championnat de France</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=2000&Stat=CJoueesN" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td></td>
									</tr>
									<tr class='{cycle values="impair,pair"}'>
										<td>Contrôle</td>
										<td>Matchs joués Coupe de France</td>
										<td><a href="FeuilleStats.php?Compets={$detailsCompet.Code}&nbLignes=2000&Stat=CJoueesCF" Target="_blank"><img height="22" src="../img/pdf.png" /></a></td>
										<td></td>
									</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class='blocRight'>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>Compétition</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
									{if $detailsCompet.Kpi_ffck_actif == 'O'}<img src='../img/logoKPI-small.jpg' width='70'>{/if}
									{if $detailsCompet.Bandeau_actif == 'O'}<br><img src='{$detailsCompet.BandeauLink}' width=200>{/if}
									{if $detailsCompet.Logo_actif == 'O'}<br><img src='{$detailsCompet.LogoLink}' width=100>{/if}
									{if $detailsCompet.Titre_actif == 'O'}<br><b>{$detailsCompet.Libelle}</b>{else}<br><b>{$detailsCompet.Soustitre}</b>{/if}
									<br>{$detailsCompet.Soustitre2}
                                    {if $detailsCompet.Sponsor_actif == 'O'}<br><img src='{$detailsCompet.SponsorLink}' width=200>{/if}
									<br>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>
								{if $detailsCompet.Publication == 'O'}<img height="22" src="../img/oeil2O.gif" />
								{else}<img height="22" src="../img/oeil2N.gif" />{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4><hr></td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Equipes <a href='GestionEquipe.php'><img width="10" height="10" src="../img/b_plus.png" alt="Equipes" title="Equipes" /></a>
							</td>
							<td align='center'>
								{$nbEquipes}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>Qualifiées : {$detailsCompet.Qualifies} - Eliminées : {$detailsCompet.Elimines}</i>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>Feuilles de présence</i> <a href='GestionCompetition.php'><img width="10" height="10" src="../img/b_plus.png" alt="Compétitions" title="Compétitions" /></a>
							</td>
							<td align='center'>
								{if $detailsCompet.Verrou == 'O'}<img width="15" height="15" src="../img/verrou2O.gif" />{else}<img width="15" height="15" src="../img/verrou2N.gif" />{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								{if $detailsCompet.Code_typeclt == 'CHPT'}
									Journées <a href='GestionCalendrier.php'><img width="10" height="10" src="../img/b_plus.png" alt="Journées" title="Journées" /></a>
								{else}
									Phases <a href='GestionCalendrier.php'><img width="10" height="10" src="../img/b_plus.png" alt="Phases" title="Phases" /></a>
								{/if}
							</td>
							<td align='center'>
								{$nbJournees}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>publiées ({$nbJourneesPubli})</i>
							</td>
							<td align='center'>
								{if $nbJourneesPubli == $nbJournees}<img width="15" height="15" src="../img/oeil2O.gif" />
								{elseif $nbJourneesPubli == 0}<img width="15" height="15" src="../img/oeil2N.gif" />
								{else}<img width="18" height="15" src="../img/oeil2.gif" />
								{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Matchs <a href='GestionJournee.php'><img width="10" height="10" src="../img/b_plus.png" alt="Matchs" title="Matchs" /></a>
							</td>
							<td align='center'>
								{$nbMatchs}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>publiés ({$nbMatchsPubli})</i>
							</td>
							<td align='center'>
								{if $nbMatchsPubli == $nbMatchs}<img width="15" height="15" src="../img/oeil2O.gif" />
								{elseif $nbMatchsPubli == 0}<img width="15" height="15" src="../img/oeil2N.gif" />
								{else}<img width="18" height="15" src="../img/oeil2.gif" />
								{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>validés ({$nbMatchsValid})</i>
							</td>
							<td align='center'>
								{if $nbMatchsValid == $nbMatchs}<img width="15" height="15" src="../img/verrou2O.gif" />
								{elseif $nbMatchsValid == 0}<img width="15" height="15" src="../img/verrou2N.gif" />
								{else}<img width="18" height="15" src="../img/verrou2.gif" />
								{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Type classement <a href='GestionClassement.php'><img width="10" height="10" src="../img/b_plus.png" alt="Classement" title="Classement" /></a>
							</td>
							<td align='center'>
								{$detailsCompet.Code_typeclt}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>{if $detailsCompet.Date_calcul != '00/00/00 à 00h00'}calculé le {$detailsCompet.Date_calcul}{/if}</i>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>{if $detailsCompet.Date_publication != '00/00/00 à 00h00'}publié le {$detailsCompet.Date_publication}
									{else}non publié{/if}</i>
							</td>
							<td align='center'>{if $detailsCompet.Date_publication != '00/00/00 à 00h00'}<img width="15" height="15" src="../img/oeil2O.gif" />
								{else}<img width="15" height="15" src="../img/oeil2N.gif" />{/if}
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Statistiques <a href='GestionStats.php'><img width="10" height="10" src="../img/b_plus.png" alt="Stats" title="Stats" /></a>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=4><hr></td>
						</tr>
						<tr>
							<td align='center' colspan=4>{$detailsCompet.commentairesCompet}</td>
						</tr>
						<tr>
							<td align='center' colspan=4>{if $detailsCompet.Sponsor_actif == 'O'}<img src='{$detailsCompet.SponsorLink}' width=220px><br>{/if}</td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>{if $detailsCompet.Code_typeclt == 'CHPT'}Journées{else}Phases{/if}</label>
							</th>
						</tr>
						<tr>
							{if $detailsCompet.Code_typeclt == 'CHPT'}
								<td align='left' colspan=4>
									{section name=i loop=$arrayJournees}
										{$arrayJournees[i].Date_debut} - {$arrayJournees[i].Lieu}<br>
									{/section}
								</td>
							{else}
								<td align='center' colspan=4>
									<b>{$arrayJournees[0].Date_debut} au {$arrayJournees[0].Date_fin}</b>
									<br><br>
									{section name=i loop=$arrayJournees}
										{if $smarty.section.i.iteration > 1}{if $arrayJournees[i].Niveau != $niveauTmp}<br>{else} | {/if}{/if}
										{$arrayJournees[i].Phase}
										{assign var='niveauTmp' value=$arrayJournees[i].Niveau}
									{/section}
								</td>
							{/if}
						</tr>
					</table>
				</div>
						
			</form>			
					
		</div>	  	   
