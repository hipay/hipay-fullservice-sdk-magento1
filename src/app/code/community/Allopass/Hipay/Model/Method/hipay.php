<?php
/**
 * HiPay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @author HiPay <support.wallet@hipay.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 */

if (!defined('_PS_VERSION_'))
    exit;

define('DEV', 0);
define('PROD', 1);
define('HIPAY_LOG', 1);

class Hipay extends PaymentModule
{
    private $arrayCategories;
    private $env = PROD;

    protected $ws_client = false;

    const WS_SERVER = 'http://api.prestashop.com/';
    const WS_URL = 'http://api.prestashop.com/partner/hipay/hipay.php';

    public static $available_rates_links = array('EN', 'FR');
    const PAYMENT_FEED_BASE_LINK = 'https://www.prestashop.com/download/pdf/pspayments/Fees_PSpayments_';

    public function __construct()
    {
        $this->name = 'hipay';
        $this->tab = 'payments_gateways';
        $this->version = '1.6.16';
        $this->author = 'HiPay';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->module_key = 'ab188f639335535838c7ee492a2e89f8';
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->displayName = 'HiPay';
        $this->description = $this->l('Secure payement with Visa, Mastercard and European solutions.');

        $request = '
			SELECT iso_code
			FROM '._DB_PREFIX_.'country as c
			LEFT JOIN '._DB_PREFIX_.'zone as z
			ON z.id_zone = c.id_zone
			WHERE ';

        $result = Db::getInstance()->ExecuteS($request.$this->getRequestZones());

        foreach ($result as $num => $iso)
            $this->limited_countries[] = $iso['iso_code'];

        if ($this->id)
        {
            // Define extracted from mapi/mapi_defs.php
            if (!defined('HIPAY_GATEWAY_URL'))
                define('HIPAY_GATEWAY_URL','https://'.($this->env ? '' : 'test.').'payment.hipay.com/order/');
        }

        /** Backward compatibility */
        require(_PS_MODULE_DIR_.'hipay/backward_compatibility/backward.php');

        if (!class_exists('SoapClient'))
            $this->warning .= $this->l('To work properly the module need the Soap library to be installed.');
        else
            $this->ws_client = $this->getWsClient();
    }

    public function install()
    {
        Configuration::updateValue('HIPAY_SALT', uniqid());

        if (!Configuration::get('HIPAY_UNIQID'))
            Configuration::updateValue('HIPAY_UNIQID', uniqid());
        if (!Configuration::get('HIPAY_RATING'))
            Configuration::updateValue('HIPAY_RATING', 'ALL');

        if (!(parent::install() &&
            $this->registerHook('payment') &&
                $this->registerHook('displayPaymentEU') &&
                    $this->registerHook('paymentReturn') &&
                        $this->_createAuthorizationOrderState())){
            return false;
        }

        if (_PS_VERSION_ >= '1.5' && (!$this->registerHook('displayBackOfficeHeader'))) {
            return false;
        }


        $result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');

        foreach ($result as $rowNumber => $rowValues)
        {
            Configuration::deleteByName('HIPAY_AZ_'.$rowValues['id_zone']);
            Configuration::deleteByName('HIPAY_AZ_ALL_'.$rowValues['id_zone']);
        }
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);

