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
     * Prétraite le fichier MyLang.ini pour remplacer les tirets par des underscores
     * dans les clés de configuration (compatibilité Smarty 4)
     */
    private function preprocessConfigFile()
    {
        $sourceFile = PATH_ABS . 'commun/MyLang.ini';
        $targetFile = PATH_ABS . 'commun/MyLang_processed.ini';

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

        // Remplacer TOUS les tirets par des underscores dans les clés de configuration
        // Format: key-name = "value" ou key-name-part = "value"
        // Cette regex capture n'importe quel identifiant (lettres, chiffres, tirets) avant le signe =
        $processedContent = preg_replace_callback(
            '/^([a-zA-Z0-9_-]+)\s*=\s*(.*)$/m',
            function($matches) {
                $key = str_replace('-', '_', $matches[1]);
                return $key . ' = ' . $matches[2];
            },
            $content
        );

        // Écrire le fichier traité
        file_put_contents($targetFile, $processedContent);
    }

}
