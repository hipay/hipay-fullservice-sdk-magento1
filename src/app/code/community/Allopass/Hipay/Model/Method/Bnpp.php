<?php
class Allopass_Hipay_Model_Method_Bnpp extends Allopass_Hipay_Model_Method_AbstractOrder
{	

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $phone = $paymentInfo->getOrder()->getBillingAddress()->getTelephone();
        } else {
            $phone = $paymentInfo->getQuote()->getBillingAddress()->getTelephone();
        }

        if (!preg_match('"(0|\\+33|0033)[1-9][0-9]{8}"',$phone)) {
            Mage::throwException(Mage::helper('payment')->__('Please check the phone number entered.'));
        }
    }
}