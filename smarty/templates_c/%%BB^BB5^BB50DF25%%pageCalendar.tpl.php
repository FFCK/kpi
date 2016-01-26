<?php /* Smarty version 2.6.18, created on 2015-03-14 23:08:22
         compiled from pageCalendar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'pageCalendar.tpl', 3, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => '../../commun/MyLang.conf','section' => $this->_tpl_vars['lang']), $this);?>


<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="Author" Content="LG" />
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />

		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel='stylesheet' type='text/css' href='css/redmond/theme.css' />
		<link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
		<link rel="stylesheet" type="text/css" href="css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css" />

		<script language="JavaScript" type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.core.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.draggable.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/ui.resizable.js'></script>
		<script language="JavaScript" type='text/javascript' src='js/fullcalendar.min.js'></script>
		<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
		<script language="JavaScript" type="text/javascript" src="js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>

		<title><?php echo $this->_tpl_vars['title']; ?>
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
