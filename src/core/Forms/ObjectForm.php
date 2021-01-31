<?php
/**
 * Gère le formulaire d'un model
 *
 * @author:  Michel Dumont <michel.dumont.io>
 * @version: 1.0.4 - 2021-01-31
 * @license: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package: prestashop 1.6 - 1.7
 */

namespace mdg\homepush\core\Forms;

abstract class ObjectForm
{
    /** @var object \Context instancié */
    public $context;

    /** @var object */
    public $object;

    /** @var object */
    public $module;

    /** @var string nom du fichier nécessaire pour les traductions */
    public $form_name;

    /** @var string nom du controller chargeant le formulaire */
    public $controller_name;

    /** @var string action du formulaire */
    public $form_action;

    /** @var array attributs et valeures de l'objet */
    public $fields_value;

    /** @var array structure du formulaire */
    public $fields_form;

    /** @var string */
    public $currentToken;

    /** @var string */
    public $currentIndex;

    /** Déclare les valeurs communes à tous les formulaires
     *
     * @param string Object::class du formulaire
     * @param object
     */
    public function __construct($object = null, $legacyContext = null)
    {
        $this->context = \Context::getContext();
        $this->module = \Module::getInstanceByName(basename(realpath(dirname(__FILE__) . '/../../..')));
        $this->controller_name = $this->context->controller->controller_name;
    }

    /** Renseigne les variables nécessaires
     *  Si on est dans le context d'un FormHelper
     *  On renseigne les paramètres grâce à ceux renseignés dans l'adminModuleController
     *
     * @param string chemin|nonm du fichier qui sert de controller
     * @param int|object Object::class du formulaire
     *
     * @return void
     */
    public function constructFormHelper($controllerFile, $object = null)
    {
        if (is_object($object)) {
            $className = get_class($object);
            $this->identifier = $className::$definition['primary'];
            $this->table = $className::$definition['table'];
            $this->object = $object;
        } else {
            $className = $this->context->controller->className;
            $this->table = $this->context->controller->table;
            $this->identifier = $className::$definition['primary'];
            $this->object = new $className($object);
        }

        $this->currentToken = \Tools::getValue('token');
        $this->currentIndex = "index.php?controller={$controllerFile}";

        $this->form_name = basename($controllerFile, '.php');
        $this->form_action = "submit_{$this->form_name}";
    }

    /** Renseigne les variables nécessaires
     *  il faut déclarer les paramètres dans le constructeur enfant qui instancie cet objet
     *
     * @param string chemin|nonm du fichier qui sert de controller
     * @param string Object::class du formulaire
     * @param int|object
     *
     * @return void
     */
    public function constructFormBuilder($controllerFile, $className, $object = null)
    {
        $this->table = $className::$definition['table'];
        $this->identifier = $className::$definition['primary'];
        $this->object = is_object($object) ? $object : $className::getInstanceByIdObject($object);

        $this->form_name = basename($controllerFile, '.php');
        $this->form_action = "submit_{$this->form_name}";
    }

    public function setMedia()
    {
    }

    /** Ajoute des entrées au Formbuilder de symfony
     *
     * @since PS 1.7.6
     *
     * @param array params du hook
     *
     * @return void
     */
    public function modifyFormBuilder(&$params)
    {
    }

    /** Ajoute des entrées au Formulaire Prestashop
     *
     * @param array params du hook
     *
     */
    public function modifyFormHelper(&$params)
    {
    }

    /**
     * Retourne les données pour AdminModuleController::renderForm
     * @see AdminModuleController
     *      public function renderForm()
     *      {
     *          foreach ((new BlockForm($this->object))->modifyControllerFormHelper() as $key => $value) {
     *              $this->$key = $value;
     *          }
     *          return parent::renderForm();
     *      }
     *
     * @return array
     */
    public function modifyControllerFormHelper()
    {
        /**
         * Exemple:
         *   $this->fields_value = (array) $this->object;
         *   $this->multiple_fieldsets = true;
         *   $this->fields_form = [];
         *   $this->fields_form[] = [
         *       'form' => [
         *           'legend' => [
         *               'title' => $this->module->l('Sample', $this->form_name),
         *               'icon' => 'icon-align-left',
         *           ],
         *           'input' => [
         *               [
         *                   'type' => 'text',
         *                   'label' => $this->module->l('Title', $this->form_name),
         *                   'name' => 'title',
         *                   'lang' => true,
         *                   'required' => true,
         *               ],
         *           ],
         *       ],
         *   ];
         */

        $output = [
            'multiple_fieldsets' => isset($this->multiple_fieldsets) ? $this->multiple_fieldsets : count($this->fields_form),
            'fields_value' => $this->fields_value ? $this->fields_value : (array) $this->object,
            'fields_form' => $this->fields_form,
        ];
        return $output;
    }

    /** Retourne les data formatées pour les select de HelperForm
     *
     * @param array couple [id => value, ...]
     *
     * @return array
     */
    public function getBuilderFormChoices(array $params)
    {
        $output = [];

        foreach ($params as $id => $value) {
            $output[$id] = $this->module->l($value, $this->form_name);
        }

        return $output;
    }

    /** Retourne les data formatées pour les select de HelperForm
     *
     * @param array couple [id => value, ...]
     *
     * @return array
     */
    public function getHelperFormChoices(array $params)
    {
        $output = [];

        foreach ($params as $id => $value) {
            $output[] = ['id' => $id, 'name' => $this->module->l($value, $this->form_name)];
        }

        return [
            'id' => 'id',
            'name' => 'name',
            'query' => $output,
        ];
    }

    /** Retourne l'index du module
     *
     * @return string
     */
    public function getModuleIndex()
    {
        return $this->context->link->getAdminLink('AdminModules', true) . "&configure={$this->module->name}&tab_module={$this->module->tab}&module_name={$this->module->name}";
    }
}
