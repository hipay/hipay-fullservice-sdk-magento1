<?php
class Allopass_Hipay_Model_Method_Dexia extends Allopass_Hipay_Model_Method_AbstractOrder
{	
	protected $_code  = 'hipay_dexia';	
	
	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;

    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';
}