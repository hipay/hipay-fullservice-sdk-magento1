<?php

class Allopass_Hipay_Model_Method_PaypalApi extends Allopass_Hipay_Model_Method_AbstractOrder
{
    protected $_code = 'hipay_paypalapi';

    protected $_formBlockType = 'hipay/form_hosted';
    protected $_infoBlockType = 'hipay/info_hosted';
}
