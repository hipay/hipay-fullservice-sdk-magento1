<?php

$installer = $this;


$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('hipay_payment_profile')};
CREATE TABLE {$this->getTable('hipay_payment_profile')} (
  `profile_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(150) NOT NULL,
  `period_unit` varchar(30) NOT NULL,
  `period_frequency` int(10) unsigned NOT NULL ,
  `period_max_cycles` int(10) unsigned NOT NULL ,
  `payment_type` varchar(60) NOT NULL default 'split_payment',
  PRIMARY KEY  (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('hipay_split_payment')};
CREATE TABLE {$this->getTable('hipay_split_payment')} (
  `split_payment_id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL ,
  `real_order_id` int(10) unsigned NOT NULL ,
  `customer_id` int(10) unsigned NOT NULL ,
  `card_token` text NOT NULL ,
  `total_amount` decimal(12,4) NOT NULL,
  `amount_to_pay` decimal(12,4) NOT NULL,
  `date_to_pay` datetime NOT NULL ,
  `method_code` varchar(150) NOT NULL,
  `attempts` int(4) unsigned NOT NULL DEFAULT '0' ,
  `status` varchar(60) NOT NULL default 'pending',
  PRIMARY KEY  (`split_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

"
);



$installer->endSetup();

