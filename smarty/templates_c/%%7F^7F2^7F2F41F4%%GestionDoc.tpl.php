<?php /* Smarty version 2.6.18, created on 2015-03-14 23:06:08
         compiled from GestionDoc.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionDoc.tpl', 37, false),)), $this); ?>
 	 	&nbsp;(<a href="GestionCompetition.php">Retour</a>)

		<div class="main">
			<form method="POST" action="GestionDoc.php" name="formDoc" id="formDoc" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value=''/>
				<input type='hidden' name='laSaison' id='laSaison' Value='<?php echo $this->_tpl_vars['sessionSaison']; ?>
'/>
				<input type='hidden' name='laCompet' id='laCompet' Value='<?php echo $this->_tpl_vars['codeCompet']; ?>
'/>

				<div class='blocLeft'>
					<div class='titrePage'>Documents <?php echo $this->_tpl_vars['detailsCompet']['Libelle']; ?>
</div>
					<label for="saisonTravail">Saison :</label>
					<select name="saisonTravail" onChange="submit()">
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
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
<?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['sessionSaison']): ?> (Travail)<?php endif; ?></Option>
						<?php endfor; endif; ?>
					</select>
					<br />
					<label for="codeCompet">Compétition :</label>
					<select name="codeCompet" onChange="submit();">
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
					</select>

					<div class='blocTable table2'>
						<table class='tableauJQ tableauClassement tableau'>
							<thead>
								<tr>
									<th>Catégorie</th>
									<th>DOCUMENTS</th>
									<th>Admin<br>(provisoire)</th>
									<th>Public</th>
								</tr>
							</thead>
							<tbody>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Equipes</td>
										<td>Liste Equipes par poule</td>
										<td><a href="FeuilleGroups.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Equipes</td>
										<td>Feuilles de présence FR</td>
										<td><a href="FeuillePresence.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Equipes</td>
										<td>Feuilles de présence EN</td>
										<td><a href="FeuillePresenceEN.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Matchs</td>
										<td>Liste FR</td>
										<td><a href="FeuilleListeMatchs.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfListeMatchs.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Matchs</td>
										<td>Liste EN</td>
										<td><a href="FeuilleListeMatchsEN.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfListeMatchsEN.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Matchs</td>
										<td>Liste OpenOffice</td>
										<td><a href="tableau_tbs.php" Target="_blank"><img width="20" src="../img/OOo.gif" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Matchs</td>
										<td>Feuilles de match</td>
										<td><a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['listMatchs']; ?>
" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['listMatchs']; ?>
" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
								<?php if ($this->_tpl_vars['detailsCompet']['Code_typeclt'] == 'CHPT'): ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Classement Général</td>
										<td><a href="FeuilleCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Détail par équipe</td>
										<td><a href="FeuilleCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Détail par journée</td>
										<td><a href="FeuilleCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<?php if ($this->_tpl_vars['user'] == '42054'): ?>
										<tr>
											<th>&nbsp;</th>
											<th><i>Classement Coupe</i></th>
											<th>&nbsp;</th>
											<th>&nbsp;</th>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Classement Général</td>
											<td><a href="FeuilleCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Détail par phase</td>
											<td><a href="FeuilleCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<!--<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Détail par niveau</td>
											<td><a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>-->
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Détail par équipe</td>
											<td><a href="FeuilleCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
									<?php endif; ?>
								<?php else: ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Classement Général</td>
										<td><a href="FeuilleCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Détail par phase</td>
										<td><a href="FeuilleCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltNiveauPhase.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<!--<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Détail par niveau</td>
										<td><a href="FeuilleCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltNiveauNiveau.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>-->
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Classements</td>
										<td>Détail par équipe</td>
										<td><a href="FeuilleCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="../PdfCltNiveauDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<?php if ($this->_tpl_vars['user'] == '42054'): ?>
										<tr>
											<th>&nbsp;</th>
											<th><i>Classement Championnat</i></th>
											<th>&nbsp;</th>
											<th>&nbsp;</th>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Classement Général</td>
											<td><a href="FeuilleCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltChpt.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Détail par équipe</td>
											<td><a href="FeuilleCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltChptDetail.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Classements</td>
											<td>Détail par journée</td>
											<td><a href="FeuilleCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a href="../PdfCltNiveauJournee.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
									<?php endif; ?>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Liens</td>
										<td>Liens accès direct</td>
										<td><a href="../PdfQrCodes.php?S=<?php echo $this->_tpl_vars['sessionSaison']; ?>
&Compet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Live</td>
										<td>En direct des terrains</td>
										<td><a href="../DirectPitchs.php?saison=<?php echo $this->_tpl_vars['sessionSaison']; ?>
&idCompet=<?php echo $this->_tpl_vars['codeCompet']; ?>
" Target="_blank"><img width="20" src="../img/web.png" /></a></td>
										<td></td>
									</tr>
								<?php endif; ?>
									<?php if ($this->_tpl_vars['user'] == '42054'): ?>
										<thead>
											<tr>
												<th>&nbsp;</th>
												<th>Evénement</th>
												<th>Admin</th>
												<th>Public</th>
											</tr>
										</thead>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Evénement</td>
											<td>
												<select name="evenement" id="evenement">
													<?php unset($this->_sections['i']);
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
?> 
														<Option Value="<?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Selection']; ?>
><?php echo $this->_tpl_vars['arrayEvenement'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
													<?php endfor; endif; ?>
												</select>
											</td>
											<td></td>
											<td></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Matchs</td>
											<td>Matchs de l'événement</td>
											<td><a id="linkEvt1" href="FeuilleListeMatchs.php?idEvenement=" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a id="linkEvt2" href="../PdfListeMatchs.php?idEvenement=" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Matchs</td>
											<td>Matchs de l'événement EN</td>
											<td><a id="linkEvt3" href="FeuilleListeMatchsEN.php?idEvenement=" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td><a id="linkEvt4" href="../PdfListeMatchsEN.php?idEvenement=" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Liens</td>
											<td>Liens accès direct</td>
											<td><a id="linkEvt5" href="../PdfQrCodes.php?" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
											<td></td>
										</tr>
										<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td>Live</td>
											<td>En direct des terrains</td>
											<td><a id="linkEvt6" href="../DirectPitchs.php" Target="_blank"><img width="20" src="../img/web.png" /></a></td>
											<td></td>
										</tr>
									<?php endif; ?>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>STATISTIQUES</th>
									<th>FR</th>
									<th>EN</th>
								</tr>
							</thead>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Meilleurs buteurs</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Buteurs" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Buteurs" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Meilleure attaque</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Attaque" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Attaque" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Meilleure défense</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Defense" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Defense" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Cartons</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Cartons" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Cartons" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Cartons par équipe</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CartonsEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CartonsEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Classement disciplinaire individuel</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Fairplay" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Fairplay" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Classement disciplinaire par équipe</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=FairplayEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=FairplayEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Arbitrages individuels</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Arbitrage" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=Arbitrage" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Arbitrages par équipe</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=ArbitrageEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=ArbitrageEquipe" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<!--<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Stats</td>
										<td>Joueurs catégorie <select id="Cat" name="Cat">
												<option>Sélectionner</option>
												<Option Value="MIN">Minime</Option>
												<Option Value="CAD">Cadet</Option>
												<Option Value="JUN">Junior</Option>
												<Option Value="SEN">Senior</Option>
												<Option Value="V1">Vétéran 1</Option>
												<Option Value="V2">Vétéran 2</Option>
												<Option Value="V3">Vétéran 3</Option>
											</select> (matchs joués)</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees4" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees4" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>-->
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>CONTROLE IRREGULARITES</th>
									<th>FR</th>
									<th>EN</th>
								</tr>
							</thead>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Contrôle</td>
										<td>Feuilles de présence par catégorie</td>
										<td><a href="FeuillePresenceCat.php" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td>&nbsp;</td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Contrôle</td>
										<td>Matchs joués par club</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Contrôle</td>
										<td>Matchs joués par équipe</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees2" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees2" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Contrôle</td>
										<td>Irrégularités à contrôler (matchs joués)</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees3" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td><a href="FeuilleStatsEN.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=30&Stat=CJouees3" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td class='drag'>Contrôle</td>
										<td>Matchs joués Championnat de France</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=2000&Stat=CJoueesN" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td></td>
									</tr>
									<tr class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
										<td>Contrôle</td>
										<td>Matchs joués Coupe de France</td>
										<td><a href="FeuilleStats.php?Compets=<?php echo $this->_tpl_vars['detailsCompet']['Code']; ?>
&nbLignes=2000&Stat=CJoueesCF" Target="_blank"><img width="20" src="../img/pdf.gif" /></a></td>
										<td></td>
									</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class='blocRight'>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label>Compétition</label>
							</th>
						</tr>
						<tr>
							<td align='center' colspan=4>
								<center>
									<?php if ($this->_tpl_vars['detailsCompet']['Kpi_ffck_actif'] == 'O'): ?><img src='../css/banniere1.jpg' width=120px>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php endif; ?>
									<?php if ($this->_tpl_vars['detailsCompet']['Kpi_ffck_actif'] == 'O'): ?>&nbsp;&nbsp;<img src='../img/ffck2.jpg' width=70px><?php endif; ?>
									<br>
									<?php if ($this->_tpl_vars['detailsCompet']['Logo_actif'] == 'O'): ?><img src='<?php echo $this->_tpl_vars['detailsCompet']['LogoLink']; ?>
' width=80px><?php endif; ?>
									<br>
									<b><?php if ($this->_tpl_vars['detailsCompet']['Titre_actif'] == 'O'): ?><?php echo $this->_tpl_vars['detailsCompet']['Libelle']; ?>
<br><?php else: ?><?php echo $this->_tpl_vars['detailsCompet']['Soustitre']; ?>
<br><?php endif; ?></b>
									<?php echo $this->_tpl_vars['detailsCompet']['Soustitre2']; ?>

							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>
								<?php if ($this->_tpl_vars['detailsCompet']['Publication'] == 'O'): ?><img width="20" src="../img/oeil2O.gif" />
								<?php else: ?><img width="20" src="../img/oeil2N.gif" /><?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4><hr></td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Equipes <a href='GestionEquipe.php'><img width="10" height="10" src="../img/b_plus.png" alt="Equipes" title="Equipes" /></a>
							</td>
							<td align='center'>
								<?php echo $this->_tpl_vars['nbEquipes']; ?>

							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>Qualifiées : <?php echo $this->_tpl_vars['detailsCompet']['Qualifies']; ?>
 - Eliminées : <?php echo $this->_tpl_vars['detailsCompet']['Elimines']; ?>
</i>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>Feuilles de présence</i> <a href='GestionCompetition.php'><img width="10" height="10" src="../img/b_plus.png" alt="Compétitions" title="Compétitions" /></a>
							</td>
							<td align='center'>
								<?php if ($this->_tpl_vars['detailsCompet']['Verrou'] == 'O'): ?><img width="15" height="15" src="../img/verrou2O.gif" /><?php else: ?><img width="15" height="15" src="../img/verrou2N.gif" /><?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<?php if ($this->_tpl_vars['detailsCompet']['Code_typeclt'] == 'CHPT'): ?>
									Journées <a href='GestionCalendrier.php'><img width="10" height="10" src="../img/b_plus.png" alt="Journées" title="Journées" /></a>
								<?php else: ?>
									Phases <a href='GestionCalendrier.php'><img width="10" height="10" src="../img/b_plus.png" alt="Phases" title="Phases" /></a>
								<?php endif; ?>
							</td>
							<td align='center'>
								<?php echo $this->_tpl_vars['nbJournees']; ?>

							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>publiées (<?php echo $this->_tpl_vars['nbJourneesPubli']; ?>
)</i>
							</td>
							<td align='center'>
								<?php if ($this->_tpl_vars['nbJourneesPubli'] == $this->_tpl_vars['nbJournees']): ?><img width="15" height="15" src="../img/oeil2O.gif" />
								<?php elseif ($this->_tpl_vars['nbJourneesPubli'] == 0): ?><img width="15" height="15" src="../img/oeil2N.gif" />
								<?php else: ?><img width="18" height="15" src="../img/oeil2.gif" />
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Matchs <a href='GestionJournee.php'><img width="10" height="10" src="../img/b_plus.png" alt="Matchs" title="Matchs" /></a>
							</td>
							<td align='center'>
								<?php echo $this->_tpl_vars['nbMatchs']; ?>

							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>publiés (<?php echo $this->_tpl_vars['nbMatchsPubli']; ?>
)</i>
							</td>
							<td align='center'>
								<?php if ($this->_tpl_vars['nbMatchsPubli'] == $this->_tpl_vars['nbMatchs']): ?><img width="15" height="15" src="../img/oeil2O.gif" />
								<?php elseif ($this->_tpl_vars['nbMatchsPubli'] == 0): ?><img width="15" height="15" src="../img/oeil2N.gif" />
								<?php else: ?><img width="18" height="15" src="../img/oeil2.gif" />
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i>validés (<?php echo $this->_tpl_vars['nbMatchsValid']; ?>
)</i>
							</td>
							<td align='center'>
								<?php if ($this->_tpl_vars['nbMatchsValid'] == $this->_tpl_vars['nbMatchs']): ?><img width="15" height="15" src="../img/verrou2O.gif" />
								<?php elseif ($this->_tpl_vars['nbMatchsValid'] == 0): ?><img width="15" height="15" src="../img/verrou2N.gif" />
								<?php else: ?><img width="18" height="15" src="../img/verrou2.gif" />
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Type classement <a href='GestionClassement.php'><img width="10" height="10" src="../img/b_plus.png" alt="Classement" title="Classement" /></a>
							</td>
							<td align='center'>
								<?php echo $this->_tpl_vars['detailsCompet']['Code_typeclt']; ?>

							</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i><?php if ($this->_tpl_vars['detailsCompet']['Date_calcul'] != '00/00/00 à 00h00'): ?>calculé le <?php echo $this->_tpl_vars['detailsCompet']['Date_calcul']; ?>
<?php endif; ?></i>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								<i><?php if ($this->_tpl_vars['detailsCompet']['Date_publication'] != '00/00/00 à 00h00'): ?>publié le <?php echo $this->_tpl_vars['detailsCompet']['Date_publication']; ?>

									<?php else: ?>non publié<?php endif; ?></i>
							</td>
							<td align='center'><?php if ($this->_tpl_vars['detailsCompet']['Date_publication'] != '00/00/00 à 00h00'): ?><img width="15" height="15" src="../img/oeil2O.gif" />
								<?php else: ?><img width="15" height="15" src="../img/oeil2N.gif" /><?php endif; ?>
							</td>
						</tr>
						<tr>
							<td align='center' colspan=4>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=3>
								Statistiques <a href='GestionStats.php'><img width="10" height="10" src="../img/b_plus.png" alt="Stats" title="Stats" /></a>
							</td>
							<td align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td align='center' colspan=4><hr></td>
						</tr>
						<tr>
							<td align='center' colspan=4><?php echo $this->_tpl_vars['detailsCompet']['commentairesCompet']; ?>
</td>
						</tr>
						<tr>
							<td align='center' colspan=4><?php if ($this->_tpl_vars['detailsCompet']['Sponsor_actif'] == 'O'): ?><img src='<?php echo $this->_tpl_vars['detailsCompet']['SponsorLink']; ?>
' width=220px><br><?php endif; ?></td>
						</tr>
					</table>
					<table width="100%">
						<tr>
							<th class='titreForm' colspan=4>
								<label><?php if ($this->_tpl_vars['detailsCompet']['Code_typeclt'] == 'CHPT'): ?>Journées<?php else: ?>Phases<?php endif; ?></label>
							</th>
						</tr>
						<tr>
							<?php if ($this->_tpl_vars['detailsCompet']['Code_typeclt'] == 'CHPT'): ?>
								<td align='left' colspan=4>
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
										<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_debut']; ?>
 - <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Lieu']; ?>
<br>
									<?php endfor; endif; ?>
								</td>
							<?php else: ?>
								<td align='center' colspan=4>
									<b><?php echo $this->_tpl_vars['arrayJournees'][0]['Date_debut']; ?>
 au <?php echo $this->_tpl_vars['arrayJournees'][0]['Date_fin']; ?>
</b>
									<br><br>
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
										<?php if ($this->_sections['i']['iteration'] > 1): ?><?php if ($this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau'] != $this->_tpl_vars['niveauTmp']): ?><br><?php else: ?> | <?php endif; ?><?php endif; ?>
										<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Phase']; ?>

										<?php $this->assign('niveauTmp', $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau']); ?>
									<?php endfor; endif; ?>
								</td>
							<?php endif; ?>
						</tr>
					</table>
				</div>
						
			</form>			
					
		</div>	  	   