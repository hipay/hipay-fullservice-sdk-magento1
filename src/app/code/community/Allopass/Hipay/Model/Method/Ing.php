<?php
class Allopass_Hipay_Model_Method_Ing extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code  = 'hipay_ing';

    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;
}
