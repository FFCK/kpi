<?php

class MySmarty extends Smarty
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplateDir(PATH_ABS . 'smarty/templates');
        $this->setCompileDir(PATH_ABS . 'smarty/templates_c');
        $this->setCacheDir(PATH_ABS . 'smarty/cache');
        $this->setConfigDir(PATH_ABS . 'smarty/configs');
        $this->addPluginsDir(PATH_ABS . 'smarty/plugins');

        $this->setCaching(false);		// $this->debugging = true;

        $this->registerPlugin('modifier', 'is_file', 'is_file');

        // Prétraiter MyLang.conf pour Smarty 4 (remplacer les tirets dans les clés)
        $this->preprocessConfigFile();

        $this->assign('app_name', 'KAYAK POLO');
    }

    /**
     * Prétraite le fichier MyLang.conf pour remplacer les tirets par des underscores
     * dans les clés de configuration (compatibilité Smarty 4)
     */
    private function preprocessConfigFile()
    {
        $sourceFile = PATH_ABS . 'commun/MyLang.conf';
        $targetFile = PATH_ABS . 'commun/MyLang_processed.conf';

        // Vérifier si le fichier source existe
        if (!file_exists($sourceFile)) {
            return;
        }

        // Vérifier si le fichier traité est à jour
        if (file_exists($targetFile) && filemtime($targetFile) >= filemtime($sourceFile)) {
            return; // Déjà à jour
        }

        // Lire et traiter le fichier
        $content = file_get_contents($sourceFile);

        // Remplacer les tirets par des underscores dans les clés (format: KEY-PART = "value")
        $processedContent = preg_replace('/^([A-Z0-9]+)-([A-Z0-9]+)\s*=/m', '$1_$2 =', $content);

        // Écrire le fichier traité
        file_put_contents($targetFile, $processedContent);
    }

}
