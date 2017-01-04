<?php
class Allopass_Hipay_Model_Api_Request
{
    const VAULT_ACTION_CREATE = 'create';
    
    const VAULT_ACTION_UPDATE = 'update';
    
    const VAULT_ACTION_LOOKUP = '';
    
    const GATEWAY_ACTION_ORDER = 'order';
    
    const GATEWAY_ACTION_MAINTENANCE = 'maintenance/transaction/';
    
    const GATEWAY_ACTION_HOSTED = "hpayment";

    
    /**
     *
     * @var Zend_Http_Client
     */
    protected $_client = null;
    
    protected $_methodInstance = null;
    
    protected $_storeId = null;
    
    public function __construct($methodInstance)
    {
        $this->_methodInstance = $methodInstance[0];
    }
    
    protected function getMethodInstance()
    {
        if (!$this->_methodInstance instanceof Mage_Payment_Model_Method_Abstract) {
            Mage::throwException("Method instance must be setted or must be type of Mage_Payment_Model_Method_Abstract");
        }
    
        return $this->_methodInstance;
    }
    
    /**
     *
     * @param Mage_Payment_Model_Method_Abstract $methodInstance
     */
    protected function setMethodInstance($methodInstance)
    {
        $this->_methodInstance = $methodInstance;
    }
    
    
    protected function getApiUsername($storeId=null)
    {
        if ($this->isTestMode()) {
            return $this->getConfig()->getApiUsernameTest($storeId);
        }
    
        return $this->getConfig()->getApiUsername($storeId);
    }
    
    protected function getApiPassword($storeId=null)
    {
        if ($this->isTestMode()) {
            return $this->getConfig()->getApiPasswordTest($storeId);
        }
    
        return $this->getConfig()->getApiPassword($storeId);
    }
    
    protected function isTestMode()
    {
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
            // ----------------------------------------------------------------------

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
    
    protected function _request($uri, $params=array(), $method=Zend_Http_Client::POST, $storeId=null)
    {
        if ($method == Zend_Http_Client::POST) {
            $this->getClient()->setParameterPost($params);
        } else {
            $this->getClient()->setParameterGet($params);
        }

        $this->getClient()->setUri($uri);
    
        /* @var $response Zend_Http_Response */
        $response = $this->getClient()->request($method);
    
        if ($response->isSuccessful()) {

            //$this->getClient()->getAdapter()->close();
            return json_decode($response->getBody(), true);
        } else {
            /* @var $error Allopass_Hipay_Model_Api_Response_Error */
            $error = Mage::getSingleton('hipay/api_response_error');
            $error->setData(json_decode($response->getBody(), true));
            $messageError = "Code: " . $error->getCode() . ". Message: " . $error->getMessage();
            if ($error->getDescription() != "") {
                $messageError .= ". Details: " . $error->getDescription();
            }
            
            Mage::throwException($messageError);
        }
    }
    
    public function getMethodHttp($action)
    {
        if ($action == self::VAULT_ACTION_LOOKUP) {
            return Zend_Http_Client::GET;
        }
    
        return Zend_Http_Client::POST;
    }
    
    /**
     *
     */
    protected function getVaultApiEndpoint($storeId=null)
    {
        if ($this->isTestMode()) {
            return $this->getConfig()->getVaultEndpointTest($storeId);
        }
    
        return $this->getConfig()->getVaultEndpoint($storeId);
    }
    
    /**
     *
     */
    protected function getGatewayApiEndpoint($storeId=null)
    {
        if ($this->isTestMode()) {
            return $this->getConfig()->getGatewayEndpointTest($storeId);
        }
    
        return $this->getConfig()->getGatewayEndpoint($storeId);
    }
    
    
    /**
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Allopass_Hipay_Model_Response_Vault
     */
    public function vaultRequest($action, $params, $storeId=null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getVaultApiEndpoint($storeId) . $action . "/";
    
        /* @var $response Allopass_Hipay_Model_Api_Response_Vault */
        $response = Mage::getSingleton('hipay/api_response_vault', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));
    
        return $response;
    }
    
    /**
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Allopass_Hipay_Model_Response_Abstract
     */
    public function gatewayRequest($action, $params, $storeId=null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getGatewayApiEndpoint($storeId) . $action;
    
        /* @var $response Allopass_Hipay_Model_Api_Response_Gateway */
        $response  = Mage::getModel('hipay/api_response_gateway', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));
        //Mage::log($response, null, 'log-hipay-gatewayRequest.log', true);

        return $response;
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
