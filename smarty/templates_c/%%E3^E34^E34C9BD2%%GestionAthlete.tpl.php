<?php /* Smarty version 2.6.18, created on 2015-04-19 23:25:58
         compiled from GestionAthlete.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'GestionAthlete.tpl', 38, false),array('modifier', 'replace', 'GestionAthlete.tpl', 57, false),)), $this); ?>
		&nbsp;(<a href="javascript:history.back()">Retour</a>)
		<br>
		<iframe name="iframeRechercheLicenceIndi2" id="iframeRechercheLicenceIndi2" SRC="RechercheLicenceIndi2.php" scrolling="auto" width="950" height="450" FRAMEBORDER="yes"></iframe>
		<div class="main">
			<form method="POST" action="GestionAthlete.php" name="formAthlete" id="formAthlete" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' id='ParamCmd' Value='' />
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
' />
				
				<div class='titrePage'>Statistiques athlète</div>
				<!--<center class='rouge'><i>Version BETA (Signaler les bugs)</i></center>-->
				<div class='blocTop'>
								<label>Recherche (nom, prénom ou licence)</label>
								<input type="text" name="choixJoueur" id="choixJoueur" size="30" />
								<input type="submit" name="maj" id="maj" value="Mise à jour" />
								&nbsp;&nbsp;&nbsp;&nbsp;
								<label for="comboarbitre1">Recherche avancée</label>
								<a href="#"  id='rechercheAthlete'><img width="16" src="../img/b_search.png" alt="Recherche Licencié" title="Recherche Licencié" align=absmiddle /></a>
								<br />
								<!--<label for="Athlete">Athlète sélectionné</label>-->
								<input type="hidden" size="5" name="Athlete" id="Athlete" value="<?php echo $this->_tpl_vars['Athlete']; ?>
" />
								<!--<input type="text" size="30" name="Athlete_id" readonly id="Athlete_id" value="<?php echo $this->_tpl_vars['Athlete_id']; ?>
" tabindex="12"/>-->
								
				</div>
				<?php if ($this->_tpl_vars['Courreur']['Matric'] != ''): ?>
				<div class='blocMiddle'>
					<table class='tableau'>
						<tr>
							<th colspan=4>
								Saison:
                                                                    <select name="SaisonAthlete"  id="SaisonAthlete" onChange="submit()">
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
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['SaisonAthlete']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
</Option>
                                                                            <?php endfor; endif; ?>
                                                                    </select>
                                                                        &nbsp;&nbsp;
									<u>Licence n° <?php echo $this->_tpl_vars['Courreur']['Matric']; ?>
</u>&nbsp;&nbsp;
                                                                        <b><?php echo $this->_tpl_vars['Courreur']['Nom']; ?>
 <?php echo $this->_tpl_vars['Courreur']['Prenom']; ?>
</b> (<?php echo $this->_tpl_vars['Courreur']['Sexe']; ?>
) né(e) le <?php echo ((is_array($_tmp=$this->_tpl_vars['Courreur']['Naissance'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>

                                                                        <br>
									<br>
							</th>
						</tr>
						<tr>
							<th>Club</th>
							<th>Pagaie couleur</th>
							<th>Certificats médicaux</th>
							<th>Arbitrage</th>
						</tr>
						<tr>
							<td>
								<b><?php echo $this->_tpl_vars['Courreur']['nomclub']; ?>
</b><br>
								<?php echo $this->_tpl_vars['Courreur']['nomcd']; ?>
<br>
								<?php echo $this->_tpl_vars['Courreur']['nomcr']; ?>
<br>
								Dernière saison : <b><?php echo $this->_tpl_vars['Courreur']['Origine']; ?>
</b>
							</td>
							<td>
								Eau vive : <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Courreur']['Pagaie_EVI'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGN', 'Noire') : smarty_modifier_replace($_tmp, 'PAGN', 'Noire')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGV', 'Verte') : smarty_modifier_replace($_tmp, 'PAGV', 'Verte')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGR', 'Rouge') : smarty_modifier_replace($_tmp, 'PAGR', 'Rouge')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGJ', 'Jaune') : smarty_modifier_replace($_tmp, 'PAGJ', 'Jaune')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGBL', 'Bleue') : smarty_modifier_replace($_tmp, 'PAGBL', 'Bleue')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGB', 'Blanche') : smarty_modifier_replace($_tmp, 'PAGB', 'Blanche')); ?>
<br>
								Mer : <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Courreur']['Pagaie_MER'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGN', 'Noire') : smarty_modifier_replace($_tmp, 'PAGN', 'Noire')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGV', 'Verte') : smarty_modifier_replace($_tmp, 'PAGV', 'Verte')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGR', 'Rouge') : smarty_modifier_replace($_tmp, 'PAGR', 'Rouge')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGJ', 'Jaune') : smarty_modifier_replace($_tmp, 'PAGJ', 'Jaune')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGBL', 'Bleue') : smarty_modifier_replace($_tmp, 'PAGBL', 'Bleue')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGB', 'Blanche') : smarty_modifier_replace($_tmp, 'PAGB', 'Blanche')); ?>
<br>
								Eau calme : <b><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Courreur']['Pagaie_ECA'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGN', 'Noire') : smarty_modifier_replace($_tmp, 'PAGN', 'Noire')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGV', 'Verte') : smarty_modifier_replace($_tmp, 'PAGV', 'Verte')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGR', 'Rouge') : smarty_modifier_replace($_tmp, 'PAGR', 'Rouge')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGJ', 'Jaune') : smarty_modifier_replace($_tmp, 'PAGJ', 'Jaune')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGBL', 'Bleue') : smarty_modifier_replace($_tmp, 'PAGBL', 'Bleue')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'PAGB', 'Blanche') : smarty_modifier_replace($_tmp, 'PAGB', 'Blanche')); ?>
</b><br>
							</td>
							<td>
								APS (Loisirs) : <?php echo $this->_tpl_vars['Courreur']['Etat_certificat_APS']; ?>
<br>
								CK (Compétition) : <?php echo $this->_tpl_vars['Courreur']['Etat_certificat_CK']; ?>

							</td>
							<td>
								<?php echo $this->_tpl_vars['Arbitre']['Arb']; ?>
<br>
								Niveau : <?php echo $this->_tpl_vars['Arbitre']['niveau']; ?>
<br>
								Saison : <?php echo $this->_tpl_vars['Arbitre']['saison']; ?>
<br>
								Livret : <?php echo $this->_tpl_vars['Arbitre']['Livret']; ?>
<br>
							</td>
						</tr>
					</table>
					<table class='tableau'>
						<tr>
							<td valign=top>
								<?php if ($this->_tpl_vars['Titulaire'][0]['Code_compet'] != ''): ?>
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=5>Feuilles de présence</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Compét.</th>
											<th>Equipe</th>
											<th>n°</th>
											<th>Catégorie</th>
										</tr>
									</thead>
									<tbody>
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['Titulaire']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<td><?php echo $this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Code_saison']; ?>
</td>
												<td><?php echo $this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Code_compet']; ?>
</td>
												<td><?php echo $this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
												<td>n°<?php echo $this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Num']; ?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Capitaine'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'E', 'Entraineur') : smarty_modifier_replace($_tmp, 'E', 'Entraineur')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'A', 'Arbitre') : smarty_modifier_replace($_tmp, 'A', 'Arbitre')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'C', 'Cap') : smarty_modifier_replace($_tmp, 'C', 'Cap')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'X', 'INACTIF') : smarty_modifier_replace($_tmp, 'X', 'INACTIF')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '') : smarty_modifier_replace($_tmp, '-', '')); ?>
</td>
												<td>(<?php echo $this->_tpl_vars['Titulaire'][$this->_sections['i']['index']]['Categ']; ?>
)</td>
											</tr>
										<?php endfor; endif; ?>
									</tbody>
								</table>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['Arbitrages'][0]['Code_competition'] != ''): ?>
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=6>Arbitrages</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Date</th>
											<th>Compét.</th>
											<th>Match</th>
											<th>Prin</th>
											<th>Sec</th>
										</tr>
									</thead>
									<tbody>
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['Arbitrages']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<td><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Code_saison']; ?>
</td>
												<td><?php echo ((is_array($_tmp=$this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Date_match'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m") : smarty_modifier_date_format($_tmp, "%d/%m")); ?>
</td>
												<td><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Code_competition']; ?>
</td>
												<td><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Numero_ordre']; ?>

													<?php if ($this->_tpl_vars['profile'] <= 3): ?>
														<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Identifiant']; ?>
" target="_blank"><img width="10" src="../img/b_plus.png" alt="Feuille de match" title="Feuille de match" /></a>
													<?php endif; ?>
												</td>
												<?php if ($this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['ScoreOK'] == 'O'): ?>
													<td><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Prin']; ?>
</td>
													<td><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Sec']; ?>
</td>
												<?php else: ?>
													<td><i><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Prin']; ?>
</i></td>
													<td><i><?php echo $this->_tpl_vars['Arbitrages'][$this->_sections['i']['index']]['Sec']; ?>
</i></td>
												<?php endif; ?>
											</tr>
										<?php endfor; endif; ?>
									</tbody>
								</table>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($this->_tpl_vars['Joueur'][0]['Code_competition'] != ''): ?>
								<table class='tableau2'>
									<thead>
										<tr>
											<th colspan=11>Matchs joués</th>
										</tr>
										<tr>
											<th>Saison</th>
											<th>Date</th>
											<th>Compétition</th>
											<th>Match</th>
											<th>Equipes</th>
											<th>Score</th>
											<th>n°</th>
											<th>Buts</th>
											<th>Vert</th>
											<th>Jaune</th>
											<th>Rouge</th>
										</tr>
									</thead>
									<tbody>
										<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['Joueur']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
												<td><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Code_saison']; ?>
</td>
												<td><?php echo ((is_array($_tmp=$this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Date_match'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m") : smarty_modifier_date_format($_tmp, "%d/%m")); ?>
</td>
												<td><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Code_competition']; ?>
</td>
												<td>
													<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Numero_ordre']; ?>

													<?php if ($this->_tpl_vars['profile'] <= 3): ?>
														<a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Identifiant']; ?>
" target="_blank"><img width="10" src="../img/b_plus.png" alt="Feuille de match" title="Feuille de match" /></a>
													<?php endif; ?>
												</td>
												<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['ScoreOK'] == 'O'): ?>
													<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Equipe'] == 'A'): ?>
														<td><b><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqA']; ?>
</b> / <?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqB']; ?>
</td>
														<td>(<b><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['ScoreA']; ?>
</b>/<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['ScoreB']; ?>
)</td>
													<?php else: ?>
														<td><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqA']; ?>
 / <b><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqB']; ?>
</b></td>
														<td>(<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['ScoreA']; ?>
/<b><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['ScoreB']; ?>
</b>)</td>
													<?php endif; ?>
													<td>n°<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Num']; ?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Capitaine'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'E', 'Entraineur') : smarty_modifier_replace($_tmp, 'E', 'Entraineur')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'A', 'Arbitre') : smarty_modifier_replace($_tmp, 'A', 'Arbitre')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'C', 'Cap') : smarty_modifier_replace($_tmp, 'C', 'Cap')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '') : smarty_modifier_replace($_tmp, '-', '')); ?>
</td>
													<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['But'] > 0): ?><td class='gris'><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['But']; ?>
</td><?php else: ?><td></td><?php endif; ?>
													<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Vert'] > 0): ?><td class='vert'><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Vert']; ?>
</td><?php else: ?><td></td><?php endif; ?>
													<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Jaune'] > 0): ?><td class='jaune'><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Jaune']; ?>
</td><?php else: ?><td></td><?php endif; ?>
													<?php if ($this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Rouge'] > 0): ?><td class='rouge'><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Rouge']; ?>
</td><?php else: ?><td></td><?php endif; ?>
												<?php else: ?>
													<td><i><?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqA']; ?>
 / <?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['eqB']; ?>
</i></td>
													<td>&nbsp;</td>
													<td><i>n°<?php echo $this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Num']; ?>
 <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Joueur'][$this->_sections['i']['index']]['Capitaine'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'E', 'Entraineur') : smarty_modifier_replace($_tmp, 'E', 'Entraineur')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'A', 'Arbitre') : smarty_modifier_replace($_tmp, 'A', 'Arbitre')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'C', 'Cap') : smarty_modifier_replace($_tmp, 'C', 'Cap')))) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '') : smarty_modifier_replace($_tmp, '-', '')); ?>
</i></td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												<?php endif; ?>
											</tr>
										<?php endfor; endif; ?>
									</tbody>
								</table>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
				<?php endif; ?>
				<div class='blocBottom'>
					

				</div>
			</form>
		</div>
		