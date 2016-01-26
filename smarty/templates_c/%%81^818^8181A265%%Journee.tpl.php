<?php /* Smarty version 2.6.18, created on 2015-03-08 12:51:38
         compiled from Journee.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'Journee.tpl', 28, false),array('modifier', 'replace', 'Journee.tpl', 117, false),array('function', 'cycle', 'Journee.tpl', 102, false),)), $this); ?>
		<span class='repere'>&nbsp;(<a href="Calendrier.php"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)
		<br></span>
		<div class="main">
			<form method="POST" action="Journee.php" name="formJournee" id="formJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='idEquipeA' Value=''/>
				<input type='hidden' name='idEquipeB' Value=''/>
				<input type='hidden' name='Pub' Value=''/>
				<input type='hidden' name='Verrou' Value=''/>
				
				<div class='titrePage'><?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
</div>
				<div class='blocMiddle soustitrePage'>
					<table width=100%>
						<tr>
							<td>
								<label for="saisonTravail"><?php echo $this->_config[0]['vars']['Saison']; ?>
 :</label>
								<select name="saisonTravail" onChange="submit()">
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
								<label for="comboCompet"><?php echo $this->_config[0]['vars']['Competition']; ?>
 :</label>
								<select name="comboCompet" onChange="changeCompetition();">
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
										<?php $this->assign('temporaire', $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][0]); ?>
										<Option Value="<?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][0]; ?>
" <?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][2]; ?>
><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['temporaire']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][1]) : smarty_modifier_default($_tmp, @$this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][1])); ?>
</Option>
									<?php endfor; endif; ?>
								</select>
								<?php if ($this->_tpl_vars['Code_typeclt'] == 'CHPT'): ?>
									<br>
									<label for="J"><?php echo $this->_config[0]['vars']['Journee']; ?>
 :</label>
									<select name="J" id="J" onChange="submit();">
										<Option Value="*" Selected><?php echo $this->_config[0]['vars']['Toutes']; ?>
</Option>
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayChoixJournees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<Option Value="<?php echo $this->_tpl_vars['arrayChoixJournees'][$this->_sections['i']['index']]['Id']; ?>
" <?php if ($this->_tpl_vars['idSelJournee'] == $this->_tpl_vars['arrayChoixJournees'][$this->_sections['i']['index']]['Id']): ?>Selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayChoixJournees'][$this->_sections['i']['index']]['Date_debut']; ?>
 - <?php echo $this->_tpl_vars['arrayChoixJournees'][$this->_sections['i']['index']]['Libelle']; ?>
 (<?php echo $this->_tpl_vars['arrayChoixJournees'][$this->_sections['i']['index']]['Lieu']; ?>
)</Option>
										<?php endfor; endif; ?>
									</select>
								<?php endif; ?>
								<label for="orderMatchs"><?php echo $this->_config[0]['vars']['Tri']; ?>
 :</label>
								<select name="orderMatchs" onChange="ChangeOrderMatchs();">
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayOrderMatchs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<?php $this->assign('temporaire', $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value']); ?>
									<?php if ($this->_tpl_vars['orderMatchs'] == $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']): ?>
										<Option Value="<?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']; ?>
" Selected><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['temporaire']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value'])); ?>
</Option>
									<?php else: ?>
										<Option Value="<?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']; ?>
"><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['temporaire']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value'])); ?>
</Option>
									<?php endif; ?>
								<?php endfor; endif; ?>
								</select>
							</td>
							<td align="right">
								<a href='Classements.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
&Group=<?php echo $this->_tpl_vars['codeCompetGroup']; ?>
&Saison=<?php echo $this->_tpl_vars['sessionSaison']; ?>
'><img width="10" src="img/b_plus.png" alt="Classement" title="Classement" /><?php echo $this->_config[0]['vars']['Classements']; ?>
</a>
								<a href="PdfListeMatchs.php" Target="_blank" alt="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
" title="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
"><img width="16" src="img/pdf.gif" alt="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
" title="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
" /><?php echo $this->_config[0]['vars']['Liste']; ?>
 (pdf)</a>
								<a href="PdfListeMatchsEN.php" Target="_blank" alt="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
 (EN)" title="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
 (EN)"><img width="16" src="img/pdf.gif" alt="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
 (EN)" title="<?php echo $this->_config[0]['vars']['Liste_des_Matchs']; ?>
 (EN)" /><?php echo $this->_config[0]['vars']['Liste']; ?>
 (EN)</a>
							</td>
						</tr>
					</table>
				</div>
				<div class="centre">
					<?php if ($this->_tpl_vars['Web'] != ''): ?>
						<a href='<?php echo $this->_tpl_vars['Web']; ?>
' target='_blank'>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['LogoLink'] != ''): ?>
							<img height="100" id='logo' src='<?php echo $this->_tpl_vars['LogoLink']; ?>
' />
							<br>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['Web'] != ''): ?>
							<?php echo $this->_tpl_vars['Web']; ?>

						</a>
						<br>
					<?php endif; ?>
				</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau tableauPublic' id='tableMatchs'>
							<thead>
								<tr>
									<th><?php echo $this->_config[0]['vars']['Num']; ?>
</th>
									<th><?php echo $this->_config[0]['vars']['Cat']; ?>
</th>
									<th><?php echo $this->_config[0]['vars']['Date']; ?>
 - <?php echo $this->_config[0]['vars']['Heure']; ?>
</th>
									<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
										<th><?php echo $this->_config[0]['vars']['Poules']; ?>
</th>
										<!--<th><?php echo $this->_config[0]['vars']['Intitule']; ?>
</th>-->
									<?php else: ?>
										<th><?php echo $this->_config[0]['vars']['Lieu']; ?>
</th>
									<?php endif; ?>
									<th><?php echo $this->_config[0]['vars']['Terr']; ?>
</th>
									<th><?php echo $this->_config[0]['vars']['Equipe_A']; ?>
</th>
									<th colspan=2><?php echo $this->_config[0]['vars']['Score']; ?>
</th>
									<th><?php echo $this->_config[0]['vars']['Equipe_B']; ?>
</th>
									<th class="arb1"><?php echo $this->_config[0]['vars']['Arbitre_1']; ?>
</th>	
									<th class="arb2"><?php echo $this->_config[0]['vars']['Arbitre_2']; ?>
</th>	
								</tr>
							</thead>
							<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayMatchs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<?php $this->assign('validation', $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']); ?>
									<?php $this->assign('statut', $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']); ?>
									<?php $this->assign('periode', $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']); ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
 <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['StdOrSelected']; ?>
 <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['past']; ?>
'>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</td>
											<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
												<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
												<!--<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>-->
											<?php else: ?>
												<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<?php endif; ?>
											<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<td class="cliquableNomEquipe"><a href="Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['NumA']; ?>
" title="<?php echo $this->_config[0]['vars']['Palmares']; ?>
"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</a></td>
											<td colspan=2 class="cliquableScore">
												<?php if ($this->_tpl_vars['validation'] == 'O' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA'] != '?' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA'] != '' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB'] != '?' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB'] != ''): ?>
													<a href="PdfMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank" title="<?php echo $this->_config[0]['vars']['Feuille_marque']; ?>
">
													<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA'])) ? $this->_run_mod_handler('replace', true, $_tmp, '?', '&nbsp;') : smarty_modifier_replace($_tmp, '?', '&nbsp;')))) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
 - <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB'])) ? $this->_run_mod_handler('replace', true, $_tmp, '?', '&nbsp;') : smarty_modifier_replace($_tmp, '?', '&nbsp;')))) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>

													</a>
													<br />
													<span class="statutMatch" title="<?php echo $this->_config[0]['vars']['END']; ?>
"><?php echo $this->_config[0]['vars']['END']; ?>
</span>
												<?php elseif ($this->_tpl_vars['statut'] == 'ON' && $this->_tpl_vars['validation'] != 'O'): ?>
													<span class="scoreProvisoire" title="<?php echo $this->_config[0]['vars']['scoreProvisoire']; ?>
"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<br />
													<span class="statutMatchOn" title="<?php echo $this->_config[0]['vars'][$this->_tpl_vars['periode']]; ?>
"><?php echo $this->_config[0]['vars'][$this->_tpl_vars['periode']]; ?>
</span>
												<?php elseif ($this->_tpl_vars['statut'] == 'END' && $this->_tpl_vars['validation'] != 'O'): ?>
													<span class="scoreProvisoire" title="<?php echo $this->_config[0]['vars']['scoreProvisoire']; ?>
"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<br />
													<span class="statutMatchOn" title="<?php echo $this->_config[0]['vars']['scoreProvisoire']; ?>
"><?php echo $this->_config[0]['vars']['scoreProvisoire']; ?>
</span>
												<?php else: ?>
													<br />
													<span class="statutMatchATT" title="<?php echo $this->_config[0]['vars']['ATT']; ?>
"><?php echo $this->_config[0]['vars']['ATT']; ?>
</span>
												<?php endif; ?>
											</td>
											<td class="cliquableNomEquipe"><a href="Palmares.php?Equipe=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['NumB']; ?>
" title="<?php echo $this->_config[0]['vars']['Palmares']; ?>
"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</a></td>

											<td class="arb1"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', '<br>(') : smarty_modifier_replace($_tmp, ' (', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
											<td class="arb2"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', '<br>(') : smarty_modifier_replace($_tmp, ' (', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
									</tr>
								<?php endfor; else: ?>
									<tr class='pair' height=30>
										<td colspan=13 align=center><i><?php echo $this->_config[0]['vars']['Aucun_match']; ?>
</i></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
		