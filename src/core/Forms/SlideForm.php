<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Forms;

class SlideForm extends \mdg\homepush\Forms\ObjectForm
{
    /**
     * @inheritdoc
     */
    public function __construct($object = null, $legacyContext = null)
    {
        parent::__construct($object, $legacyContext);
        parent::constructFormHelper(basename(__FILE__, '.php'), $object);
    }

    /**
     * @inheritdoc
     */
    public function modifyControllerFormHelper()
    {
        /** Traduction des select */
        // $this->_Module->l('open in same window', $this->form_name);
        // $this->_Module->l('open in new window', $this->form_name);

        // Paramètres pour les images
        $nocache = '?' . time();
        $imagesNames = $this->object::getImagesParams();
        foreach ($imagesNames as $imageName => $imgParams) {
            $fileUrl = $this->object::getImageUrl($this->object->id, $imageName, $imgParams['ext']);
            ${"{$imageName}_html"} = $fileUrl ? "<img src=\"{$fileUrl}{$nocache}\" height=\"120\" />" : false;
            ${"{$imageName}_delete"} = $this->context->link->getAdminLink($this->controller_name, true, [], [$this->identifier => $this->object->id, "update{$this->table}" => 1, "deleteImage" => $imageName]);
        }

        // Champs du formulaire
        $this->fields_form = [];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Main content', $this->form_name),
                    'icon' => 'icon-align-left',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Title', $this->form_name),
                        'name' => 'title',
                        'lang' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->module->l('Text', $this->form_name),
                        'name' => 'text',
                        'class' => 'autoload_rte',
                        'lang' => true,
                    ],
                ],
            ],
        ];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Images setting', $this->form_name),
                    'icon' => 'icon-picture-o',
                ],
                'input' => [
                    [
                        'type' => 'file',
                        'label' => $this->module->l('Main image', $this->form_name),
                        'name' => 'image',
                        'display_image' => true,
                        'image' => $image_html ? $image_html : false,
                        'delete_url' => $image_delete,
                        'desc' => sprintf($this->module->l('Use size %s x %spx.', $this->form_name), $imagesNames['image']['width'] ? $imagesNames['image']['width'] : 'auto', $imagesNames['image']['height'] ? $imagesNames['image']['height'] : 'auto'),
                    ],
                ],
            ],
        ];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Link setting', $this->form_name),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Link', $this->form_name),
                        'name' => 'link',
                        'lang' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Action', $this->form_name),
                        'name' => 'link_target',
                        'options' => $this->getHelperFormChoices([
                            0 => 'open in same window',
                            1 => 'open in new window',
                        ]),
                    ],
                ],
            ],
        ];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Display setting', $this->form_name),
                    'icon' => 'icon-eye-open',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display', $this->form_name),
                        'name' => 'active',
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->module->l('Enabled', $this->form_name)],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->module->l('Disabled', $this->form_name)],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display title', $this->form_name),
                        'name' => 'display_title',
                        'values' => [
                            ['id' => 'display_title_on', 'value' => 1, 'label' => $this->module->l('Enabled', $this->form_name)],
                            ['id' => 'display_title_off', 'value' => 0, 'label' => $this->module->l('Disabled', $this->form_name)],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display text', $this->form_name),
                        'name' => 'display_text',
                        'values' => [
                            ['id' => 'display_text_on', 'value' => 1, 'label' => $this->module->l('Enabled', $this->form_name)],
                            ['id' => 'display_text_off', 'value' => 0, 'label' => $this->module->l('Disabled', $this->form_name)],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Id html', $this->form_name),
                        'name' => 'html_id',
                        'class' => 'fixed-width-lg',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Classe html', $this->form_name),
                        'desc' => \Translate::getModuleTranslation($this->module, 'Seperate class with a space. <a href="%s" target="_blank">You can use Boostrap documentation to see classes used by Prestashop</a>', $this->form_name, ['https://getbootstrap.com/docs/4.0/utilities/display/'], null, null, null, false),
                        'name' => 'html_class',
                        'class' => 'fixed-width-lg',
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->module->l('Text color', $this->form_name),
                        'name' => 'color_text',
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->module->l('Background color', $this->form_name),
                        'name' => 'color_bg',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save', $this->form_name),
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        return parent::modifyControllerFormHelper();
    }

    /** Traite l'enregistrement du formulaire de la page produit
     *
     * @param array datas à enregistrer
     *
     * @return bool
     */
    public function processForm($formData)
    {
        $output = true;

        // Enregistrement des données
        foreach ($this->object::$definition['fields'] as $fieldName => $fieldParams) {
            $this->object->{$fieldName} = (isset($formData[$fieldName]) ? $formData[$fieldName] : $this->object->{$fieldName});
        }

        $output &= $this->object->save();

        return $output;
    }
}
