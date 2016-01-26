<?php /* Smarty version 2.6.18, created on 2015-07-01 21:32:31
         compiled from GestionCompetition.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionCompetition.tpl', 73, false),array('modifier', 'default', 'GestionCompetition.tpl', 85, false),)), $this); ?>
		<div class="main">
			<form method="POST" action="GestionCompetition.php" name="formCompet" enctype="multipart/form-data">
						<input type='hidden' name='Cmd' Value=''/>
						<input type='hidden' name='ParamCmd' Value=''/>
						<input type='hidden' name='verrouCompet' Value=''/>
						<input type='hidden' name='Verrou' Value=''/>
						<input type='hidden' name='Pub' Value=''/>

			<?php if ($this->_tpl_vars['profile'] != 9): ?>
				<div class='blocLeft'>
					<h3 class='titrePage'>Compétitions / poules</h3>
					<br>
										<div class='liens'>		
						<label for="saisonTravail">Saison :</label>
						<select name="saisonTravail"  id="saisonTravail" onChange="sessionSaison()">
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaison']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
<?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?> (Travail)<?php endif; ?></Option>
							<?php endfor; endif; ?>
						</select>
						<label for="AfficheCompet">Afficher :</label>
						<select name="AfficheNiveau" onChange="changeAffiche()">
							<Option Value="" selected>Tous les niveaux</Option>
							<Option Value="INT"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'INT'): ?> selected<?php endif; ?>>Compétitions Internationales</Option>
							<Option Value="NAT"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'NAT'): ?> selected<?php endif; ?>>Compétitions Nationales</Option>
							<Option Value="REG"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'REG'): ?> selected<?php endif; ?>>Compétitions Régionales</Option>
						</select>
						<select name="AfficheCompet" onChange="changeAffiche()">
							<Option Value="" selected>Toutes les compétitions</Option>
							<Option Value="N"<?php if ($this->_tpl_vars['AfficheCompet'] == 'N'): ?> selected<?php endif; ?>>Championnat de France</Option>
							<Option Value="CF"<?php if ($this->_tpl_vars['AfficheCompet'] == 'CF'): ?> selected<?php endif; ?>>Coupe de France</Option>
							<Option Value="NCF"<?php if ($this->_tpl_vars['AfficheCompet'] == 'NCF'): ?> selected<?php endif; ?>>Championnat & Coupe de France</Option>
							<Option Value="REG"<?php if ($this->_tpl_vars['AfficheCompet'] == 'REG'): ?> selected<?php endif; ?>>Championnats régionaux</Option>
							<Option Value="DEP"<?php if ($this->_tpl_vars['AfficheCompet'] == 'DEP'): ?> selected<?php endif; ?>>Championnats départementaux</Option>
							<Option Value="T-"<?php if ($this->_tpl_vars['AfficheCompet'] == 'T-'): ?> selected<?php endif; ?>>Tournois Internationaux</Option>
							<?php if ($this->_tpl_vars['profile'] <= 4): ?>
								<Option Value="M"<?php if ($this->_tpl_vars['AfficheCompet'] == 'M'): ?> selected<?php endif; ?>>Modèles</Option>
							<?php endif; ?>
						</select>
					</div>
					<div class='blocTable' id='blocCompet'>
						<table class='tableau' id='tableCompet'>
							<thead> 
								<tr>
									<th width=18><img hspace="2" width="19" height="16" src="../img/oeil2.gif" alt="Publication de la compétition" title="Publication de la compétition" border="0"></th>
									<th width=63 title="Code">Code</th>
									<th title="Modifier">&nbsp;</th>
									<th title="Niveau">Niv.</th>
									<th title="Titre de la compétition">Libelle</th>
									<th width=63 title="Compétition de référence">Groupe</th>
									<th title="Tour/Phase">Tour</th>
									<th title="Type de compétition (Championnat ou Coupe/Tournoi)">Type</th>
									<th title="Statut">Statut</th>
									<th title="Nombre d'équipes affectées">Equipes</th>
									<th><img width="19" height="16" src="../img/verrou2.gif" alt="Verrouiller les feuilles de présence" title="Verrouiller les feuilles de présence" border="0"></th>
									<!--
									<th><img width="16" height="16" src="../img/up.gif" alt="Nb d'équipes qualifiées" title="Nb d'équipes qualifiées" border="0"></th>
									<th><img width="16" height="16" src="../img/down.gif" alt="Nb d'équipes éliminées" title="Nb d'équipes éliminées" border="0"></th>
									-->
									<th title="Nombre de matchs">Matchs</th>
									<th title="Suppression">&nbsp;</th>
								</tr>
							</thead> 
							
							<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompet']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
 <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['StdOrSelected']; ?>
'>
																				
										<td class='color<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication']; ?>
2'>
											<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O' && $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_ref'] != 'M'): ?>
											<!--
												<a href="#" Id="Publication<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" onclick="publiCompet('<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
', '<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication']; ?>
')">
													<img hspace="2" width="24" height="24" src="../img/oeil2<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication']; ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
												</a>
											-->	
												<img class="publiCompet" data-valeur="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" width="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="<?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication'] == 'O'): ?>Public<?php else: ?>Non public<?php endif; ?>" />
											<?php elseif ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_ref'] != 'M'): ?>
												<img width="24" src="../img/oeil2<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Publication']; ?>
.gif" alt="Publier O/N" title="Publier O/N" />
											<?php else: ?>-<?php endif; ?>
										</td>
										<td><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
</td>
										<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td><a href="#" Id="Param<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" onclick="paramCompet('<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
')"><img hspace="2" width="18" height="18" src="../img/b_edit.png" alt="Modifier" title="Modifier" border="0"></a></td>
										<?php else: ?><td>&nbsp;</td><?php endif; ?>
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_niveau'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<td	class="cliquableNomEquipe"
											title="<center>
											<?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Titre_actif'] == 'O'): ?><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Libelle']; ?>
<br><?php else: ?><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre']; ?>
<br><?php endif; ?>
											<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre2']; ?>

											<br>- - -
											<br>- - -
											<br>Qualifiés : <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Qualifies']; ?>

											<br>Eliminés : <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Elimines']; ?>

											<br>- - -
											<br><i><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['commentairesCompet']; ?>
</i><br><br>
											</center>"
										><a href='GestionDoc.php?Compet=<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
'><?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Titre_actif'] != 'O' && $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre'] != ''): ?><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Libelle']; ?>
<?php endif; ?><?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><br /><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php endif; ?></a></td>
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_ref'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<td><?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_tour'] == '10'): ?>F<?php else: ?><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code_tour'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
<?php endif; ?></td>
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['codeTypeClt'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<td title="Changer le statut">
											<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
												<span class="statutCompet statutCompet<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Statut']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" ><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Statut']; ?>
</span>
											<?php else: ?>
												<span class="statutCompet<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Statut']; ?>
"><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Statut']; ?>
</span>
											<?php endif; ?>
										</td>
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Nb_equipes'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<td alt="Verrouiller les feuilles de présence" title="Verrouiller les feuilles de présence">
											<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<!--
												<a href="#" Id="Verrou<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" onclick="verrou('<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
', '<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou']; ?>
')">
													<img width="24" height="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" border="0">
												</a>
											-->
												<img class="verrouCompet" data-valeur="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="<?php if ($this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou'] == 'O'): ?>Feuilles de présence verrouillées<?php else: ?>Feuilles de présence modifiables<?php endif; ?>" />
											<?php else: ?>
												<img width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Verrou'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouillage des Titulaires" title="Verrouillage des Titulaires" />
											<?php endif; ?>
										</td>
										<!--
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Qualifies'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Elimines'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										-->
										<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['nbMatchs'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
										<?php if ($this->_tpl_vars['profile'] <= 2 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td><a href="#" onclick="RemoveCheckbox('formCompet', '<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
');return false;"><img hspace="2" width="16" height="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" border="0"></a></td>
										<?php else: ?><td>&nbsp;</td><?php endif; ?>
									</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>
		        
  				<div class='blocRight'>
					<?php if ($this->_tpl_vars['profile'] == 9): ?>
					</form>
					<form method="GET" action="FeuilleMarque2.php" name="formCompet" enctype="multipart/form-data">
						<table width="100%" class='vert'>
							<tr>
								<th class='titreForm' colspan=2>
									<label class='maxWith'>Accès direct feuille de marque</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="accesFeuille" class='maxWith'>Identifiant feuille de marque : </label>
									<img border="0" title="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) " 
									alt="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque)" 
									src="../img/b_help.png" 
									onclick="alert('Saisissez l\'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) ')">
								</td>
							</tr>
							<tr>
								<td width="60%">
									<input class='maxWith newInput' type="tel" name="idMatch" maxlength=15 id="idMatch" />
								</td>
								<td>
									<input class='maxWith newBtn' type="submit" value="Accès" />
								</td>
							</tr>
						</table>
					<?php elseif ($this->_tpl_vars['profile'] <= 6): ?>
						<table width="100%" class='vert'>
							<tr>
								<th class='titreForm' colspan=2>
									<label class='maxWith'>Accès direct feuille de marque</label>
								</th>
							</tr>
							<tr>
								<td colspan=2>
									<label for="accesFeuille" class='maxWith'>Identifiant feuille de marque : </label>
									<img border="0" title="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) " 
									alt="Saisissez l'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque)" 
									src="../img/b_help.png" 
									onclick="alert('Saisissez l\'identifiant de la feuille de marque (numéro précédé de ID# en bas à droite de la feuille de marque) ')">
								</td>
							</tr>
							<tr>
								<td width="60%">
									<input class='maxWith newInput' type="tel" name="accesFeuille" maxlength=15 id="accesFeuille" />
								</td>
								<td>
									<input class='maxWith newBtn' type="button" name="accesFeuilleBtn" id="accesFeuilleBtn" value="Accès" />
								</td>
							</tr>
						</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
						<table width="100%">
							<tr>
								<th class='titreForm' colspan=4>
									<label class='maxWith'><?php if ($this->_tpl_vars['editCompet'] == ''): ?>Ajouter une <?php else: ?>Modifier la <?php endif; ?>compétition</label>
								</th>
							</tr>
							<?php if ($this->_tpl_vars['editCompet'] == ''): ?>
							<tr>
								<td colspan=4>
									<label for="choixCompet" class='maxWith'>Sélectionner la compétition : </label>
									<img border="0" title="Saisissez les premier caractères d'un code compétition, sélectionnez un code existant et ajustez tous les paramètres. Si la compétition a créer n'existe pas, demander au webmaster." 
									alt="Saisissez les premier caractères d'un code compétition, sélectionnez un code existant et ajustez tous les paramètres. Si la compétition a créer n'existe pas, demander au webmaster." 
									src="../img/b_help.png" 
									onclick="alert('Saisissez les premier caractères d\'un code compétition, \nsélectionnez un code existant \net ajustez tous les paramètres.\n\nSi la compétition a créer n\'existe pas, demandez au webmaster.')">
									<input class='maxWith' type="text" name="choixCompet" maxlength=50 id="choixCompet" />
								</td>
							</tr>
							<tr>
								<td width=55% colspan=2>
									<label for="codeCompet">Code :</label>
									<input type="text" name="codeCompet" maxlength=12 id="codeCompet" <?php if ($this->_tpl_vars['user'] == '42054' || $this->_tpl_vars['user'] == '63155'): ?>class='gris'<?php else: ?>readonly<?php endif; ?> <?php if ($this->_tpl_vars['editCompet'] != ''): ?>value="<?php echo $this->_tpl_vars['codeCompet']; ?>
"<?php endif; ?> />
								</td>
								<td colspan=2>
									<label for="niveauCompet">Niveau : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG"<?php if ($this->_tpl_vars['niveauCompet'] == 'REG'): ?> selected<?php endif; ?>>REG-Régional</Option>
										<Option Value="NAT"<?php if ($this->_tpl_vars['niveauCompet'] == 'NAT' || $this->_tpl_vars['niveauCompet'] == ''): ?> selected<?php endif; ?>>NAT-National</Option>
										<Option Value="INT"<?php if ($this->_tpl_vars['niveauCompet'] == 'INT'): ?> selected<?php endif; ?>>INT-International</Option>
									</select>
								</td>
							</tr>
							<?php else: ?>
							<tr>
								<td width=55% colspan=2>
									<label for="codeCompet">Code :</label>
									<input type="text" name="codeCompet" maxlength=12 id="codeCompet" readonly value="<?php echo $this->_tpl_vars['codeCompet']; ?>
" />
								</td>
								<td colspan=2>
									<label for="niveauCompet">Niveau : </label>
									<select name="niveauCompet" id="niveauCompet" onChange="">
										<Option Value="REG"<?php if ($this->_tpl_vars['niveauCompet'] == 'REG'): ?> selected<?php endif; ?>>REG-Régional</Option>
										<Option Value="NAT"<?php if ($this->_tpl_vars['niveauCompet'] == 'NAT' || $this->_tpl_vars['niveauCompet'] == ''): ?> selected<?php endif; ?>>NAT-National</Option>
										<Option Value="INT"<?php if ($this->_tpl_vars['niveauCompet'] == 'INT'): ?> selected<?php endif; ?>>INT-International</Option>
									</select>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<td colspan=4>
									<label for="labelCompet">Libellé : </label>
									<input type="text" name="labelCompet" value="<?php echo $this->_tpl_vars['labelCompet']; ?>
" maxlength=50 id="labelCompet" <?php if ($this->_tpl_vars['user'] == '42054' || $this->_tpl_vars['user'] == '63155'): ?>class='gris'<?php else: ?>readonly<?php endif; ?> />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Exemple : <br>ICF World Championships - Milan (ITA)<br>'>
									<hr>
									<label for="soustitre">Libellé 2<br>
									<i>Titre public - Ville (CODE PAYS CIO)</i></label>
									<input type="text" name="soustitre" id="soustitre" maxlength=80 value="<?php echo $this->_tpl_vars['soustitre']; ?>
" />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Exemples : <br>Women U21, Men, Tournoi 1, 2nd Division<br>'>
									<label for="soustitre2">Catégorie (compétitions internationales)<br>
									<i>Men, Women U21, Tournoi 1...</i></label>
									<input type="text" name="soustitre2" id="soustitre2" maxlength=80 value="<?php echo $this->_tpl_vars['soustitre2']; ?>
" />
								</td>
							</tr>
							<tr>
								<td colspan=3>
									<label for="codeRef">Groupe :</label>
									<select name="codeRef" id="codeRef">
										<Option Value="">= OBLIGATOIRE =</Option>
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayGroupCompet']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<Option Value="<?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Groupe']; ?>
"<?php if ($this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Groupe'] == $this->_tpl_vars['codeRef']): ?> selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Groupe']; ?>
 - <?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php endfor; endif; ?>
									</select>
								</td>
								<td>
									<label for="groupOrder">Ordre :</label>
									<input type="text" name="groupOrder" value="<?php echo $this->_tpl_vars['groupOrder']; ?>
" maxlength=1 id="groupOrder" />
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<label for="codeTypeClt">Type : </label>
									<select name="codeTypeClt" id="codeTypeClt" onChange="changeCodeTypeClt();">
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayTypeClt']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayTypeClt'][$this->_sections['i']['index']][0]; ?>
"<?php if ($this->_tpl_vars['arrayTypeClt'][$this->_sections['i']['index']][0] == $this->_tpl_vars['codeTypeClt']): ?> selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayTypeClt'][$this->_sections['i']['index']][1]; ?>
</Option>
										<?php endfor; endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2 width=55%>
									<label for="etape">Tour/Phase :</label>
									<select name="etape" id="etape">
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=6) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['start'] = (int)1;
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
if ($this->_sections['i']['start'] < 0)
    $this->_sections['i']['start'] = max($this->_sections['i']['step'] > 0 ? 0 : -1, $this->_sections['i']['loop'] + $this->_sections['i']['start']);
else
    $this->_sections['i']['start'] = min($this->_sections['i']['start'], $this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] : $this->_sections['i']['loop']-1);
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = min(ceil(($this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] - $this->_sections['i']['start'] : $this->_sections['i']['start']+1)/abs($this->_sections['i']['step'])), $this->_sections['i']['max']);
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
											<Option Value="<?php echo $this->_sections['i']['index']; ?>
"<?php if ($this->_sections['i']['index'] == $this->_tpl_vars['etape']): ?> selected<?php endif; ?>><?php echo $this->_sections['i']['index']; ?>
</Option>
										<?php endfor; endif; ?>
											<Option Value="10"<?php if ($this->_tpl_vars['etape'] == 10 || $this->_tpl_vars['etape'] == ''): ?> selected<?php endif; ?>>Unique/Finale</Option>
									</select>
								</td>
								<td>
									<label for="qualifies">Qualifiés</label>
									<input type="text" name="qualifies" id="qualifies" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['qualifies'])) ? $this->_run_mod_handler('default', true, $_tmp, '3') : smarty_modifier_default($_tmp, '3')); ?>
" />
								</td>
								<td>
									<label for="elimines">Eliminés</label>
									<input type="text" name="elimines" id="elimines" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['elimines'])) ? $this->_run_mod_handler('default', true, $_tmp, '0') : smarty_modifier_default($_tmp, '0')); ?>
" />
								</td>
							</tr>
							<tr>
								<td colspan=4 title='Points pour chaque match Gagné, Null, Perdu, Forfait'>
									<label for="points">Points G-N-P-F : </label>
									<input type="radio" name="points" value='4-2-1-0' <?php if ($this->_tpl_vars['points'] == '4-2-1-0'): ?>checked<?php endif; ?>><label>4-2-1-0</label>
									<input type="radio" name="points" value='3-1-0-0' <?php if ($this->_tpl_vars['points'] == '3-1-0-0'): ?>checked<?php endif; ?>><label>3-1-0-0</label>
								</td>
							</tr>
							<tr>
								<td colspan=4>
									<hr />
									<label for="web">Site Web</label>
									<input type="text" name="web" id="web" maxlength=80 value="<?php echo $this->_tpl_vars['web']; ?>
" />
								</td>
							</tr>
							<?php if ($this->_tpl_vars['editCompet'] == ''): ?>
								<tr>
									<td colspan=4>
										<label for="logoLink">Lien image logo compet :</label>
										<input type="text" id="logoLink" name="logoLink">
										<br>
										<img hspace="2" width="200" src="" border="0" id='logoprovisoire'>
										<br>
									</td>
								</tr>
								<?php if ($this->_tpl_vars['profile'] <= 2 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
									<tr>
										<td colspan=4>
											<label for="sponsorLink">Lien image sponsor compet :</label>
											<input type="text" id="sponsorLink" name="sponsorLink">
											<br>
											<img hspace="2" width="200" src="" border="0" id='sponsorprovisoire'>
											<br>
										</td>
									</tr>
<!--									<tr>
										<td colspan=4>
											<label for="toutGroup">Attribuer à :</label>
											<br>
											<input type="checkbox" name="toutGroup" id="toutGroup" value='O' <?php if ($this->_tpl_vars['toutGroup'] == 'O'): ?>checked<?php endif; ?>><label>tout le groupe</label>
											<input type="checkbox" name="touteSaisons" id="touteSaisons" value='O' <?php if ($this->_tpl_vars['touteSaisons'] == 'O'): ?>checked<?php endif; ?>><label>toutes les saisons</label>
										</td>
									</tr>
-->									<tr>
										<td colspan=4>
											<label for="logo_actif">Activer :</label>
											<br>
											<input type="checkbox" name="kpi_ffck_actif" id="kpi_ffck_actif" value='O' <?php if ($this->_tpl_vars['kpi_ffck_actif'] != ''): ?>checked<?php endif; ?>><label>Logo KPI/FFCK</label>
											<br>
											<input type="checkbox" name="titre_actif" id="titre_actif" value='O' <?php if ($this->_tpl_vars['titre_actif'] != ''): ?>checked<?php endif; ?>><label>Libellé (sinon : libellé 2)</label>
											<br>
											<input type="checkbox" name="logo_actif" id="logo_actif" value='O' <?php if ($this->_tpl_vars['logo_actif'] == 'O'): ?>checked<?php endif; ?>><label>Logo compétition</label>
											<br>
											<input type="checkbox" name="sponsor_actif" id="sponsor_actif" value='O' <?php if ($this->_tpl_vars['sponsor_actif'] == 'O'): ?>checked<?php endif; ?>><label>Image Sponsor</label>
											<br>
											<input type="checkbox" name="en_actif" id="en_actif" value='O' <?php if ($this->_tpl_vars['en_actif'] != ''): ?>checked<?php endif; ?>><label>Compétition en Anglais</label>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<label for="statut">Statut :</label>
										</td>
										<td colspan="2">
											<select name="statut" id="statut">
												<option value="ATT" <?php if ($this->_tpl_vars['statut'] == 'ATT'): ?>selected<?php endif; ?>>Attente (ATT)</option>
												<option value="ON" <?php if ($this->_tpl_vars['statut'] == 'ON'): ?>selected<?php endif; ?>>En cours (ON)</option>
												<option value="END" <?php if ($this->_tpl_vars['statut'] == 'END'): ?>selected<?php endif; ?>>Terminée (END)</option>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="4">
											<label>Publier la compétition</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O' <?php if ($this->_tpl_vars['publierCompet'] == 'O'): ?>checked<?php endif; ?>>
										</td>
									</tr>
								<?php endif; ?>
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<hr>
										<label><b>Insertion dans le calendrier public</b>
										<img border="0" title="Si vous remplissez cette section, une journée sera insérée dans le calendrier mais non publiée." 
										alt="Si vous remplissez cette section, une journée sera insérée dans le calendrier mais non publiée." 
										src="../img/b_help.png" 
										onclick="alert('Si vous remplissez cette section, une journée sera insérée dans le calendrier\n mais non publiée.')">
										<br>(journée unique, optionnel)</label>
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<label for="TitreJournee">Nom de la journée</label>
										<input type="text" name="TitreJournee" id="TitreJournee" value="">
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=2>
										<label for="Date_debut">Date_debut</label>
										<input type="text" class='date' name="Date_debut" id="Date_debut" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" >
									</td>
									<td colspan=2>
										<label for="Date_fin">Date_fin</label>
										<input type="text" class='date' name="Date_fin" id="Date_fin" value="<?php echo $this->_tpl_vars['Date_fin']; ?>
" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" >
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=3>
										<label for="Lieu">Lieu</label>
										<input type="text" name="Lieu" id="Lieu" value="<?php echo $this->_tpl_vars['Lieu']; ?>
"/>
									</td>
									<td>
										<label for="Departement">Dpt/Pays</label>
										<input type="text" class='dpt' name="Departement" id="Departement" value="<?php echo $this->_tpl_vars['Departement']; ?>
"/>
									</td>
								</tr>
								<tr class='ajoutCalendrier'>
									<td colspan=4>
										<label>Publier la journee</label><input type="checkbox" name="publierJournee" id="publierJournee" value='O'>
									</td>
								</tr>
								<tr>
									<td colspan=4>
										<br>
										<input type="button" onclick="Add();" name="addCompet" value="<< Ajouter">
									</td>
								</tr>
							<?php else: ?>
								<tr>
									<td colspan=4 align=center>
										<label for="logoLink"><b>Lien image logo :</b></label>
										<input type="text" id="logoLink" name="logoLink" value="<?php echo $this->_tpl_vars['logoLink']; ?>
">
											<img hspace="2" id='logoprovisoire' width="200" src="<?php echo $this->_tpl_vars['logo']; ?>
" alt="Logo actuel de la compétition" title="Logo actuel de la compétition" border="0">
										<br>
										<label for="sponsorLink"><b>Lien image sponsor :</b></label>
										<input type="text" id="sponsorLink" name="sponsorLink" value="<?php echo $this->_tpl_vars['sponsorLink']; ?>
">
											<img hspace="2" id='sponsorprovisoire' width="200" src="<?php echo $this->_tpl_vars['sponsor']; ?>
" alt="Sponsor actuel de la compétition" title="Sponsor actuel de la compétition" border="0">
									</td>
								</tr>
																<tr>
									<td colspan=4>
										<label for="logo_actif">Activer :</label>
										<br>
										<input type="checkbox" name="kpi_ffck_actif" id="kpi_ffck_actif" value='O' <?php if ($this->_tpl_vars['kpi_ffck_actif'] != ''): ?>checked<?php endif; ?>><label>Logo KPI/FFCK</label>
										<br>
										<input type="checkbox" name="titre_actif" id="titre_actif" value='O' <?php if ($this->_tpl_vars['titre_actif'] != ''): ?>checked<?php endif; ?>><label>Libellé (sinon : libellé 2)</label>
										<br>
										<input type="checkbox" name="logo_actif" id="logo_actif" value='O' <?php if ($this->_tpl_vars['logo_actif'] == 'O'): ?>checked<?php endif; ?>><label>Logo compétition</label>
										<br>
										<input type="checkbox" name="sponsor_actif" id="sponsor_actif" value='O' <?php if ($this->_tpl_vars['sponsor_actif'] == 'O'): ?>checked<?php endif; ?>><label>Image Sponsor</label>
										<br>
										<input type="checkbox" name="en_actif" id="en_actif" value='O' <?php if ($this->_tpl_vars['en_actif'] != ''): ?>checked<?php endif; ?>><label>Compétition en Anglais</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="statut">Statut :</label>
									</td>
									<td colspan="2">
										<select name="statut" id="statut">
											<option value="ATT" <?php if ($this->_tpl_vars['statut'] == 'ATT'): ?>selected<?php endif; ?>>Attente (ATT)</option>
											<option value="ON" <?php if ($this->_tpl_vars['statut'] == 'ON'): ?>selected<?php endif; ?>>En cours (ON)</option>
											<option value="END" <?php if ($this->_tpl_vars['statut'] == 'END'): ?>selected<?php endif; ?>>Terminée (END)</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<label>Publier la compétition</label><input type="checkbox" name="publierCompet" id="publierCompet" value='O' <?php if ($this->_tpl_vars['publierCompet'] == 'O'): ?>checked<?php endif; ?>>
									</td>
								</tr>
								<tr>
									<td colspan=4>
										<label for="commentairesCompet">Commentaires (non public) :</label>
										<br>
										<textarea name="commentairesCompet" rows=5 cols=27 id="commentairesCompet" wrap="soft"><?php echo $this->_tpl_vars['commentairesCompet']; ?>
</textarea>
									</td>
								</tr>
								<tr>
									<td colspan=2>
										<br>
										<input type="button" onclick="updateCompet()" id="updateCompetition" name="updateCompetition" value="<< Modifier">
									</td>
									<td colspan=2>
										<br>
										<input type="button" onclick="razCompet()" id="razCompetition" name="razCompetition" value="Annuler">
									</td>
								</tr>
							<?php endif; ?>
						</table>
					<?php else: ?>
						<table width="100%">
							<tr>
								<td align=center>
									<img hspace="2" width="200" src="<?php echo $this->_tpl_vars['logo']; ?>
" alt="" border="0">
								</td>
							</tr>
						</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 4): ?>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Copie de structure de matchs</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<a href="GestionCopieCompetition.php">Copier la struture des matchs d'une compétition à l'autre...</a>
							</td>
						</tr>
					</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 2 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
					<br>
					<br>
					<hr>
					<div align='center' class='rouge'><i>Profils 1 - 2</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Changer de saison (publique)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="saisonActive"><b>Saison active :</b></label>
								<select name="saisonActive" onChange="activeSaison()">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaison']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Etat'] == 'A'): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
<?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Etat'] == 'A'): ?> (Active)<?php endif; ?></Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Ajouter une saison</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Saison :</label>
								<input type="text" name="newSaison">
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>
								<label for="newSaison">Debut National</label>
								<input type="text" class='date' name="newSaisonDN" onfocus="displayCalendar(document.forms[0].newSaisonDN,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFN">Fin National</label>
								<input type="text" class='date' name="newSaisonFN" onfocus="displayCalendar(document.forms[0].newSaisonFN,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td>
								<label for="newSaisonDI">Debut International</label>
								<input type="text" class='date' name="newSaisonDI" onfocus="displayCalendar(document.forms[0].newSaisonDI,'dd/mm/yyyy',this)" >
							</td>
							<td>
								<label for="newSaisonFI">Fin International</label>
								<input type="text" class='date' name="newSaisonFI" onfocus="displayCalendar(document.forms[0].newSaisonFI,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" name="AjoutSaison" onclick="AddSaison();" value="Créer">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Fusionner des licenciés</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="FusionSource">Source (sera supprimé)</label>
								<input type="hidden" name="numFusionSource" id="numFusionSource">
								<input type="text" name="FusionSource" size=40 id="FusionSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="FusionCible">Cible (sera conservé)</label>
								<input type="hidden" name="numFusionCible" id="numFusionCible">
								<input type="text" name="FusionCible" size=40 id="FusionCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="FusionJoueurs" id="FusionJoueurs" value="Fusionner">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Renommer une équipe</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="RenomSource">Source (ancien nom)</label>
								<input type="hidden" name="numRenomSource" id="numRenomSource">
								<input type="text" name="RenomSource" size=40 id="RenomSource">
							</td>
						</tr>
						<tr>
							<td>
								<label for="RenomCible">Cible (nouveau nom)</label>
								<input type="text" name="RenomCible" size=40 id="RenomCible">
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" name="RenomEquipe" id="RenomEquipe" value="Renommer">
							</td>
						</tr>
					</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 4): ?>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Verrou saisons précédentes</label>
							</th>
						</tr>
						<tr>
							<td colspan=2 align="center">
								<label for="AuthSaison">Verrou</label>
								<?php if ($this->_tpl_vars['AuthSaison'] == 'O'): ?><b><i>INACTIF</i></b>
								<?php else: ?><b>ACTIF</b>
								<?php endif; ?>
							</td>
						</tr>
						<?php if ($this->_tpl_vars['profile'] <= 2): ?>
						<tr>
							<td>
								<input type="button" name="ChangeAuthSaison" id="ChangeAuthSaison" onclick="changeAuthSaison()" value="Changer">
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td colspan=2 align="center">
								<?php if ($this->_tpl_vars['AuthModif'] == 'O'): ?><b><i>Compétitions déverrouillées.</i></b>
								<?php else: ?><b>Par mesure de sécurité, les compétitions des saisons précédentes sont verrouillées.</b>
								<?php endif; ?>
							</td>
						</tr>
					</table>
					<br>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['user'] == '42054' || $this->_tpl_vars['user'] == '63155'): ?>
					<hr>
					<div align='center' class='rouge'><i>User Laurent</i></div>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=2>
								<label>Tester un autre profil</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="profilTest">Tester un autre profil</label>
								<select name="profilTest" onChange="submit();">
									<option value="1" <?php if ($this->_tpl_vars['profile'] == 1): ?>Selected<?php endif; ?>>1 - Webmaster / Président</option>
									<option value="2" <?php if ($this->_tpl_vars['profile'] == 2): ?>Selected<?php endif; ?>>2 - Bureau</option>
									<option value="3" <?php if ($this->_tpl_vars['profile'] == 3): ?>Selected<?php endif; ?>>3 - Resp. Compétition</option>
									<option value="4" <?php if ($this->_tpl_vars['profile'] == 4): ?>Selected<?php endif; ?>>4 - Resp. Poule</option>
									<option value="5" <?php if ($this->_tpl_vars['profile'] == 5): ?>Selected<?php endif; ?>>5 - Délégué fédéral</option>
									<option value="6" <?php if ($this->_tpl_vars['profile'] == 6): ?>Selected<?php endif; ?>>6 - Organisateur Journée</option>
									<option value="7" <?php if ($this->_tpl_vars['profile'] == 7): ?>Selected<?php endif; ?>>7 - Resp. Club / Equipe</option>
									<option value="8" <?php if ($this->_tpl_vars['profile'] == 8): ?>Selected<?php endif; ?>>8 - Consultation simple</option>
									<option value="9" <?php if ($this->_tpl_vars['profile'] == 9): ?>Selected<?php endif; ?>>9 - Table de Marque</option>
									<option value="10" <?php if ($this->_tpl_vars['profile'] == 10): ?>Selected<?php endif; ?>>10 - Non utilisé</option>
								</select>
							</td>
						</tr>
					</table>
					<?php endif; ?>
		        </div>
			</form>			
		</div>
	