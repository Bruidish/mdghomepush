<?php
/**
 * @author:  Michel Dumont <michel.dumont.io>
 * @version: 1.0.0 - 2018-03-30
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Traits;

use mdg\homepush\Forms\ConfigurationForm;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait ConfigurationTrait
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return (new ConfigurationForm())->displayControllerFormHelper();
    }
}
