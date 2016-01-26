<?php /* Smarty version 2.6.18, created on 2015-04-10 12:12:45
         compiled from GestionEvenement.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionEvenement.tpl', 1, false),)), $this); ?>
    &nbsp;(<a href="GestionCompetition.php">Retour</a>)	<div class="main">		<form method="POST" action="GestionEvenement.php" name="formEvenement" enctype="multipart/form-data">			<input type='hidden' name='Cmd' Value='' />			<input type='hidden' name='ParamCmd' Value='' />			<input type='hidden' name='Pub' Value='' />			<input type='hidden' name='idEvenement' Value='<?php echo $this->_tpl_vars['idEvenement']; ?>
' />			<div class='blocLeft'>				<div class='titrePage'>Evènements</div>				<div class='blocTable' id='blocCompet'>					<table class='tableau' id='tableCompet'>						<thead>							<tr class='header'>								<th width=18><img width="19" src="../img/oeil2.gif" alt="Publier ?" title="Publier ?" /></th>								<th>Id</th>								<th>&nbsp;</th>								<th>Libelle</th>								<th>Lieu</th>								<th>Début</th>								<th>Fin</th>								<th>&nbsp;</th>							</tr>						</thead>						<tbody>							<?php unset($this->_sections['i']);
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
?>								<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
 <?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['StdOrSelected']; ?>
'>																											<td class='color<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Publication']; ?>
2'>										<a href="#" Id="Publication<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
" onclick="publiEvt(<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
,'<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Publication']; ?>
')">											<img width="24" src="../img/oeil2<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Publication']; ?>
.gif" alt="Publier O/N" title="Publier O/N" />										</a>									</td>									<td><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
</td>									<td><a href="#" Id="Param<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
" onclick="paramEvt(<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
)"><img width="18" src="../img/b_edit.png" alt="Modifier" title="Modifier" /></a></td>									<td><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Libelle']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Lieu']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Date_debut']; ?>
</td>									<td><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Date_fin']; ?>
</td>									<?php if ($this->_tpl_vars['profile'] <= 1): ?>										<td><a href="#" onclick="RemoveCheckbox('formEvenement', '<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
');return false;"><img width="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" /></a></td>									<?php else: ?>										<td>&nbsp;</td>									<?php endif; ?>								</tr>							<?php endfor; endif; ?>						</tbody>					</table>				</div>			</div>			<div class='blocRight'>				<table width=100%>					<tr>						<th class='titreForm' colspan=2>							<label><?php if ($this->_tpl_vars['idEvenement'] == -1): ?>Ajouter un <?php else: ?>Modifier l'<?php endif; ?>évènement</label>						</th>					</tr>					<tr>						<td colspan=2>							<label for="Libelle">Libellé :</label>							<input type="text" name="Libelle" value="<?php echo $this->_tpl_vars['Libelle']; ?>
" maxlength=40 id="Libelle"/>						</td>					</tr>					<tr>						<td colspan=2>							<label for="Lieu">Lieu : </label>							<input type="text" name="Lieu" value="<?php echo $this->_tpl_vars['Lieu']; ?>
" maxlength=40 id="Lieu"/>						</td>					</tr>					<tr>						<td>							<label for="Date_debut">Date Début :</label>							<input type="text" class='date' name="Date_debut" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" id="Date_debut" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" />						</td>						<td>							<label for="Date_fin">Date Fin :</label>							<input type="text" class='date' name="Date_fin" value="<?php echo $this->_tpl_vars['Date_fin']; ?>
" id="Date_fin" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" />						</td>					</tr>					<tr>						<?php if ($this->_tpl_vars['idEvenement'] != -1): ?>							<td>								<br>								<br>								<input type="button" onclick="updateEvt()" id="updateEvenement" name="updateEvenement" value="<< Modifier">							</td>							<td>								<br>								<br>								<input type="button" onclick="razEvt()" id="razEvenement" name="razEvenement" value="Annuler">							</td>						<?php else: ?>							<td colspan=2>								<br>								<br>								<input type="button" onclick="addEvt()" name="addEvenement" value="<< Ajouter">							</td>						<?php endif; ?>					</tr>				</table>			</div>		</form>	</div>	