<?php
class Allopass_Hipay_Model_Observer
{
	/**
	 * Cancel orders stayed in pending because customer not validated payment form
	 */
	public function cancelOrdersInPending()
	{
		
		//$methodCodes = array('hipay_cc'=>'hipay/method_cc','hipay_hosted'=>'hipay/method_hosted');
		$methodCodes = Mage::helper('hipay')->getHipayMethods();
		foreach ($methodCodes as $methodCode=>$model)
		{
			if(!Mage::getStoreConfig('payment/'.$methodCode."/cancel_pending_order"))
				continue;
			
			$limitedTime = 30;
				
			$date = new Zend_Date();//Mage::app()->getLocale()->date();
			
			/* @var $collection Mage_Sales_Model_Resource_Order_Collection */
			$collection = Mage::getResourceModel('sales/order_collection');
			$collection->addFieldToSelect(array('entity_id','state'))
				
			->addAttributeToFilter('created_at', array('to' => ($date->subMinute($limitedTime)->toString('Y-MM-dd HH:mm:ss'))))
			;
			
			
			/* @var $order Mage_Sales_Model_Order */
			foreach ($collection as $order)
			{
	
				if($order->getPayment()->getMethod() == $methodCode)
				{
					if($order->canCancel() && $order->getState() == Mage_Sales_Model_Order::STATE_NEW)
					{
						try {
							$order->cancel();
							$order
							->addStatusToHistory($order->getStatus(),
									// keep order status/state
									Mage::helper('hipay')->__("Order canceled automatically by cron because order is pending since %d minutes",$limitedTime));
	
							$order->save();
						} catch (Exception $e) {
							Mage::logException($e);
						}
					}
				}
			}
		}
		return $this;
	}
	
	public function manageOrdersInPendingCapture()
	{
		$methods = array('hipay_cc','hipay_hosted');
		/* @var $collection Mage_Sales_Model_Resource_Order_Collection */
		$collection = Mage::getResourceModel('sales/order_collection');
		$collection->addFieldToFilter('status','pending_capture');
		
		/* @var $order Mage_Sales_Model_Order */
		foreach ($collection as $order)
		{
			if(!in_array($order->getPayment()->getMethod(), $methods))
				continue;
			
			$orderDate = "";
		}
		
	}
	
	public function displaySectionCheckoutIframe($observer)
	{
		$payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
		if($payment->getAdditionalInformation('use_oneclick'))
			return $this;
		/* @var $controller Mage_Checkout_OnepageController */
		$controller = $observer->getControllerAction();
		
		$result = Mage::helper('core')->jsonDecode($controller->getResponse()->getBody());
		
		//TODO check if payment method is hosted and iframe active and is success
		$methodInstance =  $payment->getMethodInstance(); 
		if($result['success'] 
				&& $methodInstance->getCode() == 'hipay_hosted' 
				&& $methodInstance->getConfigData('display_iframe'))
		{
			$result['iframeUrl'] = $result['redirect']; 
		}
		
		$controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		
		return $this;
		
	}
	
	public function paySplitPayments()
	{
		
		$date = new Zend_Date();
		
		//TODO add filter for max attempts
		$splitPayments = Mage::getModel('hipay/splitPayment')->getCollection()
								->addFieldToFilter('status',array('in'=>array(Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_PENDING,
																			Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_FAILED)))
								->addFieldTofilter('date_to_pay',array('to' => $date->toString('Y-MM-dd 00:00:00')));

		
		foreach ($splitPayments as $splitPayment) {
			try {
				$splitPayment->pay();
			} catch (Exception $e) {
				$splitPayment->sendErrorEmail();
				Mage::logException($e);
			}
		}
	}
	
	public function arrangeOrderView($observer)
	{
		/* @var $block Mage_Adminhtml_Block_Sales_Order_Invoice_View|Mage_Adminhtml_Block_Sales_Transactions_Detail */
		$block = $observer->getBlock();
		
		
		/*if($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View)
		{
			$order = $block->getInvoice()->getOrder();
			if($order->getStatus() == Allopass_Hipay_Model_Method_Abstract::STATUS_CAPTURE_REQUESTED)
				$order->setForcedCanCreditmemo(false);
		}
		else*/
		if($block instanceof Mage_Adminhtml_Block_Sales_Transactions_Detail)
		{
			$txnId = $block->getTxnIdHtml();
			$orderIncrementId = $block->getOrderIncrementIdHtml();
			
			/* @var $order Mage_Sales_Model_Order */
			$order = Mage::getModel('sales/order')->loadByIncrementId(trim($orderIncrementId));
			if($order->getId() && strpos($order->getPayment()->getMethod(), 'hipay') !== false)
			{
				$link = '<a href="https://merchant.hipay-tpp.com//transaction/detail/index/trxid/'.$txnId.'" target="_blank">'.$txnId.'</a>';
				$block->setTxnIdHtml($link);
			}
			
			
			
		}
	}
	
	public function orderCanRefund($observer)
	{
		$order = $observer->getOrder();
		if($order->getStatus() == Allopass_Hipay_Model_Method_Abstract::STATUS_CAPTURE_REQUESTED)
			$order->setForcedCanCreditmemo(false);
	}
}