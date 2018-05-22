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
 *  Config Scope for hashing algorithm synchronization
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
abstract class ScopeConfig
{
    /**
     *  Environment PRODUCTION
     *
     * @var string
     */
    const PRODUCTION = 'production';

    /**
     *  Environment TEST
     *
     * @var string
     */
    const TEST = 'test';

    /**
     *  Environment PRODUCTION with MOTO
     *
     * @var string
     */
    const PRODUCTION_MOTO = 'production_moto';

    /**
     *  Environment TEST with MOTO
     *
     * @var string
     */
    const TEST_MOTO = 'test_moto';


    /**
     * Display label for one environment
     *
     * @param $environment
     * @return string
     *
     */
    public static function getLabelFromEnvironment($environment)
    {
        $label = '';
        switch ($environment) {
            case ScopeConfig::PRODUCTION:
                $label = 'Production';
                break;
            case ScopeConfig::TEST:
                $label = 'Test';
                break;
            case ScopeConfig::PRODUCTION_MOTO:
                $label = 'Production MO/TO';
                break;
            case ScopeConfig::TEST_MOTO:
                $label = 'Test MO/TO';
                break;
        }
        
        return $label;
    }

}
