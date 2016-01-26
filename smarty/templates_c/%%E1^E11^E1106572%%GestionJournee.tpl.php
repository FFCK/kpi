<?php /* Smarty version 2.6.18, created on 2015-06-25 17:31:42
         compiled from GestionJournee.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_format', 'GestionJournee.tpl', 52, false),array('modifier', 'default', 'GestionJournee.tpl', 123, false),array('modifier', 'replace', 'GestionJournee.tpl', 411, false),array('function', 'cycle', 'GestionJournee.tpl', 342, false),)), $this); ?>
		&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
		<br>
		<iframe name="iframeRechercheLicenceIndi2" id="iframeRechercheLicenceIndi2" SRC="RechercheLicenceIndi2.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionJournee.php" name="formJournee" id="formJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='idEquipeA' id='idEquipeA' Value=''/>
				<input type='hidden' name='idEquipeB' id='idEquipeB' Value=''/>
				<input type='hidden' name='Pub' id='Pub' Value=''/>
				<input type='hidden' name='Verrou' id='Verrou' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Matchs'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
'/>
				
				<div class='titrePage'>Liste des matchs</div>
					<table id="formMatch">
						<tr class='filtres cadregris'>
							<td align="center">
								<label for="evenement">Filtre Evénement</label>
								<br>
								<select name="evenement" id="evenement" onChange="submit();">
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
							<td align="center">
								<label for="comboCompet">Filtre Compétition</label>
								<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
								<a href="#" id="InitTitulaireCompet"><img width="16" src="../img/init.gif" alt="Ré-affecter les joueurs présents pour toute la compétition sélectionnée" title="Ré-affecter les joueurs présents pour toute la compétition sélectionnée" /></a>
								<?php endif; ?>
								<br>
								<select id="comboCompet" name="comboCompet" onChange="changeCompet();" tabindex="1">
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
										<?php if ($this->_tpl_vars['codeCurrentCompet'] == $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']): ?>
											<Option Value="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" Selected><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
 - <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php else: ?>
											<Option Value="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
"><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
 - <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php endif; ?>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td align="center">
								<label for="comboJournee2">Filtre Journée/Phase/Poule</label>
								<br>
								<select id="comboJournee2" name="comboJournee2" onChange="changeCompet();" tabindex="2">
									<Option Value="*" Selected>Toutes...</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayJourneesAutoriseesFiltre']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<?php if ($this->_tpl_vars['idSelJournee'] == $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id']): ?>
											<?php if ($this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP'): ?>
												<Option Value="<?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id']; ?>
" Selected><?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_competition']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "[%s]") : smarty_modifier_string_format($_tmp, "[%s]")); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Niveau'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "(%s)") : smarty_modifier_string_format($_tmp, "(%s)")); ?>
</Option>
											<?php else: ?>
												<Option Value="<?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id']; ?>
" Selected><?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_competition']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "[%s]") : smarty_modifier_string_format($_tmp, "[%s]")); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('string_format', true, $_tmp, " le %s ") : smarty_modifier_string_format($_tmp, " le %s ")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "à %s") : smarty_modifier_string_format($_tmp, "à %s")); ?>
</Option>
											<?php endif; ?>
										<?php else: ?>
											<?php if ($this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP'): ?>
												<Option Value="<?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id']; ?>
"><?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_competition']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "[%s]") : smarty_modifier_string_format($_tmp, "[%s]")); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Niveau'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "(%s)") : smarty_modifier_string_format($_tmp, "(%s)")); ?>
</Option>
											<?php else: ?>
												<Option Value="<?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id']; ?>
"><?php echo $this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Code_competition']; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "[%s]") : smarty_modifier_string_format($_tmp, "[%s]")); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('string_format', true, $_tmp, " le %s ") : smarty_modifier_string_format($_tmp, " le %s ")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutoriseesFiltre'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "à %s") : smarty_modifier_string_format($_tmp, "à %s")); ?>
</Option>
											<?php endif; ?>
										<?php endif; ?>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td align="center">
								<label for="filtreMois">Filtre Date / Terrain</label>
								<br>
								<select name="filtreJour" id="filtreJour" onChange="submit();">
										<Option Value="" <?php if ($this->_tpl_vars['filtreJour'] == ''): ?>selected<?php endif; ?>>---Tous---</Option>
									 <?php $_from = $this->_tpl_vars['listeJours']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
										<Option Value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['filtreJour'] == $this->_tpl_vars['k']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['v']; ?>
</Option>
									 <?php endforeach; endif; unset($_from); ?>
							    </select>
								<select name="filtreTerrain" id="filtreTerrain" onChange="submit();">
										<Option Value="" <?php if ($this->_tpl_vars['filtreTerrain'] == ''): ?>selected<?php endif; ?>>---Tous---</Option>
										<Option Value="1" <?php if ($this->_tpl_vars['filtreTerrain'] == '1'): ?>selected<?php endif; ?>>Terr. 1</Option>
										<Option Value="2" <?php if ($this->_tpl_vars['filtreTerrain'] == '2'): ?>selected<?php endif; ?>>Terr. 2</Option>
										<Option Value="3" <?php if ($this->_tpl_vars['filtreTerrain'] == '3'): ?>selected<?php endif; ?>>Terr. 3</Option>
										<Option Value="4" <?php if ($this->_tpl_vars['filtreTerrain'] == '4'): ?>selected<?php endif; ?>>Terr. 4</Option>
										<Option Value="5" <?php if ($this->_tpl_vars['filtreTerrain'] == '5'): ?>selected<?php endif; ?>>Terr. 5</Option>
										<Option Value="6" <?php if ($this->_tpl_vars['filtreTerrain'] == '6'): ?>selected<?php endif; ?>>Terr. 6</Option>
										<Option Value="7" <?php if ($this->_tpl_vars['filtreTerrain'] == '7'): ?>selected<?php endif; ?>>Terr. 7</Option>
										<Option Value="8" <?php if ($this->_tpl_vars['filtreTerrain'] == '8'): ?>selected<?php endif; ?>>Terr. 8</Option>
							    </select>
							</td>
							<td align="center">
								<label for="filtreMois">Ordre de tri</label>
								<br>
								<select name="orderMatchs" onChange="ChangeOrderMatchs('<?php echo $this->_tpl_vars['idSelJournee']; ?>
');">
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
									<?php if ($this->_tpl_vars['orderMatchs'] == $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']): ?>
										<Option Value="<?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']; ?>
" Selected><?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value']; ?>
</Option>
									<?php else: ?>
										<Option Value="<?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Key']; ?>
"><?php echo $this->_tpl_vars['arrayOrderMatchs'][$this->_sections['i']['index']]['Value']; ?>
</Option>
									<?php endif; ?>
								<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
					</table>
				<div class='blocTop'>
					<?php if (( $this->_tpl_vars['profile'] <= 6 || $this->_tpl_vars['profile'] == 9 ) && $this->_tpl_vars['AuthModif'] == 'O'): ?>
					<table id="formMatch">
						<tr class="hideTr">
							<td align="left" title="Intervalle entre chaque début de match">
								<label for="Intervalle_match">Intervale matchs</label>
								<br>
								<input type="text" size="1" name="Intervalle_match" value="<?php echo $this->_tpl_vars['Intervalle_match']; ?>
">min.
							</td>
							<td>
								Type :
								<img id="typeMatch1" src="../img/type<?php echo ((is_array($_tmp=@$this->_tpl_vars['Type'])) ? $this->_run_mod_handler('default', true, $_tmp, 'C') : smarty_modifier_default($_tmp, 'C')); ?>
.png" <?php if ($this->_tpl_vars['Type'] == 'E'): ?>alt="Elimination" title="Match éliminatoire"<?php else: ?>alt="Classement" title="Match de classement"<?php endif; ?> />
								<input type="hidden" name="Type" id="Type" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['Type'])) ? $this->_run_mod_handler('default', true, $_tmp, 'C') : smarty_modifier_default($_tmp, 'C')); ?>
" />
							</td>
							<td align="left">
								<table>
									<tr>
										<td>
											<label for="Libelle">Intitulé [codage]</label>
											<br>
											<input type="text" size="18" name="Libelle" placeholder="[A-B/PRIN-SEC]" value="<?php echo $this->_tpl_vars['Libelle']; ?>
" maxlength=30" tabindex="7"/>
										</td>
										<td>
											<label for="Num_match">Match N°</label>
											<br>
											<input type="text" size="3" name="Num_match" id="Num_match" value="<?php echo $this->_tpl_vars['Num_match']; ?>
" tabindex="5"/>
										</td>
									</tr>
								</table>
							</td>
							<td align="left">
								<label for="equipeA">Equipe A</label>
								<a href="#" id="InitTitulaireEquipeA"><img width="16" src="../img/init.gif" alt="Ré-affecter les joueurs présents pour l'équipe A sélectionnée" title="Ré-affecter les joueurs présents pour l'équipe A sélectionnée" /></a>
								<br>
								<select name="equipeA" id="equipeA" onChange="changeEquipeA();" tabindex="8">
									<Option Value="-1">---</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipeA']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayEquipeA'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEquipeA'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEquipeA'][$this->_sections['i']['index']]['Libelle']; ?>
<?php if ($this->_tpl_vars['arrayEquipeA'][$this->_sections['i']['index']]['Poule'] != ''): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayEquipeA'][$this->_sections['i']['index']]['Poule'])) ? $this->_run_mod_handler('string_format', true, $_tmp, " (%s)") : smarty_modifier_string_format($_tmp, " (%s)")))) ? $this->_run_mod_handler('default', true, $_tmp, "") : smarty_modifier_default($_tmp, "")); ?>
<?php endif; ?></Option>
									<?php endfor; endif; ?>
								</select>
								&nbsp;<label for="coeffA">Coef.</label>
								<input size="1" type="text" name="coeffA" value="<?php echo $this->_tpl_vars['coeffA']; ?>
" tabindex="9" />
							</td>
							<td align="left">
								<label for="arbitre1">Arbitre</label>
								<input type="text" size="30" name="arbitre1" id="arbitre1" placeholder="nom prenom, n° licence ou équipe" value="<?php echo $this->_tpl_vars['arbitre1']; ?>
" tabindex="12"/>
								<input type="text" size="5" name="arbitre1_matric" readonly id="arbitre1_matric" value="<?php echo $this->_tpl_vars['arbitre1_matric']; ?>
"/>
								<br />
								<label for="comboarbitre1">principal</label>
								<select class="combolong" name="comboarbitre1" onChange="arbitre1_matric.value=this.options[this.selectedIndex].value; arbitre1.value=this.options[this.selectedIndex].text;" tabindex="13">
									<Option Value="-1"></Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayArbitre']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayArbitre'][$this->_sections['i']['index']]['Matric']; ?>
"><?php echo $this->_tpl_vars['arrayArbitre'][$this->_sections['i']['index']]['Identite']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
								<a href="#"  id='rechercheArbitre1'><img width="16" src="../img/b_search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
							</td>
						</tr>
						<tr class="hideTr">
							<td align="center" colspan="2">
								<label for="comboJournee">Journée/Phase/Poule du match</label>
								<a href="#" id="InitTitulaireJournee"><img width="16" src="../img/init.gif" alt="Ré-affecter les joueurs présents pour toute la journée / phase sélectionnée" title="Ré-affecter les joueurs présents pour toute la journée / phase sélectionnée" /></a>
								<br>
								<select id="comboJournee" name="comboJournee" tabindex="2">
									<option Value="*" Selected>--- Sélectionnez --- (OBLIGATOIRE)</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayJourneesAutorisees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<?php if ($this->_tpl_vars['idCurrentJournee'] == $this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Id']): ?>
											<?php if ($this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP'): ?>
												<option Value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" data-type="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
" Selected><?php echo $this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_competition']; ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Niveau'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "(%s)") : smarty_modifier_string_format($_tmp, "(%s)")); ?>
</option>
											<?php else: ?>
												<option Value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" data-type="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
" Selected><?php echo $this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_competition']; ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "à %s") : smarty_modifier_string_format($_tmp, "à %s")); ?>
</option>
											<?php endif; ?>
										<?php else: ?>
											<?php if ($this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_typeclt'] == 'CP'): ?>
												<option Value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" data-type="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
"><?php echo $this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_competition']; ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Niveau'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "(%s)") : smarty_modifier_string_format($_tmp, "(%s)")); ?>
</option>
											<?php else: ?>
												<option Value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" data-type="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Type']; ?>
"><?php echo $this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Code_competition']; ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%s") : smarty_modifier_string_format($_tmp, "%s")); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJourneesAutorisees'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "à %s") : smarty_modifier_string_format($_tmp, "à %s")); ?>
</option>
											<?php endif; ?>
										<?php endif; ?>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td align="left">
								<table>
									<tr>
										<td>
											<label for="Date_match">Date</label>
											<br>
											<input type="text" size="10" class='date' name="Date_match" value="<?php echo $this->_tpl_vars['Date_match']; ?>
" tabindex="3" onfocus="displayCalendar(document.forms[0].Date_match,'dd/mm/yyyy',this)" >
										</td>
										<td>
											<label for="Heure_match">Heure</label>
											<br>
											<input type="text" size="5" class='champsHeure' name="Heure_match" value="<?php echo $this->_tpl_vars['Heure_match']; ?>
" tabindex="4"/>
										</td>
										<td>
											<label for="Terrain">Terrain</label>
											<br>
											<input type="text" size="3" name="Terrain" value="<?php echo $this->_tpl_vars['Terrain']; ?>
" maxlength=12 tabindex="6"/>
										</td>
									</tr>
								</table>
							</td>
							<td align="left">
								<label for="equipeB">Equipe B</label>
								<a href="#" id="InitTitulaireEquipeB"><img width="16" src="../img/init.gif" alt="Ré-affecter les joueurs présents pour l'équipe B sélectionnée" title="Ré-affecter les joueurs présents pour l'équipe B sélectionnée" /></a>
								<br>
								<select name="equipeB" id="equipeB" onChange="changeEquipeB();" tabindex="10">
									<Option Value="-1">---</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipeB']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayEquipeB'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEquipeB'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEquipeB'][$this->_sections['i']['index']]['Libelle']; ?>
<?php if ($this->_tpl_vars['arrayEquipeB'][$this->_sections['i']['index']]['Poule'] != ''): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayEquipeB'][$this->_sections['i']['index']]['Poule'])) ? $this->_run_mod_handler('string_format', true, $_tmp, " (%s)") : smarty_modifier_string_format($_tmp, " (%s)")))) ? $this->_run_mod_handler('default', true, $_tmp, "") : smarty_modifier_default($_tmp, "")); ?>
<?php endif; ?></Option>
									<?php endfor; endif; ?>
								</select>
								<label for="coeffB">Coef.</label>
								<input size="1" type="text" name="coeffB" value="<?php echo $this->_tpl_vars['coeffB']; ?>
" tabindex="11" />
							</td>
							<td align="left">
								<label for="arbitre2">Arbitre</label>
								<input type="text" size="30" name="arbitre2" id="arbitre2" placeholder="nom prenom, n° licence ou équipe" value="<?php echo $this->_tpl_vars['arbitre2']; ?>
" tabindex="14"/>
								<input type="text" size="5" name="arbitre2_matric" readonly id="arbitre2_matric" value="<?php echo $this->_tpl_vars['arbitre2_matric']; ?>
"/>
								<br />
								<label for="comboarbitre2">secondaire</label>
								<select class="combolong" name="comboarbitre2" onChange="arbitre2_matric.value=this.options[this.selectedIndex].value; arbitre2.value=this.options[this.selectedIndex].text;" tabindex="15">
									<Option Value="-1"></Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayArbitre']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayArbitre'][$this->_sections['i']['index']]['Matric']; ?>
"><?php echo $this->_tpl_vars['arrayArbitre'][$this->_sections['i']['index']]['Identite']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
								<a href="#" id='rechercheArbitre2'><img width="16" src="../img/b_search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
							</td>
						</tr>
						<tr class="hideTr">
							<td align="left" id='clickup' name='clickup' style="color:#555555" colspan="2">
								<i><u>Masquer le formulaire</u></i>
							</td>
							<td align="center" colspan=2>
								<input type="button" onclick="Add();" id="addMatch" name="addMatch" value="Ajouter" tabindex="16">
								<input type="button" <?php if ($this->_tpl_vars['idMatch'] == '-1'): ?> disabled <?php endif; ?> onclick="Update();" id="updateMatch" name="updateMatch" value="Modifier" tabindex="17">
								<input type="button" onclick="Raz();" id="razMatch" name="razMatch" value="Annuler" tabindex="18">
							</td>
							<td align="right">
								<a href="GestionEquipeJoueur.php?idEquipe=1"><i>Pool arbitres...</i></a>
							</td>
						</tr>
						<tr id='clickdown' name='clickdown'>
							<td colspan="6" align="left" style="color:#555555"><i><u>Afficher le formulaire</u></i></td>
						</tr>
					</table>
					<?php endif; ?>
				</div>
				<div class='blocMiddle'>
					<table width=100%>
						<tr>
							<td width=480>
						       	<fieldset>
									<label>Sélection:</label>
									&nbsp;
									<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="setCheckboxes('formJournee', 'checkMatch', true);return false;"><img width="24" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" /></a>
									<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="setCheckboxes('formJournee', 'checkMatch', false);return false;"><img width="24" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" /></a>
									<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
										<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="RemoveCheckboxes('formJournee', 'checkMatch')" alt="Supprimer la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Supprimer la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="24" src="../img/supprimer.gif" /></a>
										<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');publiMultiMatchs();" alt="Publier/dépublier la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Publier/dépublier la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="29" src="../img/oeil2.gif" /></a>
										<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouPubliMultiMatchs();" alt="Verrouiller & Publier la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Verrouiller & Publier la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="29" src="../img/oeilverrou2.gif" /></a>
											<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');verrouMultiMatchs();" alt="Verrouiller/déverrouiller la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Verrouiller/déverrouiller la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="29" src="../img/verrou2.gif" /></a>
										<?php endif; ?>
										<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');affectMultiMatchs();" alt="Affectation auto des équipes et arbitres pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Affectation auto des équipes et arbitres pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="29" src="../img/AffectAuto.gif" /></a>
										<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');annulMultiMatchs();" alt="Annuler l'affectation auto des équipes et arbitres pour la sélection (supprime équipes et arbitres) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Annuler l'affectation auto des équipes et arbitres pour la sélection (supprime équipes et arbitres) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="29" src="../img/AnnulAuto.gif" /></a>
										<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');changeMultiMatchs();" alt="Changer de journée / de phase / de poule pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Changer de journée / de phase / de poule pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="24" height="24" src="../img/Chang.gif" border="0"></a>
									<?php endif; ?>
									<a href="#" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 onclick="SelectedCheckboxes('formJournee', 'checkMatch');this.href='FeuilleMatchMulti.php?listMatch='+document.formJournee.ParamCmd.value;" Target="_blank" alt="Feuilles de Matchs pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Feuilles de Matchs pour la sélection <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="24" src="../img/pdfMulti.gif" /></a>
								</fieldset>
							</td>
							<td width=450>
						       	<fieldset>
									<label>Tous les matchs:</label>
									&nbsp;
									<a href="FeuilleListeMatchs.php" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 Target="_blank" alt="Liste des Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Liste des Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="36" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="FeuilleListeMatchsEN.php" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 Target="_blank" alt="Game list (EN) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Game list (EN) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="36" src="../img/ListeEN.gif" /></a>
									&nbsp;
									<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['listMatch']; ?>
" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 Target="_blank" alt="Toutes les feuilles de Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Toutes les feuilles de Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="24" src="../img/pdfMulti.gif" /></a>
									&nbsp;
									<a href="tableau_tbs.php" alt="Export tableau des matchs (OpenOffice / Excel)" title="Export tableau des matchs (OpenOffice / Excel)"><img width="24" src="../img/OOo.gif" /></a>
									&nbsp;
									<a href="../PdfListeMatchs.php" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 Target="_blank" alt="Liste publique des Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Liste publique des Matchs <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="36" src="../img/ListeFR.gif" /></a>
									&nbsp;
									<a href="../PdfListeMatchsEN.php" <?php echo $this->_tpl_vars['TropDeMatchs']; ?>
 Target="_blank" alt="Public Game list (EN) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
" title="Public Game list (EN) <?php echo $this->_tpl_vars['TropDeMatchsMsg']; ?>
"><img width="36" src="../img/ListeEN.gif" /></a>
								</fieldset>
							</td>
							<td>
								&nbsp;&nbsp;
								<span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='20'>
							</td>
						</tr>
					</table>
				</div>
				<div class='blocBottom'>
					<div class='blocTable' id='blocMatchs'>
						<table class='tableau' id='tableMatchs'>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th><img width="19" height="16" src="../img/oeil2.gif" alt="Publier ?" title="Publier ?" border="0"></th>
									<th>N°</th>
									<th width=45>&nbsp;</th>
									<th>Heure</th>
									<th>Cat.</th>
									<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
										<th>Phase</th>
										<th>Intitulé</th>
									<?php else: ?>
										<th>Code</th>
										<th>Lieu</th>
									<?php endif; ?>
									<th>Type</th>
									<th>Terr</th>
									<th>Equipe A</th>
									<th>Sc A</th>
									<th><img width="19" height="16" src="../img/verrou2.gif" alt="Verrouiller ?" title="Verrouiller ? (et publier le score)" border="0"></th>
									<th>Sc B</th>
									<th>Equipe B</th>
									<th>Arbitre 1 </th>	
									<th>Arbitre 2 </th>	
									<th colspan=2>coef.</th>
									<th>&nbsp;</th>
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
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
 <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['StdOrSelected']; ?>
'>
										<td><input type="checkbox" name="checkMatch" value="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
										<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['MatchAutorisation'] == 'O' && $this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td class='color<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication']; ?>
2'>
											<!--
												<a href="#" Id="Publication<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" onclick="publiMatch('<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication']; ?>
')">
													<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
												</a>
											-->
												<img class="publiMatch" data-valeur="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" width="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication'] == 'O'): ?>Public<?php else: ?>Non public<?php endif; ?>" />
											</td>
											<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'] != 'O'): ?>
												<td><span class='directInput numMatch' Id="Numero_ordre-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
0'><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</span></td>
												<td width=80>
													<a href="#" class="showOn" onclick="ParamMatch(<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
)"><img width="16" height="16" src="../img/b_edit.png" alt="Modifier" title="Modifier le match" border="0"></a>
													<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank"><img width="17" height="17" src="../img/pdf.gif" alt="Feuille de match Pdf" title="Feuille de match Pdf" border="0"></a>
													<br />
													<!--<a href="#" class="showOn" onclick="window.open('GestionMatchDetail1.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&numOrdre=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
','Feuille'); return false;" ><img width="20" height="16" src="../img/b_match.png" alt="Feuille de match en ligne" title="Feuille de match en ligne" border="0"></a>-->
													<a href="#" onclick="window.open('FeuilleMarque2.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','FeuilleV2'); return false;" ><img height="24" src="v2/images/tab.png" alt="Feuille de match en ligne" title="Feuille de match en ligne v2.0" border="0"></a>
												</td>
												<td><span class='directInput date' Id="Date_match-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-date" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
1"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
</span><br>
													<span class='directInput heure' Id="Heure_match-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-time" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
2"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</span></td>
												<td title="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
"><span class="compet"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php endif; ?></span></td>
												<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
													<td><span class="phase"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><span class='directInput text eq' tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
3' Id="Libelle-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php else: ?>
													<td><span class='directInput text eq' tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
3' Id="Libelle-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><span class="lieu"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php endif; ?>
												<td><img class="typeMatch" data-valeur="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" src="../img/type<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
												<td><span class='directInput terrain' tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
4" Id="Terrain-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<td>
													<!--<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA'] != '0' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA'] != ''): ?>
														<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</a>&nbsp;
													<?php else: ?>-->													<!--<?php endif; ?>-->
													<span class="directInput equipe<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA'] < 1): ?> undefTeam<?php endif; ?>" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
9" Id="EquipeA-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" data-idequipe="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA']; ?>
" data-equipe="A"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A" title="Composition équipe A"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td><span class='directInput score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
5" Id="ScoreA-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA']; ?>
</span></td>
												<td class='color<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
2'>
													<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
													<!--
														<a href="#" Id="Validation<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" onclick="verrouMatch('<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
')">
															<img width="24" height="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
														</a>
													-->
														<img class="verrouMatch" data-valeur="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" width="24" src="../img/verrou2<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
.gif" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'] == 'O'): ?>Validé / verrouillé (score public)<?php else: ?>Non validé (score non public)<?php endif; ?>" />
													<?php else: ?>
														<img width="24" height="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
													<?php endif; ?>
													<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'ON'): ?>
														<span class="statutMatchOn" title="Période <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php elseif ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'END'): ?>
														<span class="statutMatchOn" title="Match terminé"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php else: ?>
														<span class="scoreProvisoire" title="Match en attente"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']; ?>
</span>
													<?php endif; ?>
												</td>
												<td><span class='directInput score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" Id="ScoreB-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB']; ?>
</span></td>
												<td>
													<span class="directInput equipe<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeB'] < 1): ?> undefTeam<?php endif; ?>" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
9" Id="EquipeB-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" data-idequipe="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeB']; ?>
" data-equipe="B"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=B" title="Composition équipe B"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td>
													<span class="directInput arbitre<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Matric_arbitre_principal'] == 0): ?> pbArb<?php endif; ?>" tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" data-id="Arbitre_principal" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" Id="Arbitre_principal-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', ' <br />(') : smarty_modifier_replace($_tmp, ' (', ' <br />(')))) ? $this->_run_mod_handler('replace', true, $_tmp, ') ', ')<br /> ') : smarty_modifier_replace($_tmp, ') ', ')<br /> ')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-1', '') : smarty_modifier_replace($_tmp, '-1', '')); ?>
</span>
												</td>
												<td>
													<span class="directInput arbitre<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Matric_arbitre_secondaire'] == 0): ?> pbArb<?php endif; ?>" tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" data-id="Arbitre_secondaire" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" Id="Arbitre_secondaire-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', ' <br />(') : smarty_modifier_replace($_tmp, ' (', ' <br />(')))) ? $this->_run_mod_handler('replace', true, $_tmp, ') ', ')<br /> ') : smarty_modifier_replace($_tmp, ') ', ')<br /> ')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-1', '') : smarty_modifier_replace($_tmp, '-1', '')); ?>
</span>
												</td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td><a href="#" onclick="RemoveCheckbox('formJournee', '<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
');return false;"><img width="16" height="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" border="0"></a></td>
											<?php else: ?>
												<td><span class='directInputOff numMatch' Id="Numero_ordre-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
0'><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</span></td>
												<td width=80>
													<a href="#" class="showOff" onclick="ParamMatch(<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
)"><img width="16" height="16" src="../img/b_edit.png" alt="Modifier" title="Modifier le match" border="0"></a>
													<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank"><img width="17" height="17" src="../img/pdf.gif" alt="Feuille de match Pdf" title="Feuille de match Pdf" border="0"></a>
													<br />
													<!--<a href="#" class="showOff" onclick="window.open('GestionMatchDetail1.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&numOrdre=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
','Feuille'); return false;" ><img width="20" height="16" src="../img/b_match.png" alt="Feuille de match en ligne" title="Feuille de match en ligne" border="0"></a>-->
													<a href="#" onclick="window.open('FeuilleMarque2.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','FeuilleV2'); return false;" ><img height="24" src="v2/images/tab.png" alt="Feuille de match en ligne" title="Feuille de match en ligne v2.0" border="0"></a>
												</td>
												<td><span class='directInputOff date' Id="Date_match-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-date" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
1"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
</span><br>
													<span class='directInputOff heure' Id="Heure_match-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-time" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
2"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</span></td>
												<td title="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
"><span class="compet"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php endif; ?></span></td>
												<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
													<td><span class="phase"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><span class='directInputOff text eq' tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
3' Id="Libelle-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php else: ?>
													<td><span class='directInputOff text eq' tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
3' Id="Libelle-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><span class="lieu"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php endif; ?>
												<td><img class="typeMatchOff" data-valeur="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" src="../img/type<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
												<td><span class='directInputOff terrain' tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
4" Id="Terrain-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<td>
													<span class="directInputOff equipe<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA'] < 1): ?> undefTeam<?php endif; ?>" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
9" Id="EquipeA-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" data-idequipe="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeA']; ?>
" data-equipe="A"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A" title="Composition équipe A"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td><span class='directInputOff score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
5" Id="ScoreA-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA']; ?>
</span></td>
												<td class='color<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
2'>
													<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
													<!--	
														<a href="#" Id="Validation<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" onclick="verrouMatch('<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
')">
															<img width="24" height="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
														</a>
													-->	
														<img class="verrouMatch" data-valeur="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
" data-id="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" width="24" src="../img/verrou2<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
.gif" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'] == 'O'): ?>Validé / verrouillé (score public)<?php else: ?>Non validé (score non public)<?php endif; ?>" />
													<?php else: ?>
														<img width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
													<?php endif; ?>
													<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'ON'): ?>
														<span class="statutMatchOn" title="Période <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php elseif ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'END'): ?>
														<span class="statutMatchOn" title="Match terminé"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php else: ?>
														<span class="scoreProvisoire" title="Match en attente"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']; ?>
</span>
													<?php endif; ?>
												</td>
												<td><span class='directInputOff score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" Id="ScoreB-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB']; ?>
</span></td>
												<td>
													<span class="directInputOff equipe<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeB'] < 1): ?> undefTeam<?php endif; ?>" tabindex="1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
9" Id="EquipeB-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" data-idequipe="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_equipeB']; ?>
" data-equipe="B"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=B" title="Composition équipe B"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td>
													<span class="directInputOff arbitre<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Matric_arbitre_principal'] == 0): ?> pbArb<?php endif; ?>" tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" data-id="Arbitre_principal" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" Id="Arbitre_principal-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', ' <br />(') : smarty_modifier_replace($_tmp, ' (', ' <br />(')))) ? $this->_run_mod_handler('replace', true, $_tmp, ') ', ')<br /> ') : smarty_modifier_replace($_tmp, ') ', ')<br /> ')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-1', '') : smarty_modifier_replace($_tmp, '-1', '')); ?>
</span>
												</td>
												<td>
													<span class="directInputOff arbitre<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1' && $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Matric_arbitre_secondaire'] == 0): ?> pbArb<?php endif; ?>" tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6" data-id="Arbitre_secondaire" data-match="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" data-journee="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id_journee']; ?>
" Id="Arbitre_secondaire-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, ' (', ' <br />(') : smarty_modifier_replace($_tmp, ' (', ' <br />(')))) ? $this->_run_mod_handler('replace', true, $_tmp, ') ', ')<br /> ') : smarty_modifier_replace($_tmp, ') ', ')<br /> ')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-1', '') : smarty_modifier_replace($_tmp, '-1', '')); ?>
</span>
												</td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td>&nbsp;</td>
											<?php endif; ?>
										<?php elseif ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['MatchAutorisation'] == 'O' && $this->_tpl_vars['profile'] == 9 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
											<td>	
												<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
											</td>
											<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'] != 'O'): ?>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</td>
												<td width=80>
													<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank"><img width="17" height="17" src="../img/pdf.gif" alt="Feuille de match Pdf" title="Feuille de match Pdf" border="0"></a>
													<br />
													<!--<a href="#" onclick="window.open('GestionMatchDetail1.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&numOrdre=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
','Feuille'); return false;" ><img width="20" height="16" src="../img/b_match.png" alt="Feuille de match en ligne" title="Feuille de match en ligne" border="0"></a>-->
													<a href="hey#" onclick="window.open('FeuilleMarque2.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
','FeuilleV2'); return false;" ><img height="24" src="v2/images/tab.png" alt="Feuille de match en ligne" title="Feuille de match en ligne v2.0" border="0"></a>
												</td>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
<br>
													<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</td>
												<td title="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
"><span class="compet"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php endif; ?></span></td>
												<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
													<td><span class="phase"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
												<?php else: ?>
													<td colspan=2><span class="lieu"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php endif; ?>
												<td><img src="../img/type<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
												<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
												<td>
													<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A" title="Composition équipe A"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td class='directInput score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
5"><span Id="ScoreA-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA']; ?>
</span></td>
												<td class='color<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
2'>
													<img width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
													<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'ON'): ?>
														<span class="statutMatchOn" title="Période <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Periode']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php elseif ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut'] == 'END'): ?>
														<span class="statutMatchOn" title="Match terminé"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Statut']; ?>
</span>
														<span class="scoreProvisoire" title="Score provisoire"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreDetailB']; ?>
</span>
													<?php endif; ?>
												</td>
												<td class='directInput score' tabindex="2<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%02d') : smarty_modifier_string_format($_tmp, '%02d')); ?>
6"><span Id="ScoreB-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB']; ?>
</span></td>
												<td>
													<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=B" title="Composition équipe B"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td>&nbsp;</td>
											<?php else: ?>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</td>
												<td width=80>
													<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank"><img width="17" height="17" src="../img/pdf.gif" alt="Feuille de match Pdf" title="Feuille de match Pdf" border="0"></a>
												</td>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
<br>
													<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</td>
												<td title="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
"><span class="compet"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php endif; ?></span></td>
												<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
													<td><span class="phase"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
													<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
												<?php else: ?>
													<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
													<td><span class="lieu"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<?php endif; ?>
												<td><img src="../img/type<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
												<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
												<td>
													<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A" title="Composition équipe A"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA']; ?>
</td>
												<td class='color<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation']; ?>
2'>
													<img width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
												</td>
												<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB']; ?>
</td>
												<td>
													<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB']; ?>
</span>
													<br />
													<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=B" title="Composition équipe B"><img width="10" src="../img/b_sbrowse.png"></a>
												</td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB'] != 1): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
/<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
<?php endif; ?></td>
												<td>&nbsp;</td>
											<?php endif; ?>
										<?php else: ?>
											<td>	
												<img width="24" height="24" src="../img/oeil2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Publication'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Publier O/N" title="Publier O/N" border="0">
											</td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</td>
											<td width=80>
												<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
" Target="_blank"><img width="17" height="17" src="../img/pdf.gif" alt="Feuille de match Pdf" title="Feuille de match Pdf" border="0"></a>
											</td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Date_match']; ?>
<br>
												<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>
</td>
											<td title="<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
"><span class="compet"><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2'] != ''): ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Soustitre2']; ?>
<?php else: ?><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
<?php endif; ?></span></td>
											<?php if ($this->_tpl_vars['PhaseLibelle'] == 1): ?>
												<td><span class="phase"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
												<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Libelle'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<?php else: ?>
												<td colspan=2><span class="lieu"><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</span></td>
											<?php endif; ?>
											<td><img src="../img/type<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type']; ?>
.png" title="<?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Type'] == 'C'): ?>Classement<?php else: ?>Elimination<?php endif; ?>" /></td>
											<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Terrain'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<td>
												<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeA']; ?>
</span>
												<br />
												<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=A" title="Composition équipe A"><img width="10" src="../img/b_sbrowse.png"></a>
											</td>
											<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreA'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<td>
												<img width="24" src="../img/verrou2<?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Validation'])) ? $this->_run_mod_handler('default', true, $_tmp, 'N') : smarty_modifier_default($_tmp, 'N')); ?>
.gif" alt="Verrouiller O/N" title="Verrouiller O/N (et publier le score)" border="0">
											</td>
											<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['ScoreB'])) ? $this->_run_mod_handler('default', true, $_tmp, '&nbsp;') : smarty_modifier_default($_tmp, '&nbsp;')); ?>
</td>
											<td>
												<span><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['EquipeB']; ?>
</span>
												<br />
												<a href="GestionMatchEquipeJoueur.php?idMatch=<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
&codeEquipe=B" title="Composition équipe B"><img width="10" src="../img/b_sbrowse.png"></a>
											</td>
											<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_principal'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
											<td><?php if ($this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'] != '-1'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire'])) ? $this->_run_mod_handler('replace', true, $_tmp, '(', '<br>(') : smarty_modifier_replace($_tmp, '(', '<br>(')); ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffA']; ?>
</td>
											<td><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['CoeffB']; ?>
</td>
											<td>&nbsp;</td>
										<?php endif; ?>
									</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
						<br />
					</div>
						<?php $this->assign('nbmatch', $this->_sections['i']['iteration']-1); ?>
						<?php if ($this->_tpl_vars['nbmatch'] > 0): ?>Nb matchs : <?php echo $this->_tpl_vars['nbmatch']; ?>
<?php endif; ?>
				</div>
			</form>
		</div>
		