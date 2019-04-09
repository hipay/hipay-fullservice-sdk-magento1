<?php

/**
 * HiPay Fullservice SDK Magento 1
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Request
{
    const GATEWAY_ACTION_ORDER = 'v1/order';

    const GATEWAY_ACTION_MAINTENANCE = 'v1/maintenance/transaction/';

    const GATEWAY_ACTION_HOSTED = "v1/hpayment";

    const GATEWAY_SECURITY_SETTINGS = "v2/security-settings";

    const TYPE_RESPONSE_GATEWAY = "hipay/api_response_gateway";

    const TYPE_RESPONSE_COMMON = "hipay/api_response_common";

    /**
     *
     * @var Zend_Http_Client
     */
    protected $_client = null;

    protected $_methodInstance = null;

    protected $_storeId = null;

    protected $_useMotoCredentials = false;

    protected $_environment = null;

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
    protected function getApiPassword($storeId = null)
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

    protected function isTestMode($storeId = null)
    {
        // Method is null for calling from admin
        if ($this->getMethodInstance() == null) {
            // Take priority of the test credential if informed
            return (empty($this->getConfig()->getApiPasswordTest($storeId))) ? false : true;
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
            //adapter options
            $config = array(
                'curloptions' => array(
                    CURLOPT_FAILONERROR => false,
                    CURLOPT_HEADER => false,
                    CURLOPT_RETURNTRANSFER => true
                ),
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
                $config['curloptions'][CURLOPT_PROXY] = $proxy_host . ':' . $proxy_port;
                // if user and password not empty, we use the credentials
                if (!empty($proxy_user) && !empty($proxy_pass)) {
                    $config['curloptions'][CURLOPT_PROXYUSERPWD] = $proxy_user . ':' . $proxy_pass;
                }
            }
            // ---------------------------------------------------------------------
            try {
                //innitialize http client and adapter curl
                $adapter = Mage::getSingleton('hipay/api_http_client_adapter_curl');

                $this->_client = new Zend_Http_Client();
                $this->_client->setConfig($config);
                $this->_client->setHeaders(
                    array(
                        'Content-Type' => 'application/xml',
                        'Accept' => 'application/json'
                    )
                );

                $this->_client->setAdapter($adapter);
            } catch (Exception $e) {
                Mage::throwException($e);
            }
        }

        $this->setAuthentification();
        return $this->_client;
    }

    /**
     * Set Basic Authentification with credentials according to environment
     *
     * If Environment exist, Do not take payment method configuration
     *
     */
    protected function setAuthentification()
    {
        if (!empty($this->_environment)) {
            switch ($this->_environment) {
                case ScopeConfig::PRODUCTION:
                    $apiUsername = $this->getConfig()->getApiUsername($this->getStoreId());
                    $apiPassword = $this->getConfig()->getApiPassword($this->getStoreId());
                    break;
                case ScopeConfig::TEST:
                    $apiUsername = $this->getConfig()->getApiUsernameTest($this->getStoreId());
                    $apiPassword = $this->getConfig()->getApiPasswordTest($this->getStoreId());
                    break;
                case ScopeConfig::PRODUCTION_MOTO:
                    $apiUsername = $this->getConfig()->getApiUsernameMoto($this->getStoreId());
                    $apiPassword = $this->getConfig()->getApiPasswordMoto($this->getStoreId());
                    break;
                case ScopeConfig::TEST_MOTO:
                    $apiUsername = $this->getConfig()->getApiUsernameTestMoto($this->getStoreId());
                    $apiPassword = $this->getConfig()->getApiPasswordTestMoto($this->getStoreId());
                    break;
            }
        } else {
            $apiUsername = $this->getApiUsername($this->getStoreId());
            $apiPassword = $this->getApiPassword($this->getStoreId());
        }

        $this->_client->setAuth($apiUsername, $apiPassword, Zend_Http_Client::AUTH_BASIC);
    }

    /**
     * @param $uri
     * @param array $params
     * @param string $method
     * @param null $storeId
     * @param bool $throwException
     * @return Allopass_Hipay_Model_Api_Response_Error|mixed
     * @throws Mage_Core_Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function _request(
        $uri,
        $params = array(),
        $method = Zend_Http_Client::POST,
        $storeId = null,
        $throwException = true
    ) {

        if ($method == Zend_Http_Client::POST) {
            $this->getClient()->setParameterPost($params);
        } else {
            $this->getClient()->setParameterGet($params);
        }

        $this->getClient()->setUri($uri);

        /* @var $response Zend_Http_Response */
        $response = $this->getClient()->request($method);

        if ($response->isSuccessful()) {
            return json_decode($response->getBody(), true);
        } else {
            /* @var $error Allopass_Hipay_Model_Api_Response_Error */
            $error = Mage::getSingleton('hipay/api_response_error');
            $error->setData(json_decode($response->getBody(), true));
            $messageError = "Code: " . $error->getCode() . ". Message: " . $error->getMessage();
            if ($error->getDescription() != "") {
                $messageError .= ". Details: " . $error->getDescription();
            }

            if ($throwException) {
                Mage::throwException($messageError);
            } else {
                return $error;
            }
        }


    }

    public function getMethodHttp($action)
    {
        if ($action == self::GATEWAY_SECURITY_SETTINGS) {
            return Zend_Http_Client::GET;
        }

        return Zend_Http_Client::POST;
    }

    /**
     *
     */
    protected function getGatewayApiEndpoint($storeId = null)
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
     * @param string $payment
     *
     * @return Allopass_Hipay_Model_Response_Abstract
     */
    public function gatewayRequest(
        $action,
        $params,
        $storeId = null,
        $typeResponse = self::TYPE_RESPONSE_GATEWAY,
        $payment = null
    ) {
        $this->setStoreId($storeId);
        $uri = $this->getGatewayApiEndpoint($storeId) . $action;

        if (preg_match('/maintenance/', $uri) && $payment != null ) {
            $isMoto = $payment->getAdditionalInformation('isMoto');
            $this->_environment =  $isMoto ? ScopeConfig::PRODUCTION_MOTO : ScopeConfig::PRODUCTION;
            if ($this->isTestMode()) {
                $this->_environment =  $isMoto ? ScopeConfig::TEST_MOTO : ScopeConfig::TEST;
            }
        }

        /* @var $response Allopass_Hipay_Model_Api_Response_Gateway */
        $response = $this->_request($uri, $params, $this->getMethodHttp($action), $storeId, true);

        switch ($typeResponse) {
            case self::TYPE_RESPONSE_GATEWAY:
                return Mage::getModel('hipay/api_response_gateway', $response);
            case self::TYPE_RESPONSE_COMMON:
                return $response;
        }

        return $response;
    }

    /**
     * Test if "test" or "production" credentials are filled
     *
     * @param null $storeId
     * @return bool|mixed
     */
    public function existsCredentials($storeId = null)
    {
        $existCredential = false;
        switch ($this->_environment) {
            case ScopeConfig::PRODUCTION:
                $existCredential = $this->getConfig()->getApiPassword($storeId);
                break;
            case ScopeConfig::TEST:
                $existCredential = $this->getConfig()->getApiPasswordTest($storeId);
                break;
            case ScopeConfig::PRODUCTION_MOTO:
                $existCredential = $this->getConfig()->getApiPasswordMoto($storeId);
                break;
            case ScopeConfig::TEST_MOTO:
                $existCredential = $this->getConfig()->getApiPasswordTestMoto($storeId);
                break;
        }

        return $existCredential;
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

    /**
     *  Set environment platform for Request (Prod, Test, Prod Moto, Test Moto)
     *
     * @param ScopeConfig $environment
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
        return $this;
    }


    /**
     *  Get current environment platform for Request (Prod, Test, Prod Moto, Test Moto)
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }
}

