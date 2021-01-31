<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Traits;

use mdg\homepush\Models\PushModel;
use mdg\homepush\Models\SlideModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait WidgetTrait
{
    /**
     * @inheritdoc
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        switch ($hookName) {
            case 'displayContentWrapperTop':
            case 'displayWrapperTop':
            case 'displayTopColumn':
                $elements = $this->_getSlides();
                break;
            case 'displayHome':
                $elements = $this->_getPushes();
                break;
        }

        return [
            'mdgHomePush_height' => (int) \Configuration::get('MDG_HOMEPUSH_HEIGHT'),
            'displayFullscreen' => \Configuration::get('MDG_HOMESLIDE_FULLSCREEN'),
            'elements' => $elements,
            'hookName' => $hookName,
        ];
    }

    /** Returns list of actives slides
     *
     * @return array|false
     */
    private function _getSlides()
    {
        $output = [];

        // Récupère les éléments disponibles
        $collectionQuery = (new \PrestaShopCollection(SlideModel::class, $this->context->language->id))
            ->where("active", "=", "1")
            ->orderBy('position');

        // Ajoute les urls des images
        $output = $collectionQuery->getResults();
        if ($output) {
            foreach ($output as &$element) {
                foreach (SlideModel::IMAGES_NAMES as $imageName => $imageParams) {
                    $element->{$imageName} = SlideModel::getImageURL($element->id, $imageName, $imageParams['ext']);
                }
            }
        }

        return $output;
    }

    /** Returns list of actives pushes
     *
     * @return array|false
     */
    private function _getPushes()
    {
        $output = [];

        // Récupère les éléments disponibles
        $collectionQuery = (new \PrestaShopCollection(PushModel::class, $this->context->language->id))
            ->where("active", "=", "1")
            ->orderBy('position');

        // Ajoute les urls des images
        $output = $collectionQuery->getResults();
        if ($output) {
            foreach ($output as &$element) {
                foreach (PushModel::IMAGES_NAMES as $imageName => $imageParams) {
                    $element->{$imageName} = PushModel::getImageURL($element->id, $imageName, $imageParams['ext']);
                }
            }
        }

        return $output;
    }

}
