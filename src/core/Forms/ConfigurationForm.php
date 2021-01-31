<?php
/**
 * @author Michel Dumont <michel.dumont.io>
 * @version 1.0.0 - 2021-01-28
 * @copyright 2021
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Forms;

class ConfigurationForm extends \mdg\homepush\Forms\ObjectForm
{
    public $table = null;
    public $identifier = null;
    public $form_name = 'ConfigurationForm';
    public $_html = '';

    /**
     * @inheritdoc
     */
    public function displayControllerFormHelper()
    {
        // Display process
        if (\Tools::isSubmit('submitAdd')) {
            $process = $this->processForm($_POST);
            if ($process) {
                $this->_html .= $this->module->displayConfirmation($this->module->l('Settings saved', $this->form_name));
            } else {
                $this->_html .= $this->module->displayError($this->module->l('An error occured during process', $this->form_name));
            }
        }

        // Prepare Form values
        $this->fields_value = [
            'MDG_HOMESLIDE_FULLSCREEN' => \Configuration::get('MDG_HOMESLIDE_FULLSCREEN'),
            'MDG_HOMESLIDE_HEIGHT' => \Configuration::get('MDG_HOMESLIDE_HEIGHT'),
            'MDG_HOMESLIDE_IMGWIDTH' => \Configuration::get('MDG_HOMESLIDE_IMGWIDTH'),
            'MDG_HOMESLIDE_IMGHEIGHT' => \Configuration::get('MDG_HOMESLIDE_IMGHEIGHT'),
            'MDG_HOMESLIDE_BREAK' => \Configuration::get('MDG_HOMESLIDE_BREAK'),
            'MDG_HOMESLIDE_TANSITION' => \Configuration::get('MDG_HOMESLIDE_TANSITION'),
            'MDG_HOMESLIDE_AXE' => \Configuration::get('MDG_HOMESLIDE_AXE'),
            'MDG_HOMEPUSH_HEIGHT' => \Configuration::get('MDG_HOMEPUSH_HEIGHT'),
            'MDG_HOMEPUSH_IMGHEIGHT' => \Configuration::get('MDG_HOMEPUSH_IMGHEIGHT'),
            'MDG_HOMEPUSH_IMGWIDTH' => \Configuration::get('MDG_HOMEPUSH_IMGWIDTH'),
        ];

        // Prepare form content
        $this->fields_form = [];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Slides settings', $this->form_name),
                    'icon' => 'icon-cog',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Display in fullscreen', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_FULLSCREEN',
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->module->l('Enabled', $this->form_name)],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->module->l('Disabled', $this->form_name)],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Container height', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_HEIGHT',
                        'suffix' => 'vh | px | %',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Break for autorun', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_BREAK',
                        'description' => $this->module->l('Time between two autorun, leave empty to desable autorun', $this->form_name),
                        'suffix' => 'ms',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Speed of transition', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_TANSITION',
                        'suffix' => 'ms',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Kind of movement', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_AXE',
                        'options' => [
                            'id' => 'id',
                            'name' => 'name',
                            'query' => [
                                ['id' => 0, 'name' => $this->module->l('Horizontal', $this->form_name)],
                                ['id' => 1, 'name' => $this->module->l('Vertical', $this->form_name)],
                                ['id' => 2, 'name' => $this->module->l('Fade', $this->form_name)],
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Image width', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_IMGWIDTH',
                        'suffix' => 'px',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Image height', $this->form_name),
                        'name' => 'MDG_HOMESLIDE_IMGHEIGHT',
                        'suffix' => 'px',
                        'class' => 'fixed-width-xs',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save', $this->form_name),
                    'icon' => 'process-icon-save',
                ],
            ],
        ];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Pushes settings', $this->form_name),
                    'icon' => 'icon-cog',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Containers height', $this->form_name),
                        'name' => 'MDG_HOMEPUSH_HEIGHT',
                        'suffix' => 'px',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Image width', $this->form_name),
                        'name' => 'MDG_HOMEPUSH_IMGWIDTH',
                        'suffix' => 'px',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Image height', $this->form_name),
                        'name' => 'MDG_HOMEPUSH_IMGHEIGHT',
                        'suffix' => 'px',
                        'class' => 'fixed-width-xs',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save', $this->form_name),
                    'icon' => 'process-icon-save',
                ],
            ],
        ];

        #region HELPER
        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->module = $this->module;
        $helper->identifier = $this->identifier;

        // START FIX BUG - Language
        $languages = \Language::getLanguages(true);
        foreach ($languages as &$language) {
            $language['is_default'] = ($language['id_lang'] == $this->context->language->id);
        }

        // END FIX BUG - Language
        $helper->languages = $languages;
        $helper->default_form_language = $this->context->language->id;
        $helper->id_language = $this->context->language->id;

        $helper->submit_action = $this->form_action;
        $helper->currentIndex = $this->getModuleIndex();
        $helper->token = \Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = ['fields_value' => $this->fields_value];
        $helper->override_folder = '/';

        $this->_html .= $helper->generateForm($this->fields_form);

        return $this->_html;
        #endregion HELPER
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
        foreach ($formData as $key => $value) {
            if (preg_match('/^MDG_/', $key)) {
                $output &= \Configuration::updateValue($key, $value);
            }
        }

        return $output;
    }
}
