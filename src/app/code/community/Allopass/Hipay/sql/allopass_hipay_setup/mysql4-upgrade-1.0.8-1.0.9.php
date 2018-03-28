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

CREATE TABLE {$this->getTable('hipay_customer_card')} (
  `card_id` int(10) unsigned NOT NULL auto_increment,
  `customer_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `cc_exp_month` varchar(2) NOT NULL COMMENT 'Cc Exp Month',
  `cc_exp_year` varchar(4) NOT NULL COMMENT 'Cc Exp Year',
  `cc_secure_verify` varchar(10) DEFAULT NULL COMMENT 'Cc Secure Verify',
  `cc_last4` varchar(4) DEFAULT NULL COMMENT 'Cc Last4',
  `cc_owner` varchar(255) DEFAULT NULL COMMENT 'Cc Owner',
  `cc_type` varchar(255) NOT NULL COMMENT 'Cc Type',
  `cc_number_enc` varchar(40) NOT NULL COMMENT 'Cc Number Enc',
  `cc_status` TINYINT( 1 ) NOT NULL COMMENT 'Cc Status',
  `cc_token` varchar(255) NOT NULL COMMENT 'Cc Token',
  `is_default` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'Cc is default',
  PRIMARY KEY  (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

"
);


$installer->endSetup();
