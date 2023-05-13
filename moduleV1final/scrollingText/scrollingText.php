<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class ScrollingText extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'scrollingText';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'Julien RUIZ';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();


        $this->displayName = $this->l('ScrollingText');
        $this->description = $this->l('Création d\'un bandeau défilant. Celui-ci va permettre d\'afficher un message d\'erreur, une annonce ou autre.');

        $this->confirmUninstall = $this->l('Vous voulez vraiment supprimer ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SCROLLINGTEXT_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHome') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayFooterBefore') &&
            $this->registerHook('displaySCROLLINGTEXTAdmin') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayShoppingCart') &&
            $this->registerHook('displayHomeTab');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SCROLLINGTEXT_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitScrollingTextModule')) == true) {
            $this->postProcess();
        }
        $Traduc  = $this->l('Traduction');
        $this->context->smarty->assign('Traduc', $Traduc);

        $TraducText  = $this->l('Voici les deux langues disponibles pour la traduction du module :');
        $this->context->smarty->assign('TraducText', $TraducText);

        $lang1  = $this->l('Français');
        $this->context->smarty->assign('lang1', $lang1);

        $lang2  = $this->l('Anglais');
        $this->context->smarty->assign('lang2', $lang2);

        $PosHook  = $this->l('Position points d\'accroche');
        $this->context->smarty->assign('PosHook', $PosHook);

        $PosHook  = $this->l('Position points d\'accroche');
        $this->context->smarty->assign('PosHook', $PosHook);

        $TextPosHook  = $this->l('Pour la configuration du module, vous allez devoir vous diriger vers les points d\'accroche afin de déterminer l\'emplacement sur le front. Pour cela, allez sur Apparence/Positions puis sélectionnez le module.');
        $this->context->smarty->assign('TextPosHook', $TextPosHook);

        $Visu  = $this->l('Visualisation');
        $this->context->smarty->assign('Visu', $Visu);


        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitScrollingTextModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('HOOKS'),
                        'name' => 'SCROLLINGTEXT_HOOK_HEADER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Header - on')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Header - off')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(' '),
                        'name' => 'SCROLLINGTEXT_HOOK_HOME',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Home - on')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Home - off')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(' '),
                        'name' => 'SCROLLINGTEXT_HOOK_FOOTER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Footer - on')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Footer - off')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(' '),
                        'name' => 'SCROLLINGTEXT_HOOK_PANIER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Panier - on')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Panier - off')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(' '),
                        'name' => 'SCROLLINGTEXT_HOOK_PRODUCT',
                        'is_bool' => true,
                        'desc' => $this->l('Vous allez pouvoir sélectionner le hook d\'affichage du message'),
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Produit - on')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Produit - off')
                            ),
                        ),
                    ),



                    array(
                        'type' => 'text',
                        'name' => 'SCROLLINGTEXT_CHOICE_TEXT',
                        'label' => $this->l('Texte'),
                        'required' => true,
                        'class' => 'sm',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SCROLLINGTEXT_FONT_SIZE',
                        'label' => $this->l('Taille du texte'),
                        'required' => true,
                        'desc' => $this->l('Valeur de la taille du texte en px'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SCROLLINGTEXT_PADDING',
                        'label' => $this->l('Le Padding'),
                        'required' => true,
                        'desc' => $this->l('Le Padding est l\'écart entre le texte et le cadre'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ecriture en gras'),
                        'name' => 'SCROLLINGTEXT_BOLD',
                        'required'  => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Non')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ecriture en Italique'),
                        'name' => 'SCROLLINGTEXT_ITALIC',
                        'required'  => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 0,
                                'label' => $this->l('Non')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'SCROLLINGTEXT_TEXT_COLOR',
                        'label' => $this->l('Couleur du texte'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'SCROLLINGTEXT_CHOICE_COLOR',
                        'label' => $this->l('Couleur de fond'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'SCROLLINGTEXT_CHOICE_SPEED',
                        'label' => $this->l('Vitesse de défilement'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Sens de défilement'),
                        'name' => 'SCROLLINGTEXT_CHOICE_SENS',
                        'class' => 't',
                        'required'  => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 0,
                                'label' => $this->l('scroll: mouvement continuel')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 1,
                                'label' => $this->l('alternate: mouvement de va et vient')
                            ),
                            array(
                                'id' => 'slide',
                                'value' => 2,
                                'label' => $this->l('slide: mouvement avec stop sur un coté')
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SCROLLINGTEXT_CHOICE_COLOR' => Configuration::get('SCROLLINGTEXT_CHOICE_COLOR', null),
            'SCROLLINGTEXT_CHOICE_TEXT' => Configuration::get('SCROLLINGTEXT_CHOICE_TEXT', null),
            'SCROLLINGTEXT_CHOICE_SENS' => Configuration::get('SCROLLINGTEXT_CHOICE_SENS', null),
            'SCROLLINGTEXT_CHOICE_SPEED' => Configuration::get('SCROLLINGTEXT_CHOICE_SPEED', null),
            'SCROLLINGTEXT_TEXT_COLOR' => Configuration::get('SCROLLINGTEXT_TEXT_COLOR', null),
            'SCROLLINGTEXT_FONT_SIZE' => Configuration::get('SCROLLINGTEXT_FONT_SIZE', null),
            'SCROLLINGTEXT_BOLD' => Configuration::get('SCROLLINGTEXT_BOLD', null),
            'SCROLLINGTEXT_ITALIC' => Configuration::get('SCROLLINGTEXT_ITALIC', null),
            'SCROLLINGTEXT_PADDING' => Configuration::get('SCROLLINGTEXT_PADDING', null),

            'SCROLLINGTEXT_HOOK_HEADER' => Configuration::get('SCROLLINGTEXT_HOOK_HEADER', null),
            'SCROLLINGTEXT_HOOK_HOME' => Configuration::get('SCROLLINGTEXT_HOOK_HOME', null),
            'SCROLLINGTEXT_HOOK_FOOTER' => Configuration::get('SCROLLINGTEXT_HOOK_FOOTER', null),
            'SCROLLINGTEXT_HOOK_PANIER' => Configuration::get('SCROLLINGTEXT_HOOK_PANIER', null),
            'SCROLLINGTEXT_HOOK_PRODUCT' => Configuration::get('SCROLLINGTEXT_HOOK_PRODUCT', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * Recupération des variables avant envoi sur le tpl backoffice.
     */
    public function affichMouveText(){

        $texte = htmlspecialchars(Configuration::get('SCROLLINGTEXT_CHOICE_TEXT', null));
        $couleurFond = Configuration::get('SCROLLINGTEXT_CHOICE_COLOR', null);
        $textcouleur = Configuration::get('SCROLLINGTEXT_TEXT_COLOR', null);
        $tailleTexte = htmlspecialchars(Configuration::get('SCROLLINGTEXT_FONT_SIZE', null));
        $padding = htmlspecialchars(Configuration::get('SCROLLINGTEXT_PADDING', null));


        if(Configuration::get('SCROLLINGTEXT_BOLD', null) == 1){
            $boldText = "bold";
        }else{
            $boldText = "";
        }

        if(Configuration::get('SCROLLINGTEXT_ITALIC', null) == 1){
            $italicText = "italic";
        }else{
            $italicText = "";
        }

        if (Configuration::get('SCROLLINGTEXT_CHOICE_SENS', null) == 0){
            $sensmouve = "scroll";
        }
        elseif (Configuration::get('SCROLLINGTEXT_CHOICE_SENS', null) == 1){
            $sensmouve = "alternate";
        }
        elseif (Configuration::get('SCROLLINGTEXT_CHOICE_SENS', null) == 2){
            $sensmouve = "slide";
        }
        $vitesse = htmlspecialchars(Configuration::get('SCROLLINGTEXT_CHOICE_SPEED', null));

        $this->context->smarty->assign('padding', $padding);
        $this->context->smarty->assign('italicText', $italicText);
        $this->context->smarty->assign('boldText', $boldText);
        $this->context->smarty->assign('texte', $texte);
        $this->context->smarty->assign('tailleTexte', $tailleTexte);
        $this->context->smarty->assign('textecouleur', $textcouleur);
        $this->context->smarty->assign('couleurFond', $couleurFond);
        $this->context->smarty->assign('sensmouve', $sensmouve);
        $this->context->smarty->assign('vitesse', $vitesse);

        return $this->display(__FILE__, 'views/templates/front/top.tpl');
    }

    public function hookDisplayFooterBefore()
    {
        if(Configuration::get('SCROLLINGTEXT_HOOK_FOOTER', null) == 1){
            return $this->affichMouveText();
        }
    }
    public function hookDisplayHeader()
    {
        if(Configuration::get('SCROLLINGTEXT_HOOK_HEADER', null) == 1){
            return $this->affichMouveText();
        }
    }
    public function hookDisplayHome()
    {
        if(Configuration::get('SCROLLINGTEXT_HOOK_HOME', null) == 1){
            return $this->affichMouveText();
        }
    }
    public function hookdisplayProductAdditionalInfo()
    {
        if(Configuration::get('SCROLLINGTEXT_HOOK_PRODUCT', null) == 1){
            return $this->affichMouveText();
        }
    }
    public function hookDisplaySCROLLINGTEXTAdmin()
    {
        return $this->affichMouveText();
    }
    public function HookdisplayShoppingCart()
    {
        if(Configuration::get('SCROLLINGTEXT_HOOK_PANIER', null) == 1){
            return $this->affichMouveText();
        }
    }

}
