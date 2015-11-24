<?php
class Allopass_Hipay_Model_Method_Kbc extends Allopass_Hipay_Model_Method_Hosted
{	
	protected $_code  = 'hipay_kbc';	
	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;
}