<?php
class Allopass_Hipay_Model_Api_Request
{
    const VAULT_ACTION_CREATE = 'create';
    
    const VAULT_ACTION_UPDATE = 'update';
    
    const VAULT_ACTION_LOOKUP = '';
    
    const GATEWAY_ACTION_ORDER = 'v1/order';
    
    const GATEWAY_ACTION_MAINTENANCE = 'v1/maintenance/transaction/';
    
    const GATEWAY_ACTION_HOSTED = "v1/hpayment";

    const GATEWAY_SECURITY_SETTINGS= "v2/security-settings";

    const TYPE_RESPONSE_GATEWAY = "hipay/api_response_gateway";

    const TYPE_RESPONSE_GOMMON = "hipay/api_response_common";

    /**
     *
     * @var Zend_Http_Client
     */
    protected $_client = null;
    
    protected $_methodInstance = null;
    
    protected $_storeId = null;

    protected $_useMotoCredentials = false;
    
    public function __construct($methodInstance)
    {
        $this->_methodInstance = $methodInstance[0];
    }
    
    protected function getMethodInstance()
    {
        return $this->_methodInstance;
    }

    /**
     * @return bool
     */
    public function getUseMotoCredentials()
    {
        return $this->_useMotoCredentials;
    }

    /**
     *
     * @param Mage_Payment_Model_Method_Abstract $methodInstance
     */
    protected function setMethodInstance($methodInstance)
    {
        $this->_methodInstance = $methodInstance;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    protected function getApiUsername($storeId = null)
    {
        $this->_useMotoCredentials = false;

        if ($this->getMethodInstance() && $this->getMethodInstance()->isAdmin()) {
            if ($this->isTestMode()) {
                if ($this->getConfig()->getApiUsernameTestMoto($storeId)) {
                    $this->_useMotoCredentials = true;
                    return $this->getConfig()->getApiUsernameTestMoto($storeId);
                }
            } else {
                if ($this->getConfig()->getApiUsernameMoto($storeId)) {
                    $this->_useMotoCredentials = true;
                    return $this->getConfig()->getApiUsernameMoto($storeId);
                }
            }
        }

        if ($this->isTestMode($storeId)) {
            return $this->getConfig()->getApiUsernameTest($storeId);
        } else {
            return $this->getConfig()->getApiUsername($storeId);
        }
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    protected function getApiPassword($storeId=null)
    {
        if ($this->getMethodInstance() && $this->getMethodInstance()->isAdmin()) {
            if ($this->isTestMode()) {
                if ($this->getConfig()->getApiPasswordTestMoto($storeId)) {
                    return $this->getConfig()->getApiPasswordTestMoto($storeId);
                }
            } else {
                if ($this->getConfig()->getApiPasswordMoto($storeId)) {
                    return $this->getConfig()->getApiPasswordMoto($storeId);
                }
            }
        }

        if ($this->isTestMode($storeId)) {
            return $this->getConfig()->getApiPasswordTest($storeId);
        } else {
            return $this->getConfig()->getApiPassword($storeId);
        }
    }

    protected function isTestMode($storeId=null)
    {
        // Method is null for calling from admin
        if ($this->getMethodInstance() == null) {
            // Take priority of the test credential if informed
            return (empty($this->getConfig()->getApiPasswordTest($storeId))) ? false : true ;
        }

        return (bool)$this->getMethodInstance()->getConfigData('is_test_mode');
    }
    
    
    
    /**
     *
     * @return Allopass_Hipay_Model_Config $config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }
    
    /**
     * Get client HTTP
     * @return Zend_Http_Client
     */
    public function getClient()
    {
        if (is_null($this->_client)) {
            //$credentials = $this->getApiUsername($storeId) . ':' . $this->getApiPassword($storeId);
                
            //adapter options
            $config = array('curloptions' => array(
                    //CURLOPT_USERPWD=>$credentials,
                    //CURLOPT_HTTPHEADER => array('Accept: application/json'),
                    CURLOPT_FAILONERROR => false,
                    CURLOPT_HEADER=>false,
                    CURLOPT_RETURNTRANSFER=>true),
            );

            // ----------------------------------------------------------------------
            // init proxy if not empty
            // ----------------------------------------------------------------------
            $proxy_host = Mage::getStoreConfig('hipay/hipay_api/proxy_host', Mage::app()->getStore());
            // if host not empty, we use the proxy parameters
            if (!empty($proxy_host)) {
                $proxy_user = Mage::getStoreConfig('hipay/hipay_api/proxy_user', Mage::app()->getStore());
                $proxy_pass = Mage::getStoreConfig('hipay/hipay_api/proxy_pass', Mage::app()->getStore());
                $proxy_port = Mage::getStoreConfig('hipay/hipay_api/proxy_port', Mage::app()->getStore());
                // init config for cURL
                $config['curloptions'][CURLOPT_PROXYUSERPWD] = true;
                $config['curloptions'][CURLOPT_PROXY] = $proxy_host.':'.$proxy_port;
                // if user and password not empty, we use the credentials
                if (!empty($proxy_user) && !empty($proxy_pass)) {
                    $config['curloptions'][CURLOPT_PROXYUSERPWD] = $proxy_user.':'.$proxy_pass;
                }
            }
           // Mage::log($config, null, 'curl.log');
            // ---------------------------------------------------------------------
			try {
			    //innitialize http client and adapter curl
				$adapter = Mage::getSingleton('hipay/api_http_client_adapter_curl');
	
				$this->_client = new Zend_Http_Client();
				//$adapter->setConfig($config);
				$this->_client->setConfig($config);
				$this->_client->setHeaders(array('Content-Type'=>'application/xml',
						'Accept'=>'application/json'));
				$this->_client->setAuth($this->getApiUsername($this->getStoreId()),
						$this->getApiPassword($this->getStoreId()),
						Zend_Http_Client::AUTH_BASIC);
				$this->_client->setAdapter($adapter);
	
	
			} catch (Exception $e) {
				Mage::throwException($e);
			}
		}
	
		return $this->_client;
	}
	
	protected function _request($uri,$params=array(),$method=Zend_Http_Client::POST,$storeId=null,$throwException=true)
	{
	
		if($method == Zend_Http_Client::POST)
			$this->getClient()->setParameterPost($params);
		else
			$this->getClient()->setParameterGet($params);
	
		$this->getClient()->setUri($uri);

		/* @var $response Zend_Http_Response */
		$response = $this->getClient()->request($method);

		if($response->isSuccessful())
		{
			//$this->getClient()->getAdapter()->close();
			return json_decode($response->getBody(),true);
		}
		else
		{
			/* @var $error Allopass_Hipay_Model_Api_Response_Error */
			$error = Mage::getSingleton('hipay/api_response_error');
			$error->setData(json_decode($response->getBody(),true));
			$messageError = "Code: " . $error->getCode() . ". Message: " . $error->getMessage();
			if($error->getDescription() != "")
				$messageError .= ". Details: " . $error->getDescription();
			
			if ($throwException){
				Mage::throwException($messageError);
			}else{
				return $error;
			}
		}
			
	
	}
	
	public function getMethodHttp($action)
	{
		if($action == self::VAULT_ACTION_LOOKUP || $action == self::GATEWAY_SECURITY_SETTINGS )
			return Zend_Http_Client::GET;
	
		return Zend_Http_Client::POST;
	}
	
	/**
	 *
	 */
	protected function getVaultApiEndpoint($storeId=null) {
		if($this->isTestMode())
			return $this->getConfig()->getVaultEndpointTest($storeId);
	
		return $this->getConfig()->getVaultEndpoint($storeId);
	
	}
	
	/**
	 *
	 */
	protected function getGatewayApiEndpoint($storeId=null) {
		if($this->isTestMode())
			return $this->getConfig()->getGatewayEndpointTest($storeId);
	
		return $this->getConfig()->getGatewayEndpoint($storeId);
	
	}
	
	
	/**
	 *
	 * @param string $action
	 * @param array $params
	 * @param int $storeId
	 * @return Allopass_Hipay_Model_Response_Vault
	 */
	public function vaultRequest($action,$params,$storeId=null)
	{
		$this->setStoreId($storeId);
		$uri = $this->getVaultApiEndpoint($storeId) . $action . "/";
	
		/* @var $response Allopass_Hipay_Model_Api_Response_Vault */
		$response = Mage::getSingleton('hipay/api_response_vault', $this->_request($uri,$params,$this->getMethodHttp($action),$storeId));
	
		return $response;
	}
	
	/**
	 *
	 * @param string $action
	 * @param array $params
	 * @param int $storeId
	 * @return Allopass_Hipay_Model_Response_Abstract
	 */
	public function gatewayRequest($action,$params,$storeId=null,$typeResponse = self::TYPE_RESPONSE_GATEWAY)
	{
		$this->setStoreId($storeId);
		$uri = $this->getGatewayApiEndpoint($storeId) . $action;
	
		/* @var $response Allopass_Hipay_Model_Api_Response_Gateway */
        $response = $this->_request($uri,$params,$this->getMethodHttp($action),$storeId);

        switch ($typeResponse) {
            case self::TYPE_RESPONSE_GATEWAY:
                return Mage::getModel('hipay/api_response_gateway',$response);
            case self::TYPE_RESPONSE_GOMMON:
                return $response;
        }

		return $response;
	}

    /**
     *  Test if test or production credentials are filled
     *
     *  @param int $storeId
     *  @return bool
     */
    public function existsCredentials($storeId = null) {
        return ($this->getConfig()->getApiPassword($storeId) || $this->getConfig()->getApiPasswordTest($storeId));
    }

	public function setStoreId($storeId)
	{
		$this->_storeId = $storeId;
		return $this;
	}
	
	public function getStoreId()
	{
		return $this->_storeId;
	}
	
}

