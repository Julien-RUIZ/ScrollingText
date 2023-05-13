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
    protected $HookHeader;
    protected $HookHome;
    protected $HookFooter;
    Protected $HookPanier;
    Protected $HookProduct;
    Protected $ChoiceText;
    protected $FontSize;
    protected $Padding;
    protected $TextBold;
    protected $TextItalic;
    protected $TextColor;
    protected $ChoiceColor;
    protected $ChoiceSpeed;
    protected $ChoiceSens;
    protected $bddScrollingText;
    public $Namesmymodule= [];

    public function getNamesmymodule()
    {
        return $this->Namesmymodule;
    }

    public function setNamesmymodule($Namesmymodule)
    {
        $this->Namesmymodule = $Namesmymodule;
    }

    public function getHookHeader(){
        return $this->HookHeader;
    }
    public function setHookHeader($HookHeader){
        $this->HookHeader = $HookHeader;
    }
    public function getHookHome(){
        return $this->HookHome;
    }
    public function setHookHome($HookHome){
        $this->HookHome = $HookHome;
    }
    public function getHookFooter(){
        return $this->HookFooter;
    }
    public function setHookFooter($HookFooter){
        $this->HookFooter = $HookFooter;
    }
    public function getHookPanier(){
        return $this->HookPanier;
    }
    public function setHookPanier($HookPanier){
        $this->HookPanier = $HookPanier;
    }
    public function getHookProduct(){
        return $this->HookProduct;
    }
    public function setHookProduct($HookProduct){
        $this->HookProduct = $HookProduct;
    }
    public function getChoiceText(){
        return $this->ChoiceText;
    }
    public function setChoiceText($ChoiceText){
        $this->ChoiceText = $ChoiceText;
    }
    public function getFontsize(){
        return $this->Fontsize;
    }
    public function setFontsize($Fontsize){
        $this->Fontsize = $Fontsize;
    }
    public function getPadding(){
        return $this->Padding;
    }
    public function setPadding($Padding){
        $this->Padding = $Padding;
    }
    public function getTextBold(){
        return $this->TextBold;
    }
    public function setTextBold($TextBold){
        $this->TextBold = $TextBold;
    }
    public function getTextItalic(){
        return $this->TextItalic;
    }
    public function setTextItalic($TextItalic){
        $this->TextItalic = $TextItalic;
    }
    public function getTextColor(){
        return $this->TextColor;
    }
    public function setTextColor($TextColor){
        $this->TextColor = $TextColor;
    }
    public function getChoiceColor(){
        return $this->ChoiceColor;
    }
    public function setChoiceColor($ChoiceColor){
        $this->ChoiceColor = $ChoiceColor;
    }
    public function getChoiceSpeed(){
        return $this->ChoiceSpeed;
    }
    public function setChoiceSpeed($ChoiceSpeed){
        $this->ChoiceSpeed = $ChoiceSpeed;
    }
    public function getChoiceSens(){
        return $this->ChoiceSens;
    }
    public function setChoiceSens($ChoiceSens){
        $this->ChoiceSens = $ChoiceSens;
    }

    /**
     * C'est une fonction qui va permettre la récupération des informations de la bdd
     */
    public function LinkbddScrollingText(){
        $bddScrollingText = Db::getInstance()->ExecuteS('
	SELECT name, value, id_scrollingText
	FROM `'._DB_PREFIX_.'scrollingText`');
            $this->bddScrollingText =$bddScrollingText;
    }

   
    /**
     * Mise en place de la methode hydrate afin de distribuer les informations
     de la bdd automatiquement en fonction des setters.
     */
    public function hydrate($bddScrollingText)
    {
        $this->LinkbddScrollingText();
        foreach ($this->bddScrollingText as $value)
        {
            /*echo 'name = '. $value['name'].', valeur = '.$value['value']."<br>" ;*/
             $methode = 'set'.$value['name'];
             if (method_exists($this, $methode))
             {
                 array_push($this->Namesmymodule, $value['name'] );
                 $this->$methode($value['value']);
             }
         }
     }


    public function __construct()
    {
        $this->name = 'scrollingText';
        $this->tab = 'front_office_features';
        $this->version = '2.2.0';
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
     * ajout d'une partie a traduire
     */
    public function getContent()
    {
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
                        'name' => 'HookHeader',
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
                        'name' => 'HookHome',
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
                        'name' => 'HookFooter',
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
                        'name' => 'HookPanier',
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
                        'name' => 'HookProduct',
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
                        'name' => 'ChoiceText',
                        'label' => $this->l('Texte'),
                        'required' => true,
                        'class' => 'sm',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'FontSize',
                        'label' => $this->l('Taille du texte'),
                        'required' => true,
                        'desc' => $this->l('Valeur de la taille du texte en px'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'Padding',
                        'label' => $this->l('Le Padding'),
                        'required' => true,
                        'desc' => $this->l('Le Padding est l\'écart entre le texte et le cadre'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ecriture en gras'),
                        'name' => 'TextBold',
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
                        'name' => 'TextItalic',
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
                        'name' => 'TextColor',
                        'label' => $this->l('Couleur du texte'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'ChoiceColor',
                        'label' => $this->l('Couleur de fond'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'ChoiceSpeed',
                        'label' => $this->l('Vitesse de défilement'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Sens de défilement'),
                        'name' => 'ChoiceSens',
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
        $this->hydrate($this->bddScrollingText);
        return array(
            'HookHeader'=> $this->getHookHeader(),
            'HookHome' => $this->getHookHome(),
            'HookFooter' => $this->getHookFooter(),
            'HookPanier' => $this->getHookPanier(),
            'HookProduct' => $this->getHookProduct(),
            'ChoiceText' => $this->getChoiceText(),
            'FontSize' => $this->getFontSize(),
            'Padding' => $this->getPadding(),
            'TextBold' => $this->getTextBold(),
            'TextItalic' => $this->getTextItalic(),
            'TextColor' => $this->getTextColor(),
            'ChoiceColor' => $this->getChoiceColor(),
            'ChoiceSpeed' => $this->getChoiceSpeed(),
            'ChoiceSens' => $this->getChoiceSens(),
        );
    }

    /**
     * Sauvegarde des données du formulaire avec test si il existe en bdd.
     */
    protected function postProcess()
    {
        $value = [
        ['name' => 'HookHeader', 'value' => tools::getValue('HookHeader')],
        ['name' => 'HookHome', 'value' => tools::getValue('HookHome')],
        ['name' => 'HookFooter', 'value' => tools::getValue('HookFooter')],
        ['name' => 'HookPanier', 'value' => tools::getValue('HookPanier')],
        ['name' => 'HookProduct', 'value' => tools::getValue('HookProduct')],
        ['name' => 'ChoiceText', 'value' => tools::getValue('ChoiceText')],
        ['name' => 'FontSize', 'value' => tools::getValue('FontSize')],
        ['name' => 'Padding', 'value' => tools::getValue('Padding')],
        ['name' => 'TextBold', 'value' => tools::getValue('TextBold')],
        ['name' => 'TextItalic', 'value' => tools::getValue('TextItalic')],
        ['name' => 'TextColor', 'value' => tools::getValue('TextColor')],
        ['name' => 'ChoiceColor', 'value' => tools::getValue('ChoiceColor')],
        ['name' => 'ChoiceSpeed', 'value' => tools::getValue('ChoiceSpeed')],
        ['name' => 'ChoiceSens', 'value' => tools::getValue('ChoiceSens')],
    ];
    $this->hydrate($this->bddScrollingText);
        
    for ($i=0 ; $i < count($value) ; $i++){

        if(in_array($value[$i]['name'], $this->getNamesmymodule(), true)){
            Db::getInstance()->update('scrollingText', $value[$i], 'id_scrollingText ='. $i);
        }else{
            Db::getInstance()->insert('scrollingText', $value[$i]);
        }
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
        $this->hydrate($this->bddScrollingText);
        $texte = htmlspecialchars($this->getChoiceText());
        $couleurFond = $this->getChoiceColor();
        $textcouleur = $this->getTextColor();
        $tailleTexte = htmlspecialchars($this->getFontSize());
        $padding = htmlspecialchars($this->getPadding());

        if($this->getTextBold() == 1){
            $boldText = "bold";
        }else{
            $boldText = "";
        }

        if($this->getTextItalic() == 1){
            $italicText = "italic";
        }else{
            $italicText = "";
        }

        if ($this->getChoiceSens() == 0){
            $sensmouve = "scroll";
        }
        elseif ($this->getChoiceSens() == 1){
            $sensmouve = "alternate";
        }
        elseif ($this->getChoiceSens() == 2){
            $sensmouve = "slide";
        }
        $vitesse = htmlspecialchars($this->getChoiceSpeed());

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

    /**
     * Hook Footer
     */
    public function hookDisplayFooterBefore()
    {
        $this->hydrate($this->bddScrollingText);
        if($this->getHookFooter() == 1){
            return $this->affichMouveText();
        }
    }
    /**
     * Hook Header
     */
    public function hookDisplayHeader()
    {
        $this->hydrate($this->bddScrollingText);
        if($this->getHookHeader() == 1){
            return $this->affichMouveText();
        }
    }
    /**
     * Hook Home
     */
    public function hookDisplayHome()
    {
        $this->hydrate($this->bddScrollingText);
        if($this->getHookHome() == 1){
            return $this->affichMouveText();
        }
    }
    /**
     * Hook Product
     */
    public function hookdisplayProductAdditionalInfo()
    {
        $this->hydrate($this->bddScrollingText);
        if($this->getHookProduct() == 1){
            return $this->affichMouveText();
        }
    }
    /**
     * Hook Admin
     */
    public function hookDisplaySCROLLINGTEXTAdmin()
    {
        return $this->affichMouveText();
    }
    /**
     * Hook Cart
     */
    public function HookdisplayShoppingCart()
    {
        $this->hydrate($this->bddScrollingText);
        if($this->getHookPanier() == 1){
            return $this->affichMouveText();
        }
    }

}
