<?php /* Smarty version 2.6.18, created on 2015-01-24 21:08:41
         compiled from GestionParamJournee.tpl */ ?>
		&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
	
		<div class="main">
			<form method="POST" action="GestionParamJournee.php" name="formParamJournee" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='dupliThis' Value=''/>
				<input type='hidden' name='PrevSaison' Value='<?php echo $this->_tpl_vars['J_saison']; ?>
'/>
				<input type='hidden' name='PrevCompetition' Value='<?php echo $this->_tpl_vars['J_competition']; ?>
'/>
				<input type='hidden' name='PrevDate' Value='<?php echo $this->_tpl_vars['Date_debut']; ?>
'/>
				<input type='hidden' name='idJournee' Value='<?php echo $this->_tpl_vars['idJournee']; ?>
'/>
				<div class='blocRight Right3'>
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=2>
								<?php if ($this->_tpl_vars['Num_Journee'] == 0): ?>
								<label>Créer une journée/phase</label>
								<?php else: ?>
								<label>Modifier la journée/phase</label>
								<?php endif; ?>
							</th>
						</tr>
						<?php if ($this->_tpl_vars['profile'] <= 2): ?>
							<tr>
								<td><label for="J_saison">Saison</label>
									<img hspace="2" width="18" height="18" src="../img/danger.png" alt="Attention aux conséquences d'une modification" title="Attention aux conséquences d'une modification" border="0">
									<select name="J_saison" onchange="alert('Attention, aux conséquences d une modification de ce paramètre')">
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaisons']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<Option Value="<?php echo $this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['J_saison']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code']; ?>
</Option>
										<?php endfor; endif; ?>
								    </select>
								</td>
								<td><label for="J_competition">Competition</label>
									<img hspace="2" width="18" height="18" src="../img/danger.png" alt="Attention aux conséquences d'une modification" title="Attention aux conséquences d'une modification" border="0">
									<select name="J_competition" onchange="alert('Attention, aux conséquences d une modification de ce paramètre')">
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
" <?php if ($this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['J_competition']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Code']; ?>
 - <?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php endfor; endif; ?>
								    </select>
								</td>
							</tr>
						<?php else: ?>
							<tr>
								<td><label for="J_saison">Saison : </label><?php echo $this->_tpl_vars['J_saison']; ?>
<input type='hidden' name='J_saison' Value='<?php echo $this->_tpl_vars['J_saison']; ?>
'/></td>
								<td><label for="J_competition">Competition : </label><?php echo $this->_tpl_vars['J_competition']; ?>
<input type='hidden' name='J_competition' Value='<?php echo $this->_tpl_vars['J_competition']; ?>
'/></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td width="50%">
								<label for="Phase">Phase (compétition type Coupe)</label><input type="text" name="Phase" value="<?php echo $this->_tpl_vars['Phase']; ?>
