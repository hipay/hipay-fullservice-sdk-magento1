<?php

class Allopass_Hipay_Model_Method_Webmoney extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code = 'hipay_webmoney';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
