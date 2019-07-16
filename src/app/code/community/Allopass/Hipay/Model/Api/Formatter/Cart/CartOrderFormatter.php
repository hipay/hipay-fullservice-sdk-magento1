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
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Model_Api_Formatter_Cart_CartOrderFormatter extends Allopass_Hipay_Model_Api_Formatter_Cart_CartFormatterAbstract
{
    /**
     * Map Request
     *
     * @param Hipay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    public function mapRequest(&$cart)
    {

        $products = $this->_order->getAllVisibleItems();

        // Good items
        foreach ($products as $product) {
            if ($this->isBundleChildrenCalculated($product)) {
                foreach ($product->getChildrenItems() as $children) {
                    $item = $this->getGoodItem(
                        $children,
                        $children->getData('qty_ordered'),
                        $this->getProductTotalAmount($children)
                    );
                    $cart->addItem($item);
                }
            } else {
                $item = $this->getGoodItem(
                    $product,
                    $product->getData('qty_ordered'),
                    $this->getProductTotalAmount($product)
                );
                $cart->addItem($item);
            }
        }

        // Item Type discount (coupon)
        if (!empty($this->_order->getCouponCode())) {
            $itemTypeDiscount = $this->getDiscountItem();
            $cart->addItem($itemTypeDiscount);
        }

        // Fees items
        $item = $this->getFeesItem($this->getShippingAmount($this->_order), $this->getShippingTaxAmount($this->_order));
        if ($item) {
            $cart->addItem($item);
        }
    }
}
