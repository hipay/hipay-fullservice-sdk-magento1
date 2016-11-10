<?php
class Allopass_Hipay_Model_Method_Cc extends Allopass_Hipay_Model_Method_Abstract
{
	
	protected $_canReviewPayment		= true;
	const STATUS_PENDING_CAPTURE = 'pending_capture';
	
	protected $_code  = 'hipay_cc';
	
	protected $_formBlockType = 'hipay/form_cc';
	protected $_infoBlockType = 'hipay/info_cc';

	
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

		Mage::log($data,null,'debug_data.log');
		$info = $this->getInfoInstance();
		$info->setCcType($data->getData($this->getCode() . '_cc_type'))
		->setCcOwner($data->getData($this->getCode() . '_cc_owner'))
		->setCcLast4(substr($data->getData($this->getCode() . '_cc_number'), -4))
		->setCcNumber($data->getData($this->getCode() . '_cc_number'))
		->setCcCid($data->getData($this->getCode() . '_cc_cid'))
		->setCcExpMonth($data->getData($this->getCode() . '_cc_exp_month'))
		->setCcExpYear($data->getData($this->getCode() . '_cc_exp_year'))
		->setCcSsIssue($data->getData($this->getCode() . '_cc_ss_issue'))
		->setCcSsStartMonth($data->getData($this->getCode() . '_cc_ss_start_month'))
		->setCcSsStartYear($data->getData($this->getCode() . '_cc_ss_start_yeat'))
		;
		
		$this->assignInfoData($info, $data);
		
