<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.8 [2021-06-22] [Michel Dumont]
 * @copyright 2020
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Controllers;

class InstallerController
{
    /** @var object $config Objet json contenant les paramètres d'installation du module */
    protected $config;

    /** @var Module $module instance du module  */
    protected $module;

    public function __construct()
    {
        $moduleName = basename(dirname(realpath(__DIR__ . '/../../')));
        $this->config = json_decode(file_get_contents(realpath(__DIR__ . '/../../../config/') . (version_compare(_PS_VERSION_, '1.7', '<') ? '/v16/' : '/v17/') . 'config.json'));
        $this->module = \Module::getInstanceByName($moduleName);

        if (!$this->module) {
            throw new \PrestaShopException("Module $moduleName not found");
        }

        return $this;
    }

    #region outils public d'installation
    /**
     * Installe un ObjectModel
     * @param string|object
     * @return boolean
     */
    public static function installModel($model)
    {
        $output = true;
        if ((is_object($model) || class_exists($model)) && isset($model::$definition)) {
            /**
             * Gestion de la possibilité de forcer les ID
             * @see ObjectModel::force_id
             */
            $force_id = false;
            $classVars = get_class_vars($model);
            if (isset($classVars['force_id']) && $classVars['force_id'] === true) {
                $force_id = true;
            }

            $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $model::$definition['table'] . '` (';
            $sql .= '`' . $model::$definition['primary'] . '` int(10) unsigned NOT NULL ' . (!$force_id ? 'AUTO_INCREMENT' : '') . ', ';
            if (isset($model::$definition['multi_shop']) && $model::$definition['multi_shop'] === true) {
                $sql .= "`id_shop` int(10) unsigned NOT NULL, ";
            }

            $sql_lang = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $model::$definition['table'] . '_lang` (';
            $sql_lang .= '`' . $model::$definition['primary'] . '` int(10) unsigned NOT NULL, `id_lang` int(10) unsigned NOT NULL, ';
            foreach ($model::$definition['fields'] as $column => $params) {
                if ($column == 'id_shop') {
                    continue;
                }

                $sql_column = '';
                /**
                 * Gestion du type de colonne et du de la taille du champ
                 * La gestion de la taille ne marche que pour les champs
                 * de type INT, FLOAT (1er paramètre unquement) et STRING
                 */
                $size = isset($params['size']) && \Validate::isUnsignedInt($params['size']) ? (int) $params['size'] : null;
                $default = isset($params['default']) ? $params['default'] : null;

                $allow_null = true;
                if (!isset($params['allow_null']) || $params['allow_null'] === false || (isset($params['required']) && $params['required'] === true)) {
                    $allow_null = false;
                }

                // Gestion de la valeur par défaut
                $defaultSQL = '';
                if ($default !== null || $allow_null) {
                    $defaultSQL = 'DEFAULT ';
                    switch ($params['type']) {
                        case $model::TYPE_INT:
                            $defaultSQL .= (!is_null($default) && \Validate::isUnsignedInt($default) ? (int) $default : "NULL");
                            break;
                        case $model::TYPE_BOOL:
                            $defaultSQL .= ($default === true || $default === false ? (int) $default : "NULL");
                            break;
                        case $model::TYPE_FLOAT:
                            $defaultSQL .= (!is_null($default) && \Validate::isFloat($default) ? (float) $default : "NULL");
                            break;
                        default:
                            $defaultSQL .= (!is_null($default) ? "'$default'" : "NULL");
                            break;
                    }
                    $defaultSQL .= " ";
                }

                switch ($params['type']) {
                    case $model::TYPE_INT:
                        $sql_column = "`$column` int(" . ($size ? $size : 11) . ") unsigned " . $defaultSQL;
                        break;
                    case $model::TYPE_BOOL:
                        $sql_column = "`$column` tinyint(1) unsigned " . $defaultSQL;
                        break;
                    case $model::TYPE_FLOAT:
                        $sql_column = "`$column` decimal(" . ($size >= 7 ? $size : 20) . ",6) " . $defaultSQL;
                        break;
                    case $model::TYPE_SQL:
                    case $model::TYPE_HTML:
                        $sql_column = "`$column` text " . $defaultSQL;
                        break;
                    case $model::TYPE_STRING:
                        $sql_column = "`$column` varchar(" . ($size ? $size : 255) . ") " . $defaultSQL;
                        break;
                    case $model::TYPE_DATE:
                        $sql_column = "`$column` datetime " . $defaultSQL;
                        break;
                }

                // Gestion du paramètre standard allow_null
                if (!$allow_null) {
                    $sql_column .= "NOT NULL ";
                }

                // Gestion du paramètre custom unique
                if (isset($params['unique']) && $params['unique'] === true) {
                    $sql_column .= "UNIQUE ";
                }

                $sql_column .= ', ';

                if (isset($params['lang'])) {
                    $sql_lang .= $sql_column;
                } else {
                    $sql .= $sql_column;
                }
            }

            // Tables de l'objet
            $output &= \Db::getInstance()->Execute($sql . " PRIMARY KEY (`{$model::$definition['primary']}`) ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8 " . (!$force_id ? 'AUTO_INCREMENT=0' : '') . ";");
            if (isset($model::$definition['multilang']) && $model::$definition['multilang']) {
                $output &= \Db::getInstance()->Execute("$sql_lang PRIMARY KEY (`{$model::$definition['primary']}`, `id_lang`) ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;");
            }

            // Tables des associations
            if ($output && isset($model::$definition['associations'])) {
                foreach ($model::$definition['associations'] as $association) {
                    $associationSql = "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . $association['association'] . " (";
                    $associationSql .= "{$model::$definition['primary']} INT(11) UNSIGNED NOT NULL, ";
                    if (isset($association['field'])) {
                        $associationSql .= "{$association['field']} INT(11) UNSIGNED NOT NULL, ";
                        $associationSql .= "PRIMARY KEY (`{$model::$definition['primary']}`, `{$association['field']}`) ) ";
                    } else if (isset($association['fields'])) {
                        $primaryKeySql = "`{$model::$definition['primary']}`";
                        foreach ($association['fields'] as $field) {
                            $associationSql .= "{$field} INT(11) UNSIGNED NOT NULL, ";
                            $primaryKeySql .= ",`{$field}`";
                        }
                        $associationSql .= "PRIMARY KEY ({$primaryKeySql}) ) ";
                    }
                    $associationSql .= "ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";
                    $output &= \Db::getInstance()->Execute($associationSql);
                }
            }

            if (!$output) {
                \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $model::$definition['table'] . '`');
                \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $model::$definition['table'] . '_lang`');
                \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $model::$definition['table'] . '_shop`');
                return false;
            }
        } else {
            $output = false;
        }

        return (bool) $output;
    }

    /**
     * Créait un fihier d'index des objets module/src/core/*
     * pour que les logiciels de dev pocèdent les allias générés par l'autoload
     * @see ../../../vendor/autoload.php
     * @since 2020-11-20
     *
     * @return boolean
     */
    public static function generateClassesIndex()
    {
        $path = __DIR__;
        $moduleNameSpace = "mdg\\homepush";
        $classesIndexContent = '';

        $dirs = ['Controllers', 'Forms', 'Models'];
        foreach ($dirs as $dirName) {
            $files = glob("{$path}/../{$dirName}/*.php");
            if (count($files)) {
                $classesIndexContent .= "namespace {$moduleNameSpace}\\{$dirName};\n";
                foreach ($files as $file) {
                    $file = basename($file, '.php');
                    if ($file != 'index') {
                        $classesIndexContent .= "class {$file} extends \\{$moduleNameSpace}\\core\\{$dirName}\\{$file}{}\n";
                    }
                }
            }
        }

        if ($classesIndexContent != '') {
            $handler = fopen("{$path}/../../../vendor/classes_index.php", "w");
            fwrite($handler, "<?php\n{$classesIndexContent}");
            fclose($handler);
        }

        return true;
    }
    #endregion

    #region Installation du module
    /**
     * Lance l'installation du module
     * @return boolean
     */
    public function install()
    {
        if (!version_compare(_PS_VERSION_, '1.7', '<')) {
            $logger = new \FileLogger();
            $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/' . (_PS_MODE_DEV_ ? 'dev' : 'prod') . '.log');
        }

        $output = true;

        try {
            // Création de l'index des classes du module
            $output &= self::generateClassesIndex();

            // Installation des models
            if (isset($this->config->models) && count($this->config->models)) {
                $output &= $this->installModels();
            }

            // Installation des hooks
            if (isset($this->config->hooks) && count($this->config->hooks)) {
                $output &= $this->installHooks();
            }

            // Installation des tabs
            if (isset($this->config->tabs) && count($this->config->tabs)) {
                $output &= $this->installTabs();
            }

            // Installation des colonnes supplémentaires sql
            if (isset($this->config->fields) && count($this->config->fields)) {
                $output &= $this->installFields();
            }

            // Installation des valeurs de configuration
            if (isset($this->config->configurations) && count($this->config->configurations)) {
                $output &= $this->installConfigurations();
            }
        } catch (\PrestaShopException $e) {
            if (!version_compare(_PS_VERSION_, '1.7', '<')) {
                $logger->logError($e->getMessage());
            } else {
                var_dump($e->getMessage());
            }
        }

        return (bool) $output;
    }

    /**
     * Installe les hooks que va utiliser le module
     * @return boolean
     */
    private function installHooks()
    {
        foreach ($this->config->hooks as $hook) {
            if (!$this->module->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Installe les onglets que va utiliser le module
     * @return boolean
     */
    private function installTabs()
    {
        $languages = \Language::getLanguages();
        foreach ($this->config->tabs as $params) {
            $tab = \Tab::getInstanceFromClassName($params->class_name);
            $tab->name = [];
            $tab->class_name = $params->class_name;
            $tab->module = isset($params->module_name) ? $params->module_name : $this->module->name;
            $tab->icon = isset($params->icon) ? $params->icon : null;

            if (isset($params->parent_class_name)) {
                $tab->id_parent = (int) \Tab::getIdFromClassName($params->parent_class_name);
            }

            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = $this->module->l($params->tab_name);
            }

            if (!$tab->save()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Installe les ObjectModels du module
     * @return boolean
     */
    private function installModels()
    {
        foreach ($this->config->models as $model) {
            if (!self::installModel($model->className)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Ajoute des colonnes aux tables de la base de données
     * @return boolean
     */
    private function installFields()
    {
        $db = \Db::getInstance();
        foreach ($this->config->fields as $field) {
            if (isset($field->table) && isset($field->column) && isset($field->params)) {
                $list = $db->executeS("SHOW FIELDS FROM `" . _DB_PREFIX_ . "{$field->table}`");
                if (is_array($list)) {
                    foreach ($list as $k => $row) {
                        $list[$k] = $row['Field'];
                    }

                    if (!in_array($field->column, $list)) {
                        try {
                            $db->execute("ALTER TABLE `" . _DB_PREFIX_ . "{$field->table}` ADD COLUMN `{$field->column}` {$field->params}");
                        } catch (\PrestaShopException $e) {
                            $e->displayMessage();
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Initialise les valeurs dans la table configuration de PrestaShop
     * @return bool
     */
    protected function installConfigurations()
    {
        foreach ($this->config->configurations as $configuration) {
            $value = (isset($configuration->value) ? $configuration->value : '');
            if ($configuration->lang) {
                $languages = \Language::getLanguages();
                $lang_values = [];
                foreach ($languages as $lang) {
                    $lang_values[$lang['id_lang']] = $value;
                }
                $value = $lang_values;
            }
            $updated = \Configuration::updateValue($configuration->name, $value);
            if (!$updated) {
                return false;
            }
        }
        return true;
    }
    #endregion

    #region désinstallation du module

    /**
     * Déinstalle le module
     * @return boolean
     */
    public function uninstall()
    {
        $output = true;

        // Désinstallation des models
        if (isset($this->config->models) && count($this->config->models)) {
            $output &= $this->uninstallModels();
        }

        // Désinstallation des tabs
        if (isset($this->config->tabs) && count($this->config->tabs)) {
            $output &= $this->uninstallTabs();
        }

        // Désinstallation des valeurs de configuration
        if (isset($this->config->configurations) && count($this->config->configurations)) {
            $output &= $this->uninstallConfigurations();
        }

        return (bool) $output;
    }

    /**
     * Désinstalle les models du module
     * @return boolean
     */
    private function uninstallModels()
    {
        $output = true;
        foreach ($this->config->models as $model) {
            $Object = new $model->className;
            if (isset($Object::$definition) && $model->removeOnUninstall === true) {
                $output &= \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $Object::$definition['table'] . '`');
                $output &= \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $Object::$definition['table'] . '_lang`');
                $output &= \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $Object::$definition['table'] . '_shop`');
                // Tables des associations
                if ($output && isset($Object::$definition['associations'])) {
                    foreach ($Object::$definition['associations'] as $association) {
                        $output &= \Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $association['association'] . '`');
                    }
                }
            }
        }
        return (bool) $output;
    }

    /**
     * Désinstalle les tabs du module
     * @return boolean
     */
    private function uninstallTabs()
    {
        $output = true;
        foreach ($this->config->tabs as $params) {
            if ($idTab = (int) \Tab::getIdFromClassName($params->class_name)) {
                /**
                 * Plusieurs modules WebXY peuvent utiliser le même onglet
                 * On ne le retire que si aucun autre module ne l'utilise
                 */
                if (!$params->removeOnUninstall || !$this->tabIsUsed($idTab)) {
                    continue;
                }

                $tab = new \Tab($idTab);
                $output &= $tab->delete();
            }
        }
        return (bool) $output;
    }

    /**
     * Indique si l'onglet est utilisé par d'autre modules que celui qu'on est en train de désinstaller
     * @param int id de l'onglet à vérifier
     * @return bool
     */
    protected function tabIsUsed(int $idTab)
    {
        $isUsed = (int) \Db::getInstance()->getValue("
            SELECT count(*)
            FROM `" . _DB_PREFIX_ . "tab`
            WHERE
                `id_parent` = {$idTab}
                AND `module` IS NOT NULL
                AND `module` <> '{$this->module->name}'
        ");

        return ($isUsed == 0) ? true : false;
    }

    /**
     * Suppression des valeurs de configuration PrestaShop initialisées par le module
     * @return bool
     */
    protected function uninstallConfigurations()
    {
        foreach ($this->config->configurations as $configuration) {
            if ($configuration->removeOnUninstall) {
                $deleted = \Configuration::deleteByName($configuration->name);
                if (!$deleted) {
                    return false;
                }
            }
        }
        return true;
    }
    #endregion désinstallation du module
}
