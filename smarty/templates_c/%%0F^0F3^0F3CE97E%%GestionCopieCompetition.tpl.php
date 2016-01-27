<?php /* Smarty version 2.6.18, created on 2015-05-06 23:03:07
         compiled from GestionCopieCompetition.tpl */ ?>
		&nbsp;(<a href="GestionCompetition.php">Retour</a>)
	
		<div class="main">
			<form method="POST" action="GestionCopieCompetition.php" name="formCopieCompetition" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='saisonOrigine' Value='<?php echo $this->_tpl_vars['saisonOrigine']; ?>
'/>
				<input type='hidden' name='competOrigine' Value='<?php echo $this->_tpl_vars['competOrigine']; ?>
'/>
				<input type='hidden' name='saisonDestination' Value='<?php echo $this->_tpl_vars['saisonDestination']; ?>
'/>
				<input type='hidden' name='competDestination' Value='<?php echo $this->_tpl_vars['competDestination']; ?>
'/>
				<div class='blocRight Right3'>
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Copier la structure des journées/phases et des matchs</label>
							</th>
						</tr>
						<tr>
							<td><label for="saisonOrigine">Saison Origine</label>
								<select name="saisonOrigine" onchange="submit()">
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
" <?php if ($this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['saisonOrigine']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td><label for="competOrigine">Competition Origine</label>
								<select name="competOrigine" onchange="submit()">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetitionOrigine']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionOrigine'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arrayCompetitionOrigine'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['competOrigine']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayCompetitionOrigine'][$this->_sections['i']['index']]['Code']; ?>
 - <?php echo $this->_tpl_vars['arrayCompetitionOrigine'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2 align=center><?php echo $this->_tpl_vars['Soustitre']; ?>
</td>
						</tr>
						<tr>
							<td colspan=2 align=center><?php echo $this->_tpl_vars['Soustitre2']; ?>
</td>
						</tr>
						<tr>
							<td colspan=2 align=center><?php echo $this->_tpl_vars['commentairesCompet']; ?>
</td>
						</tr>
						<tr>
							<td>Type de classement :</td>
							<td><?php echo $this->_tpl_vars['codeTypeCltOrigine']; ?>
</td>
						</tr>
						<tr>
							<td>Nombre d'équipes :</td>
							<td><?php echo $this->_tpl_vars['equipesOrigine']; ?>
</td>
						</tr>
						<tr>
							<td>Qualifiées :</td>
							<td><?php echo $this->_tpl_vars['qualifiesOrigine']; ?>
</td>
						</tr>
						<tr>
							<td>Eliminées :</td>
							<td><?php echo $this->_tpl_vars['eliminesOrigine']; ?>
</td>
						</tr>
						<tr>
							<td>Nb Matchs :</td>
							<td><?php echo $this->_tpl_vars['nbMatchs']; ?>
</td>
						</tr>
						<tr>
							<td colspan=2>
								<hr>
							</td>
						</tr>
						<tr>
							<td colspan=2 align=center>
								<?php if ($this->_tpl_vars['codeTypeCltOrigine'] == 'CHPT'): ?>
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
										<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Lieu']; ?>
<br>
									<?php endfor; endif; ?>
								<?php else: ?>
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
										<?php if (! $this->_sections['i']['first']): ?><?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau'] != $this->_tpl_vars['niveauTmp']): ?><br><?php else: ?> | <?php endif; ?><?php endif; ?>
										<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Phase']; ?>

										<?php $this->assign('niveauTmp', $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau']); ?>
									<?php endfor; endif; ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Destination</label>
							</th>
						</tr>
						<tr>
							<td><label for="saisonDestination">Saison Destination</label>
								<select name="saisonDestination" onchange="submit()">
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
" <?php if ($this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['saisonDestination']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaisons'][$this->_sections['i']['index']]['Code']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td><label for="competDestination">Competition Destination</label>
								<select name="competDestination" onchange="submit()">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompetitionDestination']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayCompetitionDestination'][$this->_sections['i']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['arrayCompetitionDestination'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['competDestination']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arrayCompetitionDestination'][$this->_sections['i']['index']]['Code']; ?>
 - <?php echo $this->_tpl_vars['arrayCompetitionDestination'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<hr>
							</td>
						</tr>
						<tr>
							<td>Type de classement :</td>
							<td><?php echo $this->_tpl_vars['codeTypeCltDestination']; ?>
</td>
						</tr>
						<tr>
							<td>Nombre d'équipes :</td>
							<td><?php echo $this->_tpl_vars['equipesDestination']; ?>
</td>
						</tr>
						<tr>
							<td>Qualifiées :</td>
							<td><?php echo $this->_tpl_vars['qualifiesDestination']; ?>
</td>
						</tr>
						<tr>
							<td>Eliminées :</td>
							<td><?php echo $this->_tpl_vars['eliminesDestination']; ?>
</td>
						</tr>
					</table>
					<table class='tableau2'>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Valeurs communes à chaque journée / phase<br>(% pour reprendre les valeurs individuelles de chaque journée)</label>
							</th>
						</tr>
						<tr>
							<td colspan=2 class="vert" align='center'><label><b>Paramètres apparents dans le calendrier public</b></label></td>
						</tr>
						<tr>
							<td class="vert">
								<label for="Date_debut">Date_debut</label>
								<input type="text" class="date" name="Date_debut" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)" >
								<input type="hidden" name="Date_origine" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" >
							</td>
							<td class="vert">
								<label for="Date_fin">Date_fin</label>
								<input type="text" class="date" name="Date_fin" value="<?php echo $this->_tpl_vars['Date_fin']; ?>
" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)" >
							</td>
						</tr>
						<tr>
							<td class="vert"><label for="Lieu">Lieu</label><input type="text" name="Lieu" value="<?php echo $this->_tpl_vars['Lieu']; ?>
"/></td>
							<td class="vert"><label for="Departement">Département</label><input type="text" class="dpt" name="Departement" value="<?php echo $this->_tpl_vars['Departement']; ?>
"/></td>
						</tr>
						<tr>
							<td class="vert" colspan=2>
								<label for="Nom">Nom journée (Championnat) ou Nom compétition (Coupe)</label>
								<img title="Nom qui apparaîtra dans le calendrier public." 
								alt="Nom qui apparaîtra dans le calendrier public." 
								src="../img/b_help.png" onclick="alert('Nom qui apparaîtra dans le calendrier public.')" />
								<input type="text" name="Nom" value="<?php echo $this->_tpl_vars['Nom']; ?>
" />
								<label><i><u>Exemples :</u><br>
									N1F - 3ème journée<br>
									N3H - Demi-finale montante<br>
									N4H - Zone Sud-Ouest<br>
									CF Hommes - 2ème tour zone Sud<br>
									10th Veurne International Canoepolo Tournament</i><br>
								</label>
							</td>
						</tr>
<!--					<tr>
							<td colspan=2><label for="Libelle">Libelle</label><input type="text" name="Libelle" value="<?php echo $this->_tpl_vars['Libelle']; ?>
" readonly /></td>
						</tr>
-->						<tr>
							<td><br><label for="Organisateur">Club Organisateur</label><input type="text" name="Organisateur" value="<?php echo $this->_tpl_vars['Organisateur']; ?>
"/></td>
							<td><br><label for="Plan_eau">Plan d'eau</label><input type="text" name="Plan_eau" value="<?php echo $this->_tpl_vars['Plan_eau']; ?>
"/></td>
						</tr>
						<tr>
							<td><label for="Responsable_R1">Responsable local R1</label><input type="text" name="Responsable_R1" value="<?php echo $this->_tpl_vars['Responsable_R1']; ?>
"/></td>
							<td><label for="Responsable_insc">Responsable insc. RZ</label><input type="text" name="Responsable_insc" value="<?php echo $this->_tpl_vars['Responsable_insc']; ?>
"/></td>
						</tr>
						<tr>
							<td><label for="Delegue">Délégué fédéral</label><input type="text" name="Delegue" value="<?php echo $this->_tpl_vars['Delegue']; ?>
"/></td>
						</tr>
						<tr>
							<td colspan=2>
								<hr>
							</td>
						</tr>
						<tr>
							<td colspan=2><input type="checkbox" name="init1erTour" value="init"><label for="init1erTour">Encoder les équipes au premier tour (préparer le tirage au sort)</label></td>
						</tr>
						<tr>
							<td colspan=2><label>(Uniquement si ces matchs ne sont pas déjà encodés !)</label></td>
						</tr>
						<tr>
							<td colspan=2><br><input type="button" onclick="Duppli();" name="Dupliquer" value="Duppliquer la structure des matchs"></td>
						</tr>
						<tr>
							<td colspan=2>&nbsp;</td>
						</tr>
					</table>
		        </div>
			</form>			
		</div>	  	   