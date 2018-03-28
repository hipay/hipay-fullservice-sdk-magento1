<?php

$installer = $this;


$installer->startSetup();

$installer->run(
    "

ALTER TABLE {$this->getTable('hipay_rule')} ADD `config_path` VARCHAR(60) NOT NULL AFTER `method_code` ;

"
);


$installer->endSetup();

