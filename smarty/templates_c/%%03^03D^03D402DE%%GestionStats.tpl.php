<?php /* Smarty version 2.6.18, created on 2015-05-25 23:12:16
         compiled from GestionStats.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionStats.tpl', 170, false),array('function', 'html_options', 'GestionStats.tpl', 475, false),array('modifier', 'date_format', 'GestionStats.tpl', 337, false),array('modifier', 'string_format', 'GestionStats.tpl', 464, false),array('modifier', 'default', 'GestionStats.tpl', 484, false),)), $this); ?>
    &nbsp;(<a href="Admin.php">Retour</a>)
	<div class="main">
		<form method="POST" action="GestionStats.php" name="formStats" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>
			<div class='blocLeft'>
				<div class='titrePage'>Statistiques <?php echo $this->_tpl_vars['codeSaison']; ?>
 (version béta)</div>
				<?php if ($this->_tpl_vars['AfficheStat'] == 'Buteurs'): ?>
					<div class='titrePage'>Meilleur buteur</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Attaque'): ?>
					<div class='titrePage'>Meilleure attaque (buts des feuilles de match uniquement)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Defense'): ?>
					<div class='titrePage'>Meilleure défense (buts des feuilles de match uniquement)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Cartons'): ?>
					<div class='titrePage'>Cartons</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CartonsEquipe'): ?>
					<div class='titrePage'>Cartons par équipe</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Fairplay'): ?>
					<div class='titrePage'>Classement disciplinaire individuel (rouge=4, jaune=2, vert=1)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'FairplayEquipe'): ?>
					<div class='titrePage'>Classement disciplinaire par équipe (rouge=4, jaune=2, vert=1)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Arbitrage'): ?>
					<div class='titrePage'>Arbitrage</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ArbitrageEquipe'): ?>
					<div class='titrePage'>Arbitrage par équipe (seuls les arbitrages nominatifs sont pris en compte)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees'): ?>
					<div class='titrePage'>Compétitions jouées par club (matchs verrouillés)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees2'): ?>
					<div class='titrePage'>Compétitions jouées par équipe (matchs verrouillés)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees3'): ?>
					<div class='titrePage'>Irrégularités : licence, certificats, pagaie eau calme (matchs verrouillés)</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsJournees'): ?>
					<div class='titrePage'>Officiels des journées</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsMatchs'): ?>
					<div class='titrePage'>Officiels des matchs</div>
				<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ListeArbitres'): ?>
					<div class='titrePage'>Liste des arbitres</div>
				<?php endif; ?>
				<div class='liens'>
					<a href="FeuilleStats.php" Target="_blank" title="Version pdf"><img width="20" alt="pdf FR" src="../img/pdfFR.gif"></a>
					<a href="FeuilleStatsEN.php" Target="_blank" title="Version pdf EN"><img width="20" alt="pdf EN" src="../img/pdfEN.gif"></a>
                    <?php if ($this->_tpl_vars['sql_csv'] != ''): ?>
                        <a href="upload_csv.php?action=export" title="Téléchargement CSV : <?php echo $this->_tpl_vars['sql_csv']; ?>
"><img width="20" alt="CSV" src="../img/csv.png"></a>
                    <?php endif; ?>
					<div align=right><span id='reachspan'><i>Surligner:</i></span><input type=text name='reach' id='reach' size='20'></div>
				</div>
					<div class='blocTable' id='blocCompet'>
						<table class='tableau' id='tableCompet'>
							<thead>
								<tr class='header'>
									<?php if ($this->_tpl_vars['AfficheStat'] == 'Buteurs'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Sexe</th>
										<th>Equipe</th>
										<th>Buts</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Attaque'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>Equipe</th>
										<th>Buts marqués</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Defense'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>Equipe</th>
										<th>Buts concédés</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Cartons'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Sexe</th>
										<th>Equipe</th>
										<th>Vert</th>
										<th>Jaune</th>
										<th>Rouge</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CartonsEquipe'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>Equipe</th>
										<th>Vert</th>
										<th>Jaune</th>
										<th>Rouge</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Fairplay'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Sexe</th>
										<th>Equipe</th>
										<th>Pts</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'FairplayEquipe'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>Equipe</th>
										<th>Pts</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Arbitrage'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Sexe</th>
										<th>Principal</th>
										<th>Secondaire</th>
										<th>Total</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ArbitrageEquipe'): ?>
										<th>#</th>
										<th>Compet.</th>
										<th>Equipe</th>
										<th>Principal</th>
										<th>Secondaire</th>
										<th>Total</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees'): ?>
										<th>#</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Club</th>
										<th>Competition</th>
										<th>Nb_matchs</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees2'): ?>
										<th>#</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Equipe</th>
										<th>Competition</th>
										<th>Nb_matchs</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees3'): ?>
										<th>#</th>
										<th>N°</th>
										<th>Nom</th>
										<th>Prenom</th>
										<th>Equipe</th>
										<th>Competition</th>
										<th>Irrégularités</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsJournees'): ?>
										<th>Compet.</th>
										<th>Date</th>
										<th>Lieu</th>
										<th>RC</th>
										<th>R1</th>
										<th>Délégué</th>
										<th>Chef Arb.</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsMatchs'): ?>
										<th>Compet. - Lieu</th>
										<th>Date</th>
										<th>Arb.</th>
										<th>Lignes</th>
										<th>Table</th>
									<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ListeArbitres'): ?>
										<th>#</th>
										<th>Arbitre</th>
										<th>Club</th>
										<th>Niveau</th>
										<th>Saison</th>
										<th>Livret</th>
									<?php endif; ?>
								</tr>
							</thead>
						<tbody>
						<?php if ($this->_tpl_vars['AfficheStat'] == 'Buteurs'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayButeurs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Numero']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Licence']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayButeurs'][$this->_sections['i']['index']]['Buts']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Attaque'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayAttaque']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayAttaque'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayAttaque'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayAttaque'][$this->_sections['i']['index']]['Buts']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Defense'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayDefense']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayDefense'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayDefense'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayDefense'][$this->_sections['i']['index']]['Buts']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Cartons'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCartons']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Numero']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
                                                                                    <a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Licence']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Vert']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Jaune']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartons'][$this->_sections['i']['index']]['Rouge']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CartonsEquipe'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCartonsEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartonsEquipe'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartonsEquipe'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartonsEquipe'][$this->_sections['i']['index']]['Vert']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartonsEquipe'][$this->_sections['i']['index']]['Jaune']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCartonsEquipe'][$this->_sections['i']['index']]['Rouge']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Fairplay'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayFairplay']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Numero']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Licence']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplay'][$this->_sections['i']['index']]['Fairplay']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'FairplayEquipe'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayFairplayEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplayEquipe'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplayEquipe'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayFairplayEquipe'][$this->_sections['i']['index']]['Fairplay']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'Arbitrage'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayArbitrage']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Licence']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Licence']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Sexe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Principal']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Secondaire']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrage'][$this->_sections['i']['index']]['Total']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ArbitrageEquipe'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayArbitrageEquipe']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrageEquipe'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrageEquipe'][$this->_sections['i']['index']]['Equipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrageEquipe'][$this->_sections['i']['index']]['Principal']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrageEquipe'][$this->_sections['i']['index']]['Secondaire']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayArbitrageEquipe'][$this->_sections['i']['index']]['Total']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCJouees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php if ($this->_tpl_vars['MatricTemp'] == $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Matric']): ?><?php if ($this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Nb_matchs'] > 3): ?> rouge<?php else: ?> vert<?php endif; ?><?php endif; ?>'  title="<?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Nom_club']; ?>
">
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Matric']; ?>
</td>
									<?php $this->assign('MatricTemp', $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Matric']); ?>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Matric']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Numero_club']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees'][$this->_sections['i']['index']]['Nb_matchs']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees2'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCJouees2']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php if ($this->_tpl_vars['MatricTemp'] == $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Matric']): ?><?php if ($this->_tpl_vars['EquipeTemp'] == $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['nomEquipe']): ?> vert<?php else: ?> rouge<?php endif; ?><?php endif; ?>'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Matric']; ?>
</td>
									<?php $this->assign('MatricTemp', $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Matric']); ?>
									<?php $this->assign('EquipeTemp', $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['nomEquipe']); ?>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Matric']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['nomEquipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees2'][$this->_sections['i']['index']]['Nb_matchs']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'CJouees3'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCJouees3']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php if ($this->_tpl_vars['MatricTemp'] == $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Matric']): ?> vert<?php endif; ?>'>
									<td><?php echo $this->_sections['i']['iteration']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Matric']; ?>
</td>
									<?php $this->assign('MatricTemp', $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Matric']); ?>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Nom']; ?>

										<?php if ($this->_tpl_vars['profile'] <= 6): ?>
												<a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Matric']; ?>
"><img width="10" src="../img/b_plus.png" alt="Détails" title="Détails" /></a>
										<?php endif; ?></td>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Prenom']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['nomEquipe']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Competition']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayCJouees3'][$this->_sections['i']['index']]['Irreg']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsJournees'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayOfficielsJournees']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Code_competition']; ?>
</td>
									<td><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
<br /><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Date_fin'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Lieu']; ?>
 (<?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Departement']; ?>
)</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Responsable_insc']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Responsable_R1']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['Delegue']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsJournees'][$this->_sections['i']['index']]['ChefArbitre']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
								<tr>
									<td colspan="5"><i>Nb journees : <?php echo $this->_tpl_vars['nbJournees']; ?>
</i></td>
									<td colspan="3"><i>Nb journées avec officiels : <?php echo $this->_tpl_vars['nbOfficiels']; ?>
</i></td>
								</tr>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'OfficielsMatchs'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayOfficielsMatchs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
									<td><?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Code_competition']; ?>
 - <?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Lieu']; ?>
 (<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Departement']; ?>
)</td>
									<td><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Date_match'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
<br />n°<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Numero_ordre']; ?>
 - <?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Heure_match']; ?>

                                                                            <?php if ($this->_tpl_vars['profile'] <= 6): ?>
                                                                                <a href="FeuilleMatchMulti.php?listMatch=<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Id']; ?>
" target="_blank"><img width="10" src="../img/b_plus.png" alt="Détails" title="<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['equipeA']; ?>
 / <?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['equipeB']; ?>
" /></a>
                                                                            <?php endif; ?>
                                                                        </td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Arbitre_principal']; ?>
<br /><?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Arbitre_secondaire']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Ligne1']; ?>
<br /><?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Ligne2']; ?>
</td>
									<td>Sec:<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Secretaire']; ?>
<br />Chr:<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Chronometre']; ?>
<br />TS:<?php echo $this->_tpl_vars['arrayOfficielsMatchs'][$this->_sections['i']['index']]['Timeshoot']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php elseif ($this->_tpl_vars['AfficheStat'] == 'ListeArbitres'): ?>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayListeArbitres']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
'>
                                                                        <td><?php echo $this->_sections['i']['iteration']; ?>
</td>
                                                                        <td class="cliquableNomEquipe"><a href="GestionAthlete.php?Athlete=<?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Matric']; ?>
"><?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Nom']; ?>
 <?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Prenom']; ?>
 (<?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Matric']; ?>
)</a></td>
									<td><?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Club']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Arb']; ?>
 <?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['niveau']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['saison']; ?>
</td>
									<td><?php echo $this->_tpl_vars['arrayListeArbitres'][$this->_sections['i']['index']]['Livret']; ?>
</td>
								</tr>
							<?php endfor; endif; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class='blocRight'>
			<table width=100%>
				<tr>
					<th class='titreForm' colspan=2>
						<label>Sélection</label>
					</th>
				</tr>
				<tr>
					<td width=65>
						<label for="codeSaison">Saison:</label>
						<select name="codeSaison" onChange="document.formStats.submit()">
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
" <?php if ($this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code'] == $this->_tpl_vars['codeSaison']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['arraySaison'][$this->_sections['i']['index']]['Code']; ?>
</Option>
						<?php endfor; endif; ?>
						</select>
					</td>
					<td>
						<label for="AfficheStat">Statistique:</label>
						<select name="AfficheStat" onChange="document.formStats.submit()">
							<Option Value="Buteurs"<?php if ($this->_tpl_vars['AfficheStat'] == 'Buteurs'): ?> selected<?php endif; ?>>Meilleur buteur</Option>
							<Option Value="Attaque"<?php if ($this->_tpl_vars['AfficheStat'] == 'Attaque'): ?> selected<?php endif; ?>>Meilleure attaque</Option>
							<Option Value="Defense"<?php if ($this->_tpl_vars['AfficheStat'] == 'Defense'): ?> selected<?php endif; ?>>Meilleure défense</Option>
							<Option Value="Cartons"<?php if ($this->_tpl_vars['AfficheStat'] == 'Cartons'): ?> selected<?php endif; ?>>Cartons</Option>
							<Option Value="CartonsEquipe"<?php if ($this->_tpl_vars['AfficheStat'] == 'CartonsEquipe'): ?> selected<?php endif; ?>>Cartons par équipe</Option>
							<Option Value="Fairplay"<?php if ($this->_tpl_vars['AfficheStat'] == 'Fairplay'): ?> selected<?php endif; ?>>Class. disciplinaire individuel</Option>
							<Option Value="FairplayEquipe"<?php if ($this->_tpl_vars['AfficheStat'] == 'FairplayEquipe'): ?> selected<?php endif; ?>>Class. disciplinaire par équipe</Option>
							<Option Value="Arbitrage"<?php if ($this->_tpl_vars['AfficheStat'] == 'Arbitrage'): ?> selected<?php endif; ?>>Arbitrage</Option>
							<Option Value="ArbitrageEquipe"<?php if ($this->_tpl_vars['AfficheStat'] == 'ArbitrageEquipe'): ?> selected<?php endif; ?>>Arbitrage par équipe</Option>
							<?php if ($this->_tpl_vars['profile'] <= 6): ?>
								<Option Value="CJouees"<?php if ($this->_tpl_vars['AfficheStat'] == 'CJouees'): ?> selected<?php endif; ?>>Compétitions jouées (clubs)</Option>
								<Option Value="CJouees2"<?php if ($this->_tpl_vars['AfficheStat'] == 'CJouees2'): ?> selected<?php endif; ?>>Compétitions jouées (équipes)</Option>
								<Option Value="CJouees3"<?php if ($this->_tpl_vars['AfficheStat'] == 'CJouees3'): ?> selected<?php endif; ?>>Irrégularités (matchs)</Option>
								<Option Value="OfficielsJournees"<?php if ($this->_tpl_vars['AfficheStat'] == 'OfficielsJournees'): ?> selected<?php endif; ?>>Officiels journées</Option>
								<Option Value="OfficielsMatchs"<?php if ($this->_tpl_vars['AfficheStat'] == 'OfficielsMatchs'): ?> selected<?php endif; ?>>Officiels matchs</Option>
								<Option Value="ListeArbitres"<?php if ($this->_tpl_vars['AfficheStat'] == 'ListeArbitres'): ?> selected<?php endif; ?>>Liste des arbitres</Option>
							<?php endif; ?>
						</select>
					</td>
				</tr>
<!--				<tr>
					<td colspan=2>
						<label for="AfficheNiveau">Niveau :</label>
						<select name="AfficheNiveau" onChange="document.formStats.submit()">
							<Option Value="" selected>Tous les niveaux</Option>
							<Option Value="INT"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'INT'): ?> selected<?php endif; ?>>Compétitions Internationales</Option>
							<Option Value="NAT"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'NAT'): ?> selected<?php endif; ?>>Compétitions Nationales</Option>
							<Option Value="REG"<?php if ($this->_tpl_vars['AfficheNiveau'] == 'REG'): ?> selected<?php endif; ?>>Compétitions Régionales</Option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<select name="AfficheCompet" onChange="document.formStats.submit()">
							<Option Value="" selected>Toutes les compétitions</Option>
							<Option Value="N"<?php if ($this->_tpl_vars['AfficheCompet'] == 'N'): ?> selected<?php endif; ?>>Championnat de France</Option>
							<Option Value="CF"<?php if ($this->_tpl_vars['AfficheCompet'] == 'CF'): ?> selected<?php endif; ?>>Coupe de France</Option>
							<Option Value="REG"<?php if ($this->_tpl_vars['AfficheCompet'] == 'REG'): ?> selected<?php endif; ?>>Championnats régionaux</Option>
							<Option Value="DEP"<?php if ($this->_tpl_vars['AfficheCompet'] == 'DEP'): ?> selected<?php endif; ?>>Championnats départementaux</Option>
							<Option Value="TI"<?php if ($this->_tpl_vars['AfficheCompet'] == 'TI'): ?> selected<?php endif; ?>>Tournois Internationaux</Option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<label for="codeCompet">Compétition :</label>
						<select name="groupCompet" onChange="document.formStats.submit()">
							<Option Value="" selected>Toutes les compétitions (Groupées)</Option>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayGroupCompet']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Code_ref']; ?>
" <?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['StdOrSelected']; ?>
><?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Code_ref']; ?>
-<?php echo $this->_tpl_vars['arrayGroupCompet'][$this->_sections['i']['index']]['Libelle']; ?>
 (Toutes)</Option>
							<?php endfor; endif; ?>
						</select>
						<select name="codeCompet" onChange="document.formStats.submit()">
							<Option Value="" selected>Toutes les compétitions</Option>
							<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayCompet']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
								<Option Value="<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
" <?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['StdOrSelected']; ?>
><?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Code']; ?>
-<?php echo $this->_tpl_vars['arrayCompet'][$this->_sections['i']['index']]['Libelle']; ?>
</Option>
							<?php endfor; endif; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<label for="AfficheJournee">Journée :</label>
						<select name="AfficheJournee" onChange="document.formStats.submit()">
							<Option Value="" selected>Toutes les journées</Option>
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
								<Option Value="<?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Id']; ?>
" <?php echo $this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['StdOrSelected']; ?>
><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Date_debut'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "Le %s ") : smarty_modifier_string_format($_tmp, "Le %s ")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Lieu'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "à %s") : smarty_modifier_string_format($_tmp, "à %s")); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Phase'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "- %s") : smarty_modifier_string_format($_tmp, "- %s")); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournees'][$this->_sections['i']['index']]['Niveau'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "(%s)") : smarty_modifier_string_format($_tmp, "(%s)")); ?>
</Option>
							<?php endfor; endif; ?>
						</select>
					</td>
				</tr>
-->
				<tr>
					<td colspan=2>
						<label for="Compets">Compétitions:</label>
						<DIV STYLE="overflow-x:scroll; overflow-y: hidden; height:200px;width:240px"> 
							<select name="Compets[]" multiple size=12 style="width:350px">
								<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['arrayCompets'],'selected' => $this->_tpl_vars['Compets']), $this);?>

							</select>
						</div>
						<label><i>(Sélection multiple avec CTRL)</i></label>
					</td>
				</tr>
				<tr>
					<td>
						<label for="nbLignes">Nb lignes:</label>
						<input type="text" name="nbLignes" id="nbLignes" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['nbLignes'])) ? $this->_run_mod_handler('default', true, $_tmp, '30') : smarty_modifier_default($_tmp, '30')); ?>
">
					</td>
					<td>
						<br>
						<input type="button" value="Mise à jour" onClick="submit()">
						<br>
						<br>
					</td>
				</tr>
				<tr>
					<th class='titreForm' colspan=2>
						<label>Statistiques athlète</label>
					</th>
				</tr>
				<tr>
					<td colspan=2>
						<label>Recherche (nom, prénom ou licence)</label>
						<input type="text" name="choixJoueur" id="choixJoueur" size="30" />
						<!--<input type="submit" name="maj" id="maj" value="Mise à jour">
						<br />
						<label for="comboarbitre1">Recherche avancée</label>-->
						<br />
						<center><a href="GestionAthlete.php" id='rechercheAthlete'>Accès</a></center>
						<br />
						<center><a href="GestionAthlete.php">Recherche avancée</a></center>
						<input type="hidden" name="Athlete" id="Athlete" value="<?php echo $this->_tpl_vars['Athlete']; ?>
"/>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>	