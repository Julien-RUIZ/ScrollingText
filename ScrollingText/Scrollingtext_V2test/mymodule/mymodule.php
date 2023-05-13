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

class Mymodule extends Module
{
    protected $config_form = false;
    protected $MymoduleHook;
    protected $Colortext;
    protected $Mymoduletext;
    protected $CouleurFond;
    protected $TailleText;
    protected $VitesseMouv;
    protected $SensMouv;
    protected $bddMymodule;
    protected $methode;
    public $Namesmymodule= [];
    

    public function getNamesmymodule()
    {
        return $this->Namesmymodule;
    }

    public function setNamesmymodule($Namesmymodule)
    {
        $this->Namesmymodule = $Namesmymodule;
    }

    public function getSensMouv()
    {
        return $this->SensMouv;
    }

    public function setSensMouv($SensMouv)
    {
        $this->SensMouv = $SensMouv;
    }

    public function getVitesseMouv()
    {
        return $this->VitesseMouv;
    }

    public function setVitesseMouv($VitesseMouv)
    {
        $this->VitesseMouv = $VitesseMouv;
    }

    public function getCouleurFond()
    {
        return $this->CouleurFond;
    }

    public function setCouleurFond($CouleurFond)
    {
        $this->CouleurFond = $CouleurFond;
    }

    public function getTailleText()
    {
        return $this->TailleText;
    }

    public function setTailleText($TailleText)
    {
        $this->TailleText = $TailleText;
    }

    public function getMymoduleHook()
    {
        return $this->MymoduleHook;
    }

    public function setMymoduleHook($MymoduleHook)
    {
        $this->MymoduleHook = $MymoduleHook;
    }
    public function getColortext()
    {
        return $this->Colortext;
    }

    public function setColortext($Colortext)
    {
        $this->Colortext= $Colortext;
    }

    public function getMymoduletext(){
        return $this->Mymoduletext;
    }

    public function setMymoduletext($Mymoduletext)
    {
        $this->Mymoduletext = $Mymoduletext;
    }

