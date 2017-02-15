<?php

$installer = $this;


$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('hipay_split_payment')} ADD COLUMN  `split_number` varchar(150)

"
);



$installer->endSetup();

