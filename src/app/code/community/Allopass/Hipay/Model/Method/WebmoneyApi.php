<?php

class Allopass_Hipay_Model_Method_WebmoneyApi extends Allopass_Hipay_Model_Method_AbstractOrder
{
    protected $_code = 'hipay_webmoneyapi';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
