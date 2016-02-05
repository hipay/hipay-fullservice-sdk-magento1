<?php
class Allopass_Hipay_Model_Method_Sdd extends Allopass_Hipay_Model_Method_Cc
{
	protected $_code = 'hipay_sdd';

	protected $_canRefund               = false;
	protected $_canRefundInvoicePartial = false;


	/**
	 * Assign data to info model instance
	 *
	 * @param   mixed $data
	 * @return  Mage_Payment_Model_Info
	 */
	public function assignData($data)
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		$info = $this->getInfoInstance();
		$info->setCcType($this->getConfigData('cctypes'))
		->setSddIban($data->getSddIban())
		->setSddCodeBic($data->getSddCodeBic())
		->setSddBankName($data->getSddBankName());
		
		$this->assignInfoData($info, $data);
		
		return $this;
	}
	
	public function initialize($paymentAction, $stateObject)
	{
		/* @var $payment Mage_Sales_Model_Order_Payment */
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		
		if($payment->getAdditionalInformation('use_oneclick') && $customer->getId())
		{
			$cardId = $payment->getAdditionalInformation('selected_oneclick_card');
			$card = Mage::getModel('hipay/card')->load($cardId);
			if($card->getId() && $card->getCustomerId() == $customer->getId())
				$token = $card->getCcToken();
			else 
				Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
			$payment->setAdditionalInformation('token',$token);
		}
		
		return $this;
		
	}

	public function place($payment, $amount)
	{
		// check if 3DS
		$code3Dsecure = Mage::helper('hipay')->is3dSecure($this->getConfigData('use_3d_secure'), $this->getConfigData('config_3ds_rules'), $payment);
		if($code3Dsecure > 0 )
		{
			// if 3DS, action hosted			
			$order = $payment->getOrder();
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
			
			$request = Mage::getModel('hipay/api_request',array($this));
				
			$payment->setAmount($amount);
			
			$token = null;
			if($payment->getAdditionalInformation('use_oneclick'))
			{
				$cardId = $payment->getAdditionalInformation('selected_oneclick_card');
				$card = Mage::getModel('hipay/card')->load($cardId);
				
				if($card->getId() && $card->getCustomerId() == $customer->getId())
					$token = $card->getCcToken();
				else
					Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
				
			}
			
	    	$gatewayParams = $this->getGatewayParams($payment, $amount,$token);
	    	
	    	if(is_null($token))
	    	{
	    			
		    	$gatewayParams['payment_product'] = 'cb' ;
		    	$gatewayParams['operation'] = $this->getOperation();
		    	$gatewayParams['css'] = $this->getConfigData('css_url');
				$gatewayParams['template'] = $this->getConfigData('display_iframe') ? 'iframe' :  $this->getConfigData('template');
		    	if ($this->getConfigData('template') == 'basic-js' && $gatewayParams['template'] == 'iframe') $gatewayParams['template'] .= '-js';
		    	$gatewayParams['display_selector'] = $this->getConfigData('display_selector');
		    	//$gatewayParams['payment_product_list'] = $this->getConfigData('cctypes');
				
				if ($gatewayParams['country'] == 'BE') 
					$gatewayParams['payment_product_list'] = $this->getConfigData('cctypes');
				else
					$gatewayParams['payment_product_list'] = str_replace('bcmc', '', $this->getConfigData('cctypes'));

				
		    	$gatewayParams['payment_product_category_list'] = "credit-card";
		    	
		    	if(Mage::getStoreConfig('general/store_information/name') != "")
		    		$gatewayParams['merchant_display_name'] = Mage::getStoreConfig('general/store_information/name'); 
				
		    	$this->_debug($gatewayParams);
		    	
		    	$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_HOSTED,$gatewayParams,$payment->getOrder()->getStoreId());
		    	
		    	$this->_debug($gatewayResponse->debug());
		
				return  $gatewayResponse->getForwardUrl();
	    	}
	    	else
	    	{
	    		$gatewayParams['operation'] = $this->getOperation();
	    		$gatewayParams['payment_product']  = Mage::getSingleton('customer/session')->getCustomer()->getHipayCcType();
	    		
	    		$this->_debug($gatewayParams);
	    		 
	    		$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams,$payment->getOrder()->getStoreId());
	    		 
	    		$this->_debug($gatewayResponse->debug());
	    		 
	    		$redirectUrl =  $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
	    		
	    		return $redirectUrl;
	    	}
		}else{
			// if not 3DS, action API
			parent::place($payment, $amount);
		}
	}
	/**
	 * Validate payment method information object
	 *
	 * @param   Mage_Payment_Model_Info $info
	 * @return  Mage_Payment_Model_Abstract
	 */
	public function validate()
	{
		/**
		* to validate payment method is allowed for billing country or not
		*/
		$errorMsg = '';
		$paymentInfo = $this->getInfoInstance();

		// check if 3DS
		$code3Dsecure = Mage::helper('hipay')->is3dSecure($this->getConfigData('use_3d_secure'), $this->getConfigData('config_3ds_rules'));
		if($code3Dsecure == 0 )
		{
			$iban = new Zend_Validate_Iban();

			if(!$iban->isValid($paymentInfo->getSddIban()))
			{
				$errorMsg = Mage::helper('payment')->__('Iban is not correct, please enter a valid Iban.');
			}
			$result_bic = (bool) ( preg_match('/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i', $paymentInfo->getSddCodeBic)) == 1 );
			if(!$result_bic)
			{
				$errorMsg = Mage::helper('payment')->__('Code BIC is not correct, please enter a valid Code BIC.');
			}
			if($errorMsg)
			{
				Mage::throwException($errorMsg);
			}
		}
		return $this;
	}
}