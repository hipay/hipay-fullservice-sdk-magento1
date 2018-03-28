<?php

class Allopass_Hipay_Model_Method_YandexApi extends Allopass_Hipay_Model_Method_AbstractOrder
{
    protected $_code = 'hipay_yandexapi';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
