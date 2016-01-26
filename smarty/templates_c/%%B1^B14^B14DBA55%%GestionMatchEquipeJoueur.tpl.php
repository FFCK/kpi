<?php /* Smarty version 2.6.18, created on 2015-03-10 16:50:35
         compiled from GestionMatchEquipeJoueur.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionMatchEquipeJoueur.tpl', 43, false),array('modifier', 'replace', 'GestionMatchEquipeJoueur.tpl', 70, false),array('modifier', 'default', 'GestionMatchEquipeJoueur.tpl', 115, false),)), $this); ?>
    &nbsp;(<a href="GestionJournee.php">Retour</a>)
		<div class="main">
			<form method="POST" action="GestionMatchEquipeJoueur.php" name="formMatchEquipeJoueur" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Matchs_Joueurs'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Matric = '/>
				<input type='hidden' name='AjaxAnd' id='AjaxAnd' Value='And Id_match = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
'/>
				<div class='blocLeft'>
					<div class='titrePage' tabindex='1'>Joueurs de l'équipe <?php echo $this->_tpl_vars['infoEquipe']; ?>
<br>participant au match numéro <?php echo $this->_tpl_vars['Numero_ordre']; ?>
</div>
					<br>
					<?php if ($this->_tpl_vars['Validation'] != 'O'): ?>
						<div class='liens'>
							<a href="#" onclick="setCheckboxes('formMatchEquipeJoueur', 'checkJoueur', true);return false;"><img width="21" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" /></a>
							<a href="#" onclick="setCheckboxes('formMatchEquipeJoueur', 'checkJoueur', false);return false;"><img width="21" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
							<a href="#" onclick="RemoveCheckboxes('formMatchEquipeJoueur', 'checkJoueur')"><img width="16" src="../img/supprimer.gif" alt="Supprimer la sélection" title="Supprimer la sélection" /></a>
							<button id='actuButton' type="button" onclick="submit()"><img src="../img/actualiser.gif">Recharger</button>
						</div>
					<?php endif; ?>
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
									<?php if (( $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'E' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'A' ) && $this->_tpl_vars['test'] != 'OK'): ?>
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

									<?php if ($this->_tpl_vars['Validation'] == 'O' && $this->_tpl_vars['profile'] <= 6): ?>
										<td>&nbsp;</td>
										<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero']; ?>
</td>
										<td class='colorCap<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
'><?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'N'): ?>&nbsp;<?php else: ?><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
<?php endif; ?></td>
									<?php else: ?>
										<td><input type="checkbox" name="checkJoueur" value="<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
										<td width="30" class='directInput text' tabindex='1<?php echo $this->_sections['i']['iteration']; ?>
0'><span href="#" Id="Numero-<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
-<?php echo $this->_tpl_vars['idMatch']; ?>
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
-<?php echo $this->_tpl_vars['idMatch']; ?>
" class='tooltip'
													title="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'C', 'Capitaine') : smarty_modifier_replace($_tmp, 'C', 'Capitaine')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'A', 'Arbitre') : smarty_modifier_replace($_tmp, 'A', 'Arbitre')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'E', 'Entraineur') : smarty_modifier_replace($_tmp, 'E', 'Entraineur')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'X', 'Inactif') : smarty_modifier_replace($_tmp, 'X', 'Inactif')); ?>
"><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
</span>
											<!--<a href="#" Id="Capitaine<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
" onclick="choixRadioCapitaine('match', '<?php echo $this->_tpl_vars['idMatch']; ?>
','<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
','<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
')"><?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine'] == 'N'): ?>&nbsp;<?php else: ?><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Capitaine']; ?>
<?php endif; ?></a>-->
										</td>
									<?php endif; ?>
									<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Nom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Prenom']; ?>
</td>

									<td>
										<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
<?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Saison'] != $this->_tpl_vars['sSaison']): ?> <span class='highlight2'>(<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Saison']; ?>
)</span><?php endif; ?>
										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
											<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?>
									</td>
									<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Numero_club']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Categ']; ?>
 - <?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td<?php if ($this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == '' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == 'PAGB' || $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Pagaie_ECA'] == 'PAGJ'): ?> class='highlight2'<?php endif; ?>>
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
									<?php if ($this->_tpl_vars['Validation'] == 'O'): ?>
										<td>&nbsp;</td>
									<?php else: ?>
										<td><a href="#" onclick="RemoveCheckbox('formMatchEquipeJoueur', '<?php echo $this->_tpl_vars['arrayJoueur'][$this->_sections['i']['index']]['Matric']; ?>
');return false;"><img width="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" /></a></td>
									<?php endif; ?>
								</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
						<div>
							Les entraineurs et arbitres ne sont pas comptabilisés dans les statistiques.
						</div>
						<br>
						<br>
						<div class='liens'>
							<a href="#" onclick="CopieCompoEquipeJournee(<?php echo $this->_tpl_vars['idJournee']; ?>
)">Copier cette composition sur les autres matchs (non verrouillés) de la journée n°<?php echo $this->_tpl_vars['idJournee']; ?>
.</a>
							<br>
							<br>
							<?php if ($this->_tpl_vars['profile'] <= 4): ?>
							<a href="#" onclick="CopieCompoEquipeCompet(<?php echo $this->_tpl_vars['idJournee']; ?>
)">Copier cette composition sur les autres matchs (non verrouillés) de la compétition.</a>
							<br>
							<br>
							<?php endif; ?>
							<img width="21" src="../img/verrou<?php echo ((is_array($_tmp=@$this->_tpl_vars['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
" alt="Verrou" title="Verrou" />
						</div>
					</div>
					<div id='directSelecteur'>
						<select id='directSelecteurSelect' size=4>
							<option value='-'>Joueur</option>
							<option value='C'>Capitaine</option>
							<option value='E'>Entraineur (non joueur)</option>
						</select>
						<!--<img id='validButton' width="16" height="16" src="../img/valider.gif" alt="Valider" title="Valider" border="0">-->
						<img id='annulButton' width="16" src="../img/annuler.gif" alt="Annuler" title="Annuler" />
						<input type=hidden id='variables' value='' />
					</div>
		        </div>
		        <?php if ($this->_tpl_vars['Validation'] != 'O'): ?>
					<?php if ($this->_tpl_vars['profile'] <= 6 || $this->_tpl_vars['profile'] == 9): ?>
					<div class='blocRight'>
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
									<label for="capitaineJoueur2">Capit./Entr.:</label>
									<select name="capitaineJoueur2">
										<Option Value="" SELECTED>Joueur</Option>
										<Option Value="C">Capitaine</Option>
										<Option Value="E">Entraineur (non joueur)</Option>
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
								<td colspan=2>
									<br>
									<input type="button" onclick="Add2();" name="addEquipeJoueur2" value="<< Ajouter à ce match">
								</td>
							</tr>
						</table>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Recherche avancée</label>
								</th>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="FindLicence();" name="findLicence" value="&reg; Recherche Licenciés...">
								</td>
							</tr>
						</table>
						<br>
						<br>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Ajouter les joueurs présents</label>
								</th>
							</tr>
							<tr>
								<td>
									<label class="rouge">(sauf X-Inactifs)</label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="DelJoueurs();" name="delJoueurs" value="<< Supprimer tous les joueurs">
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" onclick="AddJoueurTitulaire();" name="addJoueurTitulaire" value="<< Ajouter les présents">
								</td>
							</tr>
						</table>
					</div>
					<?php endif; ?>
				<?php endif; ?>		
			</form>			
		</div>