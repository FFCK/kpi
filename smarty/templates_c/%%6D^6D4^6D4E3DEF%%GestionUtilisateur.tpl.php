<?php /* Smarty version 2.6.18, created on 2015-03-25 12:51:55
         compiled from GestionUtilisateur.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionUtilisateur.tpl', 234, false),array('modifier', 'default', 'GestionUtilisateur.tpl', 241, false),)), $this); ?>
		<!--<iframe name="iframeRechercheLicenceIndi" id='iframeRechercheLicenceIndi' SRC="RechercheLicenceIndi.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>-->
		<iframe name="iframeRechercheLicenceIndi2" id='iframeRechercheLicenceIndi2' SRC="RechercheLicenceIndi2.php?zoneMatric=guser&zoneIdentite=gidentite" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionUtilisateur.php" name="formUser" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='Action' id='Action' Value='<?php echo $this->_tpl_vars['action']; ?>
'/>
				
				<?php if ($this->_tpl_vars['action'] == 'Update'): ?>  
					<?php $this->assign('readonlyCode', 'READONLY'); ?>
				<?php else: ?>
					<?php $this->assign('readonlyCode', ''); ?>
				<?php endif; ?>

				<div class='titrePage'>Utilisateurs accrédités</div>
				<div class='blocTop'>
					<table id='tableup' border=0 width=100% cellspacing=0 cellpadding=0>
						<tr>
							<!--<td rowspan=2 width=50 align=center>
								<a href="#" OnClick="rechercheLicenceUtilisateur();"><img hspace="2" width="16" height="17" src="../img/b_search.png" alt="Recherche Utilisateur" title="Recherche Utilisateur" border="0" align=absbottom></a>
							</td>-->
							<td rowspan=2 colspan=2 width=270>
								<label for="choixJoueur">Recherche (nom, prénom ou licence)</label>
								<input size="50" type="text" name="choixJoueur" id="choixJoueur" >
								<img id='rechercheUtilisateur' hspace="2" width="16" height="17" src="../img/b_search.png" alt="Recherche avancée" title="Recherche avancée" border="0" align=absbottom>
								<hr>
								<label for="guser">Licence</label>
								<br>
								<input class="boutonlong" type="text" name="guser" id="guser" value="<?php echo $this->_tpl_vars['guser']; ?>
" READONLY>
								<br>
								<label for="gidentite">Nom</label>
								<br>
								<input class="boutonlong" type="text" name="gidentite" id="gidentite" value="<?php echo $this->_tpl_vars['gidentite']; ?>
" <?php if ($this->_tpl_vars['profile'] != 1): ?>READONLY<?php endif; ?>>
							</td>
							<td rowspan=2 width=220>
								<label for="gmail">E-mail</label>
								<input class="boutonlong" type="text" name="gmail" maxlength=60 id="gmail" placeholder="obligatoire" value="<?php echo $this->_tpl_vars['gmail']; ?>
" autocomplete="off"/>
								<br>
								<label for="gtel">Téléphone</label>
								<input class="boutonlong" type="text" name="gtel" maxlength=60 id="gtel" value="<?php echo $this->_tpl_vars['gtel']; ?>
"/>
								<br>
								<?php if ($this->_tpl_vars['action'] != 'Update' || $this->_tpl_vars['profile'] == 1): ?>  
									<label for="gpwd">Mot de passe</label>
									<input class="boutonlong" type="password" name="gpwd" id="gpwd" <?php if ($this->_tpl_vars['action'] == 'Update'): ?>placeholder="identique"<?php endif; ?> value="" autocomplete="off"/>
								<?php endif; ?>
								<br>
								<input type="checkbox" name="generepwd" id="generepwd" value="O" <?php if ($this->_tpl_vars['action'] != 'Update'): ?>checked<?php endif; ?> /><label for="generepwd">Générer un mot de passe aléatoire</label>
							</td>
							<td>
								<label for="gniveau">Profil : </label>
								<select size=1 id="gniveau" name="gniveau">
										<?php if ($this->_tpl_vars['profile'] == 1): ?>
										<Option Value="1" <?php if ($this->_tpl_vars['gniveau'] == '1'): ?>selected<?php endif; ?>>1 - Webmaster / Président<?php if ($this->_tpl_vars['profile'] > '1'): ?> (INTERDIT)<?php endif; ?></Option>
										<Option Value="2" <?php if ($this->_tpl_vars['gniveau'] == '2'): ?>selected<?php endif; ?>>2 - Bureau CNKP<?php if ($this->_tpl_vars['profile'] > '2'): ?> (INTERDIT)<?php endif; ?></Option>
										<?php endif; ?>
										<?php if ($this->_tpl_vars['profile'] <= 2): ?>
										<Option Value="3" <?php if ($this->_tpl_vars['gniveau'] == '3'): ?>selected<?php endif; ?>>3 - Resp. Division (multi-compétitions)</Option>
										<?php endif; ?>
										<Option Value="4" <?php if ($this->_tpl_vars['gniveau'] == '4'): ?>selected<?php endif; ?>>4 - Resp. Poule / Compétition</Option>
										<Option Value="5" <?php if ($this->_tpl_vars['gniveau'] == '5'): ?>selected<?php endif; ?>>5 - Délégué fédéral</Option>
										<Option Value="6" <?php if ($this->_tpl_vars['gniveau'] == '6'): ?>selected<?php endif; ?>>6 - R1:Organisateur journée</Option>
										<Option Value="7" <?php if ($this->_tpl_vars['gniveau'] == '7' || $this->_tpl_vars['action'] != 'Update'): ?>selected<?php endif; ?>>7 - Resp. club / équipe (spécifier les clubs)</Option>
										<Option Value="8" <?php if ($this->_tpl_vars['gniveau'] == '8'): ?>selected<?php endif; ?>>8 - Consultation simple</Option>
										<Option Value="9" <?php if ($this->_tpl_vars['gniveau'] == '9'): ?>selected<?php endif; ?>>9 - Table de marque</Option>
										<Option Value="10" <?php if ($this->_tpl_vars['gniveau'] == '10'): ?>selected<?php endif; ?>>10 - Inutilisé</Option>
								</select>
							</td>
							<td>
								<label for="gfonction">Fonctions</label>
								<input class="boutonlong" type="text" name="gfonction" maxlength=60 id="gfonction" value="<?php echo $this->_tpl_vars['gfonction']; ?>
"/>
							</td>
						</tr>
						<tr>
							<td colspan="2">
						       	<fieldset>
									<legend>Filtre Club</legend>	
									<label for="limitclub">Limiter l'accès aux équipes du club (codes clubs)</label>
									<input type="text" name="limitclub" id="limitclub" size=40 maxlength=50 value="<?php echo $this->_tpl_vars['limitclub']; ?>
">
						        </fieldset>
					        	<fieldset>
									<legend>Filtre Journées</legend>	
									<label for="filtre_journee">Limiter l'accès aux journées (numéros de journées)</label>
						  			<input type="text" name="filtre_journee" id="filtre_journee" size=40 maxlength=50 value="<?php echo $this->_tpl_vars['filtre_journee']; ?>
">
						        </fieldset>
							</td>
						</tr>
						<tr>
							<td colspan=6>
								<?php if ($this->_tpl_vars['typeFiltreCompetition'] == '1'): ?>
									<?php $this->assign('Checked1', 'Checked'); ?>
									<?php $this->assign('Checked2', ''); ?>
									<?php $this->assign('Checked3', ''); ?>
								<?php elseif ($this->_tpl_vars['typeFiltreCompetition'] == '3'): ?>
									<?php $this->assign('Checked1', ''); ?>
									<?php $this->assign('Checked2', ''); ?>
									<?php $this->assign('Checked3', 'Checked'); ?>
								<?php else: ?>
									<?php $this->assign('Checked1', ''); ?>
									<?php $this->assign('Checked2', 'Checked'); ?>
									<?php $this->assign('Checked3', ''); ?>
								<?php endif; ?>
								<fieldset>
									<legend>Filtre Compétitions/Saisons</legend>	
										<table>
											<tr>
												<td>
													Filtre Classique<input type="radio" onclick="" name="filtre_competition" value="2" <?php echo $this->_tpl_vars['Checked2']; ?>
>
													<i>(Sélection obligatoire)</i>
													<br>
													<select name="comboSaison[]" multiple="true" size="8">
														<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaison']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
															<Option Value="<?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Selection']; ?>
<?php if ($this->_tpl_vars['action'] != 'Update' && $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['Saison']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
														<?php endfor; endif; ?>
													</select>
													
													<select name="comboCompetition[]" multiple="true" size="8">
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
												<td>
													<?php if ($this->_tpl_vars['profile'] == 1): ?>
														Aucun Filtre<input type="radio" onclick="" name="filtre_competition" value="1" <?php echo $this->_tpl_vars['Checked1']; ?>
>
														<br>
													<?php endif; ?>
													<?php if ($this->_tpl_vars['profile'] == 1): ?>
														<br>
														<br>
														Filtre Special<input type="radio" onclick="" name="filtre_competition" value="3" <?php echo $this->_tpl_vars['Checked3']; ?>
>
														<br>
														<input type="text" name="filtre_competition_special" id="filtre_competition_special" size=60 value="<?php echo $this->_tpl_vars['filtre_competition_special']; ?>
">
													<?php endif; ?>
												</td>
											</tr>
										</table>
								</fieldset>
							</td>
						</tr>
						<tr>
							<td colspan="3">
					        	<fieldset>
									<legend>Filtre Evènement</legend>
									<?php if ($this->_tpl_vars['profile'] > 2): ?>
										<select name="comboEvenement[]" multiple="true" size="7" DISABLED>
											<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEvenements']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<Option Value="<?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Id']; ?>
-<?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Libelle']; ?>
 - <?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Lieu']; ?>
</Option>
											<?php endfor; endif; ?>
										</select>
									<?php else: ?>
										<select name="comboEvenement[]" multiple="true" size="7" >
											<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEvenements']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<Option Value="<?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Id']; ?>
-<?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Libelle']; ?>
 - <?php echo $this->_tpl_vars['arrayEvenements'][$this->_sections['i']['index']]['Lieu']; ?>
</Option>
											<?php endfor; endif; ?>
										</select>
									<?php endif; ?>
						        </fieldset>
<!--				        	<fieldset>
									<legend>Dates limites Export Evènement</legend>
									<input type="text" name="Date_debut" id="Date_debut" value="<?php echo $this->_tpl_vars['Date_debut']; ?>
" onfocus="displayCalendar(document.forms[0].Date_debut,'dd/mm/yyyy',this)">
									<input type="text" name="Date_fin" id="Date_fin" value="<?php echo $this->_tpl_vars['Date_fin']; ?>
" onfocus="displayCalendar(document.forms[0].Date_fin,'dd/mm/yyyy',this)">
								</fieldset>
-->							</td>
							<td colspan="3">
								<input type="checkbox" name='plusmail' id='plusmail' value='O' checked />Envoyer un email de confirmation<br />
								<!--<input type="checkbox" name='plusPJ' id='plusPJ' value='Manuel7.pdf' />Envoyer le manuel "profil 7-8"<br />-->
								Message complémentaire : <input type="checkbox" id='msgStandard'><i>Message standard</i><br />
								<textarea rows="6" cols="80" name='message_complementaire' id='message_complementaire'></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="6" align=center>
							</td>
						</tr>
						<tr>
							<td colspan="6" align=center>
								<?php if ($this->_tpl_vars['action'] == 'Update'): ?>  
									<input class="boutonlong" type="button" onclick="Update();" name="addUser" value="Modifier">
									<input class="boutonlong" type="button" onclick="Raz();" id="razUser" name="razUser" value="Annuler">
								<?php else: ?>
									<input class="boutonlong" type="button" onclick="Add();" name="addUser" value="Ajouter">
								<?php endif; ?>
						</tr>
						<tr id='clickup' name='clickup'>
							<td colspan="6" align="left" style="color:#555555"><i><u>Masquer le formulaire</u></i></td>
						</tr>
					</table>
					<table id='tabledown' width=100% >
						<tr id='clickdown' name='clickdown'>
							<td colspan="6" align="left" style="color:#555555"><i><u>Afficher le formulaire</u></i></td>
						</tr>
					</table>	

		        </div>
				
				<div class='blocBottom'>
					<div class='liens'>
						<a href="mailto:laurent@poloweb.org?bcc=<?php echo $this->_tpl_vars['emails']; ?>
">Envoyer un email aux utilisateurs ci-dessous</a>
						<a href="GestionJournal.php">Journal des activités</a>
						<label for='limitProfils'>Profils :</label>
						<select name="limitProfils" onChange="formUser.submit()">
								<Option Value="%">Tous</Option>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['start'] = (int)1;
$this->_sections['i']['loop'] = is_array($_loop=9) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
if ($this->_sections['i']['start'] < 0)
    $this->_sections['i']['start'] = max($this->_sections['i']['step'] > 0 ? 0 : -1, $this->_sections['i']['loop'] + $this->_sections['i']['start']);
else
    $this->_sections['i']['start'] = min($this->_sections['i']['start'], $this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] : $this->_sections['i']['loop']-1);
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = min(ceil(($this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] - $this->_sections['i']['start'] : $this->_sections['i']['start']+1)/abs($this->_sections['i']['step'])), $this->_sections['i']['max']);
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
									<Option Value="<?php echo $this->_sections['i']['index']; ?>
" <?php if ($this->_tpl_vars['limitProfils'] == $this->_sections['i']['index']): ?>selected<?php endif; ?>><?php echo $this->_sections['i']['index']; ?>
</Option>
								<?php endfor; endif; ?>
						</select>
						<label for='limitSaisons'>Saisons :</label>
						<select name="limitSaisons" onChange="formUser.submit()">
							<Option Value="">Toutes</Option>
							<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['arraySaison']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?> 
								<Option Value="<?php echo $this->_tpl_vars['arraySaison'][$this->_sections['j']['index']]['Code']; ?>
" <?php if ($this->_tpl_vars['limitSaisons'] == $this->_tpl_vars['arraySaison'][$this->_sections['j']['index']]['Code']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['j']['index']]['Code']; ?>
</Option>
							<?php endfor; endif; ?>
						</select>
						<span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='10'>
					</div>
					<div class='blocTable'>
						<table class='tableau'>
							<thead>
								<tr class='header'>
									<th>sel.</th>
									<th>Utilisateur (licence)</th>
									<th>Fonction</th>
									<th>Profil</th>
									<th>Saisons</th>
									<th width=300>Compétitions</th>
									<th title='Filtre Evènement / Journées'>Evt/J</th>
									<th>Clubs</th>
									<th>Modif.</th>
									<th>Supp.</th>
								</tr>
							</thead>
							<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayUser']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
 <?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['StdOrSelected']; ?>
'>
									<td><input type="checkbox" name="checkUser" value="<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Code']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
									<td><b><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Identite']; ?>
</b><br><i><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Code']; ?>
<br>(<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Mail']; ?>
)</i>
										<?php if ($this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Tel'] != ''): ?><br /><i>Tél: <?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Tel']; ?>
</i><?php endif; ?>
									</td>
									<td><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Fonction']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Niveau']; ?>
</td>
									<td><?php echo ((is_array($_tmp=@$this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['filtreSaisons'])) ? $this->_run_mod_handler('default', true, $_tmp, 'TOUTES') : smarty_modifier_default($_tmp, 'TOUTES')); ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['filtreCompets']; ?>
</td>
									<td title='Evènement : <?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Libelle']; ?>
 <?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Lieu']; ?>
 (<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Date_debut']; ?>
-><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Date_fin']; ?>
)/ Journées : <?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['filtre_journee']; ?>
'><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Id_Evenement']; ?>
/<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['filtre_journee']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Limitation_equipe_club']; ?>
</td>
									<td><?php if ($this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Niveau'] > $this->_tpl_vars['profile'] || $this->_tpl_vars['profile'] == 1): ?><a href="#" onclick="updateUser('<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Code']; ?>
');"><img hspace="2" width="16" height="16" src="../img/b_edit.png" alt="Modifier" title="Modifier" border="0"></a><?php endif; ?></td>
									<td><?php if ($this->_tpl_vars['profile'] <= 2): ?><a href="#" onclick="RemoveCheckbox('formUser', '<?php echo $this->_tpl_vars['arrayUser'][$this->_sections['i']['index']]['Code']; ?>
');return false;"><img hspace="2" width="16" height="16" src="../img/b_drop.png" alt="Supprimer" title="Supprimer" border="0"></a><?php endif; ?></td>
								</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
					</div>
		        </div>
	        						
			</form>		
					
		</div>	  	   