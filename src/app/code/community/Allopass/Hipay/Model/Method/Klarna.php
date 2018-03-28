<?php

class Allopass_Hipay_Model_Method_Klarna extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code = 'hipay_klarna';
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;

    const PAYMENT_PRODUCT = 'klarnainvoice';


    /**
     * Getting specifics params for payment method klarna
     *
     * @param $gatewayParams
     * @return Array
     */
    public function getSpecificsParams($gatewayParams, $payment)
    {
        $gatewayParams['payment_product'] = Allopass_Hipay_Model_Method_Klarna::PAYMENT_PRODUCT;

        $params['msisdn'] = $payment->getOrder()->getBillingAddress()->getTelephone();

        // Fake data because it's not default information in MAGENTO
        $gatewayParams['shipto_house_number'] = '999';
        unset($gatewayParams['payment_product_category_list']);

        return $gatewayParams;
    }
}
