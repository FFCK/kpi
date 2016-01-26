<?php /* Smarty version 2.6.18, created on 2015-04-09 20:05:15
         compiled from importPCE.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'importPCE.tpl', 1, false),)), $this); ?>
	<div class="main">		<div class="ImportPCE">					<form method="POST" action="ImportPCE.php" name="ImportPCE" id="ImportPCE" enctype="multipart/form-data">			<input type='hidden' name='json_data' id='json_data' Value=''/>			<input type='hidden' name='Control' id='Control' Value=''/>								<table>					<tr>						<td>							<?php if ($this->_tpl_vars['profile'] <= 6): ?>								<fieldset>									<legend>Mise à jour licenciés</legend>										<input type="button" name="importPCE2" id="importPCE2" value="Mise à jour des licenciés (base fédérale J-1)">																	</fieldset>							<?php endif; ?>						</td>						<td>							<span id="json_msg">								<?php echo $this->_tpl_vars['msg_json']; ?>
							</span>							<br>							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayinfo']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?> 								<?php echo $this->_tpl_vars['arrayinfo'][$this->_sections['i']['index']]; ?>
<BR>							<?php endfor; endif; ?>							<br>						</td>						<td>							<?php if ($this->_tpl_vars['profile'] <= 2): ?>								<fieldset>									<legend>Mise à jour calendrier fédéral</legend>										Calendrier fédéral : <input type="file" name="calendrier"><br>									<input type="submit" name="uploadCalendrierCsv" value="Importation Calendrier (calendrier.csv)">								</fieldset>							<?php endif; ?>							<?php if ($this->_tpl_vars['profile'] <= 3): ?>								<fieldset>									<?php if ($this->_tpl_vars['production'] == 'P'): ?>										<legend>Import depuis mode local</legend>									<?php else: ?>										<legend>Import vers mode local</legend>									<?php endif; ?>									<label for="lstEvent">Liste des Ev&eacute;nements à Importer</label><br>									<input type="text" name="lstEvent" maxlength=20 size=10 id="lstEvent"/>									<img title="Numéros d'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates." 												alt="Numéros d'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates." 												src="../img/b_help.png" 												onclick="alert('Numéros d\'évènements, séparés par une virgule. Vous devez avoir les autorisations adéquates.')" />									<br>																	<?php if ($this->_tpl_vars['production'] == 'P'): ?>										<input type="button" name="btnImportServer" id="btnImportServer" value="Importation (WAMP ==> KPI)">									<?php else: ?>										<br>										<label for="user">Identifiant KPI &nbsp;&nbsp;</label>										<input type="text" name="user" maxlength=20 id="user" autocomplete="off" />										<br>										<label for="pwd">Mot de passe KPI</label>										<input type="password" name="pwd" maxlength=20 id="pwd" autocomplete="off" />										<br>										<input type="button" name="btnImport" id="btnImport" value="Importation (KPI ==> WAMP)">									<?php endif; ?>								</fieldset>							<?php endif; ?>						<td>					</tr>				</table>			</form>						<?php if ($this->_tpl_vars['profile'] <= 1): ?>				<h2>Upload images</h2>				<b>Paramètres upload (avant de sélectionner l'image) :</b><br />				Type:<select id="TypeImg" name="TypeImg">					<option value="L-">Logo</option>					<option value="S-">Sponsor</option>				</select>				Compétition:<select id="CompImg" name="CompImg">				<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayGroupes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?>					<option value="<?php echo $this->_tpl_vars['arrayGroupes'][$this->_sections['i']['index']]['Groupe']; ?>
-"><?php echo $this->_tpl_vars['arrayGroupes'][$this->_sections['i']['index']]['Libelle']; ?>
</option>				<?php endfor; endif; ?>				</select>				Saison:<input class="court" type="text" size="4" id="SaisonImg" name="SaisonImg" value="<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, '%Y') : smarty_modifier_date_format($_tmp, '%Y')); ?>
">				<input type="button" id="validNomImg" value="Valider">				<form action="upload.php" class="dropzone" id="my-awesome-dropzone">					<label for="titre">Nom du fichier (max. 50 caractères) :</label>					<input type="text" name="titre" placeholder="Ex: L-N1H-2014.jpg" id="titre" /><br />					<label for="dest">Dossier de destination :</label>					<select id="dest" name="dest">						<option value="logo">Logo</option>						<option value="Pays">Pays</option>					</select>				</form>			<?php endif; ?>		</div>	</div>	  	   