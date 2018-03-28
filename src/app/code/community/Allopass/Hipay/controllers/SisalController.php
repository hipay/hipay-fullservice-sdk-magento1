<?php

class Allopass_Hipay_SisalController extends Allopass_Hipay_Controller_Payment
{
    protected function _getMethodInstance()
    {
        return Mage::getSingleton('hipay/method_sisal');
    }
}
