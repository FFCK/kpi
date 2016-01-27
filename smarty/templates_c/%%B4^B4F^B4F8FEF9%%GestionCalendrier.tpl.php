<?php /* Smarty version 2.6.18, created on 2015-04-18 23:25:41
         compiled from GestionCalendrier.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionCalendrier.tpl', 124, false),array('modifier', 'default', 'GestionCalendrier.tpl', 130, false),)), $this); ?>
	&nbsp;(<a href="Admin.php">Retour</a>)
	
	<div class="main">
		<form method="POST" action="GestionCalendrier.php" name="formCalendrier" id="formCalendrier" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>
			<input type='hidden' name='Pub' Value=''/>
			<input type='hidden' name='idEvenement' Value='<?php echo $this->_tpl_vars['idEvenement']; ?>
'/>

			<div class='titrePage'>Journées / phases</div>
			<div class='blocTop'>
				<table width="100%">
					<tr>
						<td>
							<label for="evenement">Evénement :</label>
							<select name="evenement" id="evenement" onChange="changeEvenement();">
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEvenement']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<Option Value="<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
								<?php endfor; endif; ?>
						    </select>
						</td>
						<td colspan=2>
							<label for="competition">Compétition :</label>
							<select name="competition" id="competition" onChange="changeCompetition();">
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
									<Option Value="<?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
								<?php endfor; endif; ?>
						    </select>
						</td>
						<td align="right" rowspan=2>
							<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
								<?php if ($this->_tpl_vars['modeEvenement'] == '1'): ?>
							    	Mode normal<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="1" Checked>
							    	<br>Mode association evts<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="2">		
							    <?php else: ?>
							    	Mode normal<input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="1">
							    	<br><font color="FF0000">Mode association evts</font><input type="radio" onclick="changeModeEvenement();" name="choixModeEvenement" value="2" Checked>		
								<?php endif; ?>
							<?php else: ?>
								&nbsp;
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="filtreMois">Mois :</label>
							<select name="filtreMois" onChange="document.formCalendrier.submit();">
									<Option Value="" <?php if ($this->_tpl_vars['filtreMois'] == ''): ?>selected<?php endif; ?>>---Tous---</Option>
									<Option Value="1" <?php if ($this->_tpl_vars['filtreMois'] == '1'): ?>selected<?php endif; ?>>Janvier</Option>
									<Option Value="2" <?php if ($this->_tpl_vars['filtreMois'] == '2'): ?>selected<?php endif; ?>>Février</Option>
									<Option Value="3" <?php if ($this->_tpl_vars['filtreMois'] == '3'): ?>selected<?php endif; ?>>Mars</Option>
									<Option Value="4" <?php if ($this->_tpl_vars['filtreMois'] == '4'): ?>selected<?php endif; ?>>Avril</Option>
									<Option Value="5" <?php if ($this->_tpl_vars['filtreMois'] == '5'): ?>selected<?php endif; ?>>Mai</Option>
									<Option Value="6" <?php if ($this->_tpl_vars['filtreMois'] == '6'): ?>selected<?php endif; ?>>Juin</Option>
									<Option Value="7" <?php if ($this->_tpl_vars['filtreMois'] == '7'): ?>selected<?php endif; ?>>Juillet</Option>
									<Option Value="8" <?php if ($this->_tpl_vars['filtreMois'] == '8'): ?>selected<?php endif; ?>>Août</Option>
									<Option Value="9" <?php if ($this->_tpl_vars['filtreMois'] == '9'): ?>selected<?php endif; ?>>Septembre</Option>
									<Option Value="10" <?php if ($this->_tpl_vars['filtreMois'] == '10'): ?>selected<?php endif; ?>>Octobre</Option>
									<Option Value="11" <?php if ($this->_tpl_vars['filtreMois'] == '11'): ?>selected<?php endif; ?>>Novembre</Option>
									<Option Value="12" <?php if ($this->_tpl_vars['filtreMois'] == '12'): ?>selected<?php endif; ?>>Décembre</Option>
						    </select>
							<label for="competitionOrder"> Trié par :</label>
							<select name="competitionOrder" onChange="changeCompetitionOrder();">
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetitionOrder']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionOrder'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayCompetitionOrder'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayCompetitionOrder'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
								<?php endfor; endif; ?>
						    </select>
						</td>
						<td>
							<a href="GestionJournee.php?idJournee=*"><img align="absbottom" width="16" height="16" src="../img/b_browse.png" alt="Tous les matchs" title="Tous les matchs" border="0">Voir tous les Matchs</a>
						</td>
						<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
							<td>
								<a href="#" onclick="ParamJournee(0);"><img align="bottom" width="16" height="16" src="../img/b_insrow.png" alt="Ajouter une journee" title="Ajouter une journee" border="0">Ajouter une Journée</A></td>
							</td>
						<?php endif; ?>
					</tr>
				</table>
			</div>

			<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
				<div class='blocMiddle'>
					<table width="100%">
						<tr>
							<td>
								Sélection :&nbsp;
								<a href="#" onclick="setCheckboxes('formCalendrier', 'checkJournee', true);return false;"><img width="21" height="19" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" border="0"></a>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formCalendrier', 'checkJournee', false);return false;"><img width="21" height="19" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" border="0"></a>
								&nbsp;
								<a href="#" onclick="SelectedCheckboxes('formCalendrier', 'checkJournee');publiMultiJournees();" alt="Publier/dépublier les journées/phases cochées" title="Publier/dépublier les journées/phases cochées"><img width="29" height="24" src="../img/oeil2.gif" alt="Publier/Dépublier la sélection" title="Publier/Dépublier la sélection" border="0"></a>
								&nbsp;
								<a href="#" onclick="RemoveCheckboxes('formCalendrier', 'checkJournee')" alt="Supprimer les journées cochées" title="Supprimer les journées/phases cochées"><img width="16" height="16" src="../img/supprimer.gif" alt="Supprimer la sélection" title="Supprimer la sélection" border="0"></a>
							</td>
						</tr>
					</table>
				</div>
			<?php endif; ?>
			<div class='blocBottom'>
				<div class='blocTable'>
					<table class='tableau'>
						<thead>
							<tr>
								<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
									<th>&nbsp;</th>
								<?php endif; ?>
								<th width=18><img width="19" height="16" src="../img/oeil2.gif" alt="Publier ?" title="Publier ?" border="0"></th>
								<th>N°</th>
								<th>&nbsp;</th>
								<th>Compét.</th>
								<th>Niv.</th>
								<th>Type</th>
								<th>Nom</th>
								<th>Date(s)</th>
								<th>Lieu</th>
								<th>Dpt.</th>
								<th colspan="2">Officiels</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						
						<tbody>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayJournees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<?php if ($this->_tpl_vars['modeEvenement'] == '1'): ?>
										<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Autorisation'] == true && $this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
												<td><input type="checkbox" name="checkJournee" value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
											<?php endif; ?>
											<td class="color<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
2">
										<!--	
												<a href="#" Id="Publication<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" onclick="publiJournee(<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
, '<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication']; ?>
')">
													<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
												</a>
										-->	
												<img class="publiJournee" data-valeur="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" width="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'] == 'O'): ?>Public<?php else: ?>Non public<?php endif; ?>" />
											</td>
											<td align="left"><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
</td> 
											<td width=70>
												<a href="#" onclick="ParamJournee(<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
);"><img width="16" height="16" src="../img/b_edit.png" alt="Modifier" title="Modifier les Paramètres de la journée" border="0"></a>
												<a href="#" onclick="duplicate(<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
);"><img width="16" height="16" src="../img/b_copy.png" alt="Dupliquer" title="Dupliquer" border="0"></a>
												<a href='GestionJournee.php?idJournee=<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
'><img width="16" height="16" src="../img/b_browse.png" alt="Accès aux matchs - journée <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" title="Accès aux matchs - journée <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" border="0"></a>
											</td>
										<?php else: ?>
											<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
												<td>&nbsp;</td>
											<?php endif; ?>
											<td class="color<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
2">
												<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
											</td>
											<td align="left"><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
</td> 
											<td align="left">
												<a href='GestionJournee.php?idJournee=<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
'>
													<img align="absbottom" width="16" height="16" src="../img/b_browse.png" alt="Accès aux matchs - journée <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" title="Accès aux matchs - journée <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" border="0">
												</a> 
											</td>
										<?php endif; ?>
									<?php elseif ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Autorisation'] == true && $this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td>&nbsp;</td>
										<?php endif; ?>
										<td class="color<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
2">
											<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
										</td>
										<td align="left"><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
</td> 
										<td class="rouge">
											<input type="checkbox" class="checkassoc2" data-id="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Checked']; ?>
 />
											<!--<input type="checkbox" class="checkassoc" onclick="ClickEvenementJournee(<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
);" id="checkEvenementJournee<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Checked']; ?>
 />-->
										</td>
									<?php else: ?>
										<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td>&nbsp;</td>
										<?php endif; ?>
										<td class="color<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
2">
											<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
										</td>
										<td align="left"><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
</td> 
										<td class="rouge">&nbsp;</td>
									<?php endif; ?>
									
									<td><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Phase'] != ''): ?> - <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Phase']; ?>
<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau']; ?>
</td>
									<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<td><img class="typeJournee" data-valeur="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" src="../img/type<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
									<?php else: ?>
										<td><img src="../img/type<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
									<?php endif; ?>
									<td><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Nom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_debut']; ?>

									<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_debut'] != $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_fin']): ?> - <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_fin']; ?>
<?php endif; ?></td>
									<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
									<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Departement'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
                                                                        <td><a href="GestionInstances.php?idJournee=<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" title="Officiels"><img src="../img/b_search.png" alt="Officiels"></a>
                                                                        <td>
										<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Responsable_insc'] != ''): ?>RC: <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Responsable_insc']; ?>
<br /><?php endif; ?>
										<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Responsable_R1'] != ''): ?>R1: <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Responsable_R1']; ?>
<br /><?php endif; ?>
										<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Delegue'] != ''): ?>Délégué: <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Delegue']; ?>
<br /><?php endif; ?>
										<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['ChefArbitre'] != ''): ?>Chef arbitres: <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['ChefArbitre']; ?>
<?php endif; ?>
									</td>
									
									<?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Autorisation'] == true && $this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<td><a href="#" onclick="RemoveCheckbox('formCalendrier', '<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
');return false;"><img width="16" height="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" border="0"></a></td>
									<?php else: ?>
										<td>&nbsp;</td>
									<?php endif; ?>
								</tr>
							<?php endfor; endif; ?>
						</tbody>
					</table>
				</div>
	        </div>
		</form>			
				
	</div>	  	   