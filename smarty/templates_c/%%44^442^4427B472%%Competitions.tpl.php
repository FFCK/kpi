<?php /* Smarty version 2.6.18, created on 2014-11-25 05:48:13
         compiled from Competitions.tpl */ ?>
		<span class='repere'>&nbsp;(<a href="#"><?php echo $this->_config[0]['vars']['Retour']; ?>
</a>)
		<br></span>
		<div class="main">
			<form method="POST" action="Competitions.php" name="formCompetitions" id="formCompetitions" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
				<input type='hidden' name='ParamCmd' Value=''/>
				
				<div class='titrePage'>Compétition : <?php echo $this->_tpl_vars['the_title']; ?>
</div>
				<div class='blocMiddle soustitrePage'>
					<?php echo $this->_tpl_vars['the_content']; ?>

				</div>
			</form>
		</div>
		
		