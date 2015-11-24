<?php

class Allopass_Hipay_Block_Card extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('hipay/card/account.phtml');

        $cards = Mage::getResourceModel('hipay/card_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('cc_status', Allopass_Hipay_Model_Card::STATUS_ENABLED)
            ->setOrder('card_id', 'desc')
        ;

        $this->setCards($cards);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('hipay')->__("Hipay's Cards"));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'hipay.card.account.pager')
            ->setCollection($this->getCards());
        $this->setChild('pager', $pager);
        $this->getCards()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getEditUrl($card)
    {
        return $this->getUrl('*/*/edit', array('card_id' => $card->getId()));
    }
    
    public function canDelete()
    {
    	return true;
    }


    public function getDeleteUrl($card)
    {
        return $this->getUrl('*/*/delete', array('card_id' => $card->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
