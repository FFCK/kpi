<?php /* Smarty version 2.6.18, created on 2014-11-22 23:32:47
         compiled from Calendrier.tpl */ ?>
		<span class='repere'>&nbsp;(<a href="#"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)
		<br></span>
		<div class="main">
			<form method="POST" action="Calendrier.php" name="formCalendrier" id="formCalendrier" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				
				<div class='titrePage'><?php echo $this->_config[0]['vars']['Calendrier_des_competitions']; ?>
</div>
				<div class='blocMiddle soustitrePage'>
					<div id='calendar<?php echo $this->_tpl_vars['Lang']; ?>
' class='fc'></div>
				</div>
				</div>
			</form>
		</div>
		
		