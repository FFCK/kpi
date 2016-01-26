<?php /* Smarty version 2.6.18, created on 2014-11-28 18:17:15
         compiled from SelectionCapitaineJoueur.tpl */ ?>
<html>
	<head>				  
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<link rel="stylesheet" type="text/css" href="../css/iframeUpdate.css" />
	<title><?php echo $this->_tpl_vars['title']; ?>
</title>
	
	<?php echo '
		<script language="JavaScript" src="../js/prototype.js" type="text/javascript"></script>
		<script language="JavaScript" src="../js/formTools.js" type="text/javascript"></script>
	'; ?>

		
	</head>	  
	
	<body onload="alertMsg('<?php echo $this->_tpl_vars['AlertMessage']; ?>
')">
	
		<div class="main">
					
			<form method="POST" action="SelectionCapitaineJoueur.php" name="formSelectionCapitaineJoueur" id="formSelectionCapitaineJoueur" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value=''/>
	
				<fieldset>
				<legend>Choix</legend>	
				
				<?php if ($this->_tpl_vars['etatJoueur'] == 'O'): ?>
					<?php $this->assign('checkedJoueur', ''); ?>
					<?php $this->assign('checkedCapitaine', 'Checked'); ?>
					<?php $this->assign('checkedEntraineur', ''); ?>
					<?php $this->assign('checkedArbitre', ''); ?>
					<?php $this->assign('checkedInactif', ''); ?>
				<?php elseif ($this->_tpl_vars['etatJoueur'] == 'E'): ?>
					<?php $this->assign('checkedJoueur', ''); ?>
					<?php $this->assign('checkedCapitaine', ''); ?>
					<?php $this->assign('checkedEntraineur', 'Checked'); ?>
					<?php $this->assign('checkedArbitre', ''); ?>
					<?php $this->assign('checkedInactif', ''); ?>
				<?php elseif ($this->_tpl_vars['etatJoueur'] == 'A'): ?>
					<?php $this->assign('checkedJoueur', ''); ?>
					<?php $this->assign('checkedCapitaine', ''); ?>
					<?php $this->assign('checkedEntraineur', ''); ?>
					<?php $this->assign('checkedArbitre', 'Checked'); ?>
					<?php $this->assign('checkedInactif', ''); ?>
				<?php elseif ($this->_tpl_vars['etatJoueur'] == 'X'): ?>
					<?php $this->assign('checkedJoueur', ''); ?>
					<?php $this->assign('checkedCapitaine', ''); ?>
					<?php $this->assign('checkedEntraineur', ''); ?>
					<?php $this->assign('checkedArbitre', ''); ?>
					<?php $this->assign('checkedInactif', 'Checked'); ?>
				<?php else: ?>
					<?php $this->assign('checkedJoueur', 'Checked'); ?>
					<?php $this->assign('checkedCapitaine', ''); ?>
					<?php $this->assign('checkedEntraineur', ''); ?>
					<?php $this->assign('checkedArbitre', ''); ?>
					<?php $this->assign('checkedInactif', ''); ?>
				<?php endif; ?>
				
				<input type="radio" name="radio_etatJoueur" value="-" <?php echo $this->_tpl_vars['checkedJoueur']; ?>
>Joueur
				<br>
				<input type="radio" name="radio_etatJoueur" value="O" <?php echo $this->_tpl_vars['checkedCapitaine']; ?>
>Capitaine
				<br>
				<input type="radio" name="radio_etatJoueur" value="E" <?php echo $this->_tpl_vars['checkedEntraineur']; ?>
>Entraineur (non joueur)
				<br>
				<input type="radio" name="radio_etatJoueur" value="A" <?php echo $this->_tpl_vars['checkedArbitre']; ?>
><i>Arbitre (non joueur)</i>
				<br>
				<input type="radio" name="radio_etatJoueur" value="X" <?php echo $this->_tpl_vars['checkedInactif']; ?>
><i>Inactif (non joueur)</i>
				</fieldset>
							
				<input type="button" onclick="okRadioCapitaine('<?php echo $this->_tpl_vars['actionJoueur']; ?>
');" value="Valider">
				<input type="button" onclick="cancelRadioCapitaine();" value="Annuler">
				
			</form>			
		</div>	  	   
	</body>
</html>