		return $this;
	}
	
	/**
	 * Prepare info instance for save
	 *
	 * @return Mage_Payment_Model_Abstract
	 */
	public function prepareSave()
	{
		$info = $this->getInfoInstance();
		if ($this->_canSaveCc) {
			$info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
		}
		//$info->setCcCidEnc($info->encrypt($info->getCcCid()));
		$info->setCcNumber(null)
		->setCcCid(null);
		return $this;
	}
	
	
	/**
	 * Retrieve payment iformation model object
	 *
	 * @return Mage_Payment_Model_Info
	 */
	public function getInfoInstance()
	{
		$instance = $this->getData('info_instance');
		if (!($instance instanceof Mage_Payment_Model_Info)) {
			Mage::throwException(Mage::helper('payment')->__('Cannot retrieve the payment information object instance.'));
		}
		return $instance;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @return 	array
	 */
	protected function getVaultParams($payment)
	{
		$params = array();
		$params['card_number'] = $payment->getCcNumber();
		$params['card_expiry_month'] = ($payment->getCcExpMonth() < 10) ? '0'.$payment->getCcExpMonth() : $payment->getCcExpMonth();
		$params['card_expiry_year'] = $payment->getCcExpYear();
		$params['cvc'] = $payment->getCcCid();
		$params['multi_use'] = 1; 
		
		//Add card holder
		$billing = $payment->getOrder()->getBillingAddress();
		$defaultOwner = $billing->getFirstname() && $billing->getLastname() ? $billing->getFirstname() . ' ' . $billing->getLastname() : $billing->getEmail();
		$params['card_holder'] = $payment->getCcOwner() ? $payment->getCcOwner() : $defaultOwner;

		$this->_debug($params);
		
		return $params;
	}
	
	
	public function getOrderPlaceRedirectUrl()
	{
			
		return Mage::getUrl('hipay/cc/sendRequest',array('_secure' => true));

	}
	
	
	public function initialize($paymentAction, $stateObject)
	{
		/* @var $payment Mage_Sales_Model_Order_Payment */
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		$token = "";
		
		if($payment->getAdditionalInformation('use_oneclick') && $customer->getId())
		{
			$cardId = $payment->getAdditionalInformation('selected_oneclick_card');
			$card = Mage::getModel('hipay/card')->load($cardId);
			if($card->getId() && $card->getCustomerId() == $customer->getId())
				$token = $card->getCcToken();
			else 
				Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
		}
		else 
		{
			$request = Mage::getModel('hipay/api_request',array($this));
			/* @var $request Allopass_Hipay_Model_Api_Request */
			$vaultResponse = $request->vaultRequest(Allopass_Hipay_Model_Api_Request::VAULT_ACTION_CREATE, $this->getVaultParams($payment),$payment->getOrder()->getStoreId());		
			$this->_debug($vaultResponse->debug());
			$token = $vaultResponse->getToken();
		}
		$payment->setAdditionalInformation('token',$token);
		
		return $this;
		
	}

	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::capture()
	 */
	public function capture(Varien_Object $payment, $amount)
	{
		//TODO CHECK AMOUNT FOR SPLIT PAYMENT
		
		parent::capture($payment, $amount);
	
		if ($this->isPreauthorizeCapture($payment) /*|| $this->orderDue($payment->getOrder())*/)
			$this->_preauthorizeCapture($payment, $amount);
	
		$payment->setSkipTransactionCreation(true);
		return $this;
	}
	
	
	
	
	public function place($payment, $amount)
	{

		$order = $payment->getOrder();
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		$request = Mage::getModel('hipay/api_request',array($this));
		
		
		$payment->setAmount($amount);

		$token = $payment->getAdditionalInformation('token');
    	$gatewayParams =  $this->getGatewayParams($payment, $amount,$token); 
    	
    	$gatewayParams['operation'] =$this->getOperation();
   
    	$paymentProduct = null; 	
    	if($payment->getAdditionalInformation('use_oneclick'))
    	{
    		$cardId = $payment->getAdditionalInformation('selected_oneclick_card');
    		$card = Mage::getModel('hipay/card')->load($cardId);

    		if($card->getId() && $card->getCustomerId() == $customer->getId())
    			$paymentProduct = $card->getCcType();
    		else
    			Mage::throwException(Mage::helper('hipay')->__("Error with your card!"));
    		
    	}
    	else
    		$paymentProduct = $this->getCcTypeHipay($payment->getCcType());
    	
    	$gatewayParams['payment_product'] = $paymentProduct ;
    	$this->_debug($gatewayParams);
    	
    	
    	$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams,$payment->getOrder()->getStoreId());
    	
    	$this->_debug($gatewayResponse->debug());
    	
  		$redirectUrl =  $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
  		
  		return $redirectUrl;
    	
	}
	

	
	/**
	 * Validate payment method information object
	 *
	 * @param   Mage_Payment_Model_Info $info
	 * @return  Mage_Payment_Model_Abstract
	 */
	public function validate()
	{
		/*
		 * calling parent validate function
		*/
		parent::validate();
	
		$info = $this->getInfoInstance();
		
		if($info->getAdditionalInformation('use_oneclick'))
		{
			return $this;
		}
		
		$errorMsg = false;
		$availableTypes = explode(',',$this->getConfigData('cctypes'));
	
		$ccNumber = $info->getCcNumber();
	
		// remove credit card number delimiters such as "-" and space
		$ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
		$info->setCcNumber($ccNumber);
	
		$ccType = '';
	
		if (in_array($info->getCcType(), $availableTypes)){
			if ($this->validateCcNum($ccNumber)
			// Other credit card type number validation
			|| ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {
	
				$ccType = 'OT';
				$ccTypeRegExpList = array(
						//Solo, Switch or Maestro. International safe
						/*
				// Maestro / Solo
				'SS'  => '/^((6759[0-9]{12})|(6334|6767[0-9]{12})|(6334|6767[0-9]{14,15})'
						. '|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})'
						. '|(633[34][0-9]{12})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$/',
				*/
				// Solo only
				'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
				//Bancontact / mister cash
	            'BCMC' =>  '/^[0-9]{17}$/',
				'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'
                            . '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'
                            . '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                    // Visa
                    'VI'  => '/^4[0-9]{12}([0-9]{3})?$/',
					// CB
					'CB'  => '/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC'  => '/^5[1-5][0-9]{14}$/',
            		// American Express
            		'AE'  => '/^3[47][0-9]{13}$/',
            		// Discovery
            		'DI'  => '/^6011[0-9]{12}$/',
            		// JCB
            		'JCB' => '/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/',
	             );
	
	       foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
				if (preg_match($ccTypeRegExp, $ccNumber)) {
					$ccType = $ccTypeMatch;
					break;
				}
			}
			if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
				$errorMsg = Mage::helper('payment')->__('Credit card number mismatch with credit card type.');
			}
		}
		else {
			$errorMsg = Mage::helper('payment')->__('Invalid Credit Card Number');
		}
	
	}
	else {
		$errorMsg = Mage::helper('payment')->__('Credit card type is not allowed for this payment method.');
	}
	
		//validate credit card verification number
	if ($errorMsg === false && $this->hasVerification() && $info->getCcType() != 'BCMC') {
		$verifcationRegEx = $this->getVerificationRegEx();
			$regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
			if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
			$errorMsg = Mage::helper('payment')->__('Please enter a valid credit card verification number.');
			}
			}
	
			if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
			$errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
			}
	
			if($errorMsg){
				Mage::throwException($errorMsg);
			}
	
						//This must be after all validation conditions
        if ($this->getIsCentinelValidationEnabled()) {
	        $this->getCentinelValidator()->validate($this->getCentinelValidationData());
		}
	
				return $this;
	}
	
	public function hasVerification()
	{
		$configData = $this->getConfigData('useccv');
		if(is_null($configData)){
			return true;
		}
		return (bool) $configData;
	}
	
	public function getVerificationRegEx()
	{
		$verificationExpList = array(
				'VI' => '/^[0-9]{3}$/', // Visa
				'CB' => '/^[0-9]{3}$/', // Visa
				'MC' => '/^[0-9]{3}$/',       // Master Card
				'AE' => '/^[0-9]{4}$/',        // American Express
				'DI' => '/^[0-9]{3}$/',          // Discovery
				'SS' => '/^[0-9]{3,4}$/',
				'SM' => '/^[0-9]{3,4}$/', // Switch or Maestro
				'SO' => '/^[0-9]{3,4}$/', // Solo
				'OT' => '/^[0-9]{3,4}$/',
				'JCB' => '/^[0-9]{3,4}$/' //JCB
		);
		return $verificationExpList;
	}
	
	protected function _validateExpDate($expYear, $expMonth)
	{
		$date = Mage::app()->getLocale()->date();
		if (!$expYear || !$expMonth || ($date->compareYear($expYear) == 1)
		|| ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth) == 1))
		) {
			return false;
		}
		return true;
	}
	
	public function OtherCcType($type)
	{
		return $type=='OT' || $type=='CB';
	}
	
	/**
	 * Validate credit card number
	 *
	 * @param   string $cc_number
	 * @return  bool
	 */
	public function validateCcNum($ccNumber)
	{
		$cardNumber = strrev($ccNumber);
		$numSum = 0;
	
		for ($i=0; $i<strlen($cardNumber); $i++) {
			$currentNum = substr($cardNumber, $i, 1);
	
			/**
			 * Double every second digit
			*/
			if ($i % 2 == 1) {
				$currentNum *= 2;
			}
	
			/**
			 * Add digits of 2-digit numbers together
			 */
			if ($currentNum > 9) {
				$firstNum = $currentNum % 10;
				$secondNum = ($currentNum - $firstNum) / 10;
				$currentNum = $firstNum + $secondNum;
			}
	
			$numSum += $currentNum;
		}
	
		/**
		 * If the total has no remainder it's OK
		 */
		return ($numSum % 10 == 0);
	}
	
	/**
	 * Other credit cart type number validation
	 *
	 * @param string $ccNumber
	 * @return boolean
	 */
	public function validateCcNumOther($ccNumber)
	{
		return preg_match('/^\\d+$/', $ccNumber);
	}
	
	/**
	 * Check whether there are CC types set in configuration
	 *
	 * @param Mage_Sales_Model_Quote|null $quote
	 * @return bool
	 */
	public function isAvailable($quote = null)
	{
		return $this->getConfigData('cctypes', ($quote ? $quote->getStoreId() : null))
		&& parent::isAvailable($quote);
	}
	
	/**
	 * Whether centinel service is enabled
	 *
	 * @return bool
	 */
	public function getIsCentinelValidationEnabled()
	{
		return false !== Mage::getConfig()->getNode('modules/Mage_Centinel') && 1 == $this->getConfigData('centinel');
	}
	
	/**
	 * Instantiate centinel validator model
	 *
	 * @return Mage_Centinel_Model_Service
	 */
	public function getCentinelValidator()
	{
		$validator = Mage::getSingleton('centinel/service');
		$validator
		->setIsModeStrict($this->getConfigData('centinel_is_mode_strict'))
		->setCustomApiEndpointUrl($this->getConfigData('centinel_api_url'))
		->setStore($this->getStore())
		->setIsPlaceOrder($this->_isPlaceOrder());
		return $validator;
	}
	
	/**
	 * Return data for Centinel validation
	 *
	 * @return Varien_Object
	 */
	public function getCentinelValidationData()
	{
		$info = $this->getInfoInstance();
		$params = new Varien_Object();
		$params
		->setPaymentMethodCode($this->getCode())
		->setCardType($info->getCcType())
		->setCardNumber($info->getCcNumber())
		->setCardExpMonth($info->getCcExpMonth())
		->setCardExpYear($info->getCcExpYear())
		->setAmount($this->_getAmount())
		->setCurrencyCode($this->_getCurrencyCode())
		->setOrderNumber($this->_getOrderId());
		return $params;
	}
	
	/**
	 * Order increment ID getter (either real from order or a reserved from quote)
	 *
	 * @return string
	 */
	private function _getOrderId()
	{
		$info = $this->getInfoInstance();
	
		if ($this->_isPlaceOrder()) {
			return $info->getOrder()->getIncrementId();
		} else {
			if (!$info->getQuote()->getReservedOrderId()) {
				$info->getQuote()->reserveOrderId();
			}
			return $info->getQuote()->getReservedOrderId();
		}
	}
	
	/**
	 * Grand total getter
	 *
	 * @return string
	 */
	private function _getAmount()
	{
		$info = $this->getInfoInstance();
		if ($this->_isPlaceOrder()) {
			return (double)$info->getOrder()->getQuoteBaseGrandTotal();
		} else {
			return (double)$info->getQuote()->getBaseGrandTotal();
		}
	}
	
	/**
	 * Currency code getter
	 *
	 * @return string
	 */
	private function _getCurrencyCode()
	{
		$info = $this->getInfoInstance();
	
		if ($this->_isPlaceOrder()) {
			return $info->getOrder()->getBaseCurrencyCode();
		} else {
			return $info->getQuote()->getBaseCurrencyCode();
		}
	}
	
	/**
	 * Whether current operation is order placement
	 *
	 * @return bool
	 */
	private function _isPlaceOrder()
	{
		$info = $this->getInfoInstance();
		if ($info instanceof Mage_Sales_Model_Quote_Payment) {
			return false;
		} elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
			return true;
		}
	}
	

}