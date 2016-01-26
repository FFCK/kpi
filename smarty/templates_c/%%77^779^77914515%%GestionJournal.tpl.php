<?php /* Smarty version 2.6.18, created on 2014-11-23 19:21:28
         compiled from GestionJournal.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionJournal.tpl', 1, false),array('modifier', 'date_format', 'GestionJournal.tpl', 1, false),)), $this); ?>
    &nbsp;(<a href="GestionUtilisateur.php">Retour</a>)	<div class="main">		<form method="POST" action="GestionJournal.php" name="formJournal" enctype="multipart/form-data">			<input type='hidden' name='Cmd' Value=''/>			<input type='hidden' name='ParamCmd' Value=''/>			<div class='blocLeft'>				<div class='titrePage'>Journal</div>				<div class='blocTable' id='blocJournal'>					<table class='tableau' id='tableJournal'>						<thead>							<tr class='header'>								<th>Dates</th>								<th>Identite</th>								<th>Actions</th>								<th>Journal</th>								<th>Comp.</th>								<th>Journ.</th>								<th>Match</th>							</tr>						</thead>						<tbody>							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayJournal']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?>								<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>									<td><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Dates'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "<b>%d/%m/%y</b><br>à  %H:%M") : smarty_modifier_date_format($_tmp, "<b>%d/%m/%y</b><br>à  %H:%M")); ?>
</td>									<td title='<?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Fonction']; ?>
'><b><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Identite']; ?>
</b></td>									<td><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Actions']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Journal']; ?>
</td>									<td><b><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Competitions']; ?>
</b><br><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Saisons']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Journees']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayJournal'][$this->_sections['i']['index']]['Matchs']; ?>
</td>								</tr>							<?php endfor; endif; ?>						</tbody>					</table>				</div>			</div>			<div class='blocRight'>				<table width=100%>					<tr>						<th colspan=2 class='titreForm'>							<label>Sélection</label>						</th>					</tr>					<tr>						<td colspan=2>							<label for="theLimit">Nb de lignes :</label>							<select name="theLimit">									<Option Value="25" <?php if ($this->_tpl_vars['theLimit'] == '25'): ?>selected<?php endif; ?>>25</Option>									<Option Value="50" <?php if ($this->_tpl_vars['theLimit'] == '50'): ?>selected<?php endif; ?>>50</Option>									<Option Value="100" <?php if ($this->_tpl_vars['theLimit'] == '100'): ?>selected<?php endif; ?>>100</Option>									<Option Value="200" <?php if ($this->_tpl_vars['theLimit'] == '200'): ?>selected<?php endif; ?>>200</Option>									<Option Value="500" <?php if ($this->_tpl_vars['theLimit'] == '500'): ?>selected<?php endif; ?>>500</Option>									<Option Value="1000" <?php if ($this->_tpl_vars['theLimit'] == '1000'): ?>selected<?php endif; ?>>1000</Option>							</select>						</td>					</tr>					<tr>						<td colspan=2>							<label for="theUser">Utilisateurs :</label>							<select name="theUser">								<Option Value="">Tous</Option>								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayUsers']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?> 									<Option Value="<?php echo $this->_tpl_vars['arrayUsers'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arrayUsers'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['theUser']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayUsers'][$this->_sections['i']['index']]['Identite']; ?>
 - <?php echo $this->_tpl_vars['arrayUsers'][$this->_sections['i']['index']]['Fonction']; ?>
</Option>								<?php endfor; endif; ?>							</select>						</td>					</tr>					<tr>						<td colspan=2>							<label for="theAction">Actions :</label>							<select name="theAction">								<Option Value="">Toutes</Option>								<Option Value="Connexion" <?php if ($this->_tpl_vars['theAction'] == 'Connexion'): ?>selected<?php endif; ?>>Connexions</Option>								<Option Value="Ajout" <?php if ($this->_tpl_vars['theAction'] == 'Ajout'): ?>selected<?php endif; ?>>Ajouts</Option>								<Option Value="Modif" <?php if ($this->_tpl_vars['theAction'] == 'Modif'): ?>selected<?php endif; ?>>Modifications</Option>								<Option Value="Supp" <?php if ($this->_tpl_vars['theAction'] == 'Supp'): ?>selected<?php endif; ?>>Suppressions</Option>								<Option Value="Calcul" <?php if ($this->_tpl_vars['theAction'] == 'Calcul'): ?>selected<?php endif; ?>>Calculs</Option>							</select>						</td>					</tr>					<tr>						<td>							<label for="theSaison">Saison :</label>							<input type=text name='theSaison' value='<?php echo $this->_tpl_vars['theSaison']; ?>
' size=4>						</td>						<td>							<label for="theCompet">Compétition :</label>							<input type=text name='theCompet' value='<?php echo $this->_tpl_vars['theCompet']; ?>
' >						</td>					</tr>					<tr>						<td colspan=2>							<br> 							<br>							<input type="submit" name="Selection" value="Sélectionner">						</td>					</tr>				</table>			</div>		</form>	</div>	