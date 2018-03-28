<?php

class Allopass_Hipay_Model_Method_Qiwi extends Allopass_Hipay_Model_Method_Hosted
{
    protected $_code = 'hipay_qiwi';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
