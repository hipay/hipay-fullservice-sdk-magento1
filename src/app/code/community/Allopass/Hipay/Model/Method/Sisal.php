<?php

class Allopass_Hipay_Model_Method_Sisal extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code = 'hipay_sisal';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
