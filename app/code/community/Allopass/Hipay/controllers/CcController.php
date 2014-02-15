<?php
class Allopass_Hipay_CcController extends Allopass_Hipay_Controller_Payment
{
	
	
	/**
	 * 
	 * @return Allopass_Hipay_Model_Method_Cc $methodInstance
	 */
	protected function _getMethodInstance()
	{
		return Mage::getSingleton('hipay/method_cc'); ;	
	}

}