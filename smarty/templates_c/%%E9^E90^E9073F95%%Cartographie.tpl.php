<?php /* Smarty version 2.6.18, created on 2014-11-22 23:36:26
         compiled from Cartographie.tpl */ ?>
		&nbsp;(<a href="index.php">Retour</a>)
		<div class="main">
	
			<div class='blocBottom'>
				<div class='titrePage'>Clubs pratiquant le kayak-polo</div>
				<br>
				<div class='blocMap2'>
					<div class='blocMap2' id="map_canvas" style="width: 850px; height: 650px;"></div>
					<form name="formGeocode" onsubmit="return geocode(this.address.value);" enctype="multipart/form-data">
						Adresse : <input type="text" size="70" name="address" value="Adresse, Ville, Pays" onclick="this.value=''" />
						<input type="submit" value="Localiser" />
					</form>			
				</div>
			</div>
			
			<form method="POST" action="Cartographie.php" name="formCartographie" enctype="multipart/form-data">
				<div class='blocBottom' style='text-align: center'>		
					<table width="100%">
						<tr>
							<th class='titreForm'>
								<label>Informations sur le club</label>
							</th>
						</tr>
					</table>
					<label for="club">Club</label>				    
					<br>
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
					<br>
					<label for="postal">Adresse postale</label>
					<br>
					<input type="text" name="postal" maxlength=100 size=60 id="postal"/>
					<br>
					<label for="www">Adresse Internet</label>
					<br>
					<input type="text" name="www" maxlength=100 size=60 id="www"/>
					<br>
					<label for="email">Adresse email</label>
					<br>
					<input type="text" name="email" maxlength=60 size=60 id="email"/>
					<br>
					<label for="coord">Coordonnées GPS (décimales)</label>
					<br>
					<input type="text" name="coord" maxlength=50 size=50 id="coord"/>
					<br>
					(Utiliser le bouton "Localiser" avec l'adresse postale du club, repositionner le pointeur rouge au besoin,<br>
					puis copier/coller les coordonnées GPS dans la zone ci-dessus)
					<br>
					<br>
					<input type="button" onclick="MailUpdat();" name="MailUpdate" value="Demander la mise à jour des informations de mon club">
					<br>
					(vous pouvez joindre à votre message le logo de votre club, au format .gif ou .jpg, maximum : 500 ko)
				</div>
			</form>			
		</div>	  	   