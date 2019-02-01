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

$installer = $this;


$installer->startSetup();

$installer->run(
    "

-- DROP TABLE IF EXISTS {$this->getTable('hipay_rule')};
CREATE TABLE {$this->getTable('hipay_rule')} (
  `rule_id` int(10) unsigned NOT NULL auto_increment,
  `method_code` varchar(60) NOT NULL,
  `conditions_serialized` text NOT NULL,
  `actions_serialized` text NOT NULL default '',
  `product_ids` text,
  `sort_order` int(10) unsigned NOT NULL default '0',
  `simple_action` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`rule_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);
$installer->endSetup();

