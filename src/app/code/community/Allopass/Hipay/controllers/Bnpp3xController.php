<?php

class Allopass_Hipay_Bnpp3xController extends Allopass_Hipay_Controller_Payment
{
    protected function _getMethodInstance()
    {
        return Mage::getSingleton('hipay/method_bnpp3x');
    }
}
