<?php					   

// charge la librairie Smarty
include_once('MyConfig.php');
	  
if (PRODUCTION)	 
	require('/home/users2/p/poloweb/www/agil/Smarty-Lib/Smarty.class.php');
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
			$this->template_dir = '/home/users2/p/poloweb/www/agil/smarty/templates';
			$this->compile_dir = '/home/users2/p/poloweb/www/agil/smarty/templates_c';
			$this->cache_dir = '/home/users2/p/poloweb/www/agil/smarty/cache';
			$this->config_dir = '/home/users2/p/poloweb/www/agil/smarty/configs';
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