<?php /* Smarty version 2.6.18, created on 2014-12-12 10:52:26
         compiled from RechercheLicence.tpl */ ?>
		<div class="main">
					
			<form method="POST" action="RechercheLicence.php" name="formRerchercheLicence" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<input type='hidden' name='codeComiteReg' Value=''/>
				<input type='hidden' name='codeComiteDep' Value=''/>
				<input type='hidden' name='codeClub' Value=''/>

				<div class='blocLeft'>
					<div class='titrePage'>Recherche de licenciés</div>
					<div class='liens'>
					<a href="#" onclick="setCheckboxes('formRerchercheLicence', 'checkCoureur', true);return false;">Tout cocher</a>
					<a href="#" onclick="setCheckboxes('formRerchercheLicence', 'checkCoureur', false);return false;">Tout décocher</a>
					<a href="#" onclick="Ok();">Valider la sélection</a>
					<a href="#" onclick="Cancel();">Annuler (retour)</a>
					</div>
					
					<div class='blocTable'>
						<table class='tableau' bgcolor='#FFCC99'>
							<thead>
								<tr class='header'>
									<th>&nbsp;</th>
									<th>Licence</th>
									<th>Nom</th>
									<th>Prénom</th>
									<th>Sexe</th>
									<th>Categ.</th>
									<th>Saison</th>
																	<th>N°Club</th>
																	<th>Inter</th>
									<th>Nat.</th>
																	<th>Reg.</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCoureur']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<td><input type="checkbox" name="checkCoureur" value="<?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Matric']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Matric']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Nom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Categ']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Saison']; ?>
</td>
																	<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Numero_club']; ?>
</td>
								 									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['International']; ?>
</td>
 									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['National']; ?>
</td>
 								 									<td><?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Regional']; ?>
</td>
									<td><a href="#" onclick="RemoveCheckbox('formRerchercheLicence', '<?php echo $this->_tpl_vars['arrayCoureur'][$this->_sections['i']['index']]['Matric']; ?>
');return false;"><img hspace="2" width="16" height="16" src="../img/b_drop.png" alt="Supprimer" title="Supprimer" border="0"></a></td>
								</tr>

							<?php endfor; endif; ?>
							</tbody>
						</table>
					</div>
						
		        </div>
		        

		        <div class='blocRight'>
					<table>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Paramètres de recherche</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="matricJoueur">N° Licence :</label>
								<input type="text" name="matricJoueur" value="<?php echo $this->_tpl_vars['matricJoueur']; ?>
"/>
							</td>
							<td>
								<label for="sexeJoueur">Sexe :</label>
								<select name="sexeJoueur" onChange="">
									<Option Value="" SELECTED>Tous</Option>
									<Option Value="M">Masculin</Option>
									<Option Value="F">Féminin</Option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="nomJoueur">Nom :</label>
								<input type="text" name="nomJoueur" maxlength=30 value="<?php echo $this->_tpl_vars['nomJoueur']; ?>
"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="prenomJoueur">Prénom :</label>
								<input type="text" name="prenomJoueur" maxlength=30 value="<?php echo $this->_tpl_vars['prenomJoueur']; ?>
"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteReg">Comité Régional : </label>
								<select name="comiteReg" onChange="changeComiteReg();">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayComiteReg']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteDep">Comité Départemental : </label>				    
								<select name="comiteDep" onChange="changeComiteDep();">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayComiteDep']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="club">Club : </label>				    
								<select name="club">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayClub']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayClub'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayClub'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayClub'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td><label>Juge International</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeInter" <?php echo $this->_tpl_vars['CheckJugeInter']; ?>
 />
							</td>
						</tr>
						<tr>
							<td><label>Juge National</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeNational" <?php echo $this->_tpl_vars['CheckJugeNational']; ?>
/>
							</td>
						</tr>
						<tr>
							<td><label>Juge Régional</label></td>
							<td>
								<input type="checkbox" Name="CheckJugeReg" <?php echo $this->_tpl_vars['CheckJugeReg']; ?>
/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<br>
								<input type="button" onclick="Find();" name="findLicence" value="<< Lancer la recherche">
								<br>
							</td>
						</tr>
					</table>
						    
									    			    
					</form>			
					
		</div>	  	   