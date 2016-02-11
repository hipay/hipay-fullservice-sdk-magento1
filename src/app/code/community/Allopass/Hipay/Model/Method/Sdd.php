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
		$info->setCcType('SDD')
		->setAdditionalInformation('cc_gender', $data->getCcGender())
		->setAdditionalInformation('cc_iban', $data->getCcIban())
		->setAdditionalInformation('cc_code_bic',$data->getCcCodeBic())
		->setAdditionalInformation('cc_bank_name',$data->getCcBankName());
		
		$this->assignInfoData($info, $data);
		
		return $this;
	}
	
	public function initialize($paymentAction, $stateObject)
	{
		/* @var $payment Mage_Sales_Model_Order_Payment */
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		return $this;		
	}

	public function getOrderPlaceRedirectUrl()
	{
			
		return Mage::getUrl('hipay/sdd/sendRequest',array('_secure' => true));

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
	    	$gatewayParams = $this->getGatewayParams($payment, $amount,$token);
	    	
	    	if(is_null($token))
	    	{
	    			
		    	$gatewayParams['payment_product'] = $this->getCcTypeHipay($payment->getCcType()); ;
		    	$gatewayParams['operation'] = $this->getOperation();
		    	
		    	if(Mage::getStoreConfig('general/store_information/name') != "")
		    		$gatewayParams['merchant_display_name'] = Mage::getStoreConfig('general/store_information/name'); 
				
		    	$this->_debug($gatewayParams);		    	
		    	$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams,$payment->getOrder()->getStoreId());
		    	$this->_debug($gatewayResponse->debug());
		
				return  $gatewayResponse->getForwardUrl();
	    	}
	    	else
	    	{
	    		$gatewayParams['operation'] = $this->getOperation();
	    		$gatewayParams['payment_product']  = Mage::getSingleton('customer/session')->getCustomer()->getHipaySddType();
	    		
	    		$this->_debug($gatewayParams);
	    		$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams,$payment->getOrder()->getStoreId());
	    		$this->_debug($gatewayResponse->debug());
	    		$redirectUrl =  $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
	    		return $redirectUrl;
	    	}
		}else{
			// if not 3DS, action API
			$order = $payment->getOrder();
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());			
			$request = Mage::getModel('hipay/api_request',array($this));			
			$payment->setAmount($amount);
			$token = $payment->getAdditionalInformation('token');
	    	$gatewayParams =  $this->getGatewayParams($payment, $amount,$token); 	    	
	    	$gatewayParams['operation'] =$this->getOperation();	   
	    	$paymentProduct = $this->getCcTypeHipay($payment->getCcType());
	    	
	    	$gatewayParams['payment_product'] 	= $paymentProduct ;
	    	$gatewayParams['gender']	 		= $payment->getAdditionalInformation('cc_gender');
	    	$gatewayParams['recurring_payment'] = 0;
	    	$gatewayParams['iban'] 				= $payment->getAdditionalInformation('cc_iban');
	    	$gatewayParams['issuer_bank_id'] 	= $payment->getAdditionalInformation('cc_code_bic');
	    	$gatewayParams['bank_name']	 		= $payment->getAdditionalInformation('cc_bank_name');
	    	$this->_debug($gatewayParams);
	    	$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams,$payment->getOrder()->getStoreId());
	    	$this->_debug($gatewayResponse->debug());	    	
	  		$redirectUrl =  $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
	  		
	  		return $redirectUrl;
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
			if(!$iban->isValid($paymentInfo->getAdditionalInformation('cc_iban')))
			{
				$errorMsg = Mage::helper('payment')->__('Iban is not correct, please enter a valid Iban.');
			}
			if(empty($paymentInfo->getAdditionalInformation('cc_code_bic')))
			{
				$errorMsg = Mage::helper('payment')->__('Code BIC is not correct, please enter a valid Code BIC.');
			}
			if(empty($paymentInfo->getAdditionalInformation('cc_bank_name')))
			{
				$errorMsg = Mage::helper('payment')->__('Bank name is not correct, please enter a valid Bank name.');
			}
			if($errorMsg)
			{
				Mage::throwException($errorMsg);
			}
		}
		return $this;
	}
}