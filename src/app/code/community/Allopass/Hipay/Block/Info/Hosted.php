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
 * hosted hipay payment info
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Block_Info_Hosted extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('hipay/info/cc.phtml');
    }

    /**
     * Retrieve credit card type name
     *
     * @return string
     */
    public function getCcTypeName()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }

        return (empty($ccType)) ? Mage::helper('payment')->__('N/A') : $ccType;
    }

    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();
        if ($ccType = $this->getCcTypeName()) {
            $data[Mage::helper('payment')->__('Credit Card Type')] = $ccType;
        }

        if ($this->getInfo()->getCcLast4()) {
            $data[Mage::helper('payment')->__('Credit Card Number')] = sprintf(
                'xxxx-%s',
                $this->getInfo()->getCcLast4()
            );
        }

        if ($this->getInfo()->getAdditionalInformation('fraud_type')
            && $this->getInfo()->getAdditionalInformation('fraud_score')
        ) {
            $data[Mage::helper('hipay')->__('Fraud result')] = ucfirst(
                $this->getInfo()->getAdditionalInformation('fraud_type')
            );
            $data[Mage::helper('hipay')->__('Fraud scoring')] = $this->getInfo()->getAdditionalInformation(
                'fraud_score'
            );
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
