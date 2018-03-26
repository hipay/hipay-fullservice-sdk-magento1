<?php

/**
 *
 *  Source model for signature notification
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2018 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 *
 */
require_once(dirname(__FILE__) . '/../../Helper/Enum/HashingCode.php');

class Allopass_Hipay_Model_Source_HashingAlgorithm
{
	
 	/**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => HashingCode::SHA1, 'label' => 'SHA-1'),
            array('value' => HashingCode::SHA256, 'label' => 'SHA-256'),
            array('value' => HashingCode::SHA512, 'label' => 'SHA-512'),
        );
    }
}