"/>
								<select id="PhaseList" name="PhaseList" onChange="Phase.value=this.options[this.selectedIndex].value">
									<optgroup label="Modèles FR (EN plus bas)">
										<Option Value="Poule A">Poule A</Option>
										<Option Value="Poule B">Poule B</Option>
										<Option Value="Poule C">Poule C</Option>
										<Option Value="Poule D">Poule D</Option>
										<Option Value="Poule E">Poule E</Option>
										<Option Value="Poule F">Poule F</Option>
										<Option Value="Poule G">Poule G</Option>
										<Option Value="Poule H">Poule H</Option>
										<Option Value="Poule I">Poule I</Option>
										<Option Value="Poule J">Poule J</Option>
										<Option Value="Poule K">Poule K</Option>
										<Option Value="Poule L">Poule L</Option>
										<Option Value="Poule M">Poule M</Option>
										<Option Value="Poule N">Poule N</Option>
										<Option Value="Poule O">Poule O</Option>
										<Option Value="Classement">Classement</Option>
										<Option Value="1/8 finale">1/8 finale</Option>
										<Option Value="1/4 finale">1/4 finale</Option>
										<Option Value="1/2 finale">1/2 finale</Option>
										<Option Value="27ème place">27ème place</Option>
										<Option Value="25ème place">25ème place</Option>
										<Option Value="23ème place">23ème place</Option>
										<Option Value="21ème place">21ème place</Option>
										<Option Value="19ème place">19ème place</Option>
										<Option Value="17ème place">17ème place</Option>
										<Option Value="15ème place">15ème place</Option>
										<Option Value="13ème place">13ème place</Option>
										<Option Value="11ème place">11ème place</Option>
										<Option Value="9ème place">9ème place</Option>
										<Option Value="7ème place">7ème place</Option>
										<Option Value="5ème place">5ème place</Option>
										<Option Value="3ème place">3ème place</Option>
										<Option Value="Finale">Finale</Option>
									</optgroup>
									<optgroup label="pause">
										<Option Value="PAUSE">PAUSE</Option>
									</optgroup>
									<optgroup label="Models (EN)">
										<Option Value="Group A">Group A</Option>
										<Option Value="Group B">Group B</Option>
										<Option Value="Group C">Group C</Option>
										<Option Value="Group D">Group D</Option>
										<Option Value="Group E">Group E</Option>
										<Option Value="Group F">Group F</Option>
										<Option Value="Group G">Group G</Option>
										<Option Value="Group H">Group H</Option>
										<Option Value="Group I">Group I</Option>
										<Option Value="Group J">Group J</Option>
										<Option Value="Group K">Group K</Option>
										<Option Value="Group L">Group L</Option>
										<Option Value="Group M">Group M</Option>
										<Option Value="Group N">Group N</Option>
										<Option Value="Group O">Group O</Option>
										<Option Value="Classifying">Classifying</Option>
										<Option Value="1/8 final">1/8 final</Option>
										<Option Value="1/4 final">1/4 final</Option>
										<Option Value="1/2 final">1/2 final</Option>
										<Option Value="27th place">27th place</Option>
										<Option Value="25th place">25th place</Option>
										<Option Value="23rd place">23rd place</Option>
										<Option Value="21st place">21st place</Option>
										<Option Value="19th place">19th place</Option>
										<Option Value="17th place">17th place</Option>
										<Option Value="15th place">15th place</Option>
										<Option Value="13th place">13th place</Option>
										<Option Value="11th place">11th place</Option>
										<Option Value="9th place">9th place</Option>
										<Option Value="7th place">7th place</Option>
										<Option Value="5th place">5th place</Option>
										<Option Value="3rd place">3rd place</Option>
										<Option Value="Final">Final</Option>
									</optgroup>
								</select>
							</td>
							<td width="50%">
								<label for="Niveau">Niveau (compétition type Coupe)</label>
								<select size=1 id="Niveau" name="Niveau">
								<?php unset($this->_sections['foo']);
$this->_sections['foo']['name'] = 'foo';
$this->_sections['foo']['start'] = (int)1;
$this->_sections['foo']['loop'] = is_array($_loop=30) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['foo']['show'] = true;
$this->_sections['foo']['max'] = $this->_sections['foo']['loop'];
$this->_sections['foo']['step'] = 1;
if ($this->_sections['foo']['start'] < 0)
    $this->_sections['foo']['start'] = max($this->_sections['foo']['step'] > 0 ? 0 : -1, $this->_sections['foo']['loop'] + $this->_sections['foo']['start']);
else
    $this->_sections['foo']['start'] = min($this->_sections['foo']['start'], $this->_sections['foo']['step'] > 0 ? $this->_sections['foo']['loop'] : $this->_sections['foo']['loop']-1);
if ($this->_sections['foo']['show']) {
    $this->_sections['foo']['total'] = min(ceil(($this->_sections['foo']['step'] > 0 ? $this->_sections['foo']['loop'] - $this->_sections['foo']['start'] : $this->_sections['foo']['start']+1)/abs($this->_sections['foo']['step'])), $this->_sections['foo']['max']);
    if ($this->_sections['foo']['total'] == 0)
        $this->_sections['foo']['show'] = false;
} else
    $this->_sections['foo']['total'] = 0;
if ($this->_sections['foo']['show']):

            for ($this->_sections['foo']['index'] = $this->_sections['foo']['start'], $this->_sections['foo']['iteration'] = 1;
                 $this->_sections['foo']['iteration'] <= $this->_sections['foo']['total'];
                 $this->_sections['foo']['index'] += $this->_sections['foo']['step'], $this->_sections['foo']['iteration']++):
