<?php
class Allopass_Hipay_KlarnaController extends Allopass_Hipay_Controller_Payment
{
    /**
     * Instantiate KLARNA controller
     *
     * @return Mage_Core_Model_Abstract
     */
	protected function _getMethodInstance()
	{
		return Mage::getSingleton('hipay/method_klarna');
	}
}