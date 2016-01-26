<?php /* Smarty version 2.6.18, created on 2014-11-22 23:41:34
         compiled from SelectionOuiNon.tpl */ ?>
<html>
	<head>				  
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<title><?php echo $this->_tpl_vars['title']; ?>
</title>
	
	<?php echo '
		<script language="JavaScript" src="../js/prototype.js" type="text/javascript"></script>
		<script language="JavaScript" src="../js/formTools.js" type="text/javascript"></script>
	'; ?>


	</head>	  
	
	<body onload="alertMsg('<?php echo $this->_tpl_vars['AlertMessage2']; ?>
')">
	
		<div class="main">
					
			<form method="POST" action="SelectionOuiNon.php" name="formSelectionOuiNon" id="formSelectionOuiNon" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
	
				<fieldset>
				<legend>Choix</legend>	
				
				<?php if ($this->_tpl_vars['etatOuiNon'] == 'O'): ?>
					<?php $this->assign('checkedOui', 'Checked'); ?>
					<?php $this->assign('checkedNon', ''); ?>
				<?php else: ?>
					<?php $this->assign('checkedOui', ''); ?>
					<?php $this->assign('checkedNon', 'Checked'); ?>
				<?php endif; ?>
				
				Oui<input type="radio" name="radio_ouinon" value="O" <?php echo $this->_tpl_vars['checkedOui']; ?>
>
				Non<input type="radio" name="radio_ouinon" value="N" <?php echo $this->_tpl_vars['checkedNon']; ?>
>
				</fieldset>
							
				<input type="button" onclick="okRadioOuiNon();" value="Valider">
				<input type="button" onclick="cancelRadioOuiNon();" value="Annuler">
				
			</form>			
		</div>	  	   
	</body>
</html>