        return true;
    }

    private function _createAuthorizationOrderState()
    {
        if (!Configuration::get('HIPAY_AUTHORIZATION_OS'))
        {
            $os = new OrderState();
            $os->name = array();
            foreach (Language::getLanguages(false) as $language)
                if (Tools::strtolower($language['iso_code']) == 'fr')
                    $os->name[(int)$language['id_lang']] = 'Autorisation acceptée par HiPay';
                else
                    $os->name[(int)$language['id_lang']] = 'Authorization accepted by HiPay';
            $os->color = '#4169E1';
            $os->hidden = false;
            $os->send_email = false;
            $os->delivery = false;
            $os->logable = false;
            $os->invoice = false;
            if ($os->add())
            {
                Configuration::updateValue('HIPAY_AUTHORIZATION_OS', $os->id);
                copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$os->id.'.gif');
            }
            else{
                return false;
            }
        }
        if (!Configuration::get('HIPAY_WAITINGPAYMENT_OS'))
        {
            $os = new OrderState();
            $os->name = array();
            foreach (Language::getLanguages(false) as $language)
                if (Tools::strtolower($language['iso_code']) == 'fr')
                    $os->name[(int)$language['id_lang']] = 'En attente de paiement HiPay';
                else
                    $os->name[(int)$language['id_lang']] = 'Pending payment HiPay';
            $os->color = '#FAAC58';
            $os->hidden = false;
            $os->send_email = false;
            $os->delivery = false;
            $os->logable = false;
            $os->invoice = false;
            if ($os->add())
            {
                Configuration::updateValue('HIPAY_WAITINGPAYMENT_OS', $os->id);
                copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$os->id.'.gif');
            }
            else{
                return false;
            }
        }
        if (!Configuration::get('HIPAY_VERSION'))
        {
            Configuration::updateValue('HIPAY_VERSION', $this->version);
        }
        return true;
    }

    /*
	 * if PS 1.5 add CSS bootstrap, admin-theme.css, back.css and custom.css
	 * else PS 1.6 add back.css, admin-theme.css and custom.css
	 */
    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('configure') != 'hipay')
            return false;

        if (_PS_VERSION_ < '1.6'){
            $this->context->controller->addJquery('1.12.4', $this->_path.'views/js/');
            $this->context->controller->addJS($this->_path.'views/js/bootstrap.min.js');
            $this->context->controller->addJS($this->_path.'views/js/admin-theme.js');
            $this->context->controller->addCSS($this->_path.'views/css/bootstrap.min.css', 'all');
            $this->context->controller->addCSS($this->_path.'views/css/admin-theme.min.css', 'all');
        }
        $this->context->controller->addCSS($this->_path.'views/css/custom.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/back.css', 'all');
        return;
    }

    /**
     * Set shipping zone search
     *
     * @param	string $searchField = 'z.id_zone'
     * @param	int $defaultZone = 1
     * @return	string
     */
    private function getRequestZones($searchField='z.id_zone', $defaultZone = 1)
    {
        $result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');

        $tmp = null;
        foreach ($result as $rowNumber => $rowValues)
            if (strcmp(Configuration::get('HIPAY_AZ_'.$rowValues['id_zone']), 'ok') == 0)
                $tmp .= $searchField.' = '.$rowValues['id_zone'].' OR ';

        if ($tmp == null)
            $tmp = $searchField.' = '.$defaultZone;
        else
            $tmp = Tools::substr($tmp, 0, Tools::strlen($tmp) - Tools::strlen(' OR '));

        return $tmp;
    }

    public function hookPaymentReturn()
    {
        if (!$this->active)
            return null;
        return $this->display($this->_path, '/views/templates/hook/confirmation.tpl');
    }

    public function isPaymentPossible()
    {
        $currency = new Currency($this->getModuleCurrency($this->context->cart));
        $hipayAccount = Configuration::get('HIPAY_ACCOUNT_'.$currency->iso_code);
        $hipayPassword = Configuration::get('HIPAY_PASSWORD_'.$currency->iso_code);
        $hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency->iso_code);
        $hipayCategory = Configuration::get('HIPAY_CATEGORY_'.$currency->iso_code);

        # Restructuration du return
        return array(
            $hipayAccount,
            $hipayPassword,
            $hipaySiteId,
            $hipayCategory,
            Configuration::get('HIPAY_RATING'),
            $this->context->cart->getOrderTotal() >= 2
        );
    }

    public function hookPayment($params)
    {
        $redirectionUrl = '';
        $logo_suffix = Tools::strtoupper(Configuration::get('HIPAY_PAYMENT_BUTTON'));
        if (!in_array($logo_suffix, array('DE', 'FR', 'GB', 'BE', 'ES', 'IT', 'NL', 'PT', 'BR')))
            $logo_suffix = 'DEFAULT';
        # Restructuration du return
        $isPay = $this->isPaymentPossible();
        if ($isPay[0] && $isPay[1] && $isPay[2] && $isPay[3] && $isPay[4] && $isPay[5])
        {
            if (Tools::getIsset('hipay_error') && Tools::getValue('hipay_error') == 1)
            {
                if (_PS_VERSION_ < '1.5'){
                    $this->smarty->assign('errors', array($this->l('An error has occurred during your payment, please try again.')));
                } else {
                    Context::getContext()->controller->errors[] = $this->l('An error has occurred during your payment, please try again.');
                }
            }
            $this->smarty->assign('hipay_prod', $this->env);
            $this->smarty->assign('logo_suffix', $logo_suffix);
            if (_PS_VERSION_ < '1.5'){
                $redirectionUrl = Tools::getShopDomainSsl(true).__PS_BASE_URI__.'modules/'.$this->name.'/redirect.php';
            } else {
                $redirectionUrl = Context::getContext()->link->getModuleLink('hipay', 'redirect');
            }
            $this->smarty->assign(array(
                'this_path' => $this->_path,
                'redirection_url' => $redirectionUrl,
            ));
            return $this->display($this->_path,'views/templates/hook/payment.tpl');
        }
    }

    public function hookDisplayPaymentEU($params)
    {
        $isPay = $this->isPaymentPossible();

        if ($isPay[0] && $isPay[1] && $isPay[2] && $isPay[3] && $isPay[4] && $isPay[5])
        {
            $logo = $this->_path ."payment_button/EU.png";
            return array(
                'cta_text' => $this->l('Hipay'),
                'logo' => $logo,
                'action' => Tools::getShopDomainSsl(true).__PS_BASE_URI__.'modules/'.$this->name.'/redirect.php'
            );
        }
    }

    private function getModuleCurrency($cart)
    {
        $id_currency = (int)self::MysqlGetValue('SELECT id_currency FROM `'._DB_PREFIX_.'module_currency` WHERE id_module = '.(int)$this->id);

        if (!$id_currency OR $id_currency == -2)
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        elseif ($id_currency == -1)
            $id_currency = $cart->id_currency;

        return $id_currency;
    }

    private function formatLanguageCode($language_code)
    {
        $languageCodeArray = preg_split('/-|_/', $language_code);
        if (!isset($languageCodeArray[1]))
            $languageCodeArray[1] = $languageCodeArray[0];
        return Tools::strtolower($languageCodeArray[0]).'_'.Tools::strtoupper($languageCodeArray[1]);
    }

    public function payment()
    {

        if (!$this->active)
            return;

        // init cart
        $cart = $this->context->cart;

        $id_currency = (int)$this->getModuleCurrency($cart);
        // If the currency is forced to a different one than the current one, then the cart must be updated
        if ($cart->id_currency != $id_currency)
            if (Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'cart SET id_currency = '.(int)$id_currency.' WHERE id_cart = '.(int)$cart->id))
                $cart->id_currency = $id_currency;

        $currency = new Currency($id_currency);
        $language = new Language($cart->id_lang);
        $customer = new Customer($cart->id_customer);

        require_once(dirname(__FILE__).'/mapi/mapi_package.php');

        $hipayAccount = Configuration::get('HIPAY_ACCOUNT_'.$currency->iso_code);
        $hipayPassword = Configuration::get('HIPAY_PASSWORD_'.$currency->iso_code);
        $hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency->iso_code);
        $hipaycategory = Configuration::get('HIPAY_CATEGORY_'.$currency->iso_code);

        $paymentParams = new HIPAY_MAPI_PaymentParams();
        $paymentParams->setLogin($hipayAccount, $hipayPassword);
        $paymentParams->setAccounts($hipayAccount, $hipayAccount);
        // EN_us is not a standard format, but that's what Hipay uses
        if (isset($language->language_code))
            $paymentParams->setLocale($this->formatLanguageCode($language->language_code));
        else
            $paymentParams->setLocale(Tools::strtolower($language->iso_code).'_'.Tools::strtoupper($language->iso_code));
        $paymentParams->setMedia('WEB');
        $paymentParams->setRating(Configuration::get('HIPAY_RATING'));
        $paymentParams->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
        $paymentParams->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
        $paymentParams->setCurrency(Tools::strtoupper($currency->iso_code));
        $paymentParams->setIdForMerchant($cart->id);
        $paymentParams->setMerchantSiteId($hipaySiteId);
        $paymentParams->setIssuerAccountLogin(Context::getContext()->customer->email);

        if (_PS_VERSION_ < '1.5'){
            $paymentParams->setUrlCancel(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order.php?step=3');
            $paymentParams->setUrlNok(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order.php?step=3&hipay_error=1');
            $paymentParams->setUrlOk(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&id_module='.(int)$this->id.'&key='.$customer->secure_key);
            $paymentParams->setUrlAck(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'modules/'.$this->name.'/validation.php?token='.Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT')));
        }else{
            $paymentParams->setUrlCancel(Context::getContext()->link->getPageLink('order', null, null, array('step' => 3)));
            $paymentParams->setUrlNok(Context::getContext()->link->getPageLink('order', null, null, array('step' => 3, 'hipay_error' => 1)));
            $paymentParams->setUrlOk(Context::getContext()->link->getPageLink('order-confirmation', null, null, array('id_cart' => (int)$cart->id, 'id_module' => (int)$this->id, 'key' => $customer->secure_key)));
            $paymentParams->setUrlAck(Context::getContext()->link->getModuleLink('hipay', 'validation', array('token' => Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT')))));
        }

        #
        # Patch transfert du logo vers la page de paiement
        # Le 16/11/2015
        #
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            // Si le site utilise le protocol HTTPS alors on envoit l'URL avec HTTPS
            if (_PS_VERSION_ < '1.5') {
                $logo_url = Tools::getShopDomainSsl(true) . _PS_IMG_ . Configuration::get('PS_LOGO');
            } else {
                $logo_url = $this->context->link->getMediaLink(_PS_IMG_ . Configuration::get('PS_LOGO'));
            }
            $paymentParams->setLogoUrl($logo_url);
        }
        # ------------------------------------------------
        $paymentParams->setBackgroundColor('#FFFFFF');

        if (!$paymentParams->check())
            return $this->l('[Hipay] Error: cannot create PaymentParams');

        $item = new HIPAY_MAPI_Product();
        $item->setName($this->l('Cart'));
        $item->setInfo('');
        $item->setquantity(1);
        $item->setRef($cart->id);
        $item->setCategory($hipaycategory);
        $item->setPrice($cart->getOrderTotal());

        try {
            if (!$item->check())
                return $this->l('[Hipay] Error: cannot create "Cart" Product');
        } catch (Exception $e) {
            return $this->l('[Hipay] Error: cannot create "Cart" Product');
        }

        $items = array($item);

        $order = new HIPAY_MAPI_Order();
        $order->setOrderTitle($this->l('Order total'));
        $order->setOrderCategory($hipaycategory);

        if (!$order->check())
            return $this->l('[Hipay] Error: cannot create Order');

        try {
            $commande = new HIPAY_MAPI_SimplePayment($paymentParams, $order, $items);
        } catch (Exception $e) {
            return $this->l('[Hipay] Error:').' '.$e->getMessage();
        }

        $xmlTx = $commande->getXML();
        $output = HIPAY_MAPI_SEND_XML::sendXML($xmlTx);
        $reply = HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, $url, $err_msg, $err_keyword, $err_value, $err_code);

        if ($reply === true) {
            Tools::redirectLink($url);
        } else {
            if (_PS_VERSION_ < '1.5') {
                include(dirname(__FILE__) . '/../../header.php');
                $this->smarty->assign('errors', array('[Hipay] ' . strval($err_msg) . ' (' . $output . ')'));
                $_SERVER['HTTP_REFERER'] = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'order.php?step=3';
                $this->smarty->display(_PS_THEME_DIR_ . 'errors.tpl');
                include(dirname(__FILE__) . '/../../footer.php');
            } else {
                Context::getContext()->controller->errors[] = '[Hipay] ' . strval($err_msg) . ' (' . $output . ')';
                $_SERVER['HTTP_REFERER'] = Context::getContext()->link->getPageLink('order', true, null, array('step' => 3));
            }
        }
        return $reply;
    }

    public function validation()
    {
        # LOG
        $message = '######################################'."\r\n";
        $message .= '# Date Début Validation - ' . date("d/m/Y H:i:s") ."\r\n";
        $message .= '#### Module actif - ' . ($this->active ? 'TRUE':'FALSE')."\r\n";
        $message .= '#### Variable POST :'."\r\n";
        $message .= print_r($_POST, true);
        $message .= "\r\n";
        # ---
        $this->HipayLog($message);
        if (!$this->active)
            return;

        if (!array_key_exists('xml', $_POST))
            return;

        if (_PS_MAGIC_QUOTES_GPC_)
            $_POST['xml'] = Tools::stripslashes(Tools::getValue('xml'));

        require_once(dirname(__FILE__).'/mapi/mapi_package.php');

        # LOG
        $this->HipayLog('#### Début HIPAY_MAPI_COMM_XML::analyzeNotificationXML'."\r\n");
        # ---

        if (HIPAY_MAPI_COMM_XML::analyzeNotificationXML(Tools::getValue('xml'), $operation, $status, $date, $time, $transid, $amount, $currency, $id_cart, $data) === false)
        {
            file_put_contents('logs'.Configuration::get('HIPAY_UNIQID').'.txt', '['.date('Y-m-d H:i:s').'] Analysis error: '.htmlentities(Tools::getValue('xml'))."\n", FILE_APPEND);
            return false;
        }

        # LOG
        $message = '#### Fin HIPAY_MAPI_COMM_XML::analyzeNotificationXML'."\r\n";
        $message .= '#### Version Prestashop : ' . _PS_VERSION_;
        # ---
        $this->HipayLog($message);

        if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
        {
            # LOG
            $this->HipayLog('#### ID Panier : ' . (int)$id_cart."\r\n");
            # ---
            Context::getContext()->cart = new Cart((int)$id_cart);
        }

        $cart = new Cart((int)$id_cart);

        # LOG
        $message = '#### TOKEN : ' . Tools::getValue('token')."\r\n";
        $message .= '#### SECURE KEY : ' . $cart->secure_key."\r\n";
        $message .= '#### HIPAY SALT : ' . Configuration::get('HIPAY_SALT')."\r\n";
        $message .= '#### CLE ENCRYPTE : ' . Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT'))."\r\n";
        # ---
        $this->HipayLog($message);
        if (Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT')) != Tools::getValue('token'))
        {
            # LOG
            $this->HipayLog('#### TOKEN = CLE : NOK'."\r\n");
            # ---
            file_put_contents('logs'.Configuration::get('HIPAY_UNIQID').'.txt', '['.date('Y-m-d H:i:s').'] Token error: '.htmlentities(Tools::getValue('xml'))."\n", FILE_APPEND);
        } else {

            # LOG
            $message = '#### Opération : ' . trim($operation) ."\r\n";
            $message .= '#### Status : ' . trim(Tools::strtolower($status)) ."\r\n";
            # ---
            $this->HipayLog($message);
            if (trim($operation) == 'authorization' && trim(Tools::strtolower($status)) == 'waiting')
            {
                // Authorization WAITING
                $orderMessage = $operation.": ".$status."\ndate: ".$date." ".$time."\ntransaction: ".$transid."\namount: ".(float)$amount." ".$currency."\nid_cart: ".(int)$id_cart;
                //$this->_createAuthorizationOrderState();
                $this->validateOrder((int)$id_cart, Configuration::get('HIPAY_WAITINGPAYMENT_OS'), (float)$amount, $this->displayName, $orderMessage, array(), NULL, false, $cart->secure_key);

                # LOG
                $this->HipayLog('######## AW - création Commande / status : ' . (int)Configuration::get('HIPAY_WAITINGPAYMENT_OS') . "\r\n");
                # ---

            }else if (trim($operation) == 'authorization' && trim(Tools::strtolower($status)) == 'ok')
            {
                // vérification si commande existante
                $id_order = Order::getOrderByCartId((int)$id_cart);

                # LOG
                $this->HipayLog('######## AOK - ID Commande : ' . ($id_order ? $id_order:'Pas de commande') . "\r\n");
                # ---

                if ($id_order !== false)
                {
                    // change statut si commande en attente de paiement
                    $order = new Order((int)$id_order);
                    if ((int)$order->getCurrentState() == (int)Configuration::get('HIPAY_WAITINGPAYMENT_OS'))
                    {
                        // on affecte à la commande au statut paiement autorisé par HiPay
                        $statut_id = Configuration::get('HIPAY_AUTHORIZATION_OS');
                        $order_history = new OrderHistory();
                        $order_history->id_order = $id_order;
                        $order_history->changeIdOrderState($statut_id, $id_order);
                        $order_history->addWithemail();
                        # LOG
                        $this->HipayLog('######## AOK - Historique Commande / Change status : ' . (int)Configuration::get('HIPAY_AUTHORIZATION_OS') . "\r\n");
                        # ---
                    }
                }else{
                    // on revérifie si la commande n'existe pas au cas où la capture soit arrivée avant
                    // sinon on ne fait rien
                    $id_order = Order::getOrderByCartId((int)$id_cart);
                    if ($id_order === false)
                    {
                        // Authorization OK
                        $orderMessage = $operation.": ".$status."\ndate: ".$date." ".$time."\ntransaction: ".$transid."\namount: ".(float)$amount." ".$currency."\nid_cart: ".(int)$id_cart;
                        //$this->_createAuthorizationOrderState();
                        $this->validateOrder((int)$id_cart, Configuration::get('HIPAY_AUTHORIZATION_OS'), (float)$amount, $this->displayName, $orderMessage, array(), NULL, false, $cart->secure_key);

                        # LOG
                        $this->HipayLog('######## AOK - création Commande / status : ' . (int)Configuration::get('HIPAY_AUTHORIZATION_OS') . "\r\n");
                        # ---

                    }
                }
            }
            else if (trim($operation) == 'capture' && trim(Tools::strtolower($status)) == 'ok')
            {
                // Capture OK
                $orderMessage = $operation.": ".$status."\ndate: ".$date." ".$time."\ntransaction: ".$transid."\namount: ".(float)$amount." ".$currency."\nid_cart: ".(int)$id_cart;
                $id_order = Order::getOrderByCartId((int)$id_cart);

                # LOG
                $this->HipayLog('######## COK - ID Commande : ' . ($id_order ? $id_order:'Pas de commande') . "\r\n");
                # ---

                if ($id_order !== false)
                {
                    # LOG
                    $this->HipayLog('######## COK - id_order existant' . "\r\n");
                    # ---
                    $order = new Order((int)$id_order);
                    # LOG
                    $this->HipayLog('######## COK - objet order loadé' . "\r\n");
                    # ---
                    // si la commande est au statut Autorisation ok ou en attente de paiement
                    // on change le statut en paiement accepté
                    if ((int)$order->getCurrentState() == (int)Configuration::get('HIPAY_AUTHORIZATION_OS')
                        || (int)$order->getCurrentState() == (int)Configuration::get('HIPAY_WAITINGPAYMENT_OS'))
                    {
                        $statut_id = Configuration::get('PS_OS_PAYMENT');
                        $order_history = new OrderHistory();
                        $order_history->id_order = $id_order;
                        $order_history->changeIdOrderState($statut_id, $id_order);

                        $order_history->addWithemail();

                        # LOG
                        $this->HipayLog('######## COK - Historique Commande / Change status : ' . (int)Configuration::get('PS_OS_PAYMENT') . "\r\n");
                        # ---

                    }
                }
                else
                {
                    $this->validateOrder((int)$id_cart, Configuration::get('PS_OS_PAYMENT'), (float)$amount, $this->displayName, $orderMessage, array(), NULL, false, $cart->secure_key);

                    # LOG
                    $this->HipayLog('######## COK - création Commande / status : ' . (int)Configuration::get('PS_OS_PAYMENT') . "\r\n");
                    # ---
                }
                // Commande que prestashop lance mais n'a aucune incidence dans le module...
                // Ajouté en commentaire
                // Configuration::updateValue('HIPAY_CONFIGURATION_OK', true);
            }
            else if (trim($operation) == 'capture' && trim(Tools::strtolower($status)) == 'nok')
            {
                // Capture NOK
                $id_order = Order::getOrderByCartId((int)$id_cart);

                # LOG
                $this->HipayLog('######## CNOK - ID Commande : ' . ($id_order ? $id_order:'Pas de commande') . "\r\n");
                # ---

                if ($id_order !== false)
                {
                    $order = new Order((int)$id_order);
                    if ((int)$order->getCurrentState() == (int)Configuration::get('HIPAY_AUTHORIZATION_OS'))
                    {
                        $statut_id = Configuration::get('PS_OS_ERROR');
                        $order_history = new OrderHistory();
                        $order_history->id_order = $id_order;
                        $order_history->changeIdOrderState($statut_id, $id_order);

                        $order_history->addWithemail();

                        # LOG
                        $this->HipayLog('######## CNOK - Historique Commande / Change status : ' . (int)Configuration::get('PS_OS_ERROR') . "\r\n");
                        # ---
                    }
                }
            }
            elseif (trim($operation) == 'refund' AND trim(Tools::strtolower($status)) == 'ok')
            {
                /* Paiement remboursé sur Hipay */
                if (!($id_order = Order::getOrderByCartId((int)($id_cart))))
                    die(Tools::displayError());

                $order = new Order((int)($id_order));

                if (!$order->valid OR $order->getCurrentState() === Configuration::get('PS_OS_REFUND'))
                    die(Tools::displayError());

                $statut_id = Configuration::get('PS_OS_REFUND');
                $order_history = new OrderHistory();
                $order_history->id_order = $id_order;
                $order_history->changeIdOrderState($statut_id, $id_order);

                $order_history->addWithemail();

                # LOG
                $this->HipayLog('######## ROK - Historique Commande / Change status : ' . (int)Configuration::get('PS_OS_REFUND') . "\r\n");
                # ---
            }
        }
        #
        # Patch LOG Pour les erreurs 500
        #
        $message = '# Date Fin Validation - ' . date("d/m/Y H:i:s") ."\r\n";
        $message .= '######################################'."\r\n";
        $this->HipayLog($message);
        # ---------------------------------------------------------
        return true;
    }

    /**
     * Uninstall and clean the module settings
     *
     * @return	bool
     */
    public function uninstall()
    {
        parent::uninstall();

        $result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');

        foreach ($result as $rowValues)
        {
            Configuration::deleteByName('HIPAY_AZ_'.$rowValues['id_zone']);
            Configuration::deleteByName('HIPAY_AZ_ALL_'.$rowValues['id_zone']);
        }
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);

        return (true);
    }

    public function getContent()
    {
        $warnings = '';
        $shopId = false;

        if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
            $currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));
            $shopId = Context::getContext()->shop->id;
        } else {
            $currentIndex = Tools::safeOutput($_SERVER['REQUEST_URI']);
        }
        # -------------------------------------------------
        $currencies = DB::getInstance()->ExecuteS('SELECT c.iso_code, c.name, c.sign FROM '._DB_PREFIX_.'currency c');

        if (Tools::isSubmit('submitHipayAZ'))
        {
            // Delete all configurated zones
            foreach ($_POST as $key => $val)
            {
                if (strncmp($key, 'HIPAY_AZ_ALL_', Tools::strlen('HIPAY_AZ_ALL_')) == 0)
                {
                    $id = Tools::substr($key, -(Tools::strlen($key) - Tools::strlen('HIPAY_AZ_ALL_')));
                    Configuration::updateValue('HIPAY_AZ_'.$id, 'ko');
                }
            }
            #
            # Patch Prise en compte du shop id si PS >= 1.5.0.0
            # Le 16/11/2015
            #
            $reqZone = 'DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id;
            if($shopId)
            {
                $reqZone .= ' AND `id_shop` = '.(int)$shopId;
            }
            Db::getInstance()->Execute($reqZone);
            # -------------------------------------------------
            // Add the new configuration zones
            foreach ($_POST as $key => $val)
            {
                if (strncmp($key, 'HIPAY_AZ_', Tools::strlen('HIPAY_AZ_')) == 0)
                    Configuration::updateValue($key, 'ok');
            }
            $request = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE ';
            $results = Db::getInstance()->ExecuteS($request.$this->getRequestZones('id_zone'));

            #
            # Patch Prise en compte du shop id si PS >= 1.5.0.0
            # Le 16/11/2015
            #
            foreach ($results as $rowValues){
                Db::getInstance()->Execute('
					INSERT INTO '._DB_PREFIX_.'module_country VALUE 
					('.(int)$this->id.', '.($shopId !== false ?  $shopId.',' : '').' '.(int)$rowValues['id_country'].')');
            }
            # -------------------------------------------------
        }
        elseif (Tools::isSubmit('submitHipay'))
        {

            $accounts = array();
            foreach ($currencies as $currency)
            {
                if (Configuration::get('HIPAY_SITEID_'.$currency['iso_code']) != Tools::getValue('HIPAY_SITEID_'.$currency['iso_code']))
                    Configuration::updateValue('HIPAY_CATEGORY_'.$currency['iso_code'], false);

                Configuration::updateValue('HIPAY_PASSWORD_'.$currency['iso_code'], trim(Tools::getValue('HIPAY_PASSWORD_'.$currency['iso_code'])));
                Configuration::updateValue('HIPAY_SITEID_'.$currency['iso_code'], trim(Tools::getValue('HIPAY_SITEID_'.$currency['iso_code'])));
                Configuration::updateValue('HIPAY_CATEGORY_'.$currency['iso_code'], Tools::getValue('HIPAY_CATEGORY_'.$currency['iso_code']));
                Configuration::updateValue('HIPAY_ACCOUNT_'.$currency['iso_code'], Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code']));

                if ($this->env AND Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code']))
                    $accounts[Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code'])] = 1;
            }

            $i = 1;
            $dataSync = 'http://www.prestashop.com/modules/hipay.png?mode='.($this->env ? 'prod' : 'test');
            foreach ($accounts as $account => $null)
                $dataSync .= '&account'.($i++).'='.urlencode($account);

            Configuration::updateValue('HIPAY_RATING', Tools::getValue('HIPAY_RATING'));

            $warnings .= $this->displayConfirmation($this->l('Configuration updated').'<img src="'.$dataSync.'" style="float:right" />');
        }
        elseif (Tools::isSubmit('submitHipayPaymentButton'))
        {
            Configuration::updateValue('HIPAY_PAYMENT_BUTTON', Tools::getValue('payment_button'));
        }

        // Check configuration
        $allow_url_fopen = ini_get('allow_url_fopen');
        $openssl = extension_loaded('openssl');
        $curl = extension_loaded('curl');
        $ping = ($allow_url_fopen AND $openssl AND $fd = fsockopen('payment.hipay.com', 443) AND fclose($fd));
        $online = (in_array(Tools::getRemoteAddr(), array('127.0.0.1', '::1')) ? false : true);
        $categories = true;
        $categoryRetrieval = true;

        foreach ($currencies as $currency)
        {
            $hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency['iso_code']);
            $hipayAccountId = Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code']);
            if ($hipaySiteId && $hipayAccountId && !count($this->getHipayCategories($hipaySiteId, $hipayAccountId)))
                $categoryRetrieval = false;

            if ((Configuration::get('HIPAY_SITEID_'.$currency['iso_code']) && !Configuration::get('HIPAY_CATEGORY_'.$currency['iso_code'])))
                $categories = false;
        }

        if (!$allow_url_fopen OR !$openssl OR !$curl OR !$ping OR !$categories OR !$categoryRetrieval OR !$online)
        {
            $warnings .= '
			<div class="warning warn">
				'.($allow_url_fopen ? '' : '<h3>'.$this->l('You are not allowed to open external URLs').'</h3>').'
				'.($curl ? '' : '<h3>'.$this->l('cURL is not enabled').'</h3>').'
				'.($openssl ? '' : '<h3>'.$this->l('OpenSSL is not enabled').'</h3>').'
				'.(($allow_url_fopen AND $openssl AND !$ping) ? '<h3>'.$this->l('Cannot access payment gateway').' '.HIPAY_GATEWAY_URL.' ('.$this->l('check your firewall').')</h3>' : '').'
				'.($online ? '' : '<h3>'.$this->l('Your shop is not online').'</h3>').'
				'.($categories ? '' : '<h3>'.$this->l('Hipay categories are not defined for each Site ID').'</h3>').'
				'.($categoryRetrieval ? '' : '<h3>'.$this->l('Impossible to retrieve Hipay categories. Please refer to your error log for more details.').'</h3>').'
			</div>';
        }

        // Get subscription form value
        $form_values = $this->getFormValues();

        // Lang of the button
        $iso_code = Context::getContext()->language->iso_code;
        if (!in_array($iso_code, array('fr', 'en', 'es', 'it')))
            $iso_code = 'en';

        $form_errors = '';
        $account_created = false;
        if (Tools::isSubmit('create_account_action'))
            $account_created = $this->processAccountCreation($form_errors);

        $link = Tools::safeOutput($_SERVER['REQUEST_URI']);

        if (_PS_VERSION_ >= '1.5') {
            $form = $this->displayform1516($link,$account_created, $iso_code, $warnings, $form_errors, $currentIndex, $form_values, $currencies, $ping, $hipaySiteId, $hipayAccountId);
        } else {
            $form = $this->displayform14($link,$account_created, $iso_code, $warnings, $form_errors, $currentIndex, $form_values, $currencies, $ping, $hipaySiteId, $hipayAccountId);
        }



        if ($this->ws_client == false)
            return $this->displayError('To work properly the module need the Soap library to be installed.').$form;
        return $form;
    }

    public static function getWsClient()
    {
        $ws_client = null;
        if (class_exists('SoapClient'))
        {
            if (is_null($ws_client))
            {
                $options = array(
                    'location' => self::WS_URL,
                    'uri' => self::WS_SERVER
                );
                $ws_client = new SoapClient(null, $options);
            }
            return $ws_client;
        }
        return false;
    }

    protected function getFormValues()
    {
        $values = array();

        if (Tools::isSubmit('email'))
            $values['email'] = Tools::getValue('email');
        else
            $values['email'] = Configuration::get('PS_SHOP_EMAIL');

        if (Tools::isSubmit('firstname'))
            $values['firstname'] = Tools::getValue('firstname');
        else
            $values['firstname'] = Context::getContext()->employee->firstname;

        if (Tools::isSubmit('lastname'))
            $values['lastname'] = Tools::getValue('lastname');
        else
            $values['lastname'] = Context::getContext()->employee->lastname;

        $values['currency'] = Tools::getValue('currency');

        if (Tools::isSubmit('contact-email'))
            $values['contact_email'] = Tools::getValue('contact-email');
        else
            $values['contact_email'] = Configuration::get('PS_SHOP_EMAIL');

        if (Tools::isSubmit('website-name'))
            $values['website_name'] = Tools::getValue('website-name');
        else
            $values['website_name'] = Configuration::get('PS_SHOP_NAME');

        if (Tools::isSubmit('website-url'))
            $values['website_url'] = Tools::getValue('website-url');
        else
            $values['website_url'] = Configuration::get('PS_SHOP_DOMAIN');

        $values['business_line'] = Tools::getValue('business-line');

        $values['password'] = Tools::getValue('website-password');

        return $values;
    }

    protected function getBusinessLine()
    {
        try
        {
            $iso_lang = Context::getContext()->language->iso_code;
            $format_language = $this->formatLanguageCode($iso_lang);

            if ($this->ws_client !== false)
                $business_line = $this->ws_client->getBusinessLine($format_language);
        }
        catch (Exception $e)
        {
            return array();
        }

        if (isset($business_line) && ($business_line !== false))
            return $business_line;
        return array();
    }

    protected function processAccountCreation(&$form_errors)
    {
        $form_values = $this->getFormValues();

        // STEP 1: Check if the email is available in Hipay
        try
        {
            if ($this->ws_client !== false)
                $is_available = $this->ws_client->isAvailable($form_values['email']);
        }
        catch (Exception $e)
        {
            $form_errors = $this->l('Could not connect to host');
            return false;
        }

        if (!$is_available)
        {
            $form_errors = $this->l('An account already exists with this email address');
            return false;
        }

        // STEP 2: Account creation
        try
        {
            if ($this->ws_client !== false)
                $return = $this->ws_client->createWithWebsite(
                    array(
                        'email' => $form_values['email'],
                        'firstname' => $form_values['firstname'],
                        'lastname' => $form_values['lastname'],
                        'currency' => $form_values['currency'],
                        'locale' => $this->formatLanguageCode(Context::getContext()->language->language_code),
                        'ipAddress' => $_SERVER['REMOTE_ADDR'],
                        'websiteBusinessLineId' => $form_values['business_line'],
                        'websiteTopicId' => Tools::getValue('website-topic'),
                        'websiteContactEmail' => $form_values['contact_email'],
                        'websiteName' => $form_values['website_name'],
                        'websiteUrl' => $form_values['website_url'],
                        'websiteMerchantPassword' => $form_values['password']
                    ));
        }
        catch (Exception $e)
        {
            $form_errors = $this->l('Could not connect to host');
            return false;
        }

        if ($return !== false)
        {
            if ($return['error'] == 0)
            {
                Configuration::updateValue('HIPAY_ACCOUNT_'.$form_values['currency'], $return['account_id']);
                Configuration::updateValue('HIPAY_PASSWORD_'.$form_values['currency'], Tools::getValue('website-password'));
                Configuration::updateValue('HIPAY_SITEID_'.$form_values['currency'], $return['site_id']);
                return true;
            }

            if ($return['code'] == 1)
            {
                $fields = array(
                    'firstname' => $this->l('firstname'),
                    'lastname' => $this->l('lastname'),
                    'email' => $this->l('email'),
                    'currency' => $this->l('currency'),
                    'websiteBusinessLineId' => $this->l('business line'),
                    'websiteTopicId' => $this->l('website topic'),
                    'websiteContactEmail' => $this->l('website contact email'),
                    'websiteName' => $this->l('website name'),
                    'websiteUrl' => $this->l('website url'),
                    'websiteMerchantPassword' => $this->l('website merchant password'),
                );
                $fieldnames_error = array();
                foreach ($return['vars'] as $fieldtype)
                    if(isset($fields[$fieldtype]))
                        $fieldnames_error[] = $fields[$fieldtype];

                $form_errors = sprintf($this->l('Some fields are not correct. Please check the fields: %s'), implode(', ', $fieldnames_error));
                return false;
            }
            elseif ($return['code'] == 2)
            {
                $form_errors = sprintf($this->l('An error occurs durring the account creation: %s'), Tools::htmlentitiesUTF8($return['description']));
                return false;
            }
        }
        $form_errors = $this->l('Unknow error');
        return false;
    }

    private function getHipayCategories($hipaySiteId, $hipayAccountId)
    {
        try
        {
            if ($this->ws_client !== false)
                return $this->ws_client->getCategoryList(array('site_id' => $hipaySiteId, 'account_id' => $hipayAccountId));
        }
        catch (Exception $e)
        {
            return array();
        }
        return array();
    }
    public function getLocalizedRatesPDFLink()
    {
        $shop_iso_country_id = Configuration::get('PS_COUNTRY_DEFAULT');
        $shop_iso_country = Country::getIsoById((int)$shop_iso_country_id);
        $shop_iso_country = Tools::strtoupper($shop_iso_country);

        if (!$shop_iso_country || !in_array($shop_iso_country, Hipay::$available_rates_links)) {
            $shop_iso_country = 'EN';
        }

        $localized_link = Hipay::PAYMENT_FEED_BASE_LINK.$shop_iso_country.'.pdf';

        return $localized_link;
    }
    // Retro compatibility with 1.2.5
    static private function MysqlGetValue($query)
    {
        $row = Db::getInstance()->getRow($query);
        return array_shift($row);
    }
    #
    # Patch pour logger la validation.php - appel entre HiPay et Prestashop
    # Le 16/11/2015
    #
    private function HipayLog($msg){
        if(HIPAY_LOG){
            $fp = fopen(_PS_ROOT_DIR_.'/modules/hipay/log/hipaylogs.txt','a+');
            fseek($fp,SEEK_END);
            fputs($fp,$msg);
            fclose($fp);
        }
    }

    # form content
    public function displayform14($link,$account_created,$iso_code,$warnings,$form_errors,$currentIndex,$form_values,$currencies,$ping,$hipaySiteId,$hipayAccountId){
        $form = '
            <style>
                .hipay_label {float:none;font-weight:normal;padding:0;text-align:left;width:100%;line-height:30px}
                .hipay_help {vertical-align:middle}
                #hipay_table {border:1px solid #383838}
                #hipay_table td {border:1px solid #383838; width:250px; padding-left:8px; text-align:center}
                #hipay_table td.hipay_end {border-top:none}
                #hipay_table td.hipay_block {border-bottom:none}
                #hipay_steps_infos {border:none; margin-bottom:20px}
                /*#hipay_steps_infos td {border:none; width:70px; height:60px;padding-left:8px; text-align:left}*/
                #hipay_steps_infos td.tab2 {border:none; width:700px;; height:60px;padding-left:8px; text-align:left}
                #hipay_steps_infos td.hipay_end {border-top:none}
                #hipay_steps_infos td.hipay_block {border-bottom:none}
                #hipay_steps_infos td.hipay_block {border-bottom:none}
                #hipay_steps_infos .account-creation input[type=text], #hipay_steps_infos .account-creation select {width: 300px; margin-bottom: 5px}
                .hipay_subtitle {color: #777; font-weight: bold}
                #hipay-direct fieldset, #hipay-direct .row{background-color: #FFF!important;}
            </style>
            <div id="hipay-direct">
            <fieldset>
            <legend><img src="../modules/'.$this->name.'/views/img/logo.gif" /> HiPay</legend>
            '.$warnings.'
            <div id="psphipay-marketing-content" class="collapse">
                <div class="row">
                    <hr>
                    <div class="col-md-6">
                        <h4>'.$this->l('From 1% + €0.25 per transaction!').'</h4>
                        <ul class="ul-spaced">
                            <li>'.$this->l('A rate that adapts to your volume of activity').'</li>
                            <li>'.$this->l('15% less expensive than leading solutions in the market*').'</li>
                            <li>'.$this->l('No registration, installation or monthly fee').'</li>
                        </ul>
                    </div>
    
                    <div class="col-md-6">
                        <h4>'.$this->l('A complete and easy to use solution').'</h4>
                        <ul class="ul-spaced">
                            <li>'.$this->l('Start now, no contract required').'</li>
                            <li>'.$this->l('Accept 8 currencies with 15+ local payment solutions in Europe').'</li>
                            <li>'.$this->l('Anti-fraud system and full-time monitoring of high-risk behavior').'</li>
                        </ul>
                        <br>
                        <a class="_blank" href="'.$this->getLocalizedRatesPDFLink().'" target="_blank">
                            '.$this->l('*See the complete list of rates for PrestaShop Payments by HiPay').'
                        </a>
                    </div>
                </div>
    
                <hr>
    
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <h4>'.$this->l('Accept payments from all over the world in just a few clicks').'</h4>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-12 col-xs-12 text-center">
                        <img id="cards-logo" src="../modules/'.$this->name.'/views/img/cards.jpg">
                    </div>
                </div>
    
                <hr>
    
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <h4>'.$this->l('3 simple steps:').'</h4>
                        <ol>
                            <li>'.$this->l('Download the HiPay free module').'</li>
                            <li>'.$this->l('Finalize your PrestaShop Payments by HiPay registration before you reach €2,500 on your account.').'</li>
                            <li>'.$this->l('Easily collect and transfer your money from your PrestaShop Payments by HiPay account to your own bank account.').'</li>
                        </ol>
                    </div>
                </div>
            </div>
            </fieldset>
            <div class="clear">&nbsp;</div>
            <fieldset>
            <legend><img src="../modules/'.$this->name.'/views/img/logo.gif" /> '.$this->l('Configuration').'</legend>
            '.$this->l('The configuration of Hipay is really easy and runs into 3 steps').'<br /><br />
            <table id="hipay_steps_infos" cellspacing="0" cellpadding="0">
                '.($account_created ? '<tr><td></td><td><div class="conf">'.$this->l('Account created!').'</div></td></tr>' : '').'
                <tr>
                    <td valign="top" style="padding-top:6px;"><img src="../modules/'.$this->name.'/views/img/1.png" alt="step 1" /></td>
                    <td class="tab2">'.(Configuration::get('HIPAY_SITEID')
                    ? '<a href="https://www.hipay.com/auth" style="color:#D9263F;font-weight:700">'.$this->l('Log in to your merchant account').'</a><br />'
                    : '<a id="account_creation" href="https://www.hipay.com/registration/register" style="color:#D9263F;font-weight:700"><img src="../modules/'.$this->name.'/views/img/button_'.$iso_code.'.jpg" alt="'.$this->l('Create a Hipay account').'" title="'.$this->l('Create a Hipay account').'" border="0" /></a>
                        <br /><br />'.$this->l('If you already have an account you can go directly to step 2.')).'<br /><br />
                    </td>
                </tr>
                <tr id="account_creation_form" style="'.(!Tools::isSubmit('create_account_action') || $account_created ? 'display: none;': '').'">
                    <td></td>
                    <td class="tab2">';
        if (!empty($form_errors))
        {
            $form .= '<div class="warning warn">';
            $form .= $form_errors;
            $form .= '</div>';
        }

        $form .= '
					<form class="account-creation" action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
						<div class="clear"><label for="email">'.$this->l('E-mail').'</label><input type="text" value="'.$form_values['email'].'" name="email" id="email"/></div>
						<div class="clear"><label for="firstname">'.$this->l('Firstname').'</label><input type="text" value="'.$form_values['firstname'].'" name="firstname" id="firstname"/></div>
						<div class="clear"><label for="lastname">'.$this->l('Lastname').'</label><input type="text" value="'.$form_values['lastname'].'" name="lastname" id="lastname"/></div>
						<div class="clear">
							<label for="currency">'.$this->l('Currency').'</label>
							<select name="currency" id="currency">
								<option value="EUR">'.$this->l('Euro').'</option>
								<option value="CAD">'.$this->l('Canadian dollar').'</option>
								<option value="USD">'.$this->l('United States Dollar').'</option>
								<option value="CHF">'.$this->l('Swiss franc').'</option>
								<option value="AUD">'.$this->l('Australian dollar').'</option>
								<option value="GBP">'.$this->l('British pound').'</option>
								<option value="SEK">'.$this->l('Swedish krona').'</option>
							</select>
						</div>
						<div class="clear">
							<label for="business-line">'.$this->l('Business line').'</label>
							<select name="business-line" id="business-line">';
        foreach ($this->getBusinessLine() as $business)
            if ($business->id == $form_values['business_line'])
                $form .= '<option value="'.$business->id.'" selected="selected">'.$business->label.'</option>';
            else
                $form .= '<option value="'.$business->id.'">'.$business->label.'</option>';
        $form .= '
							</select>
						</div>
						<div class="clear">
							<label for="website-topic">'.$this->l('Website topic').'</label>
							<select id="website-topic" name="website-topic"></select>
						</div>
						<div class="clear"><label for="contact-email">'.$this->l('Website contact e-mail').'</label><input type="text" value="'.$form_values['contact_email'].'" name="contact-email" id="contact-email"/></div>
						<div class="clear"><label for="website-name">'.$this->l('Website name').'</label><input type="text" value="'.$form_values['website_name'].'" name="website-name" id="website-name"/></div>
						<div class="clear"><label for="website-url">'.$this->l('Website URL').'</label><input type="text" value="'.$form_values['website_url'].'" name="website-url" id="website-url"/></div>
						<div class="clear"><label for="website-password">'.$this->l('Website merchant password').'</label><input type="text"  value="'.$form_values['password'].'"name="website-password" id="website-password"/></div>
						<div class="clear"><input type="submit" name="create_account_action"/></div>
					</form>
				</td>
			</tr>
			<tr>
				<td><img src="../modules/'.$this->name.'/views/img/2.png" alt="step 2" /></td>
				<td class="tab2">'.$this->l('Activate the Hipay solution in your Prestashop, it\'s free!').'</td>
			</tr>
			<tr>
				<td></td>
				<td class="tab2">
				<p>'.$this->l('What you should do:').'</p>
				<ul>
					<li>'.$this->l('Set your account information (id account, password and id website).').'</li>
					<li>'.$this->l('Select the category and age group.').'</li>
					<li>'.$this->l('Set up an email address for notifications of payment.').'</li>
				</ul>
				<p>'.$this->l('For more information , go on the tab " how to set Hipay ".').'</p>
				</td>
			</tr>
			
			<tr><td></td><td>

		<form action="'.$link.'" method="post" style="padding-left:6px;">
		<table id="hipay_table" cellspacing="0" cellpadding="0">
			<tr>
				<td style="">&nbsp;</td>
				<td style="height:40px;">'.$this->l('HiPay account').'</td>
			</tr>';

        foreach ($currencies as $currency)
        {
            $form .= '<tr>
						<td class="hipay_block"><b>'.$this->l('Configuration in').' '.$currency['name'].' '.$currency['sign'].'</b></td>
						<td class="hipay_prod hipay_block" style="padding-left:10px">
							<label class="hipay_label" for="HIPAY_ACCOUNT_'.$currency['iso_code'].'">'.$this->l('Account number').' <a href="../modules/'.$this->name.'/views/img/screenshots/accountnumber.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_ACCOUNT_'.$currency['iso_code'].'" name="HIPAY_ACCOUNT_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code'], Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code']))).'" />
							<br /><p style="text-align: left !important;"><i>'.$this->l('The Hipay account ID where the website is registered. This is your main account.').'<br /> <span style="color:red">'.$this->l('Do not use your member Id here!.').'</span></i></p>
							<label class="hipay_label" for="HIPAY_PASSWORD_'.$currency['iso_code'].'">'.$this->l('Merchant password').' <a href="../modules/'.$this->name.'/views/img/screenshots/merchantpassword.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_PASSWORD_'.$currency['iso_code'].'" name="HIPAY_PASSWORD_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_PASSWORD_'.$currency['iso_code'], Configuration::get('HIPAY_PASSWORD_'.$currency['iso_code']))).'" />
							<br /><p style="text-align: left !important;"><i>'.$this->l('The password of the account on which the merchant website is registered.( this is not the ID password ).').'<br />
							<span style="color:red">'.$this->l('To create a new merchant password: Log in to your Hipay account, go to Payment Buttons where you can find the list of registered sites.').' '.$this->l('Click information website of the website concerned.').' '.$this->l('Enter your merchant password and click confirm').' '.$this->l('Remember to also enter your new password here.').'</span></i></p>
							<label class="hipay_label" for="HIPAY_SITEID_'.$currency['iso_code'].'">'.$this->l('Site ID').' <a href="../modules/'.$this->name.'/views/img/screenshots/siteid.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_SITEID_'.$currency['iso_code'].'" name="HIPAY_SITEID_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_SITEID_'.$currency['iso_code'], Configuration::get('HIPAY_SITEID_'.$currency['iso_code']))).'" />
							<br /><p style="text-align: left !important;"><i>'.$this->l('Website ID selected.').'<br />
							<span style="color:red">'.$this->l('For a website ID, register your store on the corresponding HiPay account.').' '.$this->l('You can find this option on your HiPay statement under Payments buttons.').'</span></i></p>';

            if ($ping && ($hipaySiteId = (int)Configuration::get('HIPAY_SITEID_'.$currency['iso_code'])) && ($hipayAccountId = (int)Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code'])))
            {
                $form .= '	<label for="HIPAY_CATEGORY_'.$currency['iso_code'].'" class="hipay_label">'.$this->l('Category').'</label><br />
							<select id="HIPAY_CATEGORY_'.$currency['iso_code'].'" name="HIPAY_CATEGORY_'.$currency['iso_code'].'">';
                foreach ($this->getHipayCategories($hipaySiteId, $hipayAccountId) as $id => $name)
                    $form.= '	<option value="'.(int)$id.'" '.(Tools::getValue('HIPAY_CATEGORY_'.$currency['iso_code'], Configuration::get('HIPAY_CATEGORY_'.$currency['iso_code'])) == $id ? 'selected="selected"' : '').'>'.htmlentities($name, ENT_COMPAT, 'UTF-8').'</option>';
                $form .= '	</select><br /><p style="text-align: left !important;"><i>'.$this->l('Choose the order type.').'<br /><span style="color:red">'.$this->l('A list of categories (based on the choice of business type and category of your site on Hipay ).').'<br />'.$this->l('1. Enter your website ID').'<br />'.$this->l('2. Click Save Settings').'<br />'.$this->l('The list " Order Type " will be updated.').'<br />'.$this->l('4. Choose the appropriate category and click Save Settings again.').'</span></i></p>';
            }

            $form .= '	</td>
					</tr>
					<tr><td class="hipay_end">&nbsp;</td><td class="hipay_prod hipay_end">&nbsp;</td>';
            $form .= '</tr>';
        }

        $form .= '</table>
				<hr class="clear" />
				<label for="HIPAY_RATING">'.$this->l('Authorized age group').' :</label>
				<div class="margin-form">
					<select id="HIPAY_RATING" name="HIPAY_RATING">
						<option value="ALL">'.$this->l('For all ages').'</option>
						<option value="+12" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+12' ? 'selected="selected"' : '').'>'.$this->l('For ages 12 and over').'</option>
						<option value="+16" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+16' ? 'selected="selected"' : '').'>'.$this->l('For ages 16 and over').'</option>
						<option value="+18" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+18' ? 'selected="selected"' : '').'>'.$this->l('For ages 18 and over').'</option>
					</select>
				</div>
				<hr class="clear" />
				<p>'.$this->l('Notice: please verify that the currency mode you have chosen in the payment tab is compatible with your Hipay account(s).').'</p>
				<input type="submit" name="submitHipay" value="'.$this->l('Update configuration').'" class="button" style="font-weight:bold;"/>
			</form>

				</td>
			</tr>
			<tr>
				<td><img src="../modules/'.$this->name.'/views/img/3.png" alt="step 3" /></td>
				<td class="tab2">'.$this->l('Choose a set of buttons for your shop Hipay').' :</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<form action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
						<table>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_be" value="be" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'be' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_be"><img src="../modules/'.$this->name.'/views/img/payment_button/BE.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_de" value="de" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'de' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_de"><img src="../modules/'.$this->name.'/views/img/payment_button/DE.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_fr" value="fr" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'fr' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_fr"><img src="../modules/'.$this->name.'/views/img/payment_button/FR.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_gb" value="gb" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'gb' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_gb"><img src="../modules/'.$this->name.'/views/img/payment_button/GB.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_it" value="it" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'it' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_it"><img src="../modules/'.$this->name.'/views/img/payment_button/IT.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_nl" value="nl" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'nl' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_nl"><img src="../modules/'.$this->name.'/views/img/payment_button/NL.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_pt" value="pt" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'pt' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_pt"><img src="../modules/'.$this->name.'/views/img/payment_button/PT.png" /></label>
								</td>
							</tr>
						</table>
						<input type="submit" name="submitHipayPaymentButton" value="'.$this->l('Update configuration').'" class="button" style="font-weight:bold;" />
					</form>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			function loadWebsiteTopic()
			{
				var locale = "'.$this->formatLanguageCode(Context::getContext()->language->iso_code).'";
				var business_line = $("#business-line").val();
				$.ajax(
				{
					type: "POST",
					url: "'.__PS_BASE_URI__.'modules/hipay/ajax_websitetopic.php",
					data:
					{
						locale: locale,
						business_line: business_line,
						token: "'.Tools::substr(Tools::encrypt('hipay/websitetopic'), 0, 10).'"
					},
					success: function(result)
					{
						$("#website-topic").html(result);
					}
				});
			}
			$("#business-line").change(function() { loadWebsiteTopic() });
			loadWebsiteTopic();
		</script>
		</fieldset>
		<br />
		';

        $form .= '
		<fieldset>
			<legend><img src="../modules/'.$this->name.'/views/img/logo.gif" /> '.$this->l('Zones restrictions').'</legend>
			'.$this->l('Select the authorized shipping zones').' :<br /><br />
			<form action="'.$currentIndex.'&configure=hipay&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th class="center">'.$this->l('ID').'</th>
						<th>'.$this->l('Zones').'</th>
						<th align="center"><img src="../modules/'.$this->name.'/views/img/logo.gif" /></th>
					</tr>
		';

        $result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM '._DB_PREFIX_.'zone
			WHERE `active` = 1
		');

        foreach ($result as $rowNumber => $rowValues)
        {
            $form .= '<tr>';
            $form .= '<td>'.$rowValues['id_zone'].'</td>';
            $form .= '<td>'.$rowValues['name'].'</td>';
            $chk = null;
            if (Configuration::get('HIPAY_AZ_'.$rowValues['id_zone']) == 'ok')
                $chk = "checked ";

            $form .= '<td align="center"><input type="checkbox" name="HIPAY_AZ_'.$rowValues['id_zone'].'" value="ok" '.$chk.'/>';
            $form .= '<input type="hidden" name="HIPAY_AZ_ALL_'.$rowValues['id_zone'].'" value="ok" /></td>';
            $form .= '</tr>';
        }

        $form .= '
				</table><br>
				<input type="submit" name="submitHipayAZ" value="'.$this->l('Update zones').'" class="button" style="font-weight:bold;" />
			</form>
		</fieldset>
		<script type="text/javascript">
			function switchHipayAccount(prod) {
				if (prod){';
        foreach ($currencies as $currency)
            $form .= '
                    $("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
                    $("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
                    $("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");';
        $form .= '	$(".hipay_prod").css("background-color", "#AADEAA");
                    $(".hipay_test").css("background-color", "transparent");
                    $(".hipay_prod_span").css("font-weight", "700");
                    $(".hipay_test_span").css("font-weight", "200");
                }
                else
                {';
        foreach ($currencies as $currency)
            $form .= '
                    $("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
                    $("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
                    $("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");';
        $form .= '	$(".hipay_prod").css("background-color", "transparent");
                    $(".hipay_test").css("background-color", "#AADEAA");
                    $(".hipay_prod_span").css("font-weight", "200");
                    $(".hipay_test_span").css("font-weight", "700");
                }
            }
            switchHipayAccount('.(int)$this->env.');';

        if (class_exists('SoapClient'))
        {
            $form .= '
				$(\'#account_creation\').click(function() {
					$(\'#account_creation_form\').show();
					return false;
				});
			';
        }

        $form .= '
		</script>
		</div>';

        return $form;
    }

    public function displayform1516($link,$account_created,$iso_code,$warnings,$form_errors,$currentIndex,$form_values,$currencies,$ping,$hipaySiteId,$hipayAccountId){
        $form = '
		<style>
			.hipay_label {float:none;font-weight:normal;padding:0;text-align:left;width:100%;line-height:30px}
			.hipay_help {vertical-align:middle}
			#hipay_table {border:1px solid #383838}
			#hipay_table td {border:1px solid #383838; padding-left:8px; text-align:center}
			#hipay_table td.hipay_end {border-top:none}
			#hipay_table td.hipay_block {border-bottom:none}
			#hipay_steps_infos {border:none; margin-bottom:20px}
			#hipay_steps_infos td.tab2 {border:none; width:700px;; height:60px;padding-left:8px; text-align:left}
			#hipay_steps_infos td.hipay_end {border-top:none}
			#hipay_steps_infos td.hipay_block {border-bottom:none}
			#hipay_steps_infos td.hipay_block {border-bottom:none}
			#hipay_steps_infos .account-creation input[type=text], #hipay_steps_infos .account-creation select {width: 300px; margin-bottom: 5px}
			.hipay_subtitle {color: #777; font-weight: bold}
			#main {padding-top:19px!important;}
			.clear-bis {float: left;margin-bottom: 15px;margin-right: 25px;width: calc(50% - 25px);}';
        if (_PS_VERSION_ < '1.6'){
            $form .= '#main {background-color: #EFF1F2;}
					  .bootstrap { padding:0px!important;}
			          .bootstrap label{width: initial;}
			          .bootstrap table{clear: both;}
			          .panel{clear: both;}
			          #employee_links {clear: both;}
			          #menu {margin-top:3px!important;}';
        }
        $form .= '</style>
		<!-- DEBUT NEW CSS -->
		<div id="hipaydirect">
		<div class="message">'.$warnings.'</div>
		<div class="panel">
			<div id="psphipay-header" class="row">
	            <div class="col-xs-12 col-sm-12 col-md-6 text-center">
	                <img id="payment-logo" src="../modules/'.$this->name.'/views/img/hipay_direct.png">
	            </div>
	            <div class="col-xs-12 col-sm-12 col-md-6 text-center">
	                <h4>'.$this->l('A complete and easy to use solution').'</h4>
	            </div>
	        </div>
	        <hr>
	        <!-- DEBUT CONTENU MARKETING -->
	        <div id="psphipay-content">
	            <div class="row">
	                <div class="col-md-12 col-xs-12">
	                    <p class="text-center">
	                        <a aria-controls="psphipay-marketing-content" aria-expanded="false" href="#psphipay-marketing-content" data-toggle="collapse" class="btn btn-primary collapsed">
	                            '.$this->l('More info').'
	                        </a>
	                    </p>
	                    <div id="psphipay-marketing-content" class="collapse" style="height: 0px;">
	                        <div class="row">
	                            <hr>
	                            <div class="col-md-6">
	                                <h4>'.$this->l('From 1% + €0.25 per transaction!').'</h4>
	                                <ul class="ul-spaced">
		                                <li>'.$this->l('A rate that adapts to your volume of activity').'</li>
	                                    <li>'.$this->l('15% less expensive than leading solutions in the market*').'</li>
	                                    <li>'.$this->l('No registration, installation or monthly fee').'</li>
	                                </ul>
	                            </div>

	                            <div class="col-md-6">
	                                <h4>'.$this->l('A complete and easy to use solution').'</h4>
	                                <ul class="ul-spaced">
	                                    <li>'.$this->l('Start now, no contract required').'</li>
	                                    <li>'.$this->l('Accept 8 currencies with 15+ local payment solutions in Europe').'</li>
	                                    <li>'.$this->l('Anti-fraud system and full-time monitoring of high-risk behavior').'</li>
	                                </ul>
	                                <br>
	                                <a class="_blank" href="'.$this->getLocalizedRatesPDFLink().'" target="_blank">
	                                    '.$this->l('*See the complete list of rates for PrestaShop Payments by HiPay').'
	                                </a>
	                            </div>
	                        </div>

	                        <hr>

	                        <div class="row">
	                            <div class="col-md-12 col-xs-12">
	                                <h4>'.$this->l('Accept payments from all over the world in just a few clicks').'</h4>
	                            </div>
	                        </div>

	                        <div class="row">
	                            <div class="col-md-12 col-xs-12 text-center">
	                                <img id="cards-logo" src="../modules/'.$this->name.'/views/img/cards.jpg">
	                            </div>
	                        </div>

	                        <hr>

	                        <div class="row">
	                            <div class="col-md-12 col-xs-12">
	                                <h4>'.$this->l('3 simple steps:').'</h4>
	                                <ol>
	                                    <li>'.$this->l('Download the HiPay free module', 'hipay-15-16').'</li>
	                                    <li>'.$this->l('Finalize your PrestaShop Payments by HiPay registration before you reach €2,500 on your account.').'</li>
	                                    <li>'.$this->l('Easily collect and transfer your money from your PrestaShop Payments by HiPay account to your own bank account.').'</li>
	                                </ol>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
		<!-- FIN CONTENU MARKETING -->
		<!-- DEBUT CONTENU CONFIGURATION -->
		<div role="tabpanel">
	        <ul role="tablist" class="nav nav-tabs">
	            <li class="active" role="presentation"><a data-toggle="tab" role="tab" aria-controls="hipay_step1" href="#hipay_step1">'.$this->l('Step 1 - Create an account HiPay').'</a>
	            </li>
	            <li role="presentation"><a data-toggle="tab" role="tab" aria-controls="hipay_step2" href="#hipay_step2">
	                '.$this->l('Step 2 - Identification').'</a>
	            </li>
	            <li role="presentation"><a data-toggle="tab" role="tab" aria-controls="hipay_step3" href="#hipay_step3">
	                '.$this->l('Step 3 - Select a payment button').'</a>
	            </li>
	            <li role="presentation"><a data-toggle="tab" role="tab" aria-controls="hipay_step4" href="#hipay_step4">
	                '.$this->l('Step 4 - Select Zones ').'</a>
	            </li>
	        </ul>

	        <div class="tab-content">
	            <div id="hipay_step1" class="tab-pane active" role="tabpanel">
	                <div class="panel">
	                    <h4>'.$this->l('If you already have an account you can go directly to step 2.').'</h4>
	                    <hr>';
        if (!empty($form_errors))
        {
            $form .= '<div class="warning warn">';
            $form .= $form_errors;
            $form .= '</div>';
        }
        $form .= '
	                    <form method="post" action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'" class="account-creation">
	                        <div class="clear-bis"><label for="email">'.$this->l('E-mail').'</label><input type="text" id="email" name="email" value="'.$form_values['email'].'"></div>
	                        <div class="clear-bis"><label for="firstname">'.$this->l('Firstname').'</label><input type="text" id="firstname" name="firstname" value="'.$form_values['firstname'].'"></div>
	                        <div class="clear-bis"><label for="lastname">'.$this->l('Lastname').'</label><input type="text" id="lastname" name="lastname" value="'.$form_values['lastname'].'"></div>
							<div class="clear-bis">
	                            <label for="currency">Currency</label>
	                            <select id="currency" name="currency">
	                                <option value="EUR">'.$this->l('Euro').'</option>
									<option value="CAD">'.$this->l('Canadian dollar').'</option>
									<option value="USD">'.$this->l('United States Dollar').'</option>
									<option value="CHF">'.$this->l('Swiss franc').'</option>
									<option value="AUD">'.$this->l('Australian dollar').'</option>
									<option value="GBP">'.$this->l('British pound').'</option>
									<option value="SEK">'.$this->l('Swedish krona').'</option>
	                            </select>
	                        </div>
	                        <div class="clear-bis">
	                            <label for="business-line">'.$this->l('Business line').'</label>
								<select name="business-line" id="business-line">';
        foreach ($this->getBusinessLine() as $business)
            if ($business->id == $form_values['business_line'])
                $form .= '<option value="'.$business->id.'" selected="selected">'.$business->label.'</option>';
            else
                $form .= '<option value="'.$business->id.'">'.$business->label.'</option>';
        $form .= '
								</select>
	                        </div>
	                        <div class="clear-bis">
	                            <label for="website-topic">'.$this->l('Website topic').'</label>
								<select id="website-topic" name="website-topic"></select>
	                        </div>
	                        <div class="clear-bis"><label for="contact-email">'.$this->l('Website contact e-mail').'</label><input type="text" id="contact-email" name="contact-email" value="'.$form_values['contact_email'].'"></div>
	                        <div class="clear-bis"><label for="website-name">'.$this->l('Website name').'</label><input type="text" id="website-name" name="website-name" value="'.$form_values['website_name'].'"></div>
	                        <div class="clear-bis"><label for="website-url">'.$this->l('Website URL').'</label><input type="text" id="website-url" name="website-url" value="'.$form_values['website_url'].'"></div>
	                        <div class="clear-bis"><label for="website-password">'.$this->l('Website merchant password').'</label><input type="text" id="website-password" name="website-password" value="'.$form_values['password'].'"></div>
	                        <div class="clear-bis"><input type="submit" name="create_account_action" class="btn btn-default btn-primary"></div>
	                    </form>
	                </div>
	            </div>
	            <div id="hipay_step2" class="tab-pane" role="tabpanel">
	                <div class="panel">
	                    <h4>'.$this->l('Activate the Hipay solution in your Prestashop, it\'s free!').'</h4>
	                    <p>'.$this->l('What you should do:').'</p>
						<ul>
							<li>'.$this->l('Set your account information (id account, password and id website).').'</li>
							<li>'.$this->l('Select the category and age group.').'</li>
							<li>'.$this->l('Set up an email address for notifications of payment.').'</li>
						</ul>
						<p>'.$this->l('For more information , go on the tab " how to set Hipay ".').'</p>
	                    <hr>
	                    <form method="post" action="'.$link.'">
	                        <table cellspacing="0" cellpadding="0" id="hipay_table">
	                            <tbody>
	                            	<tr>
	                                	<td style="">&nbsp;</td>
	                                	<td style="height:40px;">'.$this->l('HiPay account').'</td>
	                            	</tr>';
        foreach ($currencies as $currency)
        {
            $form .= '<tr>
										<td class="hipay_block"><b>'.$this->l('Configuration in').' '.$currency['name'].' '.$currency['sign'].'</b></td>
										<td class="hipay_prod hipay_block" style="padding-left:10px">
											<label class="hipay_label" for="HIPAY_ACCOUNT_'.$currency['iso_code'].'">'.$this->l('Account number').' <a href="../modules/'.$this->name.'/views/img/screenshots/accountnumber.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
											<input type="text" id="HIPAY_ACCOUNT_'.$currency['iso_code'].'" name="HIPAY_ACCOUNT_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code'], Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code']))).'" />
											<br /><p style="text-align: left !important;"><i>'.$this->l('The Hipay account ID where the website is registered. This is your main account.').'<br /> <span style="color:red">'.$this->l('Do not use your member Id here!.').'</span></i></p>
											<label class="hipay_label" for="HIPAY_PASSWORD_'.$currency['iso_code'].'">'.$this->l('Merchant password').' <a href="../modules/'.$this->name.'/views/img/screenshots/merchantpassword.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
											<input type="text" id="HIPAY_PASSWORD_'.$currency['iso_code'].'" name="HIPAY_PASSWORD_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_PASSWORD_'.$currency['iso_code'], Configuration::get('HIPAY_PASSWORD_'.$currency['iso_code']))).'" />
											<br /><p style="text-align: left !important;"><i>'.$this->l('The password of the account on which the merchant website is registered.( this is not the ID password ).').'<br />
											<span style="color:red">'.$this->l('To create a new merchant password: Log in to your Hipay account, go to Payment Buttons where you can find the list of registered sites.').' '.$this->l('Click information website of the website concerned.').' '.$this->l('Enter your merchant password and click confirm').' '.$this->l('Remember to also enter your new password here.').'</span></i></p>
											<label class="hipay_label" for="HIPAY_SITEID_'.$currency['iso_code'].'">'.$this->l('Site ID').' <a href="../modules/'.$this->name.'/views/img/screenshots/siteid.png" target="_blank"><img src="../modules/'.$this->name.'/views/img/help.png" class="hipay_help" /></a></label><br />
											<input type="text" id="HIPAY_SITEID_'.$currency['iso_code'].'" name="HIPAY_SITEID_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_SITEID_'.$currency['iso_code'], Configuration::get('HIPAY_SITEID_'.$currency['iso_code']))).'" />
											<br /><p style="text-align: left !important;"><i>'.$this->l('Website ID selected.').'<br />
											<span style="color:red">'.$this->l('For a website ID, register your store on the corresponding HiPay account.').' '.$this->l('You can find this option on your HiPay statement under Payments buttons.').'</span></i></p>';

            if ($ping && ($hipaySiteId = (int)Configuration::get('HIPAY_SITEID_'.$currency['iso_code'])) && ($hipayAccountId = (int)Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code'])))
            {
                $form .= '	<label for="HIPAY_CATEGORY_'.$currency['iso_code'].'" class="hipay_label">'.$this->l('Category').'</label><br />
														<select id="HIPAY_CATEGORY_'.$currency['iso_code'].'" name="HIPAY_CATEGORY_'.$currency['iso_code'].'">';
                foreach ($this->getHipayCategories($hipaySiteId, $hipayAccountId) as $id => $name)
                    $form.= '	<option value="'.(int)$id.'" '.(Tools::getValue('HIPAY_CATEGORY_'.$currency['iso_code'], Configuration::get('HIPAY_CATEGORY_'.$currency['iso_code'])) == $id ? 'selected="selected"' : '').'>'.htmlentities($name, ENT_COMPAT, 'UTF-8').'</option>';
                $form .= '	</select><br /><p style="text-align: left !important;"><i>'.$this->l('Choose the order type.').'<br /><span style="color:red">'.$this->l('A list of categories (based on the choice of business type and category of your site on Hipay ).').'<br />'.$this->l('1. Enter your website ID').'<br />'.$this->l('2. Click Save Settings').'<br />'.$this->l('The list " Order Type " will be updated.').'<br />'.$this->l('4. Choose the appropriate category and click Save Settings again.').'</span></i></p>';
            }

            $form .= '	</td>
												</tr>
												<tr><td class="hipay_end">&nbsp;</td><td class="hipay_prod hipay_end">&nbsp;</td>';
            $form .= '</tr>';
        }
        $form .= '</table>
	                        <br><br>
	                        <label for="HIPAY_RATING">'.$this->l('Authorized age group').' :</label>
							<div class="margin-form">
								<select id="HIPAY_RATING" name="HIPAY_RATING">
									<option value="ALL">'.$this->l('For all ages').'</option>
									<option value="+12" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+12' ? 'selected="selected"' : '').'>'.$this->l('For ages 12 and over').'</option>
									<option value="+16" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+16' ? 'selected="selected"' : '').'>'.$this->l('For ages 16 and over').'</option>
									<option value="+18" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+18' ? 'selected="selected"' : '').'>'.$this->l('For ages 18 and over').'</option>
								</select>
							</div>									
							<br><br><p>'.$this->l('Notice: please verify that the currency mode you have chosen in the payment tab is compatible with your Hipay account(s).').'</p>
	                        <br><br>
	                        <input type="submit" style="font-weight:bold;" class="button btn btn-default btn-primary" value="'.$this->l('Update configuration').'" name="submitHipay">
	                    </form>		                        
	                </div>
	            </div>
	            <div id="hipay_step3" class="tab-pane" role="tabpanel">
	                <div class="panel">
	                    <table>
	                        <tbody>
	                        <tr>
								<td></t>
								<td>'.$this->l('Choose a set of buttons for your shop Hipay').' :</td>
	                        </tr>
	                        <tr>
	                            <td></td>
	                            <td>
	                                <form method="post" action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'">
	                                    <table>
	                                        <tbody><tr>
	                                            <td>
	                                                <input type="radio" value="be" id="payment_button_be" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'be' ? 'checked="checked"' : '').' >
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_be" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/BE.png"></label>
	                                            </td>
	                                            <td style="padding-left: 40px;">
	                                                <input type="radio" value="de" id="payment_button_de" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'de' ? 'checked="checked"' : '').' >
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_de" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/DE.png"></label>
	                                            </td>
	                                        </tr>
	                                        <tr>
	                                            <td>
	                                                <input type="radio" value="fr" id="payment_button_fr" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'fr' ? 'checked="checked"' : '').' >
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_fr" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/FR.png"></label>
	                                            </td>
	                                            <td style="padding-left: 40px;">
	                                                <input type="radio" value="gb" id="payment_button_gb" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'gb' ? 'checked="checked"' : '').' >
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_gb" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/GB.png"></label>
	                                            </td>
	                                        </tr>
	                                        <tr>
	                                            <td>
	                                                <input type="radio" value="it" id="payment_button_it" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'it' ? 'checked="checked"' : '').'>
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_it" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/IT.png"></label>
	                                            </td>
	                                            <td style="padding-left: 40px;">
	                                                <input type="radio" value="nl" id="payment_button_nl" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'nl' ? 'checked="checked"' : '').'>
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_nl" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/NL.png"></label>
	                                            </td>
	                                        </tr>
	                                        <tr>
	                                            <td>
	                                                <input type="radio" value="pt" id="payment_button_pt" name="payment_button" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'pt' ? 'checked="checked"' : '').' >
	                                            </td>
	                                            <td>
	                                                <label for="payment_button_pt" style="width: auto"><img src="../modules/'.$this->name.'/views/img/payment_button/PT.png"></label>
	                                            </td>
	                                        </tr>
	                                        </tbody></table>
	                                    <input type="submit" style="font-weight:bold;" class="button btn btn-default btn-primary" value="'.$this->l('Update configuration').'" name="submitHipayPaymentButton">
	                                </form>
	                            </td>
	                        </tr>
	                        </tbody>
	                    </table>
	                </div>
	            </div>
	            <div id="hipay_step4" class="tab-pane" role="tabpanel">
	                <div class="panel">
	                	<script type="text/javascript">
							function loadWebsiteTopic()
							{';
        if (_PS_VERSION_ < '1.6'){
            $form .= 'var tmp = $; $ = $j1124;';
            $form .= '$(\'#content\').addClass("bootstrap"); ';
        }
        $form .= '		var locale = "'.$this->formatLanguageCode(Context::getContext()->language->iso_code).'";
								var business_line = $("#business-line").val();
								$.ajax(
								{
									type: "POST",
									url: "'.__PS_BASE_URI__.'modules/hipay/ajax_websitetopic.php",
									data:
									{
										locale: locale,
										business_line: business_line,
										token: "'.Tools::substr(Tools::encrypt('hipay/websitetopic'), 0, 10).'"
									},
									success: function(result)
									{
										$("#website-topic").html(result);
									}
								});';

        if (_PS_VERSION_ < '1.6'){
            $form .= '$ = tmp;';
        }

        $form .='	}
							$("#business-line").change(function() { loadWebsiteTopic() });
							loadWebsiteTopic();
						</script>';
        $form .= '
					'.$this->l('Select the authorized shipping zones').' :<br /><br />
					<form action="'.$currentIndex.'&configure=hipay&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
					<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th class="center">'.$this->l('ID').'</th>
						<th>'.$this->l('Zones').'</th>
						<th align="center"><img src="../modules/'.$this->name.'/views/img/logo.gif" /></th>
					</tr>';

        $result = Db::getInstance()->ExecuteS('
					SELECT `id_zone`, `name`
					FROM '._DB_PREFIX_.'zone
					WHERE `active` = 1
				');

        foreach ($result as $rowNumber => $rowValues)
        {
            $form .= '<tr>';
            $form .= '<td>'.$rowValues['id_zone'].'</td>';
            $form .= '<td>'.$rowValues['name'].'</td>';
            $chk = null;
            if (Configuration::get('HIPAY_AZ_'.$rowValues['id_zone']) == 'ok')
                $chk = "checked ";

            $form .= '<td align="center"><input type="checkbox" name="HIPAY_AZ_'.$rowValues['id_zone'].'" value="ok" '.$chk.'/>';
            $form .= '<input type="hidden" name="HIPAY_AZ_ALL_'.$rowValues['id_zone'].'" value="ok" /></td>';
            $form .= '</tr>';
        }

        $form .= '	</table><br>
						<input type="submit" name="submitHipayAZ" value="'.$this->l('Update zones').'" style="font-weight:bold;" class="button btn btn-default btn-primary" />
					</form>
					<script type="text/javascript">';
        $form .= 'function switchHipayAccount(prod) {';

        if (_PS_VERSION_ < '1.6'){
            $form .= 'var tmp = $; $ = $j1124;';
        }

        $form .= 'if (prod)
							{';
        foreach ($currencies as $currency)
        {
            $form .= '
							$("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
							$("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
							$("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");';
        }
        $form .= '	$(".hipay_prod").css("background-color", "#EEEEEE");
							$(".hipay_test").css("background-color", "transparent");
							$(".hipay_prod_span").css("font-weight", "700");
							$(".hipay_test_span").css("font-weight", "200");
						}else{';
        foreach ($currencies as $currency)
        {
            $form .= '
							$("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
							$("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
							$("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");';
        }
        $form .= '	$(".hipay_prod").css("background-color", "transparent");
							$(".hipay_test").css("background-color", "#EEEEEE");
							$(".hipay_prod_span").css("font-weight", "200");
							$(".hipay_test_span").css("font-weight", "700");
						}';

        if (_PS_VERSION_ < '1.6'){
            $form .= '$ = tmp;';
        }

        $form .= '}
					switchHipayAccount('.(int)$this->env.');';

        $form .= '
					</script>';

        $form .= '    
	            	</div>
	            </div>
	        </div>
	    </div>
	    </div>
        <!-- FIN CONTENU CONFIGURATION -->
	    <!-- FIN NEW CSS -->
	    <div class="clear">&nbsp;</div>';

        return $form;
    }
}
