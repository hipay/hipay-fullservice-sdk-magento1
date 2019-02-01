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
class Allopass_Hipay_Block_Form_Cpn extends Allopass_Hipay_Block_Form_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('hipay/form/cpn.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $fingerprint = $this->getLayout()->createBlock('hipay/checkout_fingerprint', 'hipay.checkout.fingerprint');
        $this->setChild('hipay_fingerprint', $fingerprint);

        return $this;
    }
}
