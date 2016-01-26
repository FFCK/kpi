<?php /* Smarty version 2.6.18, created on 2014-11-13 23:39:29
         compiled from pageAdm.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'pageAdm.tpl', 1, false),array('modifier', 'default', 'pageAdm.tpl', 41, false),array('modifier', 'upper', 'pageAdm.tpl', 49, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => '../../commun/MyLang.conf','section' => $this->_tpl_vars['lang']), $this);?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="Author" Content="LG">
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
		<?php if ($this->_tpl_vars['bPublic']): ?>
			<link rel="stylesheet" type="text/css" href="/css/style.css">
		<?php else: ?>
			<link rel="stylesheet" type="text/css" href="/css/GestionStyle.css">
		<?php endif; ?>
		<link rel="stylesheet" href="/js/bootstrap/css/bootstrap.min.css">
		<link type="text/css" rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/jqueryui/dataTables.jqueryui.css">
		<link type="text/css" rel="stylesheet" href="/css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
		<!--<link type="text/css" rel="stylesheet" href="/css/jquery.autocomplete.css" media="screen">-->
		<!--<link type="text/css" rel="stylesheet" href="/css/jquery.tooltip.css" media="screen">-->
		<link type="text/css" rel="stylesheet" href="/css/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.css">
		<!-- 
			Css = '' (simply, zsainto, ckca...) 
			notamment sur les pages Journee.php et Classements.php 
			intégrer en iframe : 
		-->
		<?php if ($this->_tpl_vars['css_supp']): ?>
			<link type="text/css" rel="stylesheet" href="/css/<?php echo $this->_tpl_vars['css_supp']; ?>
.css">
		<?php endif; ?>
		<script language="JavaScript" type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
		<!--<script language="JavaScript" type="text/javascript" src="/js/bootstrap/js/bootstrap.min.js"></script>
		<script language="JavaScript" type="text/javascript" src="/js/dataTables.bootstrap.js"></script>-->
		<script language="JavaScript" type="text/javascript" src="/js/jquery-ui-1.10.4.custom.min.js"></script>
		<script language="JavaScript" type="text/javascript" src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
		<!--<script language="JavaScript" type="text/javascript" src="//cdn.datatables.net/plug-ins/9dcbecd42ad/integration/jqueryui/dataTables.jqueryui.js"></script>-->
		<!--<script language="JavaScript" type="text/javascript" src="/js/jquery-ui-1.10.4.custom.min.js"></script>-->
		<script language="JavaScript" type="text/javascript" src="/js/jquery.stickytableheaders.min.js"></script>
		<!--<script language="JavaScript" type="text/javascript" src="/js/jquery.tooltip.min.js"></script>-->
		<script language="JavaScript" type="text/javascript" src="/js/jquery.maskedinput.min.js"></script>
		<!--<script language="JavaScript" type="text/javascript" src="/js/jquery.fixedheadertable.min.js"></script>-->
		<script language="JavaScript" type="text/javascript" src="/js/AdmTools.js"></script>
		<script language="JavaScript" type="text/javascript" src="/js/<?php echo $this->_tpl_vars['contenutemplate']; ?>
.js"></script>
		<title><?php echo ((is_array($_tmp=@$this->_config[0]['vars'][$this->_tpl_vars['title']])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['title']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['title'])); ?>
</title>
	</head>
	<body onload="testframe(); alertMsg('<?php echo $this->_tpl_vars['AlertMessage']; ?>
')">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'headerAdm.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'main_menuAdm.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['contenutemplate']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footerAdm.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</body>
	<?php if (((is_array($_tmp=$this->_tpl_vars['contenutemplate'])) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)) == 'IMPORTPCE'): ?>	
		<?php echo '
			<script type="text/javascript">
				$(document).ready(function(){
					Init();
					
				});
			</script>
		'; ?>

	<?php endif; ?>
</html>