<?php

/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
abstract class Allopass_Hipay_Block_Form_Abstract extends Mage_Payment_Block_Form
{
    /**
     *
     * @var Allopass_Hipay_Model_Resource_Card_Collection
     */
    protected $_cards = null;

    /**
     * Retrieve payment configuration object
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

    public function getCards()
    {
        if (is_null($this->_cards)) {
            $today = new Zend_Date(Mage::app()->getLocale()->storeTimeStamp());

            $currentYear = (int)$today->getYear()->toString("Y");
            $currentMonth = (int)$today->getMonth()->toString("MM");

            $this->_cards = Mage::getResourceModel('hipay/card_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $this->getCustomer()->getId())
                ->addFieldToFilter('cc_status', Allopass_Hipay_Model_Card::STATUS_ENABLED)
                ->addFieldToFilter('cc_exp_year', array("gteq" => $currentYear))
                ->setOrder('card_id', 'desc')
                ->setOrder('is_default', 'desc');

            foreach ($this->_cards as $card) {
                if ($card->ccExpYear == $currentYear && $card->ccExpMonth < $currentMonth) {
                    $this->_cards->removeItemByKey($card->getId());
                }
            }
        }

        return $this->_cards;
    }

    /**
     * @deprecated since v1.0.9
     * @return boolean
     */
    public function getCustomerHasAlias()
    {
        return $this->getCustomer()->getHipayAliasOneclick() != "";

    }

    public function getCustomerHasCard()
    {
        return $this->getCards()->count() > 0;

    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function ccExpDateIsValid()
    {
        return $this->helper('hipay')->checkIfCcExpDateIsValid(
            (int)Mage::getSingleton('customer/session')->getCustomerId()
        );
    }

    /**
     * If checkout method is GUEST oneclick is not allowed
     * Or We check method configuration
     * @return boolean
     */
    public function oneClickIsAllowed()
    {
        $checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();

        if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST || !$this->allowUseOneClick()) {
            return false;
        }

        return true;

    }

    /**
     * @return Mage_Sales_Model_Quote
     *
     * */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function allowSplitPayment()
    {

        $checkoutMethod = $this->getQuote()->getCheckoutMethod();

        if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST
            || (strpos($this->getMethodCode(), "xtimes") === false)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get split payment profiles
     *
     * @return mixed
     */
    public function getSplitPaymentProfiles()
    {
        $profileIds = explode(",", $this->getMethod()->getConfigData('split_payment_profile'));
        return Mage::getModel('hipay/paymentProfile')->getCollection()->addIdsToFilter($profileIds);

    }

    /**
     * Allowed to use one click
     *
     * @return bool|int
     */
    protected function allowUseOneClick()
    {
        switch ((int)$this->getMethod()->getConfigData('allow_use_oneclick')) {
            case 0:
                return false;
            case 1:
                /* @var $rule Allopass_Hipay_Model_Rule */

                $rule = Mage::getModel('hipay/rule')->load($this->getMethod()->getConfigData('filter_oneclick'));
                if ($rule->getId()) {
                    return (int)$rule->validate($this->getQuote());
                }
                return true;
        }
    }

    /**
     * Return iframe config
     *
     * @return array
     */
    public function getIframeConfig()
    {
        $iframe = array();
        $iframe['iframe_width'] = $this->getMethodConfigData('iframe_width');
        $iframe['iframe_height'] = $this->getMethodConfigData('iframe_height');
        $iframe['iframe_style'] = $this->getMethodConfigData('iframe_style');
        $iframe['iframe_wrapper_style'] = $this->getMethodConfigData('iframe_style');
        return $iframe;
    }

    protected function getMethodConfigData($code, $default = "")
    {
        if ($this->getMethod()->getConfigData($code) !== null) {
            return $this->getMethod()->getConfigData($code);
        }

        return $default;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent(
            'payment_form_block_to_html_before',
            array(
                'block' => $this
            )
        );
        return parent::_toHtml();
    }

    /**
     *  Return the type for national identification number to BLOCK
     * @return string
     */
    public function getTypeNationalIdentification()
    {
        return $this->getMethod()->getTypeNationalIdentification();
    }

    /**
     * Retrieve config json
     *
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getConfigJson()
    {
        $data = array(
            "api_tokenjs_username" => $this->getConfig()->getApiTokenJSUsername(Mage::app()->getStore()),
            "api_tokenjs_publickey" => $this->getConfig()->getApiTokenJSPublickey(Mage::app()->getStore()),
            "api_tokenjs_username_test" => $this->getConfig()->getApiTokenJSUsernameTest(Mage::app()->getStore()),
            "api_tokenjs_publickey_test" => $this->getConfig()->getApiTokenJSPublickeyTest(Mage::app()->getStore()),
            "testMode" => $this->getMethod()->getConfigData('is_test_mode'),
            "lang" => substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2),
            "isOneClick" => $this->oneClickIsAllowed(),
            "defaultLastname" => $this->getQuote()->getCustomerLastname(),
            "defaultFirstname" => $this->getQuote()->getCustomerFirstname(),
            "style" => array(
                "base" => array(
                    "color" => $this->getMethod()->getConfigData('color'),
                    "fontFamily" => $this->getMethod()->getConfigData('fontFamily'),
                    "fontSize" => $this->getMethod()->getConfigData('fontSize'),
                    "fontWeight" => $this->getMethod()->getConfigData('fontWeight'),
                    "placeholderColor" => $this->getMethod()->getConfigData('placeholderColor'),
                    "caretColor" => $this->getMethod()->getConfigData('caretColor'),
                    "iconColor" => $this->getMethod()->getConfigData('iconColor')
                )
            )
        );

        return Mage::helper('core')->jsonEncode($data);
    }

    /**
     *
     * @return Allopass_Hipay_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
}
