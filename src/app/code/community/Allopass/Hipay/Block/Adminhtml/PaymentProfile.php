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
class Allopass_Hipay_Block_Adminhtml_PaymentProfile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {

        $this->_controller = 'adminhtml_paymentProfile';
        $this->_blockGroup = 'hipay';
        $this->_headerText = $this->__('Hipay Payment Profiles');
        $this->_addButtonLabel = $this->__('Add payment profile');

        parent::__construct();
    }
}
