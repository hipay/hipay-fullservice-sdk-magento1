<?php
class Allopass_Hipay_Model_Observer
{
    /**
    * Cancel orders stayed in pending because customer not validated payment form
    */
    public function cancelOrdersInPending()
    {
        $methodCodes = array();
        //Select only method with cancel orders enabled
        foreach (Mage::helper('hipay')->getHipayMethods() as $code=>$model) {
            if (Mage::getStoreConfigFlag('payment/'.$code."/cancel_pending_order")) {
                $methods[$code] = Mage::getStoreConfig('payment/'.$code."/delay_cancel_pending_order");
            }
        }
        
        if (count($methods) < 1) {
            return $this;
        }

        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        foreach ($methods as $key => $delay) {
            $date = new Zend_Date();
            if (is_numeric($delay)) {
                $delayMinutes = 60 *  $delay;
            } else {
                $delayMinutes = 30;
            }

            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToSelect(array('entity_id', 'increment_id', 'store_id', 'state'))
                ->addFieldToFilter('main_table.state', Mage_Sales_Model_Order::STATE_NEW)
                ->addFieldToFilter('op.method', array('eq' => $key))
                ->addAttributeToFilter('created_at',
                    array('to' => ($date->subMinute($delayMinutes)->toString('Y-MM-dd HH:mm:ss'))))
                ->join(array('op' => 'sales/order_payment'), 'main_table.entity_id=op.parent_id', array('method'));

          /* @var $order Mage_Sales_Model_Order */

            if (count($collection) > 1) {
                Mage::helper('hipay')->debug('##########################################');
                Mage::helper('hipay')->debug('# Start process "cancelOrdersInPending"');
                Mage::helper('hipay')->debug(count($collection) . ' orders to cancel ');
                Mage::helper('hipay')->debug('# Method : ' . $key);
                Mage::helper('hipay')->debug('# Created at : ' . $date->subMinute($delayMinutes)->toString('Y-MM-dd HH:mm:ss'));
            }

            foreach ($collection as $order) {
                if ($order->canCancel()) {
                    try {
                        $order->cancel();
                        $order
                            ->addStatusToHistory($order->getStatus(),// keep order status/state
                                Mage::helper('hipay')->__("Order canceled automatically by cron because order is pending since %d minutes",
                                    $delayMinutes));

                        $order->save();
                        Mage::helper('hipay')->debug('# Order is canceled :' . $order->getIncrementId() );
                    } catch (Exception $e) {
                        Mage::helper('hipay')->debug('# Error in cancel process for order :' . $order->getIncrementId() .' ' .$e->getMessage() );
                        Mage::logException($e);
                    }
                }else{
                    Mage::helper('hipay')->debug('# Order is not cancelable for order : ' . $order->getIncrementId());
                }
            }

            if (count($collection) > 1) {
                Mage::helper('hipay')->debug('# End process "cancelOrdersInPending"');
                Mage::helper('hipay')->debug('##########################################');
            }
        }

        return $this;
    }

    public function manageOrdersInPendingCapture()
    {
        $methods = array('hipay_cc','hipay_hosted');
        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('status', 'pending_capture');

        /* @var $order Mage_Sales_Model_Order */
        foreach ($collection as $order) {
            if (!in_array($order->getPayment()->getMethod(), $methods)) {
                continue;
            }

            $orderDate = "";
        }
    }

    public function displaySectionCheckoutIframe($observer)
    {
        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
        if ($payment->getAdditionalInformation('use_oneclick')) {
            return $this;
        }
        /* @var $controller Mage_Checkout_OnepageController */
        $controller = $observer->getControllerAction();

        $result = Mage::helper('core')->jsonDecode($controller->getResponse()->getBody());

        //TODO check if payment method is hosted and iframe active and is success
        $methodInstance =  $payment->getMethodInstance();
        if ($result['success']
        && ($methodInstance->getCode() == 'hipay_hosted' ||  $methodInstance->getCode() == 'hipay_hostedxtimes')
        && $methodInstance->getConfigData('display_iframe')) {
            $result['iframeUrl'] = $result['redirect'];
        }

        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return $this;
    }

    /**
     *  Process Payment for collection of split payment
     *
     * @param Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    private function processSplitPayment($splitPayments){
        foreach ($splitPayments as $splitPayment) {
            $splitInfo =  $splitPayment->getSplitNumber() . ' for order ' . $splitPayment->getOrderId() . ' with amount ' . $splitPayment->getAmountToPay();
            try {
                Mage::helper('hipay')->debug('# Pay ' . $splitInfo );
                $splitPayment->pay();
                Mage::helper('hipay')->debug('# Pay Success ' . $splitInfo );
            } catch (Exception $e) {
                Mage::helper('hipay')->debug('# Pay Error with ' .  $splitInfo . ' : ' . $e->getMessage());
                $splitPayment->sendErrorEmail();
                Mage::logException($e);
            }
        }
    }

    /*
     *  Check pending and failed payment and pay them
     *
     */
    public function paySplitPayments()
    {
        $date = new Zend_Date();
        Mage::helper('hipay')->debug('###################################');
        Mage::helper('hipay')->debug('# Start process "paySplitPayments"');

        $splitPaymentsFailed = Mage::getModel('hipay/splitPayment')->getCollection()
            ->addFieldToFilter('status', array('eq'=> Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_FAILED))
            ->addFieldTofilter('attempts', array('lteq' => 3));
        Mage::helper('hipay')->debug('# ' . count($splitPaymentsFailed) . ' splits in failed to pay ');
        $this->processSplitPayment($splitPaymentsFailed);

        $splitPaymentsPending = Mage::getModel('hipay/splitPayment')->getCollection()
        ->addFieldToFilter('status', array('eq'=> Allopass_Hipay_Model_SplitPayment::SPLIT_PAYMENT_STATUS_PENDING))
        ->addFieldTofilter('date_to_pay', array('to' => $date->toString('Y-MM-dd 00:00:00')))
        ->addFieldTofilter('attempts', array('lteq' => 3));
        Mage::helper('hipay')->debug('# ' . count($splitPaymentsPending) . ' splits in pending to pay ');
        $this->processSplitPayment($splitPaymentsPending);

        Mage::helper('hipay')->debug('# End process "paySplitPayments"');
        Mage::helper('hipay')->debug('###################################');
    }

