<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\v17\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait WidgetTrait
{
    use \mdg\homepush\core\Traits\WidgetTrait;

    /**
     * @inheritdoc
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return $this->fetch("module:{$this->name}/views/templates/hook/v17/{$hookName}.tpl");
    }
}
