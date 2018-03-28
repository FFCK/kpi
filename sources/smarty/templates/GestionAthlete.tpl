		&nbsp;(<a href="javascript:history.back()">{#Retour#}</a>)
		<br>
		<iframe name="iframeRechercheLicenceIndi2" id="iframeRechercheLicenceIndi2" SRC="RechercheLicenceIndi2.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionAthlete.php" name="formAthlete" id="formAthlete" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='{$user}' />
				
				<div class='titrePage'>{#Statistiques#} {#athlete#}</div>
				<div class='blocTop'>
								<label>{#Chercher#}</label>
                                <input type="text" name="choixJoueur" id="choixJoueur" size="30" placeholder="{#Nom#}, {#Prenom#}, {#Licence#}" />
								<input type="submit" name="maj" id="maj" value="{#MAJ#}" />
                                {if $profile <= 6}
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <label>{#Recherche_avancee#}</label>
                                    <a href="#" id="rechercheAthlete"><img height="18" src="../img/glyphicons-28-search.png" alt="{#Recherche_avancee#}" title="{#Recherche_avancee#}" align=absmiddle /></a>
								{/if}
                                <br />
								<input type="hidden" name="Athlete" id="Athlete" value="{$Athlete}" />
								
				</div>
                {if $profile <= 2}
                    <div class="blocTop">
                        <b>{#Fusionner_des_licencies#} : </b>
                        <label for="FusionSource">Source ({#supprime#})</label>
                        <input type="hidden" name="numFusionSource" id="numFusionSource">
                        <input type="text" name="FusionSource" size=40 id="FusionSource">
                        <label for="FusionCible">{#Cible#} ({#conserve#})</label>
                        <input type="hidden" name="numFusionCible" id="numFusionCible">
                        <input type="text" name="FusionCible" size=40 id="FusionCible">
                        <input type="button" name="FusionJoueurs" id="FusionJoueurs" value="{#Fusionner#}">
                    </div>

                {/if}
				{if $Courreur.Matric != ''}
				<div class='blocMiddle'>
					<table class='tableau'>
						<tr>
							<th colspan=4>
                                <u>Licence n° {$Courreur.Matric}</u>&nbsp;&nbsp;
                                <b>{$Courreur.Nom} {$Courreur.Prenom}</b> ({$Courreur.Sexe})
                                {if $lang == 'en'}
                                    {$Courreur.Naissance|replace:'0000-00-00':''}
                                {else}
                                    {$Courreur.Naissance|replace:'0000-00-00':''|date_format:"%d/%m/%Y"}
                                {/if}
                                <br>{if $Courreur.Matric > 2000000 && $Courreur.Reserve != NULL}(ICF #{$Courreur.Reserve}) {/if}
                                {if $Courreur.date_surclassement}<b class='vert'>{#Surclasse_le#} {$Courreur.date_surclassement}</b>{/if}
                                <br>
							</th>
						</tr>
						<tr>
							<th>Club</th>
							<th>{#Pagaie#}</th>
							<th>{#Certificat#}</th>
							<th>{#Arb#}</th>
						</tr>
						<tr>
							<td>
								<b>{$Courreur.Numero_club} - {$Courreur.nomclub}</b><br>
								{$Courreur.nomcd}<br>
								{$Courreur.nomcr}<br>
								{#Derniere_saison#} : <b>{$Courreur.Origine}</b>
							</td>
							<td>
                                {assign var="evi" value=$Courreur.Pagaie_EVI}
                                {assign var="mer" value=$Courreur.Pagaie_MER}
                                {assign var="eca" value=$Courreur.Pagaie_ECA}
								{#Eau_vive#} : {$smarty.config.$evi}<br>
								{#Mer#} : {$smarty.config.$mer}<br>
								{#Eau_calme#} : <b>{$smarty.config.$eca}</b><br>
							</td>
							<td>
								APS ({#Loisir#}) : {$Courreur.Etat_certificat_APS}<br>
								CK ({#Competition#}) : {$Courreur.Etat_certificat_CK}
							</td>
							<td>
								{$Arbitre.Arb}<br>
								{#Niveau#} : {$Arbitre.niveau}<br>
								{#Saison#} : {$Arbitre.saison}<br>
								{#Livret#} : {$Arbitre.Livret}<br>
							</td>
						</tr>
					</table>
                    {if $profile <= 2 && $Courreur.Matric > 2000000}
                        <div class='blocTop'>
                            <b>{#Modifier#} : </b>
                            <input type="hidden" name="update_matric" id="update_matric" value="{$Courreur.Matric}">
                            {#Nom#}:<input type="text" name="update_nom" id="update_nom" value="{$Courreur.Nom}">
                            {#Prenom#}:<input type="text" name="update_prenom" id="update_prenom" value="{$Courreur.Prenom}">
                            {#Sexe#}:<select id="update_sexe" name="update_sexe">
                                <option value="M" {if $Courreur.Sexe == 'M'}selected{/if}>M</option>
                                <option value="F" {if $Courreur.Sexe == 'F'}selected{/if}>F</option>
                            </select>
                            {#Naissance#}:<input type="text" name="update_naissance" id="update_naissance" size="10" 
                                            maxlength="10" minlength="10"  onfocus="displayCalendar(document.forms[0].update_naissance,'dd/mm/yyyy',this)"
                                            value="{$Courreur.Naissance|replace:'0000-00-00':''|date_format:"%d/%m/%Y"}">
                            <br>
                            {#Derniere_saison#}:<input type="text" name="update_saison" id="update_saison" size="4" maxlength="4" minlength="4" value="{$Courreur.Origine}">
                            ICF #<input type="tel" name="update_icf" id="update_icf" size="6" maxlength="10" minlength="2" value="{$Courreur.Reserve}">
                            {#Arb#}:<select id="update_arb" name="update_arb">
                                <option value="" {if $Arbitre.Arb == 'Néant'}selected{/if}>-</option>
                                <option value="Reg" {if $Arbitre.Arb == 'Arbitre REGIONAL'}selected{/if}>REGIONAL</option>
                                <option value="Nat" {if $Arbitre.Arb == 'Arbitre NATIONAL'}selected{/if}>NATIONAL</option>
                                <option value="Int" {if $Arbitre.Arb == 'Arbitre INTERNATIONAL'}selected{/if}>INTERNATIONAL</option>
                                <option value="OTM" {if $Arbitre.Arb == 'Officiel table de marque'}selected{/if}>OTM</option>
                                <option value="JO" {if $Arbitre.Arb == 'Jeune officiel'}selected{/if}>JO</option>
                            </select>
                            {#Niveau#}:<select id="update_niveau" name="update_niveau">
                                <option value="" {if $Arbitre.niveau == ''}selected{/if}>-</option>
                                <option value="A" {if $Arbitre.niveau == 'A'}selected{/if}>A</option>
                                <option value="B" {if $Arbitre.niveau == 'B'}selected{/if}>B</option>
                                <option value="C" {if $Arbitre.niveau == 'C'}selected{/if}>C</option>
                                <option value="S" {if $Arbitre.niveau == 'S'}selected{/if}>S</option>
                            </select>
                            {#Nouveau#} club:<input type="text" name="update_club" id="update_club" size="4">
                            <input type="hidden" name="update_cd" id="update_cd">
                            <input type="hidden" name="update_cr" id="update_cr">
                            <input type="submit" id="update_button" value="Modifier">
                        </div>
                    {/if}
                    
                    <p><b>{#Saison#}:</b>
                        <select name="SaisonAthlete"  id="SaisonAthlete" onChange="submit()">
                            {section name=i loop=$arraySaison} 
                                <Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $SaisonAthlete}selected{/if}>{$arraySaison[i].Code}</Option>
                            {/section}
                        </select>
                    </p>
					<table class='tableau'>
						<tr>
							<td valign=top>
                                
								{if $Titulaire[0].Code_compet != ''}
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=4>{#Feuilles_de_presence#} {$SaisonAthlete}</th>
										</tr>
										<tr>
{*											<th>Saison</th>*}
											<th>{#Comp#}</th>
											<th>{#Equipe#}</th>
											<th>#</th>
											<th>{#Categorie#}</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Titulaire}
											<tr>
{*												<td>{$Titulaire[i].Code_saison}</td>*}
												<td>{$Titulaire[i].Code_compet}</td>
												<td>{$Titulaire[i].Libelle}</td>
                                                {if $lang == 'en'}
                                                    <td>#{$Titulaire[i].Num} {$Titulaire[i].Capitaine|replace:'C':'Cap'|replace:'E':'Coach'|replace:'A':'Ref.'|replace:'X':'Unavailable'|replace:'-':''}</td>
                                                {else}
                                                    <td>n°{$Titulaire[i].Num} {$Titulaire[i].Capitaine|replace:'C':'Cap'|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'X':'INACTIF'|replace:'-':''}</td>
                                                {/if}
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
											<th colspan=6>{#Arbitrage#} {$SaisonAthlete}</th>
										</tr>
										<tr>
{*											<th>Saison</th>*}
											<th>Date</th>
											<th>{#Heure#}</th>
											<th>{#Comp#}</th>
											<th>{#Match#}</th>
											<th>{#Principal#}</th>
											<th>{#Secondaire#}</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Arbitrages}
											<tr>
{*												<td>{$Arbitrages[i].Code_saison}</td>*}
												<td>{$Arbitrages[i].Date_match|date_format:"%d/%m"}</td>
												<td>{$Arbitrages[i].Heure_match}</td>
												<td>{$Arbitrages[i].Code_competition}</td>
												<td>{$Arbitrages[i].Numero_ordre}
													{if $profile <= 3}
														<a href="FeuilleMatchMulti.php?listMatch={$Arbitrages[i].Identifiant}" target="_blank"><img width="10" src="../img/b_plus.png" alt="{#Feuille_marque#}" title="{#Feuille_marque#}" /></a>
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
								{if $OTM[0].Code_competition != ''}
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=8>{#Officiels_des_matchs#} {$SaisonAthlete}</th>
										</tr>
										<tr>
{*											<th>Saison</th>*}
											<th>Date</th>
											<th>{#Heure#}</th>
											<th>{#Comp#}</th>
											<th>{#Match#}</th>
											<th>Sec</th>
											<th>Chrono</th>
											<th>T.S</th>
											<th>{#Lignes#}</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$OTM}
											<tr>
{*												<td>{$Arbitrages[i].Code_saison}</td>*}
												<td>{$OTM[i].Date_match|date_format:"%d/%m"}</td>
												<td>{$OTM[i].Heure_match}</td>
												<td>{$OTM[i].Code_competition}</td>
												<td>{$OTM[i].Numero_ordre}
													{if $profile <= 3}
														<a href="FeuilleMatchMulti.php?listMatch={$OTM[i].Identifiant}" target="_blank"><img width="10" src="../img/b_plus.png" alt="{#Feuille_marque#}" title="{#Feuille_marque#}" /></a>
													{/if}
												</td>
												{if $OTM[i].ScoreOK == 'O'}
													<td>{$OTM[i].Sec}</td>
													<td>{$OTM[i].Chrono}</td>
													<td>{$OTM[i].TS}</td>
													<td>{$OTM[i].Ligne}</td>
												{else}
													<td><i>{$OTM[i].Sec}</i></td>
													<td><i>{$OTM[i].Chrono}</i></td>
													<td><i>{$OTM[i].TS}</i></td>
													<td><i>{$OTM[i].Ligne}</i></td>
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
											<th colspan=13>{#Matchs#} {$SaisonAthlete}</th>
										</tr>
										<tr>
{*											<th>Saison</th>*}
											<th>Date</th>
											<th>{#Competition#}</th>
											<th>{#Match#}</th>
											<th>{#Equipes#}</th>
											<th>{#Score#}</th>
											<th>#</th>
											<th>{#But#}</th>
											<th>{#Vert#}</th>
											<th>{#Jaune#}</th>
											<th>{#Rouge#}</th>
                                            <th>{#Tir#}</th>
                                            <th>{#Arret#}</th>
										</tr>
									</thead>
									<tbody>
										{section name=i loop=$Joueur}
											<tr>
{*												<td>{$Joueur[i].Code_saison}</td>*}
												<td>{$Joueur[i].Date_match|date_format:"%d/%m"}</td>
												<td>{$Joueur[i].Code_competition}</td>
												<td>
													{$Joueur[i].Numero_ordre}
													{if $profile <= 3}
														<a href="FeuilleMatchMulti.php?listMatch={$Joueur[i].Identifiant}" target="_blank"><img width="10" src="../img/b_plus.png" alt="{#Feuille_marque#}" title="{#Feuille_marque#}" /></a>
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
													{if $lang == 'en'}
                                                        <td><i>#{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'C':'Cap'|replace:'E':'Coach'|replace:'A':'Ref.'|replace:'-':''}</i></td>
													{else}
                                                        <td><i>n°{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'-':''}</i></td>
                                                    {/if}
													{if $Joueur[i].But > 0}<td class='gris'>{$Joueur[i].But}</td>{else}<td></td>{/if}
													{if $Joueur[i].Vert > 0}<td class='vert'>{$Joueur[i].Vert}</td>{else}<td></td>{/if}
													{if $Joueur[i].Jaune > 0}<td class='jaune'>{$Joueur[i].Jaune}</td>{else}<td></td>{/if}
													{if $Joueur[i].Rouge > 0}<td class='rouge'>{$Joueur[i].Rouge}</td>{else}<td></td>{/if}
													{if $Joueur[i].Tir > 0}<td class='gris'>{$Joueur[i].Tir}</td>{else}<td></td>{/if}
													{if $Joueur[i].Arret > 0}<td class='gris'>{$Joueur[i].Arret}</td>{else}<td></td>{/if}
												{else}
													<td><i>{$Joueur[i].eqA} / {$Joueur[i].eqB}</i></td>
													<td>&nbsp;</td>
													{if $lang == 'en'}
                                                        <td><i>#{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'C':'Cap'|replace:'E':'Coach'|replace:'A':'Ref.'|replace:'-':''}</i></td>
													{else}
                                                        <td><i>n°{$Joueur[i].Num} {$Joueur[i].Capitaine|replace:'E':'Entraineur'|replace:'A':'Arbitre'|replace:'C':'Cap'|replace:'-':''}</i></td>
                                                    {/if}
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
		