<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 2.3.3 - 2020-09-05
 * @copyright 2018 - 1019
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.7
 */

namespace mdg\homepush\v17\Models;

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class ObjectModel extends \mdg\homepush\core\Models\ObjectModel
{
    public function add($null_values = false, $auto_date = true)
    {
        // gestion des positions
        if (isset(static::$definition['fields']['position'])) {
            $this->position = static::getNextPosition();
        }

        $output = parent::add($null_values, $auto_date);

        // force l'id shop
        if (isset($this->def['multi_shop']) && $this->def['multi_shop']) {
            $this->id_shop = (int) \Context::getContext()->shop->id;
            $output &= \Db::getInstance()->update($this->def['table'], ['id_shop' => $this->id_shop], "{$this->def['primary']}={$this->id}");
        }

        return $output;
    }

    /**
     * clean les positions de la collection
     *
     * @inheritdoc
     */
    public function delete()
    {
        $output = parent::delete();

        // Rafraichi les positions des aurtes objets
        if (isset(static::$definition['fields']['position']) && $output) {
            $output &= static::cleanPositions(isset(static::$definition['fields'][static::$position_reference]) ? $this->{static::$position_reference} : null);
        }

        return $output;
    }
}
