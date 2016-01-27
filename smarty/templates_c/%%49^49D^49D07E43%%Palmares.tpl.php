<?php /* Smarty version 2.6.18, created on 2015-03-14 23:28:53
         compiled from Palmares.tpl */ ?>
 	 	<span class="repere">&nbsp;(<a href="Palmares.php"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)</span>
	
		<div class="main">
			<form method="POST" action="Palmares.php" name="formPalmares" id="formPalmares" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				<div class='blocCentre'>
					<br>
					<label for="choixEquipe" class='maxWith'><i><?php echo $this->_config[0]['vars']['Chercher_equipe']; ?>
 : </i></label>
					<input width=350 type="text" name="choixEquipe" id="choixEquipe" />
					<INPUT TYPE="SUBMIT" VALUE="<?php echo $this->_config[0]['vars']['Palmares']; ?>
">
					<br>
					<br>
					<div class='titrePage'><?php echo $this->_config[0]['vars']['Palmares']; ?>
</div>
					<table class='tableau tableauPublic'>
						<thead>
							<tr>
								<th><?php echo $this->_config[0]['vars']['Equipe']; ?>
 : <?php echo $this->_tpl_vars['Equipe']; ?>
</th>
							</tr>
						</thead>
					</table>
					<br>
					<table class='tableau tableauPublic'>
						<thead>
							<tr>
								<th><?php echo $this->_config[0]['vars']['Saison']; ?>
</th>
								<th><?php echo $this->_config[0]['vars']['Competition']; ?>
</th>
								<th colspan=2 width=20><?php echo $this->_config[0]['vars']['Classement']; ?>
</th>
							</tr>
						</thead>
						<tbody>
						<?php $this->assign('idSaison', $this->_tpl_vars['arrayPalmares'][0]['Saisons']); ?>
						<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['arrayPalmares']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
							<?php if ($this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons'] != $this->_tpl_vars['idSaison']): ?>
								<tr class='pair2'><td colspan=4></td></tr>
							<?php endif; ?>
							<tr class='impair2'>
								<?php if ($this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Code_tour'] == 10): ?>
									<td><?php if ($this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons'] != $this->_tpl_vars['idSaison'] || $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons'] == $this->_tpl_vars['arrayPalmares'][0]['Saisons']): ?><?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons']; ?>
<?php endif; ?></td>
									<td class="cliquableCompet">
										<a href='Classements.php?Compet=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Code']; ?>
&Group=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Code_ref']; ?>
&Saison=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons']; ?>
' title='<?php echo $this->_config[0]['vars']['Classement']; ?>
'><?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Competitions']; ?>
</a>
									</td>
									<td><?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt']; ?>
</td>
									<td>
										<?php if ($this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt'] <= 3): ?>
											<img width="16" src="img/medal<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt']; ?>
.gif" alt="<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt']; ?>
" title="<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt']; ?>
" />
										<?php endif; ?>
									</td>
								<?php else: ?>
									<td><?php if ($this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons'] != $this->_tpl_vars['idSaison'] || $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons'] == $this->_tpl_vars['arrayPalmares'][0]['Saisons']): ?><?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons']; ?>
<?php endif; ?></td>
									<td class='cliquableCompet grispetit'>
										<a href='Classements.php?Compet=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Code']; ?>
&Group=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Code_ref']; ?>
&Saison=<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons']; ?>
' title='<?php echo $this->_config[0]['vars']['Classement']; ?>
'><i><?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Competitions']; ?>
</i></a>
									</td>
									<td class='grispetit'><i>(<?php echo $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Classt']; ?>
)</i></td>
									<td>
										&nbsp;
									</td>
								<?php endif; ?>
							</tr>
							<?php $this->assign('idSaison', $this->_tpl_vars['arrayPalmares'][$this->_sections['i']['index']]['Saisons']); ?>
						<?php endfor; endif; ?>
						</tbody>
					</table>

				</div>
						
			</form>			
					
		</div>	  	   