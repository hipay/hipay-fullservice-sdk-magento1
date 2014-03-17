<?php
class Allopass_Hipay_Model_Method_Hosted extends Allopass_Hipay_Model_Method_Abstract
{
	
	protected $_code  = 'hipay_hosted';
	
	protected $_formBlockType = 'hipay/form_hosted';
	protected $_infoBlockType = 'hipay/info_hosted';	
	
	
	public function getOrderPlaceRedirectUrl()
	{
			
		return Mage::getUrl(str_replace("_", "/", $this->getCode()).'/sendRequest',array('_secure' => true));
	}
	
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
		$info->setAdditionalInformation('create_oneclick',$data->getOneclick() == "create_oneclick" ? 1 : 0)
		->setAdditionalInformation('use_oneclick',$data->getOneclick() == "use_oneclick" ? 1 : 0)
		;
	
		return $this;
	}
	

	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::capture()
	 */
	public function capture(Varien_Object $payment, $amount)
	{
		parent::capture($payment, $amount);
		
		if (self::isPreauthorizeCapture($payment))
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
		
		$token = null;
		if($payment->getAdditionalInformation('use_oneclick'))
		{
			$token = Mage::getSingleton('customer/session')->getCustomer()->getHipayAliasOneclick();
		}
		
    	$gatewayParams = $this->getGatewayParams($payment, $amount,$token);
    	
    	if(is_null($token))
    	{
    			
	    	$gatewayParams['payment_product'] = 'cb' ;
	    	$gatewayParams['operation'] = $this->getOperation();
	    	$gatewayParams['css'] = $this->getConfigData('css_url');
			$gatewayParams['template'] = $this->getConfigData('display_iframe') ? 'iframe' :  $this->getConfigData('template');
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
	    	
	    	$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_HOSTED,$gatewayParams);
	    	
	    	$this->_debug($gatewayResponse->debug());
	
			return  $gatewayResponse->getForwardUrl();
    	}
    	else
    	{
    		$gatewayParams['operation'] = $this->getOperation();
    		$gatewayParams['payment_product']  = Mage::getSingleton('customer/session')->getCustomer()->getHipayCcType();
    		
    		$this->_debug($gatewayParams);
    		 
    		$gatewayResponse = $request->gatewayRequest(Allopass_Hipay_Model_Api_Request::GATEWAY_ACTION_ORDER,$gatewayParams);
    		 
    		$this->_debug($gatewayResponse->debug());
    		 
    		$redirectUrl =  $this->processResponseToRedirect($gatewayResponse, $payment, $amount);
    		
    		return $redirectUrl;
    	}

	}
}