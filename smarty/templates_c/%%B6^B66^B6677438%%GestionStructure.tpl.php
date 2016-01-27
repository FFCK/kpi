<?php /* Smarty version 2.6.18, created on 2015-03-16 09:00:56
         compiled from GestionStructure.tpl */ ?>
		&nbsp;(<a href="GestionEquipe.php">Retour</a>)
		<div class="main">
	
			<div class='blocLeft Left2'>
				<div class='titrePage'>Structures pratiquant le kayak-polo</div>
				<br>
				<div class='blocMap'>
					<div id="map_canvas" style="width: 620px; height: 550px"></div>
					<form name="formGeocode" onsubmit="return geocode(this.address.value);" enctype="multipart/form-data">
						Adresse : <input type="text" size="70" name="address" value="Adresse, Ville, Pays" onclick="this.value=''" />
						<input type="submit" value="Localiser" />
						<?php if ($this->_tpl_vars['profile'] > 3): ?>
							<br><br>Si votre club n'apparait pas sur la carte, transmettez ses coordonnées
							<br>(adresse postale, email, site internet, coordonnées GPS, logo)
							<br>à l'adresse laurent@poloweb.org.
						<?php endif; ?>

					</form>			
				</div>
			</div>
			
			<?php if ($this->_tpl_vars['profile'] <= 3): ?>
			<form method="POST" action="GestionStructure.php" name="formStructure" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocRight Right2'>		
					<table width=100%>
						<tr>
							<th class='titreForm'>
								<label>Géolocaliser les structures</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="club">Club pratiquant le kayak-polo : </label>				    
								<select name="club" id="club" onChange="handleSelected();">
										<Option Value="">Sélectionner le Club...</Option>
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
" <?php echo $this->_tpl_vars['arrayClub'][$this->_sections['i']['index']]['Selected']; ?>
><?php echo $this->_tpl_vars['arrayClub'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="postal">Adresse postale :</label>
								<input type="text" name="postal" maxlength=100 id="postal"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="www">Adresse Internet :</label>
								<input type="text" name="www" maxlength=60 id="www"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="email">Adresse email :</label>
								<input type="text" name="email" maxlength=40 id="email"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="coord">Coordonnées géographiques lat,long<br>(ex : 48.856614, 2.3522219)</label>
								<input type="text" name="coord" maxlength=60 id="coord"/>
							</td>
						</tr>
					<!--	<tr>
							<td>
								<label for="coord2">Coordonnées terrain :</label>
								<input type="text" name="coord2" maxlength=60 id="coord2" style="width:65%"/>
								<input type="button" onclick="document.forms['formGeocode'].elements['address'].value = document.forms['formStructure'].elements['coord2'].value;document.forms['formGeocode'].submit;" name="localiser" value="Localiser" style="width:30%">
							</td>
						</tr>-->
						<tr>
							<td>
								<br>
								<input type="button" onclick="UpdatClub();" name="UpdateClub" value="Mise à jour">
							</td>
						</tr>
					</table>
					<br>
					<br>
					<?php if ($this->_tpl_vars['profile'] <= 2): ?>
					<table width=100%>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Ajouter un Comité Départemental / un Pays</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteReg">Comité Régional : </label>
								<select name="comiteReg" id="comiteReg">
										<Option Value="">Sélectionner le Comité Régional d'appartenance...</Option>
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
"><?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td width=15%>
								<label for="codeCD">Code :</label>
								<input type="text" name="codeCD" maxlength=5 id="codeCD"/>
							</td>
							<td>
								<label for="libelleCD">Nouveau comité départemental / pays :</label>
								<input type="text" name="libelleCD" maxlength=50 id="libelleCD"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<input type="button" onclick="AddCD();" name="addCD" value="Ajouter">
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th colspan=2 class='titreForm'>
								<label>Ajouter un Club / une Structure</label>
							</th>
						</tr>
						<tr>
							<td colspan=2>
								<label for="comiteDep">Comité Départemental / Pays : </label>
								<select name="comiteDep" id="comiteDep">
										<Option Value="">Sélectionner le CD / le pays d'appartenance...</Option>
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
"><?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="ClubInt">Structures internationales déjà existantes</label>
								<select name="ClubInt" id="ClubInt">
										<Option Value="">Vérifier que la nouvelle structure n'existe pas déjà !</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayClubInt']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<Option Value="<?php echo $this->_tpl_vars['arrayClubInt'][$this->_sections['i']['index']]['Code']; ?>
"><?php echo $this->_tpl_vars['arrayClubInt'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td width=15%>
								<label for="codeClub">Code :</label>
								<input type="text" name="codeClub" maxlength=5 id="codeClub"/>
							</td>
							<td>
								<label for="libelleClub">Nouveau club / nouvelle structure :</label>
								<input type="text" name="libelleClub" maxlength=50 id="libelleClub"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="postal2">Adresse postale :</label>
								<input type="text" name="postal2" maxlength=100 id="postal2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="www2">Adresse Internet :</label>
								<input type="text" name="www2" maxlength=60 id="www2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="email2">Adresse email :</label>
								<input type="text" name="email2" maxlength=40 id="email2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="coord2">Coordonnées club :</label>
								<input type="text" name="coord2" maxlength=60 id="coord2"/>
							</td>
						</tr>
						<tr>
							<td colspan=2>
								<label for="libelleEquipe2"><b>Nouvelle Equipe :</b></label>
								<img title="ATTENTION ! Cliquez pour plus d'info." 
								alt="ATTENTION ! Cliquez pour plus d'info." 
								src="../img/b_help.png" 
								onclick="alert('ATTENTION !\n Respectez bien le formalisme :\n \n -Sélectionnez le club d\'appartenance avant tout (+CR +CD),\n -Nom d\'équipe en minuscule, première lettre en majuscule,\n -Un espace avant le numéro d\'ordre et avant la catégorie\n -Numéro d\'ordre obligatoire, en chiffre romain : I II III IV\n -Catégorie féminine avec \' F\' (\' Ladies\' ou \' Women\' pour les équipes étrangères)\n -Catégorie jeunes avec \' JF\' ou \' JH\' (masculine ou mixte)\n -Catégorie -21 ans avec \' -21\' (\' U21\' pour les équipes étrangères)\n \n Exemples :\n Acigné II, Acigné I F, Acigné JH, Belgium U21 Women, Keistad Ladies...')" />
								<input type="text" name="libelleEquipe2" maxlength=40 id="libelleEquipe2" />
							</td>
						</tr>
						<?php if ($this->_tpl_vars['codeCompet'] != ''): ?>  
						<tr>
							<td>
								<input type="checkbox" name="affectEquipe" id="affectEquipe" value="<?php echo $this->_tpl_vars['codeCompet']; ?>
">
							</td>
							<td>
								Affecter l'équipe à <?php echo $this->_tpl_vars['codeCompet']; ?>

							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td colspan=2>
								<input type="button" onclick="AddClub();" name="addClub" value="Ajouter">
							</td>
						</tr>
					</table>
					<?php endif; ?>		
					<br>
				</div>
			</form>			
			<?php endif; ?>		
		</div>	  	   