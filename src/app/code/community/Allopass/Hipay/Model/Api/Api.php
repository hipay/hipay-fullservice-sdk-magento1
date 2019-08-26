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

use HiPay\Fullservice\HTTP\Configuration\Configuration;
use HiPay\Fullservice\HTTP\SimpleHTTPClient;
use HiPay\Fullservice\Gateway\Client\GatewayClient;
use HiPay\Fullservice\Request\RequestSerializer;

/**
 *
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Api
{

    protected $_methodInstance = null;
    protected $_payment = null;
    protected $_amount = null;

    public function __construct($args)
    {
        $this->_methodInstance = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_amount = $args["amount"];
    }

    public function getHostedPaymentPage($productList, $additionalParameters)
    {
        $gatewayClient = $this->createGatewayClient();
        //Set data to send to the API
        $directPostFormatter = Mage::getModel(
            'hipay/api_formatter_request_hostedPayment',
            array(
                "paymentMethod" => $this->_methodInstance,
                "payment" => $this->_payment,
                "amount" => $this->_amount,
                "productList" => $productList,
                "additionalParameters" => $additionalParameters
            )
        );

        $orderRequest = $directPostFormatter->generate();

        $this->logRequest($orderRequest);

        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction object
        return $gatewayClient->requestHostedPaymentPage($orderRequest);
    }

    public function requestDirectPost(
        $paymentProduct,
        $paymentMethodFormatter,
        $deviceFingerprint,
        $additionalParameters
    ) {

        $gatewayClient = $this->createGatewayClient();
        //Set data to send to the API
        $directPostFormatter = Mage::getModel(
            'hipay/api_formatter_request_directPost',
            array(
                "paymentMethod" => $this->_methodInstance,
                "payment" => $this->_payment,
                "amount" => $this->_amount,
                "paymentProduct" => $paymentProduct,
                "paymentMethodFormatter" => $paymentMethodFormatter,
                "deviceFingerprint" => $deviceFingerprint,
                "additionalParameters" => $additionalParameters
            )
        );

        $orderRequest = $directPostFormatter->generate();

        $this->logRequest($orderRequest);

        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction object
        return $gatewayClient->requestNewOrder($orderRequest);
    }

    public function requestMaintenance($operation, $transactionReference, $operationId)
    {
        $gatewayClient = $this->createGatewayClient();

        $maintenanceFormatter = Mage::getModel(
            'hipay/api_formatter_request_maintenance',
            array(
                "paymentMethod" => $this->_methodInstance,
                "payment" => $this->_payment,
                "amount" => $this->_amount,
                "operation" => $operation,
                "operationId" => $operationId,
            )
        );

        $maintenanceRequest = $maintenanceFormatter->generate();

        $this->logRequest($maintenanceRequest);

        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction object
        return $gatewayClient->requestMaintenanceOperation(
            $operation,
            $transactionReference,
            $maintenanceRequest->amount,
            $operationId,
            $maintenanceRequest
        );
    }

    protected function createGatewayClient()
    {
        $proxy = $this->getProxyConfig();

        $sandbox = $this->isTestMode();
        $credentials = $this->getApiCredentials();

        $env = ($sandbox) ? Configuration::API_ENV_STAGE : Configuration::API_ENV_PRODUCTION;

        $config = new Configuration(array(
            "apiUsername" => $credentials["username"],
            "apiPassword" => $credentials["password"],
            "apiEnv" => $env,
            "proxy" => $proxy
        ));

        //Instantiate client provider with configuration object
        $clientProvider = new SimpleHTTPClient($config);

        //Create your gateway client
        return new GatewayClient($clientProvider);
    }

    protected function getApiCredentials($storeId = null)
    {
        if ($this->isMoto()) {
            if ($this->isTestMode()) {
                if ($this->getConfig()->isApiCredentialsMotoEmpty(true, $storeId)) {
                    return $this->getConfig()->getApiCredentialsTestMoto($storeId);
                }
            } else {
                if ($this->getConfig()->isApiCredentialsMotoEmpty(false, $storeId)) {
                    return $this->getConfig()->getApiCredentialsMoto($storeId);
                }
            }
        }

        if ($this->isTestMode($storeId)) {
            return $this->getConfig()->getApiCredentialsTest($storeId);
        } else {
            return $this->getConfig()->getApiCredentials($storeId);
        }
    }

    protected function isTestMode()
    {
        return (bool)$this->_methodInstance->getConfigData('is_test_mode');
    }

    protected function getConfig()
    {
        return Mage::getSingleton('hipay/config');
    }

    protected function isMoto()
    {
        return $this->_payment && $this->_payment->getAdditionalInformation("isMoto");
    }

    protected function getProxyConfig()
    {
        $proxy = array();
        $proxyHost = Mage::getStoreConfig('hipay/hipay_api/proxy_host', Mage::app()->getStore());
        // if host not empty, we use the proxy parameters
        if (!empty($proxyHost)) {
            $proxy = array(
                "host" => $proxyHost,
                "port" => Mage::getStoreConfig('hipay/hipay_api/proxy_port', Mage::app()->getStore()),
                "user" => Mage::getStoreConfig('hipay/hipay_api/proxy_user', Mage::app()->getStore()),
                "password" => Mage::getStoreConfig('hipay/hipay_api/proxy_pass', Mage::app()->getStore())
            );
        }

        return $proxy;
    }

    protected function logRequest($orderRequest)
    {
        $serializer = new RequestSerializer($orderRequest);
        $this->_methodInstance->debugData($serializer->toArray());
    }
}
