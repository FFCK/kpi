<?php /* Smarty version 2.6.18, created on 2015-01-28 14:00:10
         compiled from GestionClassementInit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'GestionClassementInit.tpl', 36, false),)), $this); ?>
 	 	&nbsp;(<a href="GestionClassement.php">Retour</a>)
	
		<div class="main">
					
			<form method="POST" action="GestionClassementInit.php" name="formClassementInit" id="formClassementInit" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' id='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd'  id='ParamCmd' Value=''/>
				<input type='hidden' name='AjaxTableName' id='AjaxTableName' Value='gickp_Competitions_Equipes_Init'/>
				<input type='hidden' name='AjaxWhere' id='AjaxWhere' Value='Where Id = '/>
				<input type='hidden' name='AjaxUser' id='AjaxUser' Value='<?php echo $this->_tpl_vars['user']; ?>
'/>

				<div class='blocLeft'>
					<div class='titrePage'>Classement initial <?php echo $this->_tpl_vars['codeCompet']; ?>
</div>
					<button id='actuButton' type="button" ><img src="../img/actualiser.gif">Recharger</button>
					<input type='button' id='raz' value='Remise à zéro'>
					<div class='blocTable'>
						<table id='tableauJQ' class='tableau'>
							<thead>
								<tr class='header'>
									<th>Clt</th>
									<th>Libelle</th>
									<th>Pts</th>
									<th>J</th>
									<th>G</th>
									<th>N</th>
									<th>P</th>
									<th>F</th>
									<th>Plus</th>
									<th>Moins</th>
									<th>Diff</th>
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
									<?php if ($this->_tpl_vars['profile'] <= 4): ?>
										<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td width="30"><a href="#" Id="Clt-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
0"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Clt']; ?>
</a></td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
											<td width="40"><a href="#" Id="Pts-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
1"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Pts']; ?>
</a></td>
											<td width="30"><a href="#" Id="J-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
2"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['J']; ?>
</a></td>
											<td width="30"><a href="#" Id="G-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
3"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['G']; ?>
</a></td>
											<td width="30"><a href="#" Id="N-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
4"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['N']; ?>
</a></td>
											<td width="30"><a href="#" Id="P-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
5"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['P']; ?>
</a></td>
											<td width="30"><a href="#" Id="F-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
6"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['F']; ?>
</a></td>
											<td width="40"><a href="#" Id="Plus-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
7"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Plus']; ?>
</a></td>
											<td width="40"><a href="#" Id="Moins-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
8"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Moins']; ?>
</a></td>
											<td width="40"><a href="#" Id="Diff-<?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Id']; ?>
" tabindex="1<?php echo $this->_sections['i']['iteration']; ?>
9"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Diff']; ?>
</a></td>
										</tr>
									<?php else: ?>
										<tr height="17" class='<?php echo smarty_function_cycle(array('values' => "impair,pair"), $this);?>
'>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Clt']; ?>
</td>
											<td width="200"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Libelle']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Pts']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['J']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['G']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['N']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['P']; ?>
</td>
											<td width="30"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['F']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Plus']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Moins']; ?>
</td>
											<td width="40"><?php echo $this->_tpl_vars['arrayEquipe'][$this->_sections['i']['index']]['Diff']; ?>
</td>
										</tr>
									<?php endif; ?>
								<?php endfor; endif; ?>
							</tbody>
						</table>
					</div>

				</div>
					
			</form>			
					
		</div>	  	   
