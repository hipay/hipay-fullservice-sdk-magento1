<?php

class Allopass_Hipay_Helper_Collection extends Mage_Core_Helper_Abstract
{
    /**
     *
     * @var string $_JSON Json collection
     */
    private static $_JSON_CATEGORY = <<<EOT
    
        [
            {
                "categoryCode":1,
                "categoryName":"Home & Gardening"
            },
            {
                "categoryCode":2,
                "categoryName":"Clothing & Accessories"
            },
            {
                "categoryCode":3,
                "categoryName":"Home appliances"
            },
            {
                "categoryCode":4,
                "categoryName":"Sports & Recreations"
            },
            {
                "categoryCode":5,
                "categoryName":"Babies & Children"
            },
            {
                "categoryCode":6,
                "categoryName":"Hi-Fi, Photo & Video equipment"
            },
            {
                "categoryCode":7,
                "categoryName":"IT equipment"
            },
            {
                "categoryCode":8,
                "categoryName":"Phone & Internet services"
            },
            {
                "categoryCode":9,
                "categoryName":"Physical goods : Books, Media, Music & Movies"
            },
            {
                "categoryCode":10,
                "categoryName":"Digital goods : Books, Media, Music & Movies"
            },
             {
                "categoryCode":11,
                "categoryName":"Consoles & Video games"
            },
             {
                "categoryCode":12,
                "categoryName":"Gifts & Flowers"
            },
             {
                "categoryCode":13,
                "categoryName":"Health & Beauty"
            },
             {
                "categoryCode":14,
                "categoryName":"Car & Motorcycle"
            },
             {
               "categoryCode":15,
               "categoryName":"Traveling"
            },
             {
               "categoryCode":16,
               "categoryName":"Food & Gastronomy"
            },
             {
               "categoryCode":17,
               "categoryName":"Auctions & Group buying"
            },
             {
               "categoryCode":18,
               "categoryName":"Services to professionals"
            },
             {
               "categoryCode":19,
               "categoryName":"Services to individuals"
            },
             {
               "categoryCode":20,
               "categoryName":"Culture & Entertainment"
            },
            {
               "categoryCode":21,
               "categoryName":"Games (digital goods)"
            },
            {
               "categoryCode":22,
               "categoryName":"Games (physical goods)"
            },
            {
               "categoryCode":23,
               "categoryName":"Ticketing"
            },
            {
               "categoryCode":24,
               "categoryName":"Opticians, Opticians Goods and Eyeglasses"               
            }
        ]
        
EOT;

    /**
     *  JSON OBJECT for delivery method
     *
     * @var string $_JSON Json collection
     */
    private static $_JSON_DELIVERY = <<<EOT
    
        [
            {
                "code":1,
                "mode":"STORE",
                "shipping":"STANDARD"
            },
            {
                "code":2,
                "mode":"STORE",
                "shipping":"EXPRESS"
            },
            {
                "code":3,
                "mode":"STORE",
                "shipping":"PRIORITY24H"
            },
            {
                "code":4,
                "mode":"STORE",
                "shipping":"PRIORITY2H"
            },
            {
                "code":5,
                "mode":"STORE",
                "shipping":"PRIORITY1H"
            },
            {
                "code":6,
                "mode":"STORE",
                "shipping":"INSTANT"
            },
            {
                "code":7,
                "mode":"CARRIER",
                "shipping":"STANDARD"
            },
            {
                "code":8,
                "mode":"CARRIER",
                "shipping":"EXPRESS"
            },
            {
                "code":9,
                "mode":"CARRIER",
                "shipping":"PRIORITY24H"
            },
            {
                "code":10,
                "mode":"CARRIER",
                "shipping":"PRIORITY2H"
            },
            {
                "code":11,
                "mode":"STORE",
                "shipping":"PRIORITY1H"
            },
            {
                "code":12,
                "mode":"CARRIER",
                "shipping":"INSTANT"
            },
            {
                "code":13,
                "mode":"RELAYPOINT",
                "shipping":"STANDARD"
            },
            {
                "code":14,
                "mode":"RELAYPOINT",
                "shipping":"EXPRESS"
            },
            {
                "code":15,
                "mode":"RELAYPOINT",
                "shipping":"PRIORITY24H"
            },
            {
                "code":16,
                "mode":"RELAYPOINT",
                "shipping":"PRIORITY2H"
            },
            {
                "code":17,
                "mode":"RELAYPOINT",
                "shipping":"PRIORITY1H"
            },
            {
                "code":18,
                "mode":"RELAYPOINT",
                "shipping":"INSTANT"
            },
            {
                "code":19,
                "mode":"ELECTRONIC",
                "shipping":"STANDARD"
            },
            {
                "code":20,
                "mode":"ELECTRONIC",
                "shipping":"EXPRESS"
            },
            {
                "code":21,
                "mode":"ELECTRONIC",
                "shipping":"PRIORITY24H"
            },
            {
                "code":22,
                "mode":"ELECTRONIC",
                "shipping":"PRIORITY2H"
            },
            {
                "code":23,
                "mode":"ELECTRONIC",
                "shipping":"PRIORITY1H"
            },
            {
                "code":24,
                "mode":"ELECTRONIC",
                "shipping":"INSTANT"
            },
            {
                "code":25,
                "mode":"TRAVEL",
                "shipping":"STANDARD"
            },
            {
                "code":26,
                "mode":"TRAVEL",
                "shipping":"EXPRESS"
            },
            {
                "code":27,
                "mode":"TRAVEL",
                "shipping":"PRIORITY24H"
            },
            {
                "code":28,
                "mode":"TRAVEL",
                "shipping":"PRIORITY1H"
            },
            {
                "code":29,
                "mode":"TRAVEL",
                "shipping":"INSTANT"
            }
        ]
        
EOT;
    /**
     *  Return HiPay's Category
     *
     * @return array
     */
    public function getItemsCategory()
    {
        $jsonArr = json_decode(self::$_JSON_CATEGORY, true);
        $collection = array();
        foreach ($jsonArr as $item) {
            $collection[$item['categoryCode']] = $item['categoryName'];
        }
        return $collection;
    }

    /**
     *  Return HiPay's delivery method Full json
     *
     * @return array
     */
    public static function getFullItemsDelivery()
    {
        return json_decode(self::$_JSON_DELIVERY, true);
    }

    /**
     *  Return HiPay's delivery method for listing
     *
     * @return array
     */
    public static function getItemsDelivery()
    {
        $jsonArr = json_decode(self::$_JSON_DELIVERY, true);
        $collection = array();
        foreach ($jsonArr as $item) {
            $collection[$item['code']] = $item['mode'] . '-' . $item['shipping'];
        }
        return $collection;
    }
}
