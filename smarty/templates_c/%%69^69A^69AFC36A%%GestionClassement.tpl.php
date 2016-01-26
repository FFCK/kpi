<?php /* Smarty version 2.6.18, created on 2015-03-17 08:24:57
         compiled from GestionClassement.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionClassement.tpl', 76, false),)), $this); ?>
 	 	&nbsp;(<a href="GestionCalendrier.php">Retour</a>)

		<div class="main">
			<form method="POST" action="GestionClassement.php" name="formClassement" id="formClassement" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes'/>
				<input type='hidden' name='AjaxTableName2' id='AjaxTableName2' Value='gickp_Competitions_Equipes_Journee'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxAnd' id='AjaxAnd' Value='And Id_journee = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
'/>

				<div class='blocLeft'>
					<div class='titrePage'>Classement</div>
					<label for="saisonTravail">Saison :</label>
					<select name="saisonTravail" onChange="sessionSaison()">
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
					<label for="codeCompet">Comp&eacute;tition :</label>
					<select name="codeCompet" onChange="changeCompetition();">
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
					&nbsp;
					<a href="GestionJournee.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" title="Acc&egrave;s direct aux matchs de cette comp&eacute;tition">Matchs...</a>
					<br>
					<label for="orderCompet">Type de classement : </label>
					<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
						<select name="orderCompet" onChange="changeOrderCompetition();">
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
								<Option Value="<?php echo $this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][0]; ?>
" <?php echo $this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][2]; ?>
><?php echo $this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][1]; ?>
</Option>
									<?php if ($this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][2] == 'SELECTED'): ?>
									<?php $this->assign('typeCompetition', $this->_tpl_vars['arrayOrderCompetition'][$this->_sections['i']['index']][1]); ?>
									<?php endif; ?>
							<?php endfor; endif; ?>
						</select>
					<?php else: ?>
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
								<?php echo $this->_tpl_vars['typeCompetition']; ?>

							<?php endif; ?>
						<?php endfor; endif; ?>
					<?php endif; ?>
					&nbsp;<button id='actuButton' type="button" ><img src="../img/actualiser.gif">Recharger</button>
					<br>
					<div class='blocTable table2'>
						<table class='tableauJQ tableauClassement tableau'>
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									<th>&nbsp;</th>
									<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
										<th></th>
									<?php endif; ?>
									<th>Cl.</th>
									<th>Classement type <?php echo $this->_tpl_vars['typeCompetition']; ?>
</th>
									<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
										<th>Pts</th>
									<?php endif; ?>
									<th>J</th>
									<th>G</th>
									<th>N</th>
									<th>P</th>
									<th>F</th>
									<th>+</th>
									<th>-</th>
									<th>Diff</th>
								</tr>
							</thead>
							<tbody>
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
								<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
									<?php if ($this->_sections['i']['iteration'] <= $this->_tpl_vars['Qualifies']): ?>
										<td class='vert'><img width="16" src="../img/up.gif" alt="Qualifi&eacute;" title="Qualifi&eacute;" /></td>
									<?php elseif ($this->_sections['i']['iteration'] > $this->_sections['i']['total'] - $this->_tpl_vars['Elimines']): ?>
										<td class='rouge'><img width="16" src="../img/down.gif" alt="Elimin&eacute;s" title="Elimin&eacute;s" /></td>
									<?php else: ?>
										<td>&nbsp;</td>
									<?php endif; ?>
									<td>
									<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<input type="checkbox" name="checkClassement" value="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" id="checkClassement<?php echo $this->_sections['i']['iteration']; ?>
" />
									<?php endif; ?>
									</td>
									<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
										<td> <img width="20" src="../img/Pays/<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" /></td>
									<?php endif; ?>
									<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
											<td width="30"><span class='directInput' Id="Clt-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
0"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Clt']; ?>
</span></td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
											<td width="40"><span class='directInput' Id="Pts-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
1"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Pts']/100; ?>
</span></td>
										<?php else: ?>
											<td width="30"><span class='directInput' Id="CltNiveau-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
0"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['CltNiveau']; ?>
</span></td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
																					<?php endif; ?>
										<td width="30"><span class='directInput' Id="J-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
2"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['J']; ?>
</span></td>
										<td width="30"><span class='directInput' Id="G-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
3"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['G']; ?>
</span></td>
										<td width="30"><span class='directInput' Id="N-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
4"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['N']; ?>
</span></td>
										<td width="30"><span class='directInput' Id="P-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
5"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['P']; ?>
</span></td>
										<td width="30"><span class='directInput' Id="F-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
6"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['F']; ?>
</span></td>
										<td width="40"><span class='directInput' Id="Plus-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
7"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Plus']; ?>
</span></td>
										<td width="40"><span class='directInput' Id="Moins-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
8"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Moins']; ?>
</span></td>
										<td width="40"><span class='directInput' Id="Diff-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
9"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Diff']; ?>
</span></td>
									<?php else: ?>
										<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Clt']; ?>
</td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
										<?php else: ?>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['CltNiveau']; ?>
</td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
																					<?php endif; ?>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['J']; ?>
</td>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['G']; ?>
</td>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['N']; ?>
</td>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['P']; ?>
</td>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['F']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Plus']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Moins']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Diff']; ?>
</td>
									<?php endif; ?>
								
								</tr>
							<?php endfor; endif; ?>
							</tbody>
						</table>
						<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
							Le classement est effectu&eacute; par Points, puis Diff&eacute;rence de but. 
							<br>
							<b>Pour prendre en compte un classement diff&eacute;rent, modifier manuellement
							<br>
							l'ordre de classement des &eacute;quipes &agrave;  &eacute;galit&eacute; de points (colonne Cl.).</b>
						<?php endif; ?>
						<br>
						<?php if ($this->_tpl_vars['typeCompetition'] != 'Championnat'): ?>
							<table id='tableauJQ2' class='tableauJQ tableau'>
								<thead>
									<tr>
										<th colspan="12">Classement par phase</th>
									</tr>
								</thead>
								<tbody>
								<?php $this->assign('idJournee', '0'); ?>

								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe_journee']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<?php if ($this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['J'] != 0): ?>
										<?php if ($this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id_journee'] != $this->_tpl_vars['idJournee']): ?>
											<tr class='head2'>
												<th colspan="3"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Phase']; ?>
 (<?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Lieu']; ?>
)</th>
												<th>Pts</th>
												<th>J</th>
												<th>G</th>
												<th>N</th>
												<th>P</th>
												<th>F</th>
												<th>+</th>
												<th>-</th>
												<th>Diff</th>
											</tr>
										<?php endif; ?>
										<?php $this->assign('idJournee', $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id_journee']); ?>
										<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>&nbsp;</td>
											<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
												<td width="30"><span class='directInput' Id="Clt-<?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id']; ?>
-<?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id_journee']; ?>
" tabindex="2<?php echo $this->_sections['i']['iteration']; ?>
0"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Clt']; ?>
</span></td>
												<td width="200"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
												<td width="40"><span class='directInput' Id="Pts-<?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id']; ?>
-<?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Id_journee']; ?>
" tabindex="2<?php echo $this->_sections['i']['iteration']; ?>
1"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Pts']/100; ?>
</span></td>
											<?php else: ?>
												<td width="30" ><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Clt']; ?>
</td>
												<td width="200"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
												<td width="40" ><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
											<?php endif; ?>
																						<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['J']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['G']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['N']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['P']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['F']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Plus']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Moins']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_journee'][$this->_sections['i']['index']]['Diff']; ?>
</td>
										</tr>
									<?php endif; ?>	
								<?php endfor; endif; ?>
								</tbody>
							</table>
						<?php endif; ?>
						<br>
						<hr>
						<br>
						<table class='tableau tableauPublic'>
							<thead>
								<tr>
									<th width="17">&nbsp;</th>
									<th></th>
									<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
										<th>&nbsp;</th>
									<?php endif; ?>
									<th colspan="2">Classement public</th>
									<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
										<th>Pts</th>
									<?php endif; ?>
									<th>J</th>
									<th>G</th>
									<th>N</th>
									<th>P</th>
									<th>F</th>
									<th>+</th>
									<th>-</th>
									<th>Diff</th>
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
									<?php if ($this->_sections['i']['iteration'] <= $this->_tpl_vars['Qualifies_publi']): ?>
										<td class='vert'><img width="16" src="../img/up.gif" alt="Qualifi&eacute;" title="Qualifi&eacute;" /></td>
									<?php elseif ($this->_sections['i']['iteration'] > $this->_sections['i']['total'] - $this->_tpl_vars['Elimines_publi']): ?>
										<td class='rouge'><img width="16" src="../img/down.gif" alt="Elimin&eacute;s" title="Elimin&eacute;s" /></td>
									<?php else: ?>
										<td>&nbsp;</td>
									<?php endif; ?>
									
									<td>
										<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<input type="checkbox" name="checkClassement" value="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Id']; ?>
" id="checkClassement<?php echo $this->_sections['i']['iteration']; ?>
" />
										<?php endif; ?>
									</td>
									<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
										<td><img width="20" src="../img/Pays/<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" /></td>
									<?php endif; ?>
									<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Clt']; ?>
</td>
										<td width="200"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
										<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Pts']/100; ?>
</td>
									<?php else: ?>
										<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['CltNiveau']; ?>
</td>
										<td width="200"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
																			<?php endif; ?>
									
									<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['J']; ?>
</td>
									<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['G']; ?>
</td>
									<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['N']; ?>
</td>
									<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['P']; ?>
</td>
									<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['F']; ?>
</td>
									<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Plus']; ?>
</td>
									<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Moins']; ?>
</td>
									<td width="40"><?php echo $this->_tpl_vars['arrayEquipe_publi'][$this->_sections['i']['index']]['Diff']; ?>
</td>
								
								</tr>
							<?php endfor; endif; ?>
							</tbody>
						</table>
						<br>
						<?php if ($this->_tpl_vars['typeCompetition'] != 'Championnat'): ?>
							<table class='tableau tableauPublic'>
								<thead>
									<tr>
										<th></th>
										<th colspan="11">Classement public par phase</th>
									</tr>
								</thead>
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
												<th colspan="3"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Phase']; ?>
 (<?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Lieu']; ?>
)</th>
												<th>Pts</th>
												<th>J</th>
												<th>G</th>
												<th>N</th>
												<th>P</th>
												<th>F</th>
												<th>+</th>
												<th>-</th>
												<th>Diff</th>
											</tr>
										<?php endif; ?>
										<?php $this->assign('idJournee', $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Id_journee']); ?>
										<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair2,pair2"), $this);?>
'>
											
											<td>
												<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
													<input type="checkbox" name="checkClassement" value="<?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Id']; ?>
" id="checkClassement<?php echo $this->_sections['i']['iteration']; ?>
" />
												<?php endif; ?>
											</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Clt']; ?>
</td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe_journee_publi'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
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

					</div>
				</div>
				<div class='blocRight'>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>Classement type <?php echo $this->_tpl_vars['typeCompetition']; ?>
</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
								<?php if ($this->_tpl_vars['Date_calcul'] == '00/00/00 &agrave;  00h00'): ?>Classement non calcul&eacute;<?php else: ?>
								Calcul du <?php echo $this->_tpl_vars['Date_calcul']; ?>
<br>&nbsp;(par <?php echo $this->_tpl_vars['UserName_calcul']; ?>
)<?php endif; ?><br>
								<hr>
							</td>
						</tr>
						<?php if (( $this->_tpl_vars['profile'] <= 6 || $this->_tpl_vars['profile'] == 9 ) && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<tr>
								<td align='center' width=20><input type="checkbox" name="allMatchs" id="allMatchs" value="ok"<?php if ($this->_tpl_vars['Mode_calcul'] == 'tous'): ?> checked<?php endif; ?>></td>
								<td colspan=3>Inclure les matchs non verrouill&eacute;s</td>
							</tr>
							<tr>
								<td colspan=4>
									<input type="button" onclick="computeClt();" name="Calculer" value="Recalculer">
								</td>
							</tr>
						<?php endif; ?>
						<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<tr>
								<td colspan=4>
									<hr>
									<input type="button" onclick="initClt();" name="Initialiser" value="Classement initial...">
									<hr>
								</td>
							</tr>
						<?php endif; ?>
					<?php if ($this->_tpl_vars['typeCompetition'] == 'Championnat'): ?>
						<tr>
							<td colspan=2 align='left'><b>Admin<br><i>"provisoire"</i></b></td>
							<td colspan=2 align='right'><b>Public</b></td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Classement g&eacute;n&eacute;ral admin" title="Classement g&eacute;n&eacute;ral admin" /></a>
							</td>
							<td colspan=2 align='center'>Classement g&eacute;n&eacute;ral</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Classement g&eacute;n&eacute;ral public" title="Classement g&eacute;n&eacute;ral public" /></a>
							<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par &eacute;quipe admin" title="D&eacute;tail par &eacute;quipe admin" /></a>
							</td>
							<td colspan=2 align='center'>D&eacute;tail par &eacute;quipe</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par &eacute;quipe public" title="D&eacute;tail par &eacute;quipe public" /></a>
							<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par journ&eacute;e admin" title="D&eacute;tail par journ&eacute;e admin" /></a>
							</td>
							<td colspan=2 align='center'>D&eacute;tail par journ&eacute;e</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par journ&eacute;e public" title="D&eacute;tail par journ&eacute;e public" /></a>
							<?php endif; ?>
							</td>
						</tr>
					<?php else: ?>
						<tr>
							<td colspan=2 align='left'><b>Admin<br><i>"provisoire"</i></b></td>
							<td colspan=2 align='right'><b>Public</b></td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Classement g&eacute;n&eacute;ral admin" title="Classement g&eacute;n&eacute;ral admin" /></a>
							</td>
							<td colspan=2 align='center'>Classement g&eacute;n&eacute;ral</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Classement g&eacute;n&eacute;ral public" title="Classement g&eacute;n&eacute;ral public" /></a>
							<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par phase admin" title="D&eacute;tail par phase admin" /></a>
							</td>
							<td colspan=2 align='center'>D&eacute;tail par phase</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par phase public" title="D&eacute;tail par phase public" /></a>
							<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par niveau admin" title="D&eacute;tail par niveau admin" /></a>
							</td>
							<td colspan=2 align='center'>D&eacute;tail par niveau</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par niveau public" title="D&eacute;tail par niveau public" /></a>
							<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par &eacute;quipe admin" title="D&eacute;tail par &eacute;quipe admin" /></a>
							</td>
							<td colspan=2 align='center'>D&eacute;tail par &eacute;quipe</td>
							<td align='right'>
							<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
								<a href="../PdfCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" alt="D&eacute;tail par &eacute;quipe public" title="D&eacute;tail par &eacute;quipe public" /></a>
							<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
						<tr>
							<td colspan=4 align='center'><hr></td>
						</tr>
						<tr>
							<td align='left'>
								<a href="FeuilleListeMatchs.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Liste des matchs admin" title="Liste des matchs admin" /></a>
							</td>
							<td colspan=2 align='center'>Matchs</td>
							<td align='right'>
								<a href="../PdfListeMatchs.php?Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" Target="_blank"><img width="20" src="../img/pdf.gif" alt="Liste des matchs public" title="Liste des matchs public" /></a>
							</td>
						</tr>
					</table>
					<br>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=3>
								<label>Classement public</label>
							</th>
						</tr>
						<tr>
							
							<td colspan=3 align='center' class='color<?php if ($this->_tpl_vars['Date_publication_calcul'] == $this->_tpl_vars['Date_calcul']): ?>O<?php else: ?>N<?php endif; ?>'>
								<?php if ($this->_tpl_vars['Code_uti_publication'] != ''): ?>
									<?php if ($this->_tpl_vars['Date_publication_calcul'] == '00/00/00 &agrave;  00h00'): ?>Classement manuel<?php else: ?>Calcul&eacute; le <?php echo $this->_tpl_vars['Date_publication_calcul']; ?>
<?php endif; ?><br>
									Publi&eacute; le <?php echo $this->_tpl_vars['Date_publication']; ?>
<br>
									par <?php echo $this->_tpl_vars['UserName_publication']; ?>

									<?php if ($this->_tpl_vars['Date_publication_calcul'] != $this->_tpl_vars['Date_calcul']): ?>
									<br><br>
									Attention : Classement publi&eacute;<br>diff&eacute;rent du dernier calcul !
									<?php endif; ?>
								<?php else: ?>
									Classement non publi&eacute; !
								<?php endif; ?>
							</td>
						</tr>
						<?php if (( $this->_tpl_vars['profile'] <= 4 ) && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<tr>
								<td colspan=3 align='center'>
									<input type="button" onclick="publicationClt();" name="Publier" value="Publier le nouveau classement">
								</td>
							</tr>
						<?php endif; ?>
						<?php if (( $this->_tpl_vars['profile'] <= 3 ) && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<tr>
								<td>&nbsp;&nbsp;</td>
								<td align='center'>
									<br>
									<input type="button" onclick="depublicationClt();" name="D&eacute;-publier" value="-Supprimer le classement public-">
								</td>
								<td>&nbsp;&nbsp;</td>
							</tr>
						<?php endif; ?>
					</table>
					<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
						<br>
						<table width="100%">
							<tr>
								<th class='titreForm'>
									<label>Affectation, promotion, rel&eacute;gation</label>
								</th>
							</tr>
							<tr>
								<td>
									<label for="codeSaisonTransfert">Affecter vers saison :</label>
									<select name="codeSaisonTransfert" id="codeSaisonTransfert" onchange="changeSaisonTransfert()">
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaisonTransfert']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<Option Value="<?php echo $this->_tpl_vars['arraySaisonTransfert'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arraySaisonTransfert'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['codeSaisonTransfert']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaisonTransfert'][$this->_sections['i']['index']]['Code']; ?>
</Option>
										<?php endfor; endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<label for="codeCompetTransfert">Affecter vers comp&eacute;tition :</label>
									<select name="codeCompetTransfert" id="codeCompetTransfert">
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetitionTransfert']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<!--
											<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][0]; ?>
" <?php echo $this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][2]; ?>
><?php echo $this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][1]; ?>
</Option>
											-->
											<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][0]; ?>
" <?php if ($this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][0] == $this->_tpl_vars['codeCompetTransfert']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayCompetitionTransfert'][$this->_sections['i']['index']][1]; ?>
</Option>
										<?php endfor; endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									<br>
									<input type="button" onclick="transfert();" name="Transfert" value="Affecter les &eacute;quipes coch&eacute;es">
								</td>
							</tr>
						</table>
					<?php endif; ?>
				</div>
						
			</form>			
					
		</div>	  	   