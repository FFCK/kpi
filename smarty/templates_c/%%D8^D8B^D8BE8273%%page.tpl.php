<?php /* Smarty version 2.6.18, created on 2015-06-26 10:44:08
         compiled from page.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'page.tpl', 1, false),array('modifier', 'default', 'page.tpl', 85, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => '../../commun/MyLang.conf','section' => $this->_tpl_vars['lang']), $this);?>

<!DOCTYPE html>
<html lang="fr" xmlns:og="http://ogp.me/ns#">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<meta property="og:image" content="http://kayak-polo.info/img/KPI.png" />
		<link rel="image_src" href="http://kayak-polo.info/img/KPI.png" />
		<!--<meta property="og:title" content="kayak-polo.info" />-->
		<meta property="og:type" content="article" />
		<!--<meta property="og:url" content="http://kayak-polo.info"/>-->
		<!--<meta property="og:description" content="kayak polo français" />-->
		<meta property="og:site_name" content="KAYAK-POLO.INFO" />
		<?php if ($this->_tpl_vars['bPublic']): ?>
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link type="text/css" rel="stylesheet" href="css/jquery.autocomplete.css" media="screen" />
			<link type="text/css" rel="stylesheet" href="css/jquery.tooltip.css" media="screen" />
                        <?php $this->assign('temp', "css/".($this->_tpl_vars['contenutemplate']).".css"); ?> 
                        <?php if (is_file ( $this->_tpl_vars['temp'] )): ?>
                            <link type="text/css" rel="stylesheet" href="css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css" />
                        <?php endif; ?>
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
                        <?php $this->assign('temp', "css/".($this->_tpl_vars['css_supp']).".css"); ?> 
			<?php if ($this->_tpl_vars['css_supp'] && is_file ( $this->_tpl_vars['temp'] )): ?>
				<link type="text/css" rel="stylesheet" href="css/<?php echo $this->_tpl_vars['css_supp']; ?>
.css">
			<?php endif; ?>
			<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script src="js/jquery-1.5.2.min.js"></script>
			<script src="js/jquery.autocomplete.min.js"></script>
			<script src="js/jquery.tooltip.min.js"></script>
			<script src="js/jquery.maskedinput.min.js"></script>
			<script src="js/jquery.fixedheadertable.min.js"></script>
			<script src="js/formTools.js"></script>
                        <?php $this->assign('temp', "js/".($this->_tpl_vars['contenutemplate']).".js"); ?> 
                        <?php if (is_file ( $this->_tpl_vars['temp'] )): ?>
                            <script src="js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>
                        <?php endif; ?>

			<!--
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="http://hayageek.github.io/jQuery-Upload-File/jquery.uploadfile.min.js"></script>
			-->
		<?php else: ?>
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css" />
			<link type="text/css" rel="stylesheet" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.autocomplete.css" media="screen" />
			<link type="text/css" rel="stylesheet" href="../css/jquery.tooltip.css" media="screen" />
			<?php $this->assign('temp', "../css/".($this->_tpl_vars['contenutemplate']).".css"); ?> 
			<?php if (is_file ( $this->_tpl_vars['temp'] )): ?>
				<link type="text/css" rel="stylesheet" href="../css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css" />
			<?php endif; ?>
			
			<!-- 
				Css = '' (simply, zsainto, ckca...) 
				notamment sur les pages Journee.php et Classements.php 
				intégrer en iframe : 
			-->
            <?php $this->assign('temp', "..css/".($this->_tpl_vars['css_supp']).".css"); ?> 
			<?php if ($this->_tpl_vars['css_supp'] && is_file ( $this->_tpl_vars['temp'] )): ?>
				<link type="text/css" rel="stylesheet" href="..css/<?php echo $this->_tpl_vars['css_supp']; ?>
.css">
			<?php endif; ?>
			<script src="../js/dhtmlgoodies_calendar.js?random=20060118"></script>
			<script src="../js/jquery-1.5.2.min.js"></script>
			<script src="../js/jquery.autocomplete.min.js"></script>
			<script src="../js/jquery.tooltip.min.js"></script>
			<script src="../js/jquery.maskedinput.min.js"></script>
			<script src="../js/jquery.fixedheadertable.min.js"></script>
			<script src="../js/formTools.js"></script>
			<?php $this->assign('temp', "../js/".($this->_tpl_vars['contenutemplate']).".js"); ?> 
			<?php if (is_file ( $this->_tpl_vars['temp'] )): ?>
				<script src="../js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>
			<?php endif; ?>
			<!--
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="http://hayageek.github.io/jQuery-Upload-File/jquery.uploadfile.min.js"></script>
			-->
		<?php endif; ?>
		<title><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['title']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['title'])); ?>
</title>
	</head>
	<body onload="testframe(); alertMsg('<?php echo $this->_tpl_vars['AlertMessage']; ?>
')">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'main_menu.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['contenutemplate']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</body>
</html>