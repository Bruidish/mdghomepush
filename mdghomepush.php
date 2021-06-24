<?php
/**
 * @author Michel Dumont <https://michel.dumont.io>
 * @version 1.0.1 - 2021-06-24
 * @copyright 2019
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class mdghomepush extends \Module
{
    use mdg\homepush\Traits\ConfigurationTrait;
    use mdg\homepush\Traits\HookTrait;
    use mdg\homepush\Traits\WidgetTrait;

    public function __construct()
    {
        $this->name = 'mdghomepush';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Michel Dumont';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => _PS_VERSION_];
        $this->ps_versions_dir = version_compare(_PS_VERSION_, '1.7', '<') ? 'v16' : 'v17';

        foreach (glob(_PS_MODULE_DIR_ . "{$this->name}/controllers/front/*.php") as $file) {
            $fileName = basename($file, '.php');
            if ($fileName !== 'index') {
                $this->controllers[] = $fileName;
            }
        }

        parent::__construct();

        $this->displayName = $this->l('(mdg) Home pushes');
        $this->description = $this->l('Displays sliders and pushes to add editorial content to your homepage');

        /** Traduction des onglets */
        // $this->_Module->l('Home : Slides');
        // $this->_Module->l('Home : Pushes');
    }

    #region INSTALL
    /**
     * @inheritdoc
     */
    public function install()
    {
        if (parent::install()) {
            return (new \mdg\homepush\Controllers\InstallerController)->install();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            return (new \mdg\homepush\Controllers\InstallerController)->uninstall();
        }

        return false;
    }
    #endregion

}
