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
class Allopass_Hipay_Model_Method_OneyAbstract extends Allopass_Hipay_Model_Method_AbstractOrderApi
{

    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return array|null
     */
    public function getAdditionalParameters($payment)
    {

        $params = null;

        if (!empty($this->getConfigData('merchant_promotion'))) {
            $params["payment_product_parameters"] = json_encode(
                array(
                    "merchant_promotion" => $this->getConfigData('merchant_promotion')
                )
            );
        }

        return $params;
    }
}
