<?php /* Smarty version 2.6.18, created on 2015-04-08 23:24:22
         compiled from Historique.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'Historique.tpl', 16, false),array('function', 'cycle', 'Historique.tpl', 126, false),)), $this); ?>
 	 	<span class="repere">&nbsp;(<a href="Classements.php"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)</span>
	
		<div class="main">
			<form method="POST" action="Historique.php" name="formHistorique" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre2'>
					<div class='titrePage'><?php echo $this->_config[0]['vars']['Classement']; ?>
</div>
					<br>
					<div class='soustitrePage'>
						<label for="codeCompetGroup"><?php echo $this->_config[0]['vars']['Competition']; ?>
 :</label>
						<select name="codeCompetGroup" onChange="submit();">
								<Option Value=""><?php echo $this->_config[0]['vars']['Selectionnez']; ?>
...</Option>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetitionGroupe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<?php $this->assign('temporaire', $this->_tpl_vars['arrayCompetitionGroupe'][$this->_sections['i']['index']][1]); ?>
								<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionGroupe'][$this->_sections['i']['index']][1]; ?>
" <?php echo $this->_tpl_vars['arrayCompetitionGroupe'][$this->_sections['i']['index']][3]; ?>
><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['temporaire']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['arrayCompetitionGroupe'][$this->_sections['i']['index']][2]) : smarty_modifier_default($_tmp, @$this->_tpl_vars['arrayCompetitionGroupe'][$this->_sections['i']['index']][2])); ?>
</Option>
							<?php endfor; endif; ?>
						</select>
					</div>
					<br>
					<div>
						<?php if ($this->_tpl_vars['recordCompetition'][0]['Web'] != ''): ?>
							<a href='<?php echo $this->_tpl_vars['recordCompetition'][0]['Web']; ?>
' target='_blank'>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['recordCompetition'][0]['LogoLink'] != ''): ?>
								<img hspace="2" height="100" border="0" id='logo' src='<?php echo $this->_tpl_vars['recordCompetition'][0]['LogoLink']; ?>
'>
								<br>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['recordCompetition'][0]['Web'] != ''): ?>
								<?php echo $this->_tpl_vars['recordCompetition'][0]['Web']; ?>

							</a>
							<br>
						<?php endif; ?>
					</div>
					<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['CodeCompet']): ?>
					<div>
						<?php $this->assign('idCompet', $this->_tpl_vars['arrayEquipe_publi'][0]['CodeCompet']); ?>
						<?php $this->assign('idGroupe', $this->_tpl_vars['arrayEquipe_publi'][0]['CodeGroupe']); ?>
						<?php $this->assign('idTour', $this->_tpl_vars['arrayEquipe_publi'][0]['Code_tour']); ?>
						<?php $this->assign('idSaison', $this->_tpl_vars['arrayEquipe_publi'][0]['CodeSaison']); ?>
						<div class='histoLigne'>
								<div class='histoColonne'>
									<br>
									<b><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['CodeSaison']; ?>
</b>
									<br>
									<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['LogoLink'] != ''): ?>
										<br>
										<img hspace="2" width="110" border="0" id='logo' src='<?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['LogoLink']; ?>
'>
									<?php endif; ?>
								</div>
							<div class='histoColonne'>
								<table class='tableau tableauPublic'>
									<thead>
										<tr>
											<th width='130' style='word-wrap: break-word;' colspan=3 <?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Code_tour'] == '10'): ?>class='TitrePhaseFinale'<?php endif; ?>>
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Titre_actif'] != 'O' && $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre'] != ''): ?>
													<?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre']; ?>

												<?php else: ?>
													<?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['LibelleCompet']; ?>

												<?php endif; ?>
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre2'] != ''): ?><br><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre2']; ?>
<?php endif; ?>
											</th>
										</tr>
																		</thead>
									<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe_publi']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau'] > 0 || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt'] > 0): ?>
									<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeCompet'] != $this->_tpl_vars['idCompet'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeGroupe'] != $this->_tpl_vars['idGroupe'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison'] != $this->_tpl_vars['idSaison']): ?>
											</tbody>
										</table>
										<div class='centre'>
											<i>
											<a  class='grispetit' href='Classement.php?Group=<?php echo $this->_tpl_vars['idGroupe']; ?>
&Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
&Saison=<?php echo $this->_tpl_vars['idSaison']; ?>
'><?php echo $this->_config[0]['vars']['Details']; ?>
</a>
											<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['existMatch'] == 1): ?>
												&nbsp;&nbsp;&nbsp;<a  class='grispetit' href='Journee.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
&Saison=<?php echo $this->_tpl_vars['idSaison']; ?>
'><?php echo $this->_config[0]['vars']['Matchs']; ?>
</a>
											<?php endif; ?>
											</i>
											<br><br>
										</div>
									<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeGroupe'] != $this->_tpl_vars['idGroupe'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison'] != $this->_tpl_vars['idSaison'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?></div><?php endif; ?>
									<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison'] != $this->_tpl_vars['idSaison']): ?>
										</div>
										<div class='histoLigne'>
											<div class='histoColonne'>
												<br>
												<b><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison']; ?>
</b>
												<br>
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['LogoLink'] != ''): ?>
													<br>
													<img hspace="2" width="110" border="0" id='logo' src='<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['LogoLink']; ?>
'>
												<?php endif; ?>
											</div>
									<?php endif; ?>
									<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeGroupe'] != $this->_tpl_vars['idGroupe'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison'] != $this->_tpl_vars['idSaison'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?><div class='histoColonne'><?php endif; ?>
										<?php $this->assign('idCompet', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeCompet']); ?>
										<?php $this->assign('idGroupe', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeGroupe']); ?>
										<?php $this->assign('idTour', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour']); ?>
										<?php $this->assign('idSaison', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison']); ?>
										<?php $this->assign('ordre', 0); ?>
										<table class='tableau tableauPublic'>
											<thead>
												<tr>
													<th width='130' style='word-wrap: break-word;' colspan=3 <?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10'): ?>class='TitrePhaseFinale'<?php endif; ?>>
														<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Titre_actif'] != 'O' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre'] != ''): ?>
															<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre']; ?>

														<?php else: ?>
															<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['LibelleCompet']; ?>

														<?php endif; ?>
														<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><br><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php endif; ?>
													</th>
												</tr>
																						</thead>
											<tbody>
									<?php endif; ?>
									<?php $this->assign('ordre', $this->_tpl_vars['ordre']+1); ?>
											<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
'>
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt'] <= 3 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt'] > 0): ?>
													<td class='medaille'><img width="16" src="img/medal<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>
.gif" alt="Podium" title="Podium" /></td>
												<?php elseif ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau'] <= 3 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau'] > 0): ?>
													<td class='medaille'><img width="16" src="img/medal<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>
.gif" alt="Podium" title="Podium" /></td>
												<?php elseif ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT'): ?>
													<?php if ($this->_tpl_vars['ordre'] <= $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Qualifies']): ?>
														<td class='qualifie'><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
													<?php elseif ($this->_tpl_vars['ordre'] > $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Nb_equipes'] - $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Elimines']): ?>
														<td class='elimine'><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
													<?php else: ?>
														<td>&nbsp;</td>
													<?php endif; ?>
												<?php else: ?>
													<?php if ($this->_tpl_vars['ordre'] <= $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Qualifies']): ?>
														<td class='qualifie'><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
													<?php elseif ($this->_tpl_vars['ordre'] > $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Nb_equipes'] - $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Elimines']): ?>
														<td class='elimine'><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
													<?php else: ?>
														<td>&nbsp;</td>
													<?php endif; ?>
												<?php endif; ?>
												
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT'): ?>
													<td <?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>width="48"<?php else: ?>width="25"<?php endif; ?>>
														<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>

														<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>
															<img width="25" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
														<?php endif; ?>
													</td>
													<td class="cliquableNomEquipe" width="155">
														<a href='Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
' title='<?php echo $this->_config[0]['vars']['Palmares']; ?>
'><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a>
													</td>
												<?php else: ?>
													<td <?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>width="48"<?php else: ?>width="25"<?php endif; ?> style="text-align:right">
														<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>

														<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>
															<img width="25" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
														<?php endif; ?>
													</td>
													<td class="cliquableNomEquipe" width="155">
														<a href='Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
' title='<?php echo $this->_config[0]['vars']['Palmares']; ?>
'><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a>
													</td>
												<?php endif; ?>
											</tr>
								<?php endif; ?>
								<?php endfor; endif; ?>
										</tbody>
									</table>
									<div class="centre">
										<i>
											<a class="grispetit" href='Classement.php?Group=<?php echo $this->_tpl_vars['idGroupe']; ?>
&Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
&Saison=<?php echo $this->_tpl_vars['idSaison']; ?>
'><?php echo $this->_config[0]['vars']['Details']; ?>
</a>
											<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['existMatch'] == 1): ?>
												&nbsp;&nbsp;&nbsp;<a  class='grispetit' href='Journee.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
&Saison=<?php echo $this->_tpl_vars['idSaison']; ?>
'><?php echo $this->_config[0]['vars']['Matchs']; ?>
</a>
											<?php endif; ?>
										</i>
										<br><br>
									</div>
								</div>
					</div>
					<?php else: ?>
					<div>
						<br>
						<br>
						<?php echo $this->_config[0]['vars']['Pas_de_classement']; ?>
.
						<br>
						<br>
						<br>
					</div>
					<?php endif; ?>
				</div>
			</form>			
		</div>	  	   