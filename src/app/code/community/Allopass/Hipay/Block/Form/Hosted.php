<?php

class Allopass_Hipay_Block_Form_Hosted extends Allopass_Hipay_Block_Form_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('hipay/form/hosted.phtml');
    }

}
