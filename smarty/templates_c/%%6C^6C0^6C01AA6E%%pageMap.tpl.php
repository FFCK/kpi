<?php /* Smarty version 2.6.18, created on 2015-04-19 12:24:57
         compiled from pageMap.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'pageMap.tpl', 3, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => '../../commun/MyLang.conf','section' => $this->_tpl_vars['lang']), $this);?>


<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		<?php if ($this->_tpl_vars['bPublic']): ?>
			<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
			<link rel="stylesheet" type="text/css" href="css/style.css" />
			<link rel="stylesheet" type="text/css" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css" />

			<script language="JavaScript" type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>
		<?php else: ?>
			<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
			<link rel="stylesheet" type="text/css" href="../css/GestionStyle.css" />
			<link rel="stylesheet" type="text/css" href="../css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.autocomplete.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/jquery.tooltip.css" media="screen" />
			<link rel="stylesheet" type="text/css" href="../css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css" />

			<script language="JavaScript" type="text/javascript" src="../js/jquery-1.5.2.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.autocomplete.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.tooltip.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/jquery.maskedinput.min.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/formTools.js"></script>
			<script language="JavaScript" type="text/javascript" src="../js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>
		<?php endif; ?>
		<?php echo '
			<script language="JavaScript" type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAWonor80iC2LsJ4C5x7MJsBRfQyOgPKrZ8po1VXyQgkC373NrwRQugr1dZEkzcuIqpSAIryIaw67HyQ"></script>
			<script language="JavaScript" type="text/javascript">
				function loadparam() {
					if (GBrowserIsCompatible()) {
						'; ?>
<?php echo $this->_tpl_vars['mapParam']; ?>
<?php echo '
					}
				}
			</script>
		'; ?>


		<title><?php echo $this->_tpl_vars['title']; ?>
</title>
	</head>	  
	<body onload="testframe(); load(); loadparam(); alertMsg('<?php echo $this->_tpl_vars['AlertMessage']; ?>
')" onunload="GUnload()">
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
