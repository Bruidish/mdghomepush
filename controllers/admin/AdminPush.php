<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

use mdg\homepush\Forms\PushForm;
use mdg\homepush\Models\PushModel;

require_once __DIR__ . '../../../vendor/autoload.php';

class AdminPushController extends ModuleAdminController
{
    /** @var boolean */
    public $lang = true;

    /** @var boolean */
    public $bootstrap = true;

    /** @var boolean pour le renderrForm */
    public $multiple_fieldsets = true;

    public function __construct()
    {
        $this->table = PushModel::$definition['table'];
        $this->identifier = PushModel::$definition['primary'];
        $this->className = PushModel::class;

        parent::__construct();

        #region RenderList
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->_where .= " AND a.id_shop = {$this->context->shop->id}";
        $this->position_identifier = $this->identifier;
        $this->_orderBy = 'position';
        $this->_orderWay = 'ASC';

        $this->fields_list = [
            $this->identifier => [
                'title' => 'ID',
                'class' => 'fixed-width-xs',
            ],
            'position' => [
                'title' => 'Position',
                'filter_key' => 'a!position',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'position' => 'position',
            ],
            'title' => [
                'title' => 'Titre',
            ],
            'html_id' => [
                'title' => 'Id du slide',
            ],
            'html_class' => [
                'title' => 'Classe du slide',
            ],
            'active' => [
                'title' => 'Visible',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
            ],
        ];
        #endRegion RenderList
    }

    public function renderForm()
    {
        foreach ((new PushForm($this->object))->modifyControllerFormHelper() as $key => $value) {
            $this->$key = $value;
        }

        return parent::renderForm();
    }

    /** Traite l'enregistrement des images
     *
     * @param int $id Object id used for deleting images
     *
     * @return bool
     */
    protected function postImage($id)
    {
        foreach ($this->className::getImagesParams() as $imageName => $imageParams) {
            if (isset($_FILES[$imageName]) && !empty($_FILES[$imageName]['name'])) {
                if (!is_dir(_PS_IMG_DIR_ . "{$this->table}/")) {
                    mkdir(_PS_IMG_DIR_ . "{$this->table}/");
                }

                // force le type attendu
                $this->imageType = $imageParams['ext'];

                // Upload l'image
                $this->uploadImage("{$id}-{$imageName}", $imageName, "{$this->table}/", $imageParams['ext'], $imageParams['width'], $imageParams['height']);
            }
        }

        return !count($this->errors);
    }

    /** Supprime une image
     *
     * @var string $_GET::type
     *
     * @return object|false
     */
    public function processDeleteImage()
    {
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            if (($object->deleteImageByName(Tools::getValue('deleteImage')))) {
                $redirect = self::$currentIndex . "&update{$this->table}&{$this->identifier}=" . Tools::getValue($this->identifier) . '&conf=7&token=' . $this->token;
                if (!$this->ajax) {
                    $this->redirect_after = $redirect;
                } else {
                    $this->content = 'ok';
                }
            }
        }
        $this->errors[] = $this->trans('An error occurred while attempting to delete the image. (cannot load object).', array(), 'Admin.Notifications.Error');

        return $object;
    }

    /** Modification ajax d'une position
     *
     * @var int way: 0|1
     * @var int id
     * @var array positions
     *
     * @return string json
     */
    public function ajaxProcessUpdatePositions()
    {
        $output = [
            'hasError' => false,
        ];

        $way = (int) (Tools::getValue('way'));
        $idPrimary = (int) (Tools::getValue('id'));
        $positions = Tools::getValue(str_replace('id_', '', $this->identifier));

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int) $pos[2] === $idPrimary) {
                if ($objectModel = new $this->className((int) $pos[2])) {
                    if (!isset($position) || !$objectModel->updatePosition($way, $position)) {
                        $output['hasError'] = true;
                        $output['errors'] = "Can not update object {$idPrimary} to position {$position}.";
                    }
                } else {
                    $output['hasError'] = true;
                    $output['errors'] = "This object (' . (int) $idPrimary . ') can t be loaded";
                }

                break;
            }
        }

        header('content-type:application/json');
        die(json_encode($output));
    }
}
