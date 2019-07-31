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

use HiPay\Fullservice\Enum\Transaction\Operation;

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_Cart_CartMaintenanceFormatter extends Allopass_Hipay_Model_Api_Formatter_Cart_CartFormatterAbstract
{

    /**
     * Map Request
     *
     * @param Hipay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    public function mapRequest(&$cart)
    {
        if ($this->_operation === Operation::REFUND) {
            $this->mapRefundCart($cart);
        } elseif ($this->_operation === Operation::CAPTURE) {
            $this->mapCaptureCart($cart);
        }
    }

    protected function mapCaptureCart(&$cart)
    {
        if ($this->_order->hasInvoices()) {
            $invoice = $this->_order->getInvoiceCollection()->getLastItem();

            if ($this->_order->getInvoiceCollection()->count() === 1) {
                // Fees items
                $itemFees = $this->getFeesItem(
                    $this->getShippingAmount($invoice),
                    $this->getShippingTaxAmount($invoice)
                );
                if ($itemFees) {
                    $cart->addItem($itemFees);
                }
            }

            foreach ($invoice->getAllItems() as $item) {
                $product = $this->getRealProduct($item, $this->_order->getAllVisibleItems());

                if ($product) {
                    $itemGood = $this->getGoodItem(
                        $product,
                        $item->getData('qty'),
                        $this->getProductTotalAmount($item)
                    );

                    if ($itemGood) {
                        $cart->addItem($itemGood);
                    }
                }
            }
        }
    }

    protected function mapRefundCart(&$cart)
    {
        $creditMemo = $this->_payment->getCreditmemo();

        if ($this->_order->getCreditmemosCollection()->count() === 0) {
            // Fees items
            $itemFees = $this->getFeesItem($this->getShippingAmount($creditMemo), $this->getShippingTaxAmount($creditMemo));
            if ($itemFees) {
                $cart->addItem($itemFees);
            }
        }

        foreach ($creditMemo->getAllItems() as $item) {
            $product = $this->getRealProduct($item, $this->_order->getAllVisibleItems());

            if ($product) {
                $itemGood = $this->getGoodItem(
                    $product,
                    $item->getData('qty'),
                    $this->getProductTotalAmount($item)
                );

                if ($itemGood) {
                    $cart->addItem($itemGood);
                }
            }
        }
    }

    protected function getRealProduct($item, $products)
    {
        foreach ($products as $original) {
            if ($this->isBundleChildrenCalculated($original)) {
                return $this->getRealProduct($item, $original->getChildrenItems());
            }

            if ($item->getSku() == $original->getSku()) {
                return $original;
            }
        }

        return null;
    }
}
