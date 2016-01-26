<?php /* Smarty version 2.6.18, created on 2014-11-25 04:08:47
         compiled from Incrustation.tpl */ ?>

<div class="main" >
		<div id="incrustation">
			<table class="incrust_table">
				<tr>
					<td rowspan="2" class="incrust_equipe"><?php echo $this->_tpl_vars['equipea']; ?>
<br /><img src="img/Pays/<?php echo $this->_tpl_vars['paysA']; ?>
.png" height="20"></td>
					<td class="incrust_score"><?php echo $this->_tpl_vars['scoreDetailA']; ?>
 - <?php echo $this->_tpl_vars['scoreDetailB']; ?>
</td>
					<td rowspan="2" class="incrust_equipe"><?php echo $this->_tpl_vars['equipeb']; ?>
<br /><img src="img/Pays/<?php echo $this->_tpl_vars['paysB']; ?>
.png" height="20"></td>
				</tr>
				<tr>
					<td class="incrust_situ">Terminé</td>
				</tr>
			</table>
			<input type="hidden" value="<?php echo $this->_tpl_vars['date']; ?>
" />
			<input type="hidden" value="<?php echo $this->_tpl_vars['heure']; ?>
" />
			<input type="hidden" value="<?php echo $this->_tpl_vars['ScoreA']; ?>
 - <?php echo $this->_tpl_vars['ScoreB']; ?>
" />
		</div>
</div>	  	   