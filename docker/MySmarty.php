<?php					   

// charge la librairie Smarty
include_once('MyConfig.php');
	  
if (PRODUCTION)	 
	require('/var/www/html/Smarty-Lib/Smarty.class.php');
else
	require('/var/www/html/kpi/Smarty-Lib/Smarty.class.php');
			   
$smarty = new Smarty();

class MySmarty extends Smarty 
{
	function MySmarty()
	{
		// Constructeur de la classe.
		// Appelé automatiquement à l'instanciation de la classe.
		
		$this->Smarty();
							   
		if (PRODUCTION)
		{
			$this->template_dir = '/var/www/html/smarty/templates';
			$this->compile_dir = '/var/www/html/smarty/templates_c';
			$this->cache_dir = '/var/www/html/smarty/cache';
			$this->config_dir = '/var/www/html/smarty/configs';
		}
		else
		{
			$this->template_dir = '/var/www/html/kpi/smarty/templates';
			$this->compile_dir = '/var/www/html/kpi/smarty/templates_c';
			$this->cache_dir = '/var/www/html/kpi/smarty/cache';
			$this->config_dir = '/var/www/html/kpi/smarty/configs';
		}
				
		$this->caching = false;
		$this->assign('app_name', 'KAYAK POLO');
	}

}
?>