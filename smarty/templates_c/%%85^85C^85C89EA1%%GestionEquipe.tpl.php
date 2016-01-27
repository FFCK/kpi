<?php /* Smarty version 2.6.18, created on 2015-06-26 17:51:32
         compiled from GestionEquipe.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionEquipe.tpl', 72, false),array('modifier', 'string_format', 'GestionEquipe.tpl', 76, false),array('modifier', 'replace', 'GestionEquipe.tpl', 95, false),array('modifier', 'default', 'GestionEquipe.tpl', 171, false),)), $this); ?>
		&nbsp;(<a href="Admin.php">Retour</a>)
	
		<div class="main">
					
			<form method="POST" action="GestionEquipe.php" name="formEquipe" id="formEquipe" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes' />
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = ' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
' />
				<input type='hidden' name='Saison' id='Saison' Value='<?php echo $this->_tpl_vars['codeSaison']; ?>
' />
	
				<div class='blocLeft Left2'>
					<div class='titrePage'>Equipes engag&eacute;es</div>
					<label for="competition">Comp&eacute;tition :</label>
					<select name='competition' id='competition' onChange="changeCompetition();">
							<Option Value="">S&eacute;lectionner une comp&eacute;tition...</Option>
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
							<Option Value="<?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][0]; ?>
" <?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][2]; ?>
><?php echo $this->_tpl_vars['arrayCompetition'][$this->_sections['i']['index']][1]; ?>
</Option>
						<?php endfor; endif; ?>
							<Option Value="">--------------------</Option>
							<Option Value="POOL" <?php if ($this->_tpl_vars['codeCompet'] == 'POOL'): ?>selected<?php endif; ?>>Pool arbitres</Option>
					</select>

					<div class='liens'>
						<table>
							<tr>
								<td width=200>
									<fieldset>
										<a href="#" title="S&eacute;lectionner tous" onclick="setCheckboxes('formEquipe', 'checkEquipe', true);return false;"><img width="21" src="../img/tous.gif" /></a>
										<a href="#" title="S&eacute;lectionner aucun" onclick="setCheckboxes('formEquipe', 'checkEquipe', false);return false;"><img width="21" src="../img/aucun.gif" /></a>
										<?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O' && $this->_tpl_vars['bProd']): ?>
											<a href="#" title="Supprimer la s&eacute;lection" onclick="RemoveCheckboxes('formEquipe', 'checkEquipe')"><img width="16" src="../img/supprimer.gif" /></a>
										<?php endif; ?>
										<a href="#" onclick=""><img src="../img/map.gif" width="20" alt="Cartographier la s&eacute;lection (en construction)" title="Cartographier la s&eacute;lection (en construction)" /></a>
										&nbsp;&nbsp;&nbsp;
									</fieldset>
								</td>
								<td>
									<a href="FeuilleGroups.php" target="_blank" alt="Liste des &eacute;quipes par poule" title="Liste des &eacute;quipes par poule"><img width="20" src="../img/pdf.gif" /></a>						
									<a href="FeuillePresence.php" target="_blank" alt="Toutes les feuilles de pr&eacute;sence" title="Toutes les feuilles de pr&eacute;sence"><img width="20" src="../img/pdfMulti.gif" /></a>						
									<a href="FeuillePresenceEN.php" target="_blank" alt="Toutes les feuilles de pr&eacute;sence - Anglais" title="Toutes les feuilles de pr&eacute;sence - Anglais"><img width="20" src="../img/pdfEN.gif" /></a>						
									<a href="FeuillePresenceCat.php" target="_blank" alt="Feuilles de pr&eacute;sence par cat&eacute;gorie" title="Feuilles de pr&eacute;sence par cat&eacute;gorie"><img width="20" src="../img/pdfMulti.gif" />Cat</a>						
									&nbsp;<button id='actuButton' type="button" ><img src="../img/actualiser.gif">Recharger</button>
								</td>
							</tr>
						</table>
					</div>
					<div class='blocTable'>
						<table class='tableau' id='tableEquipes' name='tableEquipes'>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>Poule</th>
									<th># Tirage</th>
									<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
										<th>&nbsp;</th>
									<?php endif; ?>
									<th>Equipe</th>
									<th>Pr&eacute;sents</th>
									<th># Club</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<?php if ($this->_tpl_vars['PouleX'] != $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Poule'] && $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Poule'] != ''): ?>
										<tr class='colorO'>
											<th colspan=8><b>Poule <?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Poule']; ?>
</b></td>
										</tr>
									<?php endif; ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<?php $this->assign('PouleX', $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Poule']); ?>
										<td><input type="checkbox" name="checkEquipe" value="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" id="checkDelete<?php echo $this->_sections['i']['iteration']; ?>
" /></td>
						<!--<td class='directInput numMatch'><span Id="Numero_ordre-<?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
</span></td>-->
										<td><span <?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>class='directInput textPoule' <?php endif; ?>tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
0' Id="Poule-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Poule']; ?>
</span></td>
										<td><span <?php if ($this->_tpl_vars['profile'] <= 6 && $this->_tpl_vars['AuthModif'] == 'O'): ?>class='directInput textTirage' <?php endif; ?>tabindex='1<?php echo ((is_array($_tmp=$this->_sections['i']['iteration'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%02d") : smarty_modifier_string_format($_tmp, "%02d")); ?>
1' Id="Tirage-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
-text"><?php if ($this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Tirage'] == '0'): ?><?php else: ?><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Tirage']; ?>
<?php endif; ?></span></td>
										<?php if ($this->_tpl_vars['Code_niveau'] == 'INT'): ?>
											<td> <img width="20" src="../img/Pays/<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
.png" alt="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" title="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_comite_dep']; ?>
" /></td>
										<?php endif; ?>
		
																												<td class="cliquableNomEquipe"><a href="./GestionEquipeJoueur.php?idEquipe=<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" alt="Feuille de pr&eacute;sence" title="Feuille de pr&eacute;sence"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</A></td>
										<td><a href="./GestionEquipeJoueur.php?idEquipe=<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" alt="Feuille de pr&eacute;sence" title="Feuille de pr&eacute;sence"><img width="10" src="../img/b_sbrowse.png" /></A></td>
										<td><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Code_club']; ?>
</td>
										<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O' && $this->_tpl_vars['bProd']): ?>
											<td><a href="#" onclick="RemoveCheckbox('formEquipe', '<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
');return false;"><img width="16" src="../img/supprimer.gif" alt="Supprimer" title="Supprimer" /></a></td>
										<?php else: ?><td>&nbsp;</td><?php endif; ?>
									</tr>
								<?php endfor; endif; ?>
							</tbody>
						</table>
					</div>
					<b>TOTAL = <?php echo ((is_array($_tmp=$this->_sections['i']['iteration']-1)) ? $this->_run_mod_handler('replace', true, $_tmp, -1, 0) : smarty_modifier_replace($_tmp, -1, 0)); ?>
 &eacute;quipes</b>
				</div>
	        
				<?php if ($this->_tpl_vars['profile'] <= 3 && $this->_tpl_vars['AuthModif'] == 'O' && $this->_tpl_vars['bProd']): ?>
				<div class='blocRight Right2'>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=2>
								<label>Affecter une &eacute;quipe</label>
							</th>
						</tr>
						<tr>
							<td>
								<label><b>Recherche :</b></label><input type="text" name="choixEquipe" id="choixEquipe" style="width:60%">
								<br>
								<div name="ShowCompo" id="ShowCompo">
									<input type="hidden" name="EquipeNum" id="EquipeNum">
									<input type="hidden" name="EquipeNumero" id="EquipeNumero">
									<input type="text" name="EquipeNom" id="EquipeNom" style="width:100%" readonly>
									<label title="Lettre A Ã  O majuscule" alt="Lettre A Ã  O majuscule">Poule:</label><input type="text" name="plEquipe" title="Lettre A &agrave;  O majuscule" alt="Lettre A &agrave;  O majuscule" id="plEquipe" style="width:8%" size=2>
									<label title="Nombre 1 Ã  99" alt="Nombre 1 Ã  99">Tirage:</label><input type="text" name="tirEquipe" id="tirEquipe" title="Nombre 1 &agrave;  99" alt="Nombre 1 &agrave;  99" style="width:8%" size=2>
									<?php if ($this->_tpl_vars['user'] == '42054'): ?>
										&nbsp;
										<label title="Classement Championnat" alt="Classement Championnat">Clt.Chpt:</label><input type="text" name="cltChEquipe" id="cltChEquipe" style="width:8%" size=2>
										<label title="Classement Coupe" alt="Classement Coupe">Clt.CP:</label><input type="text" name="cltCpEquipe" id="cltCpEquipe" style="width:8%" size=2>
									<?php endif; ?>
									<span name="GetCompo" id="GetCompo"></span>
									<input type="button" onclick="Add2();" name="addEquipe2" id="addEquipe2" value="<< Ajouter" style="width:45%">
									<input type="button" name="annulEquipe2" id="annulEquipe2" value="Annuler" style="width:45%">
								</div>
							</td>
						</tr>
					</table>
					<table width=100%>
						<tr>
							<th class='titreForm'>
								<label>Recherche avanc&eacute;e / cr&eacute;ation</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="comiteReg">Comit&eacute; R&eacute;gional : </label>
								<select name="comiteReg" id="comiteReg" onChange="changeComiteReg();">
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
" <?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Selected']; ?>
><?php echo $this->_tpl_vars['arrayComiteReg'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="comiteDep">Comit&eacute; D&eacute;partemental / Pays : </label>				    
								<select name="comiteDep" id="comiteDep" onChange="changeComiteDep();">
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
" <?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Selected']; ?>
><?php echo $this->_tpl_vars['arrayComiteDep'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="club">Club / Structure : </label>				    
								<select name="club" id="club" onChange="changeClub();">
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
								<label>Filtre Equipes :</label>
								<input type="radio" name="filtreH" id="filtreH" value=1 <?php echo $this->_tpl_vars['filtreH']; ?>
 onclick="filtreTous.checked=false;submit()">H
								<input type="radio" name="filtreH" id="filtreH" value=0 <?php echo $this->_tpl_vars['filtreF']; ?>
 onclick="filtreTous.checked=false;submit()">F
								<input type="checkbox" name="filtreJ" id="filtreJ" <?php echo $this->_tpl_vars['filtreJ']; ?>
 onclick="filtreTous.checked=false;submit()">J
								<input type="checkbox" name="filtre21" id="filtre21" <?php echo $this->_tpl_vars['filtre21']; ?>
 onclick="filtreTous.checked=false;submit()">-21
								<input type="checkbox" name="filtreTous" id="filtreTous" <?php echo ((is_array($_tmp=@$this->_tpl_vars['filtreTous'])) ? $this->_run_mod_handler('default', true, $_tmp, 'selected') : smarty_modifier_default($_tmp, 'selected')); ?>
 onclick="submit()">TOUTES
							</td>
						</tr>
						<tr>
							<td>
								<!--
								<label>Recherche :</label>
								<input type="text" name="filtreText" id="filtreText" style="width:30%">
								<input type="button" id="filtreTextButton" style="width:20%" value="Chercher...">
								<input type="button" id="filtreAnnulButton" style="width:20%" value="Annuler">
								
								<span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='10'>
								-->
							</td>
						</tr>
						<tr>
							<td>
								<label for="histoEquipe">Choix Equipes :</label>
								<img title="Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois." 
								alt="Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois." 
								src="../img/b_help.png" 
								onclick="alert('Maintenez la touche CTRL pour s&eacute;lectionner plusieurs &eacute;quipes &agrave;  la fois.')" />
								<select name="histoEquipe[]" id="histoEquipe" class="histoEquip" onChange="changeHistoEquipe();" size="20" multiple>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayHistoEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
										<?php if ($this->_tpl_vars['arrayHistoEquipe'][$this->_sections['i']['index']]['Numero'] == ''): ?>
											<Option Value="0"><?php echo $this->_tpl_vars['arrayHistoEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php else: ?>
											<Option Value="<?php echo $this->_tpl_vars['arrayHistoEquipe'][$this->_sections['i']['index']]['Numero']; ?>
"><?php echo $this->_tpl_vars['arrayHistoEquipe'][$this->_sections['i']['index']]['Code_club']; ?>
 - <?php echo $this->_tpl_vars['arrayHistoEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
										<?php endif; ?>

									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<label for="libelleEquipe"><b>Nouvelle Equipe :</b></label>
								<img title="ATTENTION ! Cliquez pour plus d'info." 
								alt="ATTENTION ! Cliquez pour plus d'info." 
								src="../img/b_help.png" 
								onclick="alert('ATTENTION !\n Respectez bien le formalisme :\n \n -S&eacute;lectionnez le club d\'appartenance avant tout (+CR +CD),\n -Nom d\'&eacute;quipe en minuscule, premi&egrave;re lettre en majuscule,\n -Un espace avant le num&eacute;ro d\'ordre et avant la cat&eacute;gorie\n -Num&eacute;ro d\'ordre obligatoire, en chiffre romain : I II III IV\n -Cat&eacute;gorie f&eacute;minine avec \' F\' (\' Ladies\' ou \' Women\' pour les &eacute;quipes &eacute;trang&egrave;res)\n -Cat&eacute;gorie jeunes avec \' JF\' ou \' JH\' (masculine ou mixte)\n -Cat&eacute;gorie -21 ans avec \' -21\' (\' U21\' pour les &eacute;quipes &eacute;trang&egrave;res)\n \n Exemples :\n Acign&eacute; II, Acign&eacute; I F, Acign&eacute; JH, Belgium U21 Women, Keistad Ladies...')" />
								<input type="text" name="libelleEquipe" maxlength=40 id="libelleEquipe"/>
							</td>
						</tr>
						<tr>
							<td>
								<input type="button" onclick="Add();" name="addEquipe" id="addEquipe" value="<< Ajouter">
							</td>
						</tr>
					</table>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['profile'] <= 4 && $this->_tpl_vars['AuthModif'] == 'O'): ?>
					<br>
					<table width=100%>
						<tr>
							<th class='titreForm' colspan=3>
								<label>Tirage au sort</label>
							</th>
						</tr>
						<tr>
							<td>
								<label for="equipeTirage">Equipe :</label>
								<select name="equipeTirage" id="equipeTirage">
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<Option Value="<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
							<td>
								<label for="pouleTirage">Poule :</label>
								<select name="pouleTirage" id="pouleTirage">
									<Option Value="">nc</Option>
									<Option Value="A">A</Option>
									<Option Value="B">B</Option>
									<Option Value="C">C</Option>
									<Option Value="D">D</Option>
									<Option Value="E">E</Option>
									<Option Value="F">F</Option>
									<Option Value="G">G</Option>
									<Option Value="H">H</Option>
									<Option Value="I">I</Option>
									<Option Value="J">J</Option>
									<Option Value="K">K</Option>
									<Option Value="L">L</Option>
									<Option Value="M">M</Option>
									<Option Value="N">N</Option>
									<Option Value="O">O</Option>
								</select>
							</td>
							<td>
								<label for="ordreTirage">Tirage :</label>
								<select name="ordreTirage" id="ordreTirage">
									<Option Value="0">nc</Option>
									<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
											<Option Value="<?php echo $this->_sections['i']['iteration']; ?>
">T<?php echo $this->_sections['i']['iteration']; ?>
</Option>
									<?php endfor; endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=3>
								<input type="button" onclick="Tirage();" name="tirageEquipe" id="tirageEquipe" value="Valider ce tirage" />
							</td>
						</tr>
					</table>
				</div>
				<?php endif; ?>
					
			</form>			
					
		</div>	  	

		