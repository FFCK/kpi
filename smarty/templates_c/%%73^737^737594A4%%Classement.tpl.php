<?php /* Smarty version 2.6.18, created on 2015-03-14 22:23:04
         compiled from Classement.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'Classement.tpl', 60, false),)), $this); ?>
 	 	<span class="repere">&nbsp;(<a href="index.php"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)</span>
	
		<div class="main">
			<form method="POST" action="Classement.php" name="formClassement" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre'>
					<div class='titrePage'><?php echo $this->_config[0]['vars']['Classement']; ?>
</div>
					<div class='soustitrePage'>
<!--						<label for="codeCompet"><?php echo $this->_config[0]['vars']['Competition']; ?>
 :</label>
						<select name="codeCompet" onChange="changeCompetition();">
								<Option Value=""><?php echo $this->_config[0]['vars']['Selectionnez']; ?>
...</Option>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetition']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][0]; ?>
" <?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][2]; ?>
><?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][1]; ?>
</Option>
							<?php endfor; endif; ?>
						</select>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayOrderCompetition']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<?php if ($this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][2] == 'SELECTED'): ?>
									<?php $this->assign('typeCompetition', $this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][1]); ?>
								<?php endif; ?>
							<?php endfor; endif; ?>
-->						&nbsp;
						<?php if ($this->_tpl_vars['arrayEquipe_journee_publi']): ?> 
							<a href="Journee.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" title="<?php echo $this->_config[0]['vars']['Acces_direct']; ?>
"><?php echo $this->_config[0]['vars']['Matchs']; ?>
...</a>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['typeCompetition'] != 'Championnat'): ?>
							&nbsp;<a href="Classements.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" title="<?php echo $this->_config[0]['vars']['Classements']; ?>
"><?php echo $this->_config[0]['vars']['Classements']; ?>
...</a>
						<?php endif; ?>
						<br>
					</div>
					<div class='blocTable table2'>
					<?php if ($this->_tpl_vars['Statut'] == 'END'): ?>
						<table class="tableau tableauPublic<?php if ($this->_tpl_vars['typeCompetition'] != 'Championnat'): ?> classementCoupe<?php endif; ?>">
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
										<th colspan="2"><?php echo $this->_config[0]['vars']['Classement']; ?>
 <?php echo $this->_tpl_vars['codeCompet']; ?>
 <?php echo $this->_tpl_vars['codeSaison3']; ?>

											<a class="pdfLink" href="PdfCltChpt.php?S=<?php echo $this->_tpl_vars['codeSaison3']; ?>
" Target="_blank"><img width="16" src="img/pdf.gif" alt="<?php echo $this->_config[0]['vars']['Classement']; ?>
 (pdf)" title="<?php echo $this->_config[0]['vars']['Classement']; ?>
 (pdf)" /></a>
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
										<th colspan="2"><?php echo $this->_config[0]['vars']['Classement']; ?>
 <?php echo $this->_tpl_vars['codeCompet']; ?>
 <?php echo $this->_tpl_vars['codeSaison3']; ?>

											<a class="pdfLink" href="PdfCltNiveau.php?S=<?php echo $this->_tpl_vars['codeSaison3']; ?>
" Target="_blank"><img width="16" src="img/pdf.gif" alt="<?php echo $this->_config[0]['vars']['Classement']; ?>
 (pdf)" title="<?php echo $this->_config[0]['vars']['Classement']; ?>
 (pdf)" /></a>
											<?php if ($this->_tpl_vars['Statut'] != 'END'): ?><br /><span class="gris">- <?php echo $this->_config[0]['vars']['PROVISOIRE']; ?>
 -</span><?php endif; ?>
										</th>
									<?php endif; ?>
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
								<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
'>
									<?php if ($this->_sections['i']['iteration'] <= $this->_tpl_vars['Qualifies']): ?>
										<td><img width="16" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
									<?php elseif ($this->_sections['i']['iteration'] > $this->_sections['i']['total'] - $this->_tpl_vars['Elimines']): ?>
										<td><img width="16" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
									<?php else: ?>
										<td>&nbsp;</td>
									<?php endif; ?>
									
									<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
										<td width="45" class="droite">
											<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>

											<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
												<img width="25" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
											<?php endif; ?>
										</td>
										<td class="cliquableNomEquipe" width="190"><a href="Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
" title="<?php echo $this->_config[0]['vars']['Palmares']; ?>
"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a></td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
										<td width="29"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['J']; ?>
</td>
										<td width="29"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['G']; ?>
</td>
										<td width="29"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['N']; ?>
</td>
										<td width="29"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['P']; ?>
</td>
										<td width="29"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['F']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Plus']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Moins']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Diff']; ?>
</td>
									<?php else: ?>
										<td width="45" class="droite">
											<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>

											<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
												<img width="25" src="img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" />
											<?php endif; ?>
										</td>
										<td class="cliquableNomEquipe" width="190"><a href="Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Numero']; ?>
" title="<?php echo $this->_config[0]['vars']['Palmares']; ?>
"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a></td>
																			<?php endif; ?>
									
								
								</tr>
							<?php endfor; endif; ?>
							</tbody>
						</table>
						<br>
					<?php endif; ?>
						<?php if ($this->_tpl_vars['typeCompetition'] != 'Championnat'): ?>
							<table class='tableau tableauPublic'>
								<?php if ($this->_tpl_vars['arrayEquipe_journee_publi']): ?> 
								<thead>
									<tr>
										<th colspan="12"><?php echo $this->_config[0]['vars']['Classement_par_phase']; ?>

											<a class="pdfLink" href="PdfCltNiveauPhase.php?S=<?php echo $this->_tpl_vars['codeSaison3']; ?>
" Target="_blank"><img width="16" src="img/pdf.gif" alt="<?php echo $this->_config[0]['vars']['Classement_par_phase']; ?>
 (pdf)" title="<?php echo $this->_config[0]['vars']['Classement_par_phase']; ?>
 (pdf)" /></a>
										</th>
									</tr>
								</thead>
								<?php endif; ?>
								<tbody>
									<?php $this->assign('idJournee', '0'); ?>

									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe_journee_publi']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<?php if ($this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['J'] != 0): ?>
											<?php if ($this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Id_journee'] != $this->_tpl_vars['idJournee']): ?>
												<tr class='head2Public'>
													<th colspan="2"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Phase']; ?>
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
												</tr>
											<?php endif; ?>
											<?php $this->assign('idJournee', $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Id_journee']); ?>
											<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
'>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>
</td>
												<td class="cliquableNomEquipe" width="200"><a href="Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Numero']; ?>
" title="<?php echo $this->_config[0]['vars']['Palmares']; ?>
"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</a></td>
												<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['J']; ?>
</td>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['G']; ?>
</td>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['N']; ?>
</td>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['P']; ?>
</td>
												<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['F']; ?>
</td>
												<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Plus']; ?>
</td>
												<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Moins']; ?>
</td>
												<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Diff']; ?>
</td>
											</tr>
										<?php endif; ?>
								<?php endfor; endif; ?>
								</tbody>
							</table>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
							<?php echo $this->_config[0]['vars']['MAJ']; ?>
 <?php echo $this->_tpl_vars['Date_publication_calcul']; ?>
 <span class='lienExterne'><a href="http://www.kayak-polo.info/Classement.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
&S=<?php echo $this->_tpl_vars['codeSaison3']; ?>
" target="_blank">En savoir plus...</a></span><br>
						<?php endif; ?>

					</div>
				</div>
			</form>			
					
		</div>	  	   