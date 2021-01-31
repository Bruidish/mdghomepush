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

trait HookTrait
{
    use \mdg\homepush\core\Traits\HookTrait;

    /**
     * @inheritdoc
     */
    public function hookDisplayWrapperTop($params)
    {
        if ($this->context->controller->php_self != 'index') {
            return;
        }

        return $this->renderWidget('displayWrapperTop');
    }

    /**
     * @inheritdoc
     */
    public function hookDisplayContentWrapperTop($params)
    {
        if ($this->context->controller->php_self != 'index') {
            return;
        }

        return $this->renderWidget('displayContentWrapperTop');
    }

    /** Charge les médias en front
     * @since PS 1.7
     *
     * @inheritdoc
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if ($this->context->controller->php_self != 'index') {
            return;
        }

        \Media::addJsDef([
            'mdgHomeSlide_break' => (int) \Configuration::get('MDG_HOMESLIDE_BREAK'),
            'mdgHomeSlide_transition' => (int) \Configuration::get('MDG_HOMESLIDE_TANSITION'),
            'mdgHomeSlide_axe' => (int) \Configuration::get('MDG_HOMESLIDE_AXE'),
            'mdgHomeSlide_height' => \Configuration::get('MDG_HOMESLIDE_HEIGHT'),
        ]);
        $this->context->controller->registerStylesheet("module-{$this->name}-splidecore-css", "modules/{$this->name}/views/libs/splide/css/splide-core.min.css");
        $this->context->controller->registerStylesheet("module-{$this->name}-splidetheme-css", "modules/{$this->name}/views/libs/splide/css/themes/splide-default.min.css");
        $this->context->controller->registerStylesheet("module-{$this->name}-splide-css", "modules/{$this->name}/views/libs/splide/css/splide.min.css");
        $this->context->controller->registerJavascript("module-{$this->name}-splide-js", "modules/{$this->name}/views/libs/splide/js/splide.min.js", ['priority' => 999, 'attribute' => 'async']);
        $this->context->controller->registerStylesheet("module-{$this->name}-front-css", "modules/{$this->name}/views/css/front.css");
        $this->context->controller->registerJavascript("module-{$this->name}-front-js", "modules/{$this->name}/views/js/front.min.js", ['priority' => 999, 'attribute' => 'async']);
    }
}
