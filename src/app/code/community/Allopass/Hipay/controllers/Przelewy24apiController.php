<?php
class Allopass_Hipay_Przelewy24apiController extends Allopass_Hipay_Controller_Payment
{
	protected function _getMethodInstance()
	{
		return Mage::getSingleton('hipay/method_przelewy24Api');
	}
}