$this->_sections['foo']['rownum'] = $this->_sections['foo']['iteration'];
$this->_sections['foo']['index_prev'] = $this->_sections['foo']['index'] - $this->_sections['foo']['step'];
$this->_sections['foo']['index_next'] = $this->_sections['foo']['index'] + $this->_sections['foo']['step'];
$this->_sections['foo']['first']      = ($this->_sections['foo']['iteration'] == 1);
$this->_sections['foo']['last']       = ($this->_sections['foo']['iteration'] == $this->_sections['foo']['total']);
?>
									<Option Value="<?php echo $this->_sections['foo']['index']; ?>
"<?php if ($this->_sections['foo']['index'] == $this->_tpl_vars['Niveau']): ?> selected<?php endif; ?>><?php echo $this->_sections['foo']['index']; ?>
</Option>
								<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="Type">Type de matchs : </label>
								<span title="Matchs de poule, classement par points, égalité possible"><img src="../img/typeC.png" style="vertical-align: middle" />Classement</span><input type="radio" name="Type" value="C" checked />
								&nbsp;&nbsp;<span title="Matchs éliminatoires, égalité impossible, prolongation si nécessaire"><img src="../img/typeE.png" style="vertical-align: middle" />Elimination</span><input type="radio" name="Type" value="E" <?php if ($this->_tpl_vars['Type'] == 'E'): ?>checked<?php endif; ?> />
							</td>
						</tr>
						<tr>
							<td colspan=2 class="vert" align='center'><label><b>Paramètres apparents dans le calendrier public</b></label></td>
						</tr>
						<tr>
							<td class="vert">
								<label for="Date_debut"><b>Date_debut</b></label>
								<input type="text" class='date' name="Date_debut" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" >
								<input type="hidden" name="Date_origine" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" >
							</td>
							<td class="vert"><label for="Date_fin"><b>Date_fin</b></label><input type="text" class='date' name="Date_fin" value="<?php echo $this->_tpl_vars['Date_fin']; ?>
" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" ></td>
						</tr>
						<tr>
							<td class="vert"><label for="Lieu"><b>Lieu</b> (commune)</label><input type="text" name="Lieu" id="Lieu" value="<?php echo $this->_tpl_vars['Lieu']; ?>
" placeholder="Commune ou code postal" /></td>
							<td class="vert"><label for="Departement"><b>Département ou code pays (CIO)</b></label><input type="text" class='dpt' name="Departement" id="Departement" value="<?php echo $this->_tpl_vars['Departement']; ?>
" placeholder="N° département ou code pays (CIO)" /></td>
						</tr>
						<tr>
							<td colspan=2 class="vert">
								<label for="Nom"><b>Nom journée (CHPT) ou Nom compétition (CP)</b></label>
								<img border="0" title="Nom qui apparaîtra dans le calendrier public." 
								alt="Nom qui apparaîtra dans le calendrier public." 
								src="../img/b_help.png" onclick="alert('Nom qui apparaîtra dans le calendrier public.')">
								<input type="text" name="Nom" id="Nom" value="<?php echo $this->_tpl_vars['Nom']; ?>
" placeholder="Nom qui apparaîtra dans le calendrier public" />
								<br>
								<label><i><u>Exemples :</u><br />
									Championnat N1F, J4<br />
									Championnat N3H, J2-B<br />
									Coupe Senior H, 1T-N<br />
									10th Veurne International Canoepolo Tournament</i><br>
								</label>
							</td>
						</tr>
						<tr>
							<td><br><label for="Organisateur">Club Organisateur</label><input type="text" name="Organisateur" id="Organisateur" value="<?php echo $this->_tpl_vars['Organisateur']; ?>
" placeholder="Nom ou numéro de club" /></td>
							<td><br><label for="Plan_eau">Plan d'eau</label><input type="text" name="Plan_eau" value="<?php echo $this->_tpl_vars['Plan_eau']; ?>
"/></td>
						</tr>
						<tr>
							<td><label for="Responsable_insc">Responsable compétition RC</label><input type="text" id="Responsable_insc" name="Responsable_insc" value="<?php echo $this->_tpl_vars['Responsable_insc']; ?>
