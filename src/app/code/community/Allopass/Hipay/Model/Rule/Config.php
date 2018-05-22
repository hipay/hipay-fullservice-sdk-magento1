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
class Allopass_Hipay_Model_Rule_Config extends Mage_Core_Model_Config_Data
{

    protected $_rule = null;
    protected $_ruleData = null;

    /**
     * @return Allopass_Hipay_Model_Rule
     */
    public function getRule()
    {
        if (is_null($this->_rule)) {
            $this->_rule = Mage::getModel('hipay/rule');
        }

        return $this->_rule;
    }

    public function setRule($rule)
    {
        $this->_rule = $rule;
    }

    protected function _afterload()
    {

        parent::_afterload();
        $rule = Mage::getModel('hipay/rule');
        $rule->setMethodCode($this->_getMethodCode());


        if ($this->getValue()) {
            $rule->load($this->getValue());
        }

        if ($rule->getConfigPath() == "") {
            $rule->setConfigPath($this->_getConfigPath());
        }

        $this->setRule($rule);

        return $this;

    }

    protected function _beforeSave()
    {
        $rule = Mage::getModel('hipay/rule')->load($this->getValue());

        $validateResult = $rule->validateData(new Varien_Object($this->_getRuleData()));
        if ($validateResult !== true) {
            $errors = array();
            foreach ($validateResult as $errorMessage) {
                $errors[] = $errorMessage;
            }

            Mage::throwException(new Exception(print_r($errors, true)));
        }

        $rule->setMethodCode($this->_getMethodCode());
        $rule->setConfigPath($this->_getConfigPath());

        $rule->loadPost($this->_getRuleData());

        try {
            $rule->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->setRule($rule);

        $this->setValue($rule->getId());

        parent::_beforeSave();
        return $this;
    }

    protected function _getMethodCode()
    {
        list($section, $group, $field) = explode("/", $this->getData('path'));
        return $group;
    }

    protected function _getConfigPath()
    {
        return $this->getData('path');
    }

    protected function _getFormName()
    {
        return str_replace("/", "_", $this->_getConfigPath());
    }

    protected function _getRuleData()
    {
        if ($this->_ruleData === null) {
            $post = Mage::app()->getRequest()->getPost();

            $this->_ruleData = array();

            if (isset($post['rule_' . $this->_getFormName()]['conditions'])) {
                $this->_ruleData['conditions'] = $post['rule_' . $this->_getFormName()]['conditions'];
            }
        }

        return $this->_ruleData;
    }
}
