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
		->setAdditionalInformation('cc_firstname', $data->getCcFirstname())
		->setAdditionalInformation('cc_lastname', $data->getCcLastname())
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
		// check if Electronic Signature
		$codeElectronicSignature = $this->getConfigData('electronic_signature');
		if($codeElectronicSignature > 0 )
		{
			// if Electronic signature, action hosted			
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
			// if not Electronic signature, action API
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
	    	$gatewayParams['firstname']	 		= $payment->getAdditionalInformation('cc_firstname');
	    	$gatewayParams['lastname']	 		= $payment->getAdditionalInformation('cc_lastname');
	    	$gatewayParams['recurring_payment'] = 0;
	    	$gatewayParams['iban'] 				= $payment->getAdditionalInformation('cc_iban');
	    	$gatewayParams['issuer_bank_id'] 	= $payment->getAdditionalInformation('cc_code_bic');
	    	$gatewayParams['bank_name']	 		= $payment->getAdditionalInformation('cc_bank_name');
	    	$gatewayParams['authentication_indicator'] = 0;
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
		// check if Electronic signature
		$codeElectronicSignature = $this->getConfigData('electronic_signature');
		if($codeElectronicSignature == 0 )
		{
			
			$iban = new Zend_Validate_Iban();
			if(!$iban->isValid($paymentInfo->getAdditionalInformation('cc_iban')))
			{
				$errorMsg = Mage::helper('payment')->__('Iban is not correct, please enter a valid Iban.');
			}
			// variable pour la fonction empty
			$var1 = $paymentInfo->getAdditionalInformation('cc_firstname');
			$var2 = $paymentInfo->getAdditionalInformation('cc_lastname');
			$var3 = $paymentInfo->getAdditionalInformation('cc_code_bic');
			$var4 = $paymentInfo->getAdditionalInformation('cc_bank_name');
			if(empty($var1))
			{
				$errorMsg = Mage::helper('payment')->__('Firstname is mandatory.');
			}			
			if(empty($var2))
			{
				$errorMsg = Mage::helper('payment')->__('Lastname is mandatory.');
			}			
			if(empty($var3))
			{
				$errorMsg = Mage::helper('payment')->__('Code BIC is not correct, please enter a valid Code BIC.');
			}			
			if(empty($var4))
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