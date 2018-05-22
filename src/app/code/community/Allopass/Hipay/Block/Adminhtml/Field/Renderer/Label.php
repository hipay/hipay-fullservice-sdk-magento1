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
class Allopass_Hipay_Block_Adminhtml_Field_Renderer_Label extends Mage_Core_Block_Text
{
    /**
     *   Simple block to display just label with hidden input
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '<div style="width: 300px;" class="#{class}">';
        $html .= '<input type="text" value="#{magento_code}" name="'
            . $this->inputName
            . '" style="visibility: hidden;width: 0px ;height: 0px">';
        $html .= '#{magento_label}';
        $html .= '</div>';
        return $html;
    }
}
