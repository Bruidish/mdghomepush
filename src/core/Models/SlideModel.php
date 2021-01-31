<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Models;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class SlideModel extends \mdg\homepush\Models\ObjectModel
{
    /**
     * @var array paramètres des différentes images du block
     * @see static::getImagesParams()
     * largeur et hauteur sont définis dans la configuration du module
     * la valeur donnée ici sert de valeur par défaut
     */
    const IMAGES_NAMES = [
        'image' => ['width' => '', 'height' => '', 'ext' => 'jpg'],
    ];

    /** @var int id de l'obket Prestashop associé */
    public $id_object;

    /** @var bool */
    public $active;

    /** @var bool */
    public $display_title;

    /** @var bool */
    public $display_text;

    /** @var int */
    public $position;

    /** @var bool */
    public $display_home;

    /** @var bool */
    public $display_footer;

    /** @var bool */
    public $display_leftcolumn;

    /** @var bool */
    public $display_rightcolumn;

    /** @var bool */
    public $display_product;

    /** @var bool */
    public $display_cart;

    /** @var int */
    public $link_target;

    /** @var string */
    public $html_id;

    /** @var string */
    public $html_class;

    /** @var string */
    public $color_text;

    /** @var string */
    public $color_bg;

    /** @var string [lang] */
    public $title;

    /** @var string [lang] */
    public $text;

    /** @var string [lang] */
    public $link;

    public static $definition = [
        'table' => 'mdghomepush_slide',
        'primary' => 'id_block',
        'multilang' => true,
        'multi_shop' => true,
        'fields' => [
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'display_title' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'display_text' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'link_target' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],

            'html_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'allow_null' => true],
            'html_class' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'allow_null' => true],
            'color_text' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'allow_null' => true],
            'color_bg' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'allow_null' => true],

            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'lang' => true],
            'text' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true],
            'link' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'lang' => true],
        ],
    ];

    #region IMAGES

    /** Return Images params
     *
     * @return array
     */
    public static function getImagesParams()
    {
        $output = static::IMAGES_NAMES;
        foreach ($output as &$params) {
            $params['width'] = \Configuration::get('MDG_HOMESLIDE_IMGWIDTH', null, null, null, $params['width']);
            $params['height'] = \Configuration::get('MDG_HOMESLIDE_IMGHEIGHT', null, null, null, $params['height']);
        }

        return $output;
    }

    /** Retourne l'url de l'image
     *
     * @param int id de l'objet
     * @param string type de l'image
     * @param string extension de l'image
     *
     * @return string|false
     */
    public static function getImageUrl($id, $name, $ext = null)
    {
        $ext = $ext ? $ext : static::IMAGES_NAMES[$name]['ext'];
        $dirName = static::$definition['table'];
        if (file_exists(_PS_IMG_DIR_ . "{$dirName}/{$id}-{$name}.{$ext}")) {
            return _PS_IMG_ . "{$dirName}/{$id}-{$name}.{$ext}";
        }

        return false;
    }

    /** Supprime l'image de l'objet
     *
     * @param int
     *
     * @return bool
     */
    public function deleteImageByName($name)
    {
        $output = true;
        $ext = static::IMAGES_NAMES[$name]['ext'];
        $dirName = static::$definition['table'];
        $img = _PS_IMG_DIR_ . "{$dirName}/{$this->id}-{$name}.{$ext}";
        if (file_exists($img)) {
            $output &= (bool) unlink($img);
        }
        return $output;
    }

    #endregion IMAGES

}
