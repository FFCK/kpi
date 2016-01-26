<?php /* Smarty version 2.6.18, created on 2015-03-09 13:32:59
         compiled from GestionEquipeJoueur.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionEquipeJoueur.tpl', 67, false),array('modifier', 'replace', 'GestionEquipeJoueur.tpl', 89, false),)), $this); ?>
    &nbsp;(<a href="GestionEquipe.php">Retour</a>)

    <!--<iframe name="SelectionCapitaineJoueur" id="SelectionCapitaineJoueur" SRC="SelectionCapitaineJoueur.php" width="350" height="160" FRAMEBORDER="yes"></iframe>-->
	
		<div class="main">
				
			<form method="POST" action="GestionEquipeJoueur.php" name="formEquipeJoueur" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' Value='' />
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes_Joueurs' />
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Matric = ' />
				<input type='hidden' name='AjaxAnd' id='AjaxAnd' Value='And Id_equipe = ' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
'/>
				<input type='hidden' name='idEquipe' Value='<?php echo $this->_tpl_vars['idEquipe']; ?>
' />
				<input type='hidden' name='typeCompet' id='typeCompet' Value='<?php echo $this->_tpl_vars['typeCompet']; ?>
' />
				<input type='hidden' name='saisonCompet' id='saisonCompet' Value='<?php echo $this->_tpl_vars['Saison']; ?>
' />

				<div class='blocLeft'>
					<div class='titrePage' tabindex='1'>Feuille de présence <?php echo $this->_tpl_vars['infoEquipe2']; ?>
</div>
					<?php if ($this->_tpl_vars['typeCompet'] == 'CH'): ?>
						<div class='titrePage'><i>Joueurs présents pour la prochaine journée de Championnat de France</i></div>
					<?php elseif ($this->_tpl_vars['typeCompet'] == 'CF'): ?>
						<div class='titrePage'><i>Joueurs présents pour le prochain tour de la Coupe de France</i></div>
					<?php endif; ?>
					<br>
					<br>
					<div class='liens'>
						<?php if ($this->_tpl_vars['profile'] <= 8 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<a href="#" onclick="setCheckboxes('formEquipeJoueur', 'checkEquipeJoueur', true);return false;"><img width="21" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" /></a>
							<a href="#" onclick="setCheckboxes('formEquipeJoueur', 'checkEquipeJoueur', false);return false;"><img width="21" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
							<a href="#" onclick="RemoveCheckboxes('formEquipeJoueur', 'checkEquipeJoueur')"><img width="16" src="../img/supprimer.gif" alt="Supprimer la sélection" title="Supprimer la sélection" /></a>
						<?php endif; ?>
				<!--		<a href="FeuillePresence.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF" title="Feuille de présence PDF"><img width="20" height="20" src="../img/pdf.gif" border="0"></a>						
						<a href="FeuillePresenceEN.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF - EN" title="Feuille de présence PDF - EN"><img width="20" height="20" src="../img/pdfEN.gif" border="0"></a>						
				-->
						<a href="FeuilleTitulaires.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF" title="Feuille de présence PDF"><img width="20" src="../img/pdf.gif" /></a>						
						<a href="FeuilleTitulairesEN.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF - EN" title="Feuille de présence PDF - EN"><img width="20" src="../img/pdfEN.gif" /></a>						
						<select name='idEquipe' id='idEquipe' onChange="changeEquipe();">
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?> 
								<Option Value="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" <?php if ($this->_tpl_vars['idEquipe'] == $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
 - <?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
							<?php endfor; endif; ?>
						</select>
						<button id='actuButton' type="button" onclick="submit()"><img src="../img/actualiser.gif" />Recharger</button>
					</div>
					<div class='blocTable'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr class='header'>
									<th>&nbsp;</th>
									<th>N°</th>
									<th>Cap.</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Licence</th>
									<th>Club</th>
									<th>Cat.-Sexe</th>
									<th>Pagaie<br />eau calme</th>
									<th>Certificat<br /><span title="CK">Compétition</span></th>
									<th>Arb.</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayJoueur']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?> 
									<?php if (( $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'E' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'A' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'X' ) && $this->_tpl_vars['test'] != 'OK'): ?>
									<?php $this->assign('test', 'OK'); ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td><br><br></td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
									<?php endif; ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
  colorCap<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
'>
										<?php if ($this->_tpl_vars['profile'] <= 7 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td><input type="checkbox" name="checkEquipeJoueur" value="<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
											<td width="30" class='directInput text' tabindex='1<?php echo $this->_sections['i']['iteration']; ?>
0'><span href="#" Id="Numero-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
-<?php echo $this->_tpl_vars['idEquipe']; ?>
"><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero']; ?>
</span></td>
											<!--<td><a href="#" Id="numero<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
" onclick="DoNumero(<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
,'<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero']; ?>
')"><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero']; ?>
</a></td>-->
											<td class='directSelect colorCap<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
'>
												<span Id="Capitaine-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
-<?php echo $this->_tpl_vars['idEquipe']; ?>
" class='tooltip'
													title="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'C', 'Capitaine') : smarty_modifier_replace($_tmp, 'C', 'Capitaine')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'A', 'Arbitre') : smarty_modifier_replace($_tmp, 'A', 'Arbitre')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'E', 'Entraineur') : smarty_modifier_replace($_tmp, 'E', 'Entraineur')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'X', 'Inactif') : smarty_modifier_replace($_tmp, 'X', 'Inactif')); ?>
"><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
</span>
												<!--<a href="#" Id="Capitaine<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
" onclick="choixRadioCapitaine('compet', '<?php echo $this->_tpl_vars['idEquipe']; ?>
','<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
','<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
')"><?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'N'): ?>&nbsp;<?php else: ?><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
<?php endif; ?></a>-->
											</td>
										<?php else: ?>
											<td>&nbsp;</td>
											<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero']; ?>
</td>
											<td class='colorCap<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
'><?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'N'): ?>&nbsp;<?php else: ?><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
<?php endif; ?></td>
										<?php endif; ?>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Nom']; ?>
</td>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
										<td>
											<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
<?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Saison'] < $this->_tpl_vars['sSaison']): ?> <span class='highlight2'>(<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Saison']; ?>
)</span><?php endif; ?>
											<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
											<?php endif; ?>
										</td>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero_club']; ?>
</td>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Categ']; ?>
 - <?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
										<td <?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == '' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == 'PAGB' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == 'PAGJ'): ?> class='highlight2'<?php endif; ?>>
											<img width="16" src="../img/EC-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA']; ?>
.gif" alt="Pagaie Eau Calme" title="Pagaie Eau Calme" />
											<!--<img hspace="1" width="16" height="16" src="../img/EV-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_EVI']; ?>
.gif" alt="Pagaie Eau Vive" title="Pagaie Eau Vive" border="0">
											<img hspace="1" width="16" height="16" src="../img/ME-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_MER']; ?>
.gif" alt="Pagaie Mer" title="Pagaie Mer" border="0">-->
										</td>
										<td><!--<span title='Loisir'><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['CertifAPS']; ?>
</span>/--><?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['CertifCK'] != 'OUI'): ?><span class='highlight2' title='Compétition'>NON</span><?php else: ?><span title='Compétition'>OUI</span><?php endif; ?></td>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Arbitre']; ?>
</td>
										<?php if ($this->_tpl_vars['profile'] <= 7 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td><a href="#" onclick="RemoveCheckbox('formEquipeJoueur', '<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
');return false;"><img width="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" /></a></td>
										<?php else: ?>
											<td>&nbsp;</td>
										<?php endif; ?>
									</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
						<div>
							Utilisez le statut <b>Inactif (X)</b> pour rendre indisponible un joueur sans le supprimer de la liste.
							<br>
							(joueurs absents ou suspendus sur un ou plusieurs matchs, sur une ou plusieurs journées)
							<br>
							Les entraineurs (E) et arbitres (A) ne sont pas comptabilisés dans les statistiques.
							<br>
							Les joueurs inactifs (X) et les arbitres (A) ne sont pas transférés sur les feuilles de match.
								<br>
								<br>
								<br>
								<a href="FeuilleTitulaires.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF" title="Feuille de présence PDF"><img width="20" src="../img/pdf.gif" />Feuille de présence PDF</a>						
							<?php if ($this->_tpl_vars['typeCompet'] == 'CH' || $this->_tpl_vars['typeCompet'] == 'CF'): ?>
								<br>
								<b>Les feuilles de présence doivent impérativement être saisies et mises à jour au plus tard 
								<br>7 jours avant chaque journée de Championnat de France et de Coupe de France.</b>
							<?php else: ?>
								<a href="FeuilleTitulairesEN.php?equipe=<?php echo $this->_tpl_vars['idEquipe']; ?>
" target="_blank" alt="Feuille de présence PDF - EN" title="Feuille de présence PDF - EN"><img width="20" src="../img/pdfEN.gif" />Feuille de présence PDF - EN</a>						
							<?php endif; ?>
						</div>
					</div>
					<?php if ($this->_tpl_vars['profile'] <= 7): ?>
						<div>
							<i>Dernier ajout ou suppression dans la liste des présents
							<br>le <?php echo $this->_tpl_vars['LastUpdate']; ?>
 par <?php echo $this->_tpl_vars['LastUpdater']; ?>
.</i>
						</div>
					<?php endif; ?>
					<div id='directSelecteur'>
						<select id='directSelecteurSelect' size=5>
							<option value='-'>Joueur</option>
							<option value='C'>C - Capitaine</option>
							<option value='E'>E - Entraineur (non joueur)</option>
							<option value='A'>A - Arbitre (non joueur)</option>
							<option value='X'>X - Inactif (non joueur)</option>
						</select>
						<!--<img id='validButton' width="16" height="16" src="../img/valider.gif" alt="Valider" title="Valider" border="0">-->
						<img id='annulButton' width="16" src="../img/annuler.gif" alt="Annuler" title="Annuler" />
						<input type=hidden id='variables' value='' />
					</div>

		        </div>
		        

			    <div class='blocRight'>
					<?php if ($this->_tpl_vars['profile'] <= 7 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O'): ?>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Sélectionner un athlète</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label class="rouge">Recherche (nom, prénom ou licence)</label>
									<input type="text" name="choixJoueur" id="choixJoueur"/>
									<hr>
								</td>
							</tr>
							<tr>
								<td width=60%>
									<label for="matricJoueur2">N° Licence :</label>
									<input type="text" name="matricJoueur2" readonly maxlength=10 id="matricJoueur2"/>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="nomJoueur2">Nom :</label>
									<input type="text" name="nomJoueur2" readonly maxlength=30 id="nomJoueur2"/>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="prenomJoueur2">Prénom :</label>
									<input type="text" name="prenomJoueur2" readonly maxlength=30 id="prenomJoueur2"/>
								</td>
							</tr>
							<tr>
								<td>
									<label for="naissanceJoueur2">Date Naissance :</label>
									<input type="text" name="naissanceJoueur2" readonly maxlength=10 id="naissanceJoueur2" >
									<input type="hidden" name="categJoueur2" id="categJoueur2" />
								</td>
								<td>
									<label for="sexeJoueur2">Sexe :</label>
									<input type="text" name="sexeJoueur2" readonly maxlength=1 id="sexeJoueur2" >
								</td>
							</tr>
							<tr>
								<td colspan=2><center><i>Optionnel :</i></center></td>
							</tr>
							<tr>
								<td>
									<label for="capitaineJoueur2">Capit./Entr./Arbitre:</label>
									<select name="capitaineJoueur2">
										<Option Value="-" SELECTED>Joueur</Option>
										<Option Value="C">Capitaine</Option>
										<Option Value="E">Entraineur (non joueur)</Option>
										<Option Value="A">Arbitre (non joueur)</Option>
										<Option Value="X">Inactif (non joueur)</Option>
									</select>
								</td>
								<td>
									<label for="numeroJoueur">Numero</label>
									<input type="text" name="numeroJoueur2" maxlength=2 id="numeroJoueur2">
								</td>
							</tr>
							<?php if ($this->_tpl_vars['typeCompet'] == 'CH' || $this->_tpl_vars['typeCompet'] == 'CF'): ?>
								<tr>
									<td colspan=2><center><i>Contrôle :</i></center></td>
								</tr>
								<tr>
									<td>
										Licence<br />
										Certif CK (Compet.)<br />
										Certif APS (Loisir)<br />
										Pagaie ECA<br />
										Cat.
									</td>
									<td>
										<span id="origineJoueur2"></span><br />
										<span id="CKJoueur2"></span><br />
										<span id="APSJoueur2"></span><br />
										<span id="pagaieJoueur2"></span><br />
										<span id="catJoueur2"></span><br />
									</td>
								</tr>
							<?php endif; ?>
							<tr>
								<td colspan=2 align="center">
									<span id="irregularite" class='highlight2'>Ce joueur n'est pas en règle<br />pour une compétition nationale</span>
									<br />
									<span id="motif" class='highlight2'></span>
									<?php if ($this->_tpl_vars['profile'] <= 3): ?>
										<br />
										<input type="button" onclick="Add2();" name="addEquipeJoueur3" id="addEquipeJoueur3" value="<< Ajouter">
									<?php else: ?>
										<br />
										<input type="button" onclick="Add2();" name="addEquipeJoueur2" id="addEquipeJoueur2" value="<< Ajouter">
									<?php endif; ?>
								</td>
							</tr>
						</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O'): ?>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Recherche avancée</label>
								</th>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="Find();" name="findJoueur" value="&reg; Recherche Licenciés...">
								</td>
							</tr>
						</table>
						<br>
						<br>

						<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['Verrou'] != 'O' && $this->_tpl_vars['AuthModif'] == 'O' && $this->_tpl_vars['typeCompet'] != 'CH' && $this->_tpl_vars['typeCompet'] != 'CF'): ?>
						<table width=100% alt="Si un licencié est introuvable dans les formulaires de recherche ci-dessus, pensez à mettre à jour la base des licenciés dans l'onglet Import"
							title="Si un licencié est introuvable dans les formulaires de recherche ci-dessus, pensez à mettre à jour la base des licenciés dans l'onglet Import">
							<tr>
								<th class='titreForm' colspan=2>
									<label>Créer & ajouter un licencié</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label class="rouge">UNIQUEMENT POUR LES NOUVEAUX<br>COMPETITEURS ETRANGERS</label>
									<hr>
								</td>
							</tr>
<!--							<tr>
								<td width=60%>
									
									<label for="matricJoueur">N° Licence (si connu) :</label>
									<input type="text" name="matricJoueur" maxlength=10 id="matricJoueur"/>
								</td>
							</tr>
-->							<tr>
								<td colspan=2>
									<label for="nomJoueur">Nom :</label>
									<input type="text" name="nomJoueur" maxlength=30 id="nomJoueur"/>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="prenomJoueur">Prénom :</label>
									<input type="text" name="prenomJoueur" maxlength=30 id="prenomJoueur"/>
								</td>
							</tr>
							<tr>
								<td>
									<label for="naissanceJoueur">Date Naissance :</label>
									<input type="text" name="naissanceJoueur" maxlength=10 id="naissanceJoueur" onfocus="displayCalendar(document.forms[0].naissanceJoueur,'dd/mm/yyyy',this)" >
								</td>
								<td>
									<label for="sexeJoueur">Sexe :</label>
									<select name="sexeJoueur" onChange="">
										<Option Value="M" SELECTED>Masculin</Option>
										<Option Value="F">Féminin</Option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2><center><i>Optionnel :</i></center></td>
							</tr>
							<tr>
								<td>
									<label for="capitaineJoueur">Capit./Entr./Arbitre:</label>
									<select name="capitaineJoueur">
										<Option Value="-" SELECTED>Joueur</Option>
										<Option Value="C">Capitaine</Option>
										<Option Value="E">Entraineur (non joueur)</Option>
										<Option Value="A">Arbitre (non joueur)</Option>
										<Option Value="X">Inactif (non joueur)</Option>
									</select>
								</td>
								<td>
									<label for="numeroJoueur">Numero</label>
									<input type="text" name="numeroJoueur" maxlength=2 id="numeroJoueur">
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<label for="arbitreJoueur">Niveau d'arbitrage :</label>
									<select name="arbitreJoueur">
										<Option Value="" SELECTED>--- Aucun ---</Option>
										<Option Value="REG">Régional</Option>
										<Option Value="IR">Inter-Régional</Option>
										<Option Value="NAT">National</Option>
										<Option Value="INT">International</Option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2>
									<br>
									<input type="button" onclick="Add();" name="addEquipeJoueur" value="<< Ajouter">
								</td>
							</tr>
						</table>
						<?php endif; ?>
							
					<?php endif; ?>
					<?php if ($this->_tpl_vars['Verrou'] == 'O'): ?>
						<b>Vous ne pouvez pas modifier les titulaires de cette équipe :</b>
						<br>
						- La compétition est verrouillée par le responsable ou le coordinateur,
						<br>
						- ou vous n'avez pas les droits sur ce club.
					<?php endif; ?>
			    </div>
						
			</form>			
		</div>	  	   

		