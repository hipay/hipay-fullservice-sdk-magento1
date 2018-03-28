<?php

class Allopass_Hipay_Block_Card_Edit extends Mage_Core_Block_Template
{
    protected $_card;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_card = Mage::registry('current_card');


        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }

        if ($postedData = Mage::getSingleton('customer/session')->getCardFormData(true)) {
            $this->_card->addData($postedData);
        }

        return $this;
    }

    public function getCard()
    {
        return $this->_card;
    }


    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        return $this->getUrl('hipay/card');

    }

    public function getSaveUrl()
    {
        return Mage::getUrl('hipay/card/editPost', array('_secure' => true, 'id' => $this->getCard()->getId()));
    }

}
