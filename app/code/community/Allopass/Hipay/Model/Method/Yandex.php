<?php
class Allopass_Hipay_Model_Method_Yandex extends Allopass_Hipay_Model_Method_Hosted
{	
	protected $_code  = 'hipay_yandex';	
	
	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;
}