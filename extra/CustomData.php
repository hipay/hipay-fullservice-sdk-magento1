<?php

/**
*       This custom class must be placed in the folder /AlloPass/Hipay/Helper
*       You have to personalize  the method getCustomData and return an json of your choice.
*       
*/
class Allopass_Hipay_Helper_CustomData extends Mage_Core_Helper_Abstract
{
    /**
	 *  Return yours customs datas in a json for gateway transaction request 
     *
     * @param array $payment
	 * @param float $amount
	 *
     */
    public function getCustomData($payment,$amount)
    {
        $customData = array();
        
        // An example of adding custom data
        if ($payment)
        {
            $customData['my_field_custom_1'] = $payment->getOrder()->getBaseCurrencyCode();
        }
        
        return json_encode($customData);
    }
}