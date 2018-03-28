<?php


/**
 *
 *  Config Scope for hashing algorithm synchronization
 *
 * @author Aymeric Berthelot <aberthelot@hipay.com>
 * @copyright Copyright (c) 2018 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 *
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
