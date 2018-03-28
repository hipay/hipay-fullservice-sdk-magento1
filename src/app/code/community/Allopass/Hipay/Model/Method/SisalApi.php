<?php

class Allopass_Hipay_Model_Method_SisalApi extends Allopass_Hipay_Model_Method_AbstractOrder
{
    protected $_code = 'hipay_sisalapi';

    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
}