    /**
     * @param $observer
     * @return $this
     */
    public function arrangeOrderView($observer)
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_View|Mage_Adminhtml_Block_Sales_Transactions_Detail */
        $block = $observer->getBlock();

        /* @var $order Mage_Sales_Model_Order */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $isAllowedAction = Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/review_payment');
            if (!$isAllowedAction) {
                return $this;
            }

            $order = $block->getOrder();

            if (strpos($order->getPayment()->getMethod(), "hipay") === false) {
                return $this;
            }

            if ($order->canReviewPayment()) {
                $url = $block->getUrl("*/payment/reviewCapturePayment");
                $message = Mage::helper('sales')->__('Are you sure you want to accept this payment?');
                $block->addButton('accept_capture_payment', array(
                'label'     => Mage::helper('hipay')->__('Accept and Capture Payment'),
                'onclick'   => "confirmSetLocation('{$message}', '{$url}')",
                ));
            }
        } elseif ($block instanceof Mage_Adminhtml_Block_Sales_Transactions_Detail) {
            $txnId = $block->getTxnIdHtml();
            $orderIncrementId = $block->getOrderIncrementIdHtml();


            $order = Mage::getModel('sales/order')->loadByIncrementId(trim($orderIncrementId));
            if ($order->getId() && strpos($order->getPayment()->getMethod(), 'hipay') !== false) {
                $link = '<a href="https://merchant.hipay-tpp.com//transaction/detail/index/trxid/'.$txnId.'" target="_blank">'.$txnId.'</a>';
                $block->setTxnIdHtml($link);
            }
        }
    }
    /**
    * Disallow refund action in some cases
    * Used only for layout render
    * @param Varien_Object $observer
    */
    public function orderCanRefund($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        if ($order->getStatus() == Allopass_Hipay_Model_Method_Abstract::STATUS_CAPTURE_REQUESTED) {
            $order->setForcedCanCreditmemo(false);
            $order->setForcedCanCreditmemoFromHipay(true);
        } elseif ($order->getPayment() && $order->getPayment()->getMethod() == 'hipay_cc' && strtolower($order->getPayment()->getCcType()) == 'bcmc') {
            $order->setForcedCanCreditmemo(false);
            $order->setForcedCanCreditmemoFromHipay(true);
        } elseif ($order->getPayment() && strpos($order->getPayment()->getMethod(), 'hipay') !== false) {

            //If configuration validate order with status 117 (capture requested) and Notification 118 (Captured) is not received
            // we disallow refund
            if (((int)$order->getPayment()->getMethodInstance()->getConfigData('hipay_status_validate_order') == 117)  === true) {
                $histories = Mage::getResourceModel('sales/order_status_history_collection')
                ->setOrderFilter($order)
                ->addFieldToFilter('comment',
                array(
                // for new order
                array('like'=>'%code-118%'),
                // for old order
                array('like'=>'%: 118 Message: %')
                ));

                if ($histories->count() < 1) {
                    $order->setForcedCanCreditmemo(false);
                    $order->setForcedCanCreditmemoFromHipay(true);
                }
            }
        }
    }

    /**
    * Used to unset ForcedCanCreditmemo attributs from the order
    * Without restore order status is set to "C"
    * @param Varien_Object $observer
    */
    public function unsetOrderCanRefund($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        if ($order->getForcedCanCreditmemoFromHipay()) {
            $order->unsetData('forced_can_creditmemo');
            $order->unsetData('forced_can_creditmemo_from_hipay');
        }

        if ($order->getPayment()->getMethodInstance() &&
                $order->getPayment()->getMethodInstance() instanceof Allopass_Hipay_Model_Method_Abstract ) {
            // Cancel transaction in TPP if state is cancel
            if ($order->getStatus() == Mage_Sales_Model_Order::STATE_CANCELED) {
                $order->getPayment()->getMethodInstance()->cancelTransaction($order->getPayment());
            }
        }
    }

    /**
     * Autoload Hipay SDK Third party
     */
    public function autoloadLibrary()
    {
        require_once(Mage::getBaseDir('lib') . DS . 'Hipay' . DS . 'hipay-fullservice-sdk-php' . DS . 'autoload.php');
        return $this;
    }
}
