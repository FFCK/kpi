<?php					   

// charge la librairie Smarty
require_once('MyConfig.php');
require_once(PATH_ABS . 'Smarty-Lib/SmartyBC.class.php');
			   
$smarty = new Smarty();

class MySmarty extends Smarty 
{
	function __construct()
	{
		// Constructeur de la classe.
		// Appelé automatiquement à l'instanciation de la classe.
		parent::__construct();

		$this->Smarty();
							   
        // $this->template_dir = PATH_ABS . 'smarty/templates';
        // $this->compile_dir =  PATH_ABS . 'smarty/templates_c';
        // $this->cache_dir =  PATH_ABS . 'smarty/cache';
        // $this->config_dir =  PATH_ABS . 'smarty/configs';

		$this->setTemplateDir(PATH_ABS . 'smarty/templates');
		$this->setCompileDir(PATH_ABS . 'smarty/templates_c');
		$this->setConfigDir(PATH_ABS . 'smarty/configs');
		$this->setCacheDir(PATH_ABS . 'smarty/cache');

		$this->caching = false;
		// $this->debugging = true;
		$this->assign('app_name', 'KAYAK POLO');
	}

}
