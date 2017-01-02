<?php
class Allopass_Hipay_Block_Checkout_Pending extends Mage_Core_Block_Template
{
    public function getRealOrderId()
    {
        return Mage::getSingleton('checkout/session')->getLastRealOrderId();
    }

    /**
     *  Payment custom error message
     *
     *  @return	  string
     */
    public function getErrorMessage()
    {
        $error = Mage::getSingleton('checkout/session')->getErrorMessage();
        // Mage::getSingleton('checkout/session')->unsErrorMessage();
        return $error;
    }

    /**
     * Continue shopping URL
     *
     *  @return	  string
     */
    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}
