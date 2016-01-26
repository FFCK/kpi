<?php /* Smarty version 2.6.18, created on 2014-11-22 23:24:42
         compiled from Classements.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'Classements.tpl', 33, false),array('function', 'cycle', 'Classements.tpl', 152, false),)), $this); ?>
 	 	<span class="repere">&nbsp;(<a href="index.php"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)</span>
	
		<div class="main">
			<form method="POST" action="Classements.php" name="formClassement" enctype="multipart/form-data">
				<?php echo '
					<div id="fb-root"></div>
					<script>(function(d, s, id) {
						  var js, fjs = d.getElementsByTagName(s)[0];
						  if (d.getElementById(id)) return;
						  js = d.createElement(s); js.id = id;
						  js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.0";
						  fjs.parentNode.insertBefore(js, fjs);
						}(document, \'script\', \'facebook-jssdk\'));
					</script>
				'; ?>

				<input type='hidden' name='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' Value='' />
				<div class='blocCentre'>
					<div class='titrePage'><?php echo $this->_config[0]['vars']['Classement']; ?>
</div>
					<br />
					<div class='soustitrePage'>
						<label><?php echo $this->_config[0]['vars']['Saison']; ?>
 :</label>
						<select name="saisonTravail" id="saisonTravail" onChange="submit()">
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
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?>selected<?php endif; ?>><?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?>=> <?php endif; ?><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
</Option>
							<?php endfor; endif; ?>
						</select>
						<label><?php echo $this->_config[0]['vars']['Competition']; ?>
 :</label>
						<select name="codeCompetGroup" id="codeCompetGroup" onChange="submit();">
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
						<a href='Historique.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
'><?php echo $this->_config[0]['vars']['Historique']; ?>
...</a>
						<div class="fb-like" data-href="http://www.kayak-polo.info/Classements.php?Group=<?php echo $this->_tpl_vars['codeCompetGroup']; ?>
&Saison=<?php echo $this->_tpl_vars['sessionSaison']; ?>
" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
					</div>
					<br />
					<div class="centre">
						<?php if ($this->_tpl_vars['recordCompetition'][0]['Web'] != ''): ?>
							<a href='<?php echo $this->_tpl_vars['recordCompetition'][0]['Web']; ?>
' target='_blank'>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['recordCompetition'][0]['LogoLink'] != ''): ?>
								<img class="img2" width="700" id='logo' src='<?php echo $this->_tpl_vars['recordCompetition'][0]['LogoLink']; ?>
' alt="logo" />
								<br />
						<?php endif; ?>
						<?php if ($this->_tpl_vars['recordCompetition'][0]['Web'] != ''): ?>
								<?php echo $this->_tpl_vars['recordCompetition'][0]['Web']; ?>

							</a>
							<br />
						<?php endif; ?>
					</div>
					<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['CodeCompet']): ?>
					<div>
						<?php $this->assign('idCompet', $this->_tpl_vars['arrayEquipe_publi'][0]['CodeCompet']); ?>
						<?php $this->assign('idTour', $this->_tpl_vars['arrayEquipe_publi'][0]['Code_tour']); ?>
						<?php $this->assign('idSaison', $this->_tpl_vars['arrayEquipe_publi'][0]['CodeSaison']); ?>
						<div class='droite'>
							<a href='Classement.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
'><?php echo $this->_config[0]['vars']['Details']; ?>
...</a>
							<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['existMatch'] == 1): ?>
								&nbsp;<a href='Journees.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
'><?php echo $this->_config[0]['vars']['Matchs']; ?>
...</a>
							<?php endif; ?>
						</div>
						<table class='tableau tableauPublic'>
							<thead>
								<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Titre_actif'] != 'O' && $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre2'] != ''): ?>
									<tr>
										<th colspan=12 <?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Code_tour'] == '10'): ?>class='TitrePhaseFinale'<?php endif; ?>><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre']; ?>

											<br /><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre2']; ?>

											<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Statut'] != 'END'): ?><br /><span class="gris">- <?php echo $this->_config[0]['vars']['PROVISOIRE']; ?>
 -</span><?php endif; ?>
										</th>
									</tr>
								<?php else: ?>
									<tr>
										<th colspan=12 <?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Code_tour'] == '10'): ?>class='TitrePhaseFinale'<?php endif; ?>><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['CodeSaison']; ?>
 - <?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['LibelleCompet']; ?>
<br /><?php echo $this->_tpl_vars['arrayEquipe_publi'][0]['Soustitre2']; ?>
</th>
									</tr>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['arrayEquipe_publi'][0]['Code_typeclt'] == 'CHPT'): ?>
									<tr>
										<th colspan=2>&nbsp;</th>
										<th>&nbsp;</th>
										<th><?php echo $this->_config[0]['vars']['Pts']; ?>
</th>
										<th><?php echo $this->_config[0]['vars']['J']; ?>
</th>
										<th><?php echo $this->_config[0]['vars']['G']; ?>
</th>
										<th><?php echo $this->_config[0]['vars']['N']; ?>
</th>
										<th><?php echo $this->_config[0]['vars']['P']; ?>
</th>
										<th><?php echo $this->_config[0]['vars']['F']; ?>
</th>
										<th>+</th>
										<th>-</th>
										<th><?php echo $this->_config[0]['vars']['Diff']; ?>
</th>
									</tr>
								<?php else: ?>
																<?php endif; ?>
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
							<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeCompet'] != $this->_tpl_vars['idCompet'] || $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison'] != $this->_tpl_vars['idSaison']): ?>
									</tbody>
								</table>
								<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] != $this->_tpl_vars['idTour']): ?>
									<br />
									<br />
								<?php endif; ?>
								<?php $this->assign('idCompet', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeCompet']); ?>
								<?php $this->assign('idTour', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour']); ?>
								<?php $this->assign('idSaison', $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison']); ?>
								<?php $this->assign('ordre', 0); ?>
								<div class='droite'><a href='Classement.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
'><?php echo $this->_config[0]['vars']['Details']; ?>
...</a>
								<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['existMatch'] == 1): ?>
									&nbsp;<a href='Journee.php?Compet=<?php echo $this->_tpl_vars['idCompet']; ?>
'><?php echo $this->_config[0]['vars']['Matchs']; ?>
...</a>
								<?php endif; ?>
								</div>
								<table class="tableau tableauPublic">
									<thead>
										<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Titre_actif'] != 'O' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?>
											<tr>
												<th colspan=12 <?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10'): ?>class="TitrePhaseFinale"<?php endif; ?>><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre']; ?>

													<br /><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre2']; ?>

													<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Statut'] != 'END'): ?><br /><span class="gris">- <?php echo $this->_config[0]['vars']['PROVISOIRE']; ?>
 -</span><?php endif; ?>
												</th>
											</tr>
										<?php else: ?>
											<tr>
												<th colspan=12 <?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10'): ?>class="TitrePhaseFinale"<?php endif; ?>><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CodeSaison']; ?>
 - <?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['LibelleCompet']; ?>
<br /><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Soustitre2']; ?>
</th>
											</tr>
										<?php endif; ?>
										<tr>
											<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT'): ?>
												<th colspan=2><?php echo $this->_config[0]['vars']['Clt']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['Equipe']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['Pts']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['J']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['G']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['N']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['P']; ?>
</th>
												<th><?php echo $this->_config[0]['vars']['F']; ?>
</th>
												<th>+</th>
												<th>-</th>
												<th><?php echo $this->_config[0]['vars']['Diff']; ?>
</th>
											<?php else: ?>
																						<?php endif; ?>
										</tr>
									</thead>
									<tbody>
							<?php endif; ?>
							<?php $this->assign('ordre', $this->_tpl_vars['ordre']+1); ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
'>
										<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt'] <= 3 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt'] > 0 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Statut'] == 'END'): ?>
											<td class='medaille'><img width="16" height="16" src="img/medal<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>
.gif" alt="Podium" title="Podium" /></td>
										<?php elseif ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_tour'] == '10' && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau'] <= 3 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau'] > 0 && $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Statut'] == 'END'): ?>
											<td class='medaille'><img width="16" height="16" src="img/medal<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>
.gif" alt="Podium" title="Podium" /></td>
										<?php elseif ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT'): ?>
											<?php if ($this->_tpl_vars['ordre'] <= $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Qualifies']): ?>
												<td class='qualifie'><img width="16" height="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
											<?php elseif ($this->_tpl_vars['ordre'] > $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Nb_equipes'] - $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Elimines']): ?>
												<td class='elimine'><img width="16" height="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
											<?php else: ?>
												<td>&nbsp;</td>
											<?php endif; ?>
										<?php else: ?>
											<?php if ($this->_tpl_vars['ordre'] <= $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Qualifies']): ?>
												<td class='qualifie'><img width="16" height="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
											<?php elseif ($this->_tpl_vars['ordre'] > $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Nb_equipes'] - $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Elimines']): ?>
												<td class='elimine'><img width="16" height="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
											<?php else: ?>
												<td>&nbsp;</td>
											<?php endif; ?>
										<?php endif; ?>
										
										<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_typeclt'] == 'CHPT'): ?>
											<td class="droite">
												<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>

												<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>
													<img class="img2" width="25" height="16" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
												<?php endif; ?>
											</td>
											<td class="cliquableNomEquipe"><a href='Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
' title='<?php echo $this->_config[0]['vars']['Palmares']; ?>
'><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a></td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['J']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['G']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['N']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['P']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['F']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Plus']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Moins']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Diff']; ?>
</td>
										<?php else: ?>
											<td class="droite">
												<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>
.
												<?php if ($this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_niveau'] == 'INT'): ?>
													<img class="img2" width="25" height="16" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
												<?php endif; ?>
											</td>
											<td class="cliquableNomEquipe"><a href='Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
' title='<?php echo $this->_config[0]['vars']['Palmares']; ?>
'><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a></td>
										<?php endif; ?>
									</tr>
						<?php endfor; endif; ?>
								</tbody>
							</table>
					</div>
					<?php else: ?>
					<div>
						<br />
						<br />
						<?php echo $this->_config[0]['vars']['Pas_de_classement']; ?>
.
						<br />
						<br />
						<br />
					</div>
					<?php endif; ?>
				</div>
			</form>			
		</div>	  	   