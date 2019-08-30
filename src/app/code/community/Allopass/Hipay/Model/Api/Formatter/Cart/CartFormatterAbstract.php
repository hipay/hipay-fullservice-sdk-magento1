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
abstract class Allopass_Hipay_Model_Api_Formatter_Cart_CartFormatterAbstract implements Allopass_Hipay_Model_Api_Formatter_ApiFormatterInterface
{
    protected $_paymentMethod;
    protected $_payment;
    protected $_order;
    protected $_store;
    protected $_operation;

    public function __construct($args)
    {
        $this->_paymentMethod = $args["paymentMethod"];
        $this->_payment = $args["payment"];
        $this->_order = $this->_payment->getOrder();
        $this->_store = Mage::app()->getStore();
        $this->_operation = $args["operation"];
    }

    /**
     *  Generate cart and return json representation for cart or specific items
     *
     * @return json
     */
    public function generate()
    {
        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $cart->toJson();
    }

    /**
     * Map Request
     *
     * @param Hipay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    public abstract function mapRequest(&$cart);

    protected function getGoodItem($product, $qty, $totalAmount)
    {

        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $quantity = (int)$qty;

        $productReference = trim($product->getData('sku'));
        $taxRate = Mage::app()->getStore()->roundPrice($product->getData('tax_percent'));

        if ($quantity <= 0 || $totalAmount <= 0) {
            return null;
        }

        $unitPrice = round(Mage::helper('hipay')->returnUnitPrice($product, $quantity), 3);

        $europeanArticleNumbering = $this->getEan($product);

        $type = Allopass_Hipay_Helper_Data::TYPE_ITEM_BASKET_GOOD;

        $name = $product->getName();

        $discount = round($totalAmount - ($unitPrice * $quantity), 3);
        $discountDescription = "";

        //  $productDescription = $product->getDescription();
        $productDescription = "";

        $deliveryMethod = null;
        $deliveryCompany = null;
        $deliveryDelay = null;
        $deliveryNumber = null;
        $shopId = null;
        $productCategory = $this->getProductCategory($product);


        $item->__constructItem(
            $europeanArticleNumbering,
            $productReference,
            $type,
            $name,
            $quantity,
            $unitPrice,
            $taxRate,
            $discount,
            $totalAmount,
            $discountDescription,
            $productDescription,
            $deliveryMethod,
            $deliveryCompany,
            $deliveryDelay,
            $deliveryNumber,
            $productCategory,
            $shopId
        );

        return $item;
    }

    /**
     * create a discount item from discount line information
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    protected function getDiscountItem()
    {

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount(
            $this->_order->getCouponCode(),
            $this->_order->getDiscountDescription(),
            0,
            0,
            0,
            $this->_order->getDiscountDescription(),
            0
        );
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     * create a Fees item from cart informations
     *
     * @param $shippingAmount
     * @param $taxAmount
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    protected function getFeesItem($shippingAmount, $taxAmount)
    {

        $productReference = $this->_order->getShippingDescription();
        $name = $this->_order->getShippingDescription();

        $unitPrice = round($shippingAmount, 3);

        $taxRate = 0;

        if ($shippingAmount > 0) {
            $taxRate = round($taxAmount / $shippingAmount * 100, 2);
        }

        $totalAmount = round($shippingAmount, 3);

        $discount = 0.00;

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees(
            $productReference,
            $name,
            $unitPrice,
            $taxRate,
            $discount,
            $totalAmount
        );
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    protected function isUseOrderCurrency()
    {
        return Mage::getStoreConfig('hipay/hipay_api/currency_transaction', Mage::app()->getStore());
    }

    protected function getEan($product)
    {
        if (Mage::getStoreConfig('hipay/hipay_basket/attribute_ean', Mage::app()->getStore())) {
            $attribute = Mage::getStoreConfig('hipay/hipay_basket/attribute_ean', Mage::app()->getStore());
            $resource = Mage::getSingleton('catalog/product')->getResource();

            if (Mage::getStoreConfig('hipay/hipay_basket/load_product_ean', Mage::app()->getStore())) {
                return $resource->getAttributeRawValue($product->getProductId(), $attribute, Mage::app()->getStore());
            } else {
                // The custom attribute have to be present in quote and order
                return $product->getData($attribute);
            }
        }

        return null;
    }

    protected function getProductCategory($product)
    {
        $catalogProduct = Mage::getModel('catalog/product')->load($product->getProductId());
        $categoryIds = $catalogProduct->getCategoryIds();

        if (is_array($categoryIds) && !empty($categoryIds) && isset($categoryIds[0]) && $categoryIds[0]) {
            $mapping = Mage::helper('hipay')->getMappingCategory($categoryIds[0], Mage::app()->getStore()->getId());
            if (is_array($mapping) && array_key_exists('hipay_category', $mapping)) {
                return (int)$mapping['hipay_category'];
            }
        }

        return null;
    }

    protected function isBundleChildrenCalculated($product)
    {
        return $product->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE &&
            $product->isChildrenCalculated();
    }

    protected function getProductTotalAmount($product)
    {
        if ($this->isUseOrderCurrency()) {
            $totalAmount = $product->getBaseRowTotal()
                + $product->getBaseTaxAmount()
                + $product->getBaseHiddenTaxAmount()
                + $product->getBaseWeeeTaxAppliedRowAmount()
                - $product->getBaseDiscountAmount();
        } else {
            $totalAmount = $product->getRowTotal()
                + $product->getTaxAmount()
                + $product->getHiddenTaxAmount()
                + $product->getBaseWeeeTaxAppliedRowAmount()
                - $product->getDiscountAmount();
        }

        return $totalAmount;
    }

    protected function getShippingAmount($orderOrInvoice)
    {
        return $this->isUseOrderCurrency() ? $orderOrInvoice->getShippingAmount()
            : $orderOrInvoice->getBaseShippingAmount();
    }

    protected function getShippingTaxAmount($orderOrInvoice)
    {
        return $this->isUseOrderCurrency() ? $orderOrInvoice->getShippingTaxAmount()
            : $orderOrInvoice->getBaseShippingTaxAmount();
    }
}
