<?php
/**
 * HiPay Enterprise SDK Magento
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 */


/**
 * Handle new versions notifications
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-fullservice-sdk-magento1/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-fullservice-sdk-magento1
 */
class Allopass_Hipay_Block_Adminhtml_System_Config_Updatenotif extends Mage_Adminhtml_Block_Template
{
    const HIPAY_GITHUB_MAGENTO_LATEST = "https://api.github.com/repos/hipay/hipay-fullservice-sdk-magento1/releases/latest";

    /**
     * @var DateTime $lastGithubPoll Last time gitHub API was called
     */
    private $lastGithubPoll;

    /**
     * @var String $version Current module version
     */
    private $version;

    /**
     * @var String $newVersion Latest version available
     */
    private $newVersion;

    /**
     * @var String $newVersionDate Publication date of the latest version
     */
    private $newVersionDate;

    /**
     * @var String $readMeUrl URL targeting the latest version's ReadMe on GitHub
     */
    private $readMeUrl;

    /**
     * UpdateNotif constructor.
     * @throws Exception
     */
    public function __construct()
    {
        // We read info from the saved configuration first, to have values even if GitHub doesn't answer properly
        $this->readFromConf();

        $curdate = new DateTime();

        /*
         * PT1H => Interval of 1 hour
         * https://www.php.net/manual/en/dateinterval.construct.php
         */
        if ($this->lastGithubPoll->add(new DateInterval("PT1H")) < $curdate) {
            // Headers to avoid 403 error from GitHub
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: PHP'
                    ]
                ]
            ];
            $context = stream_context_create($opts);
            $gitHubInfo = json_decode(file_get_contents(self::HIPAY_GITHUB_MAGENTO_LATEST, false, $context));
            // If call is successful, reading from call
            if ($gitHubInfo) {
                $this->newVersion = $gitHubInfo->tag_name;
                $this->newVersionDate = $gitHubInfo->published_at;
                $this->readMeUrl = $gitHubInfo->html_url;
                $this->lastGithubPoll = $curdate;

                $infoFormatted = new stdClass();
                $infoFormatted->newVersion = $this->newVersion;
                $infoFormatted->newVersionDate = $this->newVersionDate;
                $infoFormatted->readMeUrl = $this->readMeUrl;
                $infoFormatted->lastCall = $curdate->format('d/m/Y H:i:s');

                $config = Mage::getSingleton('hipay/config');
                $config->setConfigData('version_info', json_encode($infoFormatted));
            }
        }
    }

    /**
     * Reads the update info from saved configuration data
     */
    public function readFromConf()
    {
        $dataHelper = Mage::helper('hipay/data');
        $lastResult = $dataHelper->readVersionDataFromConf();

        $this->version = $lastResult->version;

        // If conf exists, reading from it
        if (isset($lastResult->newVersion)) {
            $this->newVersion = $lastResult->newVersion;
            $this->newVersionDate = $lastResult->newVersionDate;
            $this->readMeUrl = $lastResult->readMeUrl;
            /*
             * GitHub limits calls over 60 per hour per IP
             * https://developer.github.com/v3/#rate-limiting
             *
             * Solution : max 1 call per hour
             */
            $this->lastGithubPoll = DateTime::createFromFormat('d/m/Y H:i:s', $lastResult->lastCall);

            // If not, setting default data with values not showing the block
        } else {
            $this->newVersion = $this->version;
            $this->newVersionDate = DateTime::createFromFormat('d/m/Y H:i:s', "01/01/1990 00:00:00");
            $this->readMeUrl = "#";
            $this->lastGithubPoll = DateTime::createFromFormat('d/m/Y H:i:s', "01/01/1990 00:00:00");
        }
    }

    public function getMessage()
    {
        //check if it is after first login
        if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
            try {
                $message = "We advise you to update the extension if you wish to get the " .
                    "latest fixes and evolutions. " .
                    "To update the extension, ";
                $title = "HiPay enterprise %s available!";
                $versionData = array(
                    'severity' => Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
                    'date_added' => $this->newVersionDate,
                    'title' => $this->__($title, $this->getNewVersion()),
                    'description' => $this->__($message . "please click here : %s", $this->readMeUrl),
                    'url' => $this->readMeUrl,
                );

                Mage::helper('hipay/notification')->addNotification($versionData);

                /*
                 * This will compare the currently installed version with the latest available one.
                 * A message will appear after the login if the two are not matching.
                 */
                if ($this->version != $this->newVersion) {
                    return $this->__($title . " " . $message . "<a href='%s' target='_blank'>please click here</a>.",
                        $this->getNewVersion(),
                        $this->getReadMeUrl());
                }
            } catch (Exception $e) {
                return;
            }
        }
        return;
    }

    /**
     * @return String
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return String
     */
    public function getNewVersion()
    {
        return $this->newVersion;
    }

    /**
     * @return String
     */
    public function getReadMeUrl()
    {
        return $this->readMeUrl;
    }

}