" placeholder="Nom prénom ou numéro de licence" /></td>
							<td><label for="Responsable_R1">Responsable local R1</label><input type="text" id="Responsable_R1" name="Responsable_R1" value="<?php echo $this->_tpl_vars['Responsable_R1']; ?>
" placeholder="Nom prénom ou numéro de licence" /></td>
						</tr>
						<tr>
							<td><label for="Delegue">Délégué fédéral CNA</label><input type="text" id="Delegue" name="Delegue" value="<?php echo $this->_tpl_vars['Delegue']; ?>
" placeholder="Nom prénom ou numéro de licence" /></td>
							<td><label for="ChefArbitre">Chef des arbitres</label><input type="text" id="ChefArbitre" name="ChefArbitre" value="<?php echo $this->_tpl_vars['ChefArbitre']; ?>
" placeholder="Nom prénom ou numéro de licence" /></td>
						</tr>
						<tr>
							<?php if ($this->_tpl_vars['Num_Journee'] == 0): ?>
								<td colspan=2><input type="button" onclick="Ok();" name="Sauvegarder" value="Insérer la journée"></td>
							<?php else: ?>
								<td colspan=2>
									<input type="button" onclick="Ok();" name="Sauvegarder" value="Sauvegarder les modifications">
									<hr>
								</td>
							<?php endif; ?>
						</tr>
						<tr>
							<td><label for="AvecMatchs">Inclure les matchs</label><input type="checkbox" name="AvecMatchs" value="oui"></td>
							<td><label for="CodMatchs">Encoder les matchs de poule *</label><input type="checkbox" name="CodMatchs" value="oui"></td>
						</tr>
						<tr>
							<td colspan=2 align="right"><label>(* Uniquement s'ils ne sont pas déjà encodés)</label></td>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" onclick="Duppli();" name="Dupliquer" value="Sauvegarder comme nouvelle journée (dupliquer)">
								<hr>
							</td>
						</tr>
					</table>
					<?php if ($this->_tpl_vars['Code_typeclt'] == 'CP'): ?>
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=2 width="100%">
								<label>Appliquer ces paramètres sur les autres phases de la compétition</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<b>Autres phases de la compétition <?php echo $this->_tpl_vars['J_competition']; ?>
 :</b>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formParamJournee', 'checkListJournees', true);return false;"><img hspace="2" width="21" height="19" src="../img/tous.gif" alt="Sélectionner tous" title="Sélectionner tous" border="0"></a>
								&nbsp;
								<a href="#" onclick="setCheckboxes('formParamJournee', 'checkListJournees', false);return false;"><img hspace="2" width="21" height="19" src="../img/aucun.gif" alt="Sélectionner aucun" title="Sélectionner aucun" border="0"></a>
								<br>
								<i>(sauf Phase et Niveau)</i>
								<br>
							</td>
						</tr>
						<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['ListJournees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
							<tr>
								<td>
									<input type="checkbox" name="checkListJournees" value="<?php echo $this->_tpl_vars['ListJournees'][$this->_sections['i']['index']]['Id']; ?>
"/>
								</td>
								<td>
									N°<?php echo $this->_tpl_vars['ListJournees'][$this->_sections['i']['index']]['Id']; ?>
 (<?php echo $this->_tpl_vars['ListJournees'][$this->_sections['i']['index']]['Date_debut']; ?>
) => <?php echo $this->_tpl_vars['ListJournees'][$this->_sections['i']['index']]['Phase']; ?>

								</td>
							</tr>
						<?php endfor; endif; ?>
						<tr>
							<td colspan=2>
								<input type="button" onclick="DuppliListJournees()" name="Dupliquer" value="Appliquer ces paramètres sur les phases sélectionnées" title="Enregistrer les nouveaux paramètres avant de les dupliquer !">
								<hr>
							</td>
						</tr>
					</table>
					<?php endif; ?>
					<table class='tableau2' width="100%">
						<tr>
							<th class='titreForm' colspan=2 width="100%">
								<label>Ajuster les dates des matchs</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" onclick="AjustDates();" name="Ajuster" value="Ajuster la date des matchs à la date de la journée">
							</td>
						</tr>
					</table>
		        </div>
			</form>			
		</div>	  	   