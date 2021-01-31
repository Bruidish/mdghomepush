<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 2.3.3 - 2020-09-05
 * @copyright 2018 - 1019
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6|1.7
 */

namespace mdg\homepush\core\Models;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class ObjectModel extends \ObjectModel
{
    /**
     * @param array BO: stock les erreur lors du process
     */
    public $errors = [];

    /**
     * @param array BO: stock les success lors du process
     */
    public $success = [];

    /**
     * @param string BO: nom du fichier (utilise notament pour les traducitons en 1.6)
     */
    public $basename;

    /**
     * @param string BO: nom du processus principal du formulaire de l'objet
     */
    public $form_action;

    /**
     * @param string colonne de référence pour le traitemnt des positions
     * @see self::updatePosition() et autres fonctions relatives aux position
     */
    public static $position_reference = 'id_parent';

    /**
     * Builds the object
     *
     * @param int|null $id If specified, loads and existing object from DB (optional).
     * @param int|null $id_lang Required if object is multilingual (optional).
     * @param int|null $id_shop ID shop for objects with multishop tables.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    /**
     * Redirect to BO > Current Module > Configuration
     */
    public function redirectAdmin()
    {
        $module_name = \Tools::getValue('configure');
        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink('AdminModules', false) . '&token=' . \Tools::getAdminTokenLite('AdminModules') . '&configure=' . $module_name . '&module_name=' . $module_name . '&form_tab=' . \Tools::getValue('form_tab', 1));
    }

    /**
     * @return Integer l'id de l'objet courant en fonction de l'id du shop courant
     * Si l'objet na pas encore été créé, il est créé
     */
    public static function getIdByShop($id_shop)
    {
        $that = get_called_class();

        if (!$id_shop) {
            $id_shop = (int) \Context::getContext()->shop->id;
        }

        if (!isset($that::$definition) || !isset($that::$definition['primary'])) {
            return 0;
        }

        $id = \Db::getInstance()->getValue('SELECT `' . $that::$definition['primary'] . '` FROM `' . _DB_PREFIX_ . $that::$definition['table'] . '` WHERE `id_shop`=' . (int) $id_shop);
        if (!$id) {
            return null;
        }

        return $id;
    }

    /* Conserve uniquement les alphanumeric et - */
    public static function str_normalize($string, $replace = '-')
    {
        $string = strtr(utf8_decode($string), utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'), 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr');
        $string = str_replace(['...', '!', '?', ';', ':'], '', $string);
        return rtrim(str_replace(array('&', '"', '\'', '(', '`', ')', '=', '~', '#', '{', '[', '|', ']', '}', '$', '¤', '%', '*', 'µ', '§', '<', '>', '²', '+', '^', ',', ' ', '-'), $replace, stripslashes($string)), $replace);
    }

    /** Créer un dossier s'il n'esiste pas
     *
     * @return bool
     */
    public static function dir_exists($path_dir, $mode = 0777)
    {
        if (is_dir($path_dir)) {
            return true;
        }

        $output = mkdir($path_dir, $mode);
        $output &= file_put_contents("{$path_dir}/index.php", \Tools::getDefaultIndexContent());

        return $output;
    }
    /* Récupère extention d'un fichier sans le . */
    public static function get_extention_file($file_name)
    {
        $ext = strtolower(substr(strrchr(basename($file_name), "."), 1));
        return str_replace(array('jpeg', 'pjpeg'), 'jpg', $ext);
    }
    /* Retourne un nom alphanuméric+extention pour le fichier fourni */
    public static function normalize_file_name($file_name)
    {
        $ext = self::get_extention_file($file_name);
        return self::str_normalize(str_replace($ext, '', $file_name)) . $ext;
    }
    /* Supprime un dossier et son contenu */
    public static function delete_dir($dir)
    {
        if (!preg_match("/^.*\/$/", $dir)) {
            $dir .= '/';
        }

        if ($supr_dir = @opendir($dir)) {
            while (false !== ($item = readdir($supr_dir))) {
                if ($item != "." && $item != "..") {
                    if (is_dir($dir . $item)) {
                        self::delete_dir($dir . $item);
                    } else {
                        unlink($dir . $item);
                    }

                }
            }
            closedir($supr_dir);
            $res = rmdir($dir);
            return true;
        } else {
            return false;
        }

    }
    public function addImg($file, $dir_name, $prefix = '')
    {
        if (!\ImageManager::checkImageMemoryLimit($file['tmp_name'])) {
            return $this->errors[] = 'This file is too heavy';
        }

        $file_name = strtolower($prefix . self::str_normalize($file['name']));
        $path_dir = __DIR__ . '/../../../' . $this::IMG_DIR . $dir_name . '/';
        self::dir_exists($path_dir);

        if (!move_uploaded_file($file['tmp_name'], $path_dir . $file_name)) {
            return $this->errors[] = 'An error occured during uploading';
        }

        chmod($path_dir . $file_name, 0777);
        return $file_name;
    }
    public static function resizeImg($img, $dst_width, $dst_height, $size_name)
    {
        $ext = '.' . substr(strtolower(strrchr(basename($img), ".")), 1);
        $src_file = (__DIR__ . '/../../../' . $img);
        $dst_file = (__DIR__ . '/../../../' . str_replace($ext, '-' . $size_name . $ext, $img));

        if ($dst_height === null) {
            list($width, $height) = getimagesize($src_file);
            $ratio = $width / $height;
            $dst_height = $dst_width / $ratio;
        }
        return \ImageManager::resize($src_file, $dst_file, $dst_width, $dst_height);
    }

    #region Base de données
    /* copyFromPost
     * Définie les variables de l'objet à partir du contenu du formulaire
     */
    public function copyFromPost()
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if (key_exists($key, $this) and $key != 'id_' . $this->table) {
                $this->{$key} = $value;
            }
        }

        /* Multilingual fields */
        $languages = \Language::getLanguages(false);
        foreach ($languages as $language) {
            foreach ($this::$definition['fields'] as $field => $params) {
                if (isset($params['lang']) && $params['lang']) {
                    if (isset($_POST[$field . '_' . (int) ($language['id_lang'])])) {
                        $this->{$field}[(int) ($language['id_lang'])] = $_POST[$field . '_' . (int) ($language['id_lang'])];
                    }
                }
            }
        }
    }
    /* getThisValues
     * Retourne les valeur de l'objet sous forme de tableau
     * Utilisé en premier lieu pour helper.form
     */
    public function getThisValues($id)
    {
        $languages = \Language::getLanguages(false);
        $Obj = new $this($id);

        $fields_values = array();
        $fields_values['id'] = (int) $id;

        foreach ($this::$definition['fields'] as $k => $f) {
            if (isset($f['lang']) && $f['lang']) {
                foreach ($languages as $lang) {
                    $fields_values[$k][$lang['id_lang']] = \Tools::getValue($k . '_' . (int) $lang['id_lang'], (!empty($Obj->{$k}) && isset($Obj->{$k}[$lang['id_lang']]) ? $Obj->{$k}[$lang['id_lang']] : null));
                }

            } else {
                $value = \Tools::getValue($k, $Obj->$k);
                $fields_values[$k] = ($value === '0000-00-00 00:00:00' ? null : $value);
            }
        }

        return $fields_values;
    }
    /* getListArray
     * Retourne la liste des objet complets sous forme de tableau
     * Utilisé en premier lieu pour helper.list
     */
    public function getListArray($where = null, $order = null, $way = 'ASC')
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $id_shop = (int) \Context::getContext()->shop->id;
        $result = [];

        if (isset($this::$definition['fields']['id_shop'])) {
            $where = ($where ? $where . ' AND ' : '') . 'id_shop=' . $id_shop;
        }

        $order = $order ? $order : (isset($this::$definition['fields']['position']) ? 'position' : $this::$definition['primary']);
        $sql = 'SELECT ' . $this::$definition['primary'] . ' as id FROM `' . _DB_PREFIX_ . $this::$definition['table'] . '` ' . ($where ? 'WHERE ' . $where : '') . ' ORDER BY ' . $order . ' ' . $way;
        if (!$res = \Db::getInstance()->executeS($sql)) {
            return $result;
        }

        foreach ($res as $row) {
            $result[] = (array) new $this($row['id'], $id_lang);
        }

        return $result;
    }
    #endregion

    #region POSITIONS
    /** Ajoute la position à l'objet
     *
     * @param int
     *
     * @return bool
     */
    public function addPosition($position)
    {
        return (bool) \Db::getInstance()->update(static::$definition['table'], ['position' => $position], static::$definition['primary'] . "={$this->id}");
    }

    /** Déplace un objet
     *
     * @param bool $way Up (1) or Down (0)
     * @param int $position
     *
     * @return bool Update result
     */
    public function updatePosition($way, $position)
    {
        $parentRef = static::$position_reference;

        if (!$res = \Db::getInstance()->executeS(
            'SELECT `' . static::$definition['primary'] . '` id, `position`
            FROM `' . _DB_PREFIX_ . static::$definition['table'] . '`
            ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $object) {
            if ((int) $object['id'] == (int) $this->id) {
                $moved_object = $object;
            }
        }

        if (!isset($moved_object) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (\Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . static::$definition['table'] . '`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE
            `position`
            ' . ($way
            ? '> ' . (int) $moved_object['position'] . ' AND `position` <= ' . (int) $position
            : '< ' . (int) $moved_object['position'] . ' AND `position` >= ' . (int) $position)
            . (isset(static::$definition['fields'][$parentRef]) ? " AND {$parentRef} = {$this->$parentRef}" : "")
        ) && \Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . static::$definition['table'] . '`
            SET `position` = ' . (int) $position . '
            WHERE
                `' . static::$definition['primary'] . '` = ' . (int) $moved_object['id'])
        );
    }

    /** Retourne la prochaine position disponible pour l'objet
     *
     * @return int
     */
    public static function getNextPosition()
    {
        $parentRef = static::$position_reference;

        $positionSql = new \DbQueryCore();
        $positionSql->select("COUNT(*)");
        $positionSql->from(static::$definition['table'], "a");
        if (isset(static::$definition['fields']['id_shop'])) {
            $idShop = (int) \Context::getContext()->shop->id;
            $positionSql->where("a.id_shop={$idShop}");
        }
        if (isset(static::$definition['fields'][$parentRef])) {
            $idParent = (int) \Tools::getValue($parentRef, 0);
            $positionSql->where("a.{$parentRef}={$idParent}");
        }

        return \Db::getInstance()->getValue($positionSql);
    }

    /** Clean les positions de la table en tenant compte de l'id shop si existante
     *
     * @param int id parent (optionnel)
     *
     * @return bool
     */
    public static function cleanPositions($idParent = null)
    {
        $output = true;

        $parentRef = static::$position_reference;
        $primary = static::$definition['primary'];

        $listSql = new \DbQuery();
        $listSql->select("a.`{$primary}` id");
        $listSql->from(static::$definition['table'], 'a');
        $listSql->orderBy("a.position, a.`{$primary}`");
        if (isset(static::$definition['fields']['id_shop'])) {
            $idShop = (int) \Context::getContext()->shop->id;
            $listSql->where("a.id_shop={$idShop}");
        }
        if (isset(static::$definition['fields'][$parentRef]) && $idParent) {
            $listSql->where("a.{$parentRef}={$idParent}");
        }
        $listResult = \Db::getInstance()->executeS($listSql);
        for ($i = 0; $i < count($listResult); $i++) {
            $output &= \Db::getInstance()->update(static::$definition['table'], ['position' => $i], "{$primary}={$listResult[$i]['id']}");
        }

        return (bool) $output;
    }
    #endregion POSITIONS
}
