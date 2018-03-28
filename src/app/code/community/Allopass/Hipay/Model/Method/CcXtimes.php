<?php

class Allopass_Hipay_Model_Method_CcXtimes extends Allopass_Hipay_Model_Method_Cc
{
    protected $_canUseInternal = false;

    protected $_code = 'hipay_ccxtimes';

    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (!is_null($quote)) {

            $checkoutMethod = $quote->getCheckoutMethod();

            if ($checkoutMethod == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
                return false;
            }
        }

        return parent::isAvailable($quote);
    }

}
