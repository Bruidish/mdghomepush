<?php
/**
 * @author:  Michel Dumont <michel.dumont.io>
 * @version: 1.0.0 - 2018-03-30
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait HookTrait
{
    /**
     * @see mdg\homepush\v16\Traits\HookTrait
     * @see mdg\homepush\v17\Traits\HookTrait
     */

    /**
     * @inheritdoc
     */
    public function hookDisplayTopColumn($params)
    {
        return $this->renderWidget('displayTopColumn');
    }

    /**
     * @inheritdoc
     */
    public function hookDisplayHome($params)
    {
        return $this->renderWidget('displayHome');
    }
}