    /*
     * Cette fonction va nous permettre de réaliser une liaison pour la lecture
     * des données et ainsi permettre la mise en place de la fonction hydrate.
     * */
    public function linkbddMymodule()
    {
        $bddMymodule = Db::getInstance()->ExecuteS('
	SELECT name, value, id_mymodule
	FROM `'._DB_PREFIX_.'mymodule`');
            $this->bddMymodule =$bddMymodule;
    }

     /* Mise en place de la methode hydrate afin de distribuer les informations
     de la bdd automatiquement en fonction des setters.
     **/
    public function hydrate($bddMymodule)
    {
        $this->linkbddMymodule();
        foreach ($this->bddMymodule as $value)
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
         $this->name = 'mymodule';
         $this->tab = 'front_office_features';
         $this->version = '2.0.6';
         $this->author = 'Julien RUIZ';
         $this->need_instance = 1;

         /**
          * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
          */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('mymodule');
        $this->description = $this->l('Création d\'un bandeau défilant. Celui-ci va permettre d\'afficher un méssage d\érreur ou une annonce. ');

        $this->confirmUninstall = $this->l('Vous voulez vraiment supprimer ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('MYMODULE_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHome') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('DisplayFooterBefore') &&
            $this->registerHook('displayMymoduleAdmin') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('displayShoppingCart') &&
            $this->registerHook('DisplayProductPriceBlock') &&
            $this->registerHook('displayHomeTab');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MYMODULE_LIVE_MODE');

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
         * la partie ou mettre l fonction update lors de la validation du submit
         */
        if (((bool)Tools::isSubmit('submitMymoduleModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();/* ligne permettant l'affichage du formulaire sur la page config ci-dessus */
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {

        $helper = new HelperForm();
/* https://devdocs.prestashop.com/1.7/development/components/helpers/helperform/  doc Prestashop sur les option a rempliur pour la conf du form
liste des attributs suivant : */
        $helper->show_toolbar = false;
        $helper->table = $this->table; /*Nom de la table où sont stockées les données, sans préfixe (par exemple "configuration"). Ceci n'est requis que dans les cas suivants :
                                        Si $submit_actionn'a pas été défini.
                                        Si le formulaire comprend un shopchamp de saisie (association de magasin).
                                        S'il y a plusieurs formulaires sur la même page.
                                       ********** public $table = 'configuration';  dans le helper.php   ********/
        $helper->module = $this; /*Instance du module utilisant ce formulaire. Ceci n'est nécessaire que pour permettre au module de remplacer le modèle du formulaire.*/
        $helper->default_form_language = $this->context->language->id; /*Identifiant de la langue par défaut (notamment pour les champs multilingues).*/
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0); /*Ceci est par défaut 0et n'a aucun effet dans PrestaShop 1.7.*/

        $helper->identifier = $this->identifier; /*Nom du champ ID dans la table où les données sont stockées, le cas échéant. S'il est présent,
                                                un champ masqué portant le même nom que cette variable sera ajouté et sa valeur sera définie sur l' $idattribut.*/
        $helper->submit_action = 'submitMymoduleModule'; /*Cette propriété gère un champ masqué du même nom qui sera ajouté au formulaire, avec sa valeur définie sur "1". Il sera également utilisé comme nom par défaut pour le bouton d'envoi, sauf indication contraire dans la configuration d'entrée du formulaire.
                                                            S'il n'est pas défini, sa valeur par défaut est "submitAdd{$table}"(lire $tableci-dessous).*/
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) /*Cette URL sera définie comme l'URL du formulaire action.*/
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules'); /*Jeton CSRF qui sera ajouté à l'URL d'action du formulaire, s'il $currentIndexest défini.*/

        $helper->tpl_vars = array( /*Cela vous permet d'ajouter de nouvelles variables Smarty au modèle ou de remplacer celles définies par HelperForm.*/
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
                        'type' => 'radio',
                        'label' => $this->l('Hooks'),
                        'name' => 'MymoduleHook',
                        'class' => 't',
                        'required'  => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'scroll',
                                'value' => 0,
                                'label' => $this->l('Home (Visible seulement sur la page principale)')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 1,
                                'label' => $this->l('Footer (Le message apparaitra sur toutes les pages du site)')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 2,
                                'label' => $this->l('Fiche produit')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 4,
                                'label' => $this->l('Panier (Sur la droite au dessus du prix)')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 3,
                                'label' => $this->l('Affichage sur tous les hooks')
                            ),
                            array(
                                'id' => 'alternate',
                                'value' => 5,
                                'label' => $this->l('Aucun')
                            ),
                        ),
                    ),


                    array(
                        'type' => 'text',
                        'name' => 'Mymoduletext',
                        'label' => $this->l('Texte'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'TailleText',
                        'label' => $this->l('Valeur de la taille du texte en px'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'Colortext',
                        'label' => $this->l('Couleur du texte'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'CouleurFond',
                        'label' => $this->l('Couleur de fond'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'VitesseMouv',
                        'label' => $this->l('Vitesse de défilement'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Sens de défilement'),
                        'name' => 'SensMouv',
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
     * Lecture des différents champs visible sur le formulaire.
     */
    protected function getConfigFormValues()
    {

        /*return array(
            'Colortext' => tools::getValue('Colortext'),
            'Mymoduletext' => tools::getValue('Mymoduletext'),
            'MymoduleHook' => tools::getValue('MymoduleHook'),
            'CouleurFond' => tools::getValue('CouleurFond'),
            'TailleText' => tools::getValue('TailleText'),
            'VitesseMouv' => tools::getValue('VitesseMouv'),
            'SensMouv' => tools::getValue('SensMouv'),
        );*/
            $this->hydrate($this->bddMymodule);
        return array(

            'Colortext' => $this->getColortext(),
            'Mymoduletext' => $this->getMymoduletext(),
            'MymoduleHook' => $this->getMymoduleHook(),
            'CouleurFond' => $this->getCouleurFond(),
            'TailleText' => $this->getTailleText(),
            'VitesseMouv' => $this->getVitesseMouv(),
            'SensMouv' => $this->getSensMouv(),
        );
    }

    /**Tools::dieObject($form_values);
     * Save form data.
     *
     */
    protected function postProcess()
    {
        $value = [['name' => 'Colortext', 'value' => tools::getValue('Colortext')],
            ['name' => 'Mymoduletext', 'value' => tools::getValue('Mymoduletext')],
        ['name' => 'MymoduleHook', 'value' => tools::getValue('MymoduleHook')],
        ['name' => 'CouleurFond', 'value' => tools::getValue('CouleurFond')],
        ['name' => 'TailleText', 'value' => tools::getValue('TailleText')],
        ['name' => 'VitesseMouv', 'value' => tools::getValue('VitesseMouv')],
        ['name' => 'SensMouv', 'value' => tools::getValue('SensMouv')],];
        $this->hydrate($this->bddMymodule);


        for ($i=0 ; $i < count($value) ; $i++){

            if(in_array($value[$i]['name'], $this->getNamesmymodule(), true)){
                Db::getInstance()->update('mymodule', $value[$i], 'id_mymodule ='. $i);
            }else{
                Db::getInstance()->insert('mymodule', $value[$i]);
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


    public function affichMouveText(){
        $this->hydrate($this->bddMymodule);
        $texte = $this->getMymoduletext();
        $couleurFond = $this->getCouleurFond();
        $textcouleur = $this->getColortext();
        $tailleTexte = $this->getTailleText();

        if ($this->getSensMouv() == 0){
            $sensmouve = "scroll";
        }
        elseif ($this->getSensMouv() == 1){
            $sensmouve = "alternate";
        }
        elseif ($this->getSensMouv() == 2){
            $sensmouve = "slide";
        }
        $vitesse = htmlspecialchars($this->getVitesseMouv());

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
        $this->hydrate($this->bddMymodule);
        if($this->getMymoduleHook() == 1){
            return $this->affichMouveText();
        }elseif ($this->getMymoduleHook() == 3){
            return $this->affichMouveText();
        }
    }
    public function hookDisplayHome()
    {
        $this->hydrate($this->bddMymodule);
        if ($this->getMymoduleHook() == 0){
            return $this->affichMouveText();
        }elseif ($this->getMymoduleHook() == 3){
            return $this->affichMouveText();
        }
    }
    public function hookdisplayProductAdditionalInfo()
    {
        $this->hydrate($this->bddMymodule);
        if ($this->getMymoduleHook() == 2){
            return $this->affichMouveText();
        }elseif ($this->getMymoduleHook() == 3){
            return $this->affichMouveText();
        }
    }
    public function hookDisplayMymoduleAdmin()
    {
        return $this->affichMouveText();
    }
    public function HookdisplayShoppingCart()
    {
        $this->hydrate($this->bddMymodule);
        if ($this->getMymoduleHook() == 4){
            return $this->affichMouveText();
        }elseif ($this->getMymoduleHook() == 3){
            return $this->affichMouveText();
        }
    }
    
}
