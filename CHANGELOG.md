# Version 1.7.2

Fix : Authentication indicator for SEPA

# Version 1.7.1

Fix : PHP Notices with Oney Facily Pay support

# Version 1.7.0
New - Oney Facily Pay support
New - Astropay support
New - Mapping yours categories with HiPay
New - Mapping yours shipping methods with HiPay and determin delivery date

# Version 1.6.2
Fix - production url tokenJS

# Version 1.6.1
Fix - [SECURITY] Check notification signature if passphrase is not empty
Fix - X-forward-for without proxy
  

# Version 1.6.0
New - Docker environnement development, stage and production
New - Payment MO/TO configuration
New - Payment MO/TO send to customer the payment page link
New - New Branding HiPay
New - New order_id nomenclature on the split payment
New - Optimization the split payment profil labels 
New - Add Request sources send to the request API
New - Change "Hipay's Cards" titles
New - Basket configuration
New - Basket: Send the basket to transaction
New - Basket: Available to capture and refund
New - Additional parameters: use order currency to transaction
New - Payment method: add Klarna integration associated with the basket feature
New - Changing Payment Methods at Store View in addition to the general config
Fix - tax-rate to the split payment
Fix - The cancel management by the back office HiPay towards Magento
Fix - Callback 142 Authorization requested
Fix - Custom_data file is on error when it's not used 

# Version 1.5.1
Fix - Add a specific success page management 
 
# Version 1.5.0
New - Add a custom_data management
New - Add a device fingerprint for Direct POST
New - Add a specific success page management 

# Version 1.4.0
New - Repository with Docker for tests
New - Direct POST with token javascript
Fix - partially refund with the status 126
Fix - Split payment and cron (*/5 * * *)
Fix - order closed when it's shipped 

# Version 1.3.10
Fix - pre-load card holder if empty

# Version 1.3.9
Update documentation URL to the HiPay portal developer

# Version 1.3.8
Fix - update config.xml version

# Version 1.3.7
Fix - Delete observer.php setForcedCanRafund()
Fix - Replace Mage::getSingleton by Mage::getModel in the request.php function getawayRequest()

# Version 1.3.6
Fix - Change status to processing when callback 118 with validate order=Capture_requested and the order is not been received with the callback 117

# Version 1.3.5
Fix - oberserver parse error

# Version 1.3.4
Fix - Order Challenged with pending payment after callback 116
Fix - Order Challenged with pending_review befor callback 116
Fix - Control Credit Card number
Fix - backward compatible Refund between new order and old order

# Version 1.3.3
Fix - API + oneclick
Fix - Block a refund request when the order status is not in processing
Fix - Fraud email
Fix - MO/TO create order by back office
Fix - Workflow optimization for the order status by callback
Fix - Retry optimization between Payment page and Magento
Fix - Show a table Split payment on the front office in HTTPS
Fix - Better management about amount (split payment)


# Version 1.3.2
Fix - Replace URL stage and production
Fix - sorted date in split payment array
Fix - Reverse array of split payment
Fix - split amount with decimals
Fix - regression for split payment

# Version 1.3.1
Bugfix - Bad characterin Abstract.php


# Version 1.3.0
New payment method SEPA (SDD)
Bugfix for refund, cards registered, order optimisation in status pending, Payment MO/TO in back office


<a name=""></a>
# [](//compare/v1.2.5...vundefined) (2016-01-04)


### Bug Fixes

* **api-response:** Set fraud detected only for status code 110 fd2ce15
* **cron:** Add fields to order collection 566be4b, closes #8 #13
* **notification:** Add logic process for state declined 7e63e2a
* **notification:** add some comments 6dc9d1a
* **notification:** Change status expired and state holded 97307da
* **response status:** Change only order status to expired not state f5dce16, closes #14
* **split payment:** Fix JS bug 79290b9
* **split-payment:** Fix display split payment 1f46174, closes #12

### Features

* **payment-x-times:** Add CcXtimes class 7883cc4
* **split-payment:** Add 2 new methods for split payment 92e690b

### Refactor

* **clean code:** Remove some comments a6390a2
* **changelog:** First generation with conventional-changelog 240a5c5
* **clean-code:** Clean log calls and comments c2bf280



<a name="1.2.5"></a>
## [1.2.5](//compare/v1.2.4...v1.2.5) (2015-12-14)


### Documents

* **how-to:** Complete doc how to use 5c2db2d
* **how-to:** Fix bug in mardown syntax da30220
* **how-to:** Update README 70a9c80

### Bug Fixes

* **split payment:** Refactor abstract class method, for compatibility of b75d08d
* **tools:** Fix composer script "package" 4e9c86c

### Refactor

* **tools:** Change release directory 3320d5c

### Features

* **tools**:Create package extension from composer script 17aca5d


<a name="1.2.4"></a>
## [1.2.4](//compare/v1.2.3...v1.2.4) (2015-11-24)


### Bug Fixes

* **refund:** Fix bug if object payment don't exist fdc1a55, closes #4

### Features

* **translation:** Change some titles 0d2996f, closes #6

### Performance Improvements

* **cron:** optimize cancel orders in pending query 6afb50c, closes #3

### Release

* **release:** v1.2.4 701ef1b



<a name="1.2.3"></a>
## [1.2.3](//compare/v1.2.2...v1.2.3) (2015-11-18)


* Init iFram config array 1925810
* Let IPN treat Authorized status if order has been in fraud state and not 20b862e, closes #2



<a name="1.2.2"></a>
## [1.2.2](//compare/v1.2.1...v1.2.2) (2015-10-29)


* Add paypal API 108e6a6



<a name="1.2.1"></a>
## [1.2.1](//compare/v1.2.0...v1.2.1) (2015-10-28)


* Fix iframe bugs 96d1fdd



<a name="1.2.0"></a>
# [1.2.0](//compare/v1.1.9...v1.2.0) (2015-10-26)


* add changelog 949150d
* Fix issue #1. Add annotations on card model. Add sessions var in Payment controller. cc2fb2d, closes #1
* remove unused Mage::log 46a2223



<a name="1.1.9"></a>
## [1.1.9](//compare/v1.1.8...v1.1.9) (2015-10-12)


* Change super class of models: card,payment profile and split payment 1dc1980
* up to version 1.1.9 a7ef3c9



<a name="1.1.8"></a>
## [1.1.8](//compare/v1.1.7...v1.1.8) (2015-09-15)


* --add last release 87739b6
* --fix bug with code storein url for admin controllers 190b17b
* --fix translation de6e93a
* --fix url admin  b611492



<a name="1.1.7"></a>
## [1.1.7](//compare/v1.1.6...v1.1.7) (2015-08-05)


* --add package 1.1.6 51999f4
* --fix bug payment BO 72b104c



<a name="1.1.6"></a>
## [1.1.6](//compare/v1.1.5...v1.1.6) (2015-07-27)


* --Fix security bug on load cardId (oneclick). We added check with 19ed601



<a name="1.1.5"></a>
## [1.1.5](//compare/v1.1.4...v1.1.5) (2015-07-27)


* --fix some bugs f1bfedd



<a name="1.1.4"></a>
## [1.1.4](//compare/v1.1.3...v1.1.4) (2015-06-11)


* --bug fix e00b35c
* --modify labels 4481fed



<a name="1.1.3"></a>
## [1.1.3](//compare/v1.1.2...v1.1.3) (2015-05-07)


* --add accept and capture for transactions in pending_review 003c656
* --add input owner card in card form 215e78b
* --add split payment info in frontend 06b6f3c



<a name="1.1.2"></a>
## [1.1.2](//compare/v1.1.1...v1.1.2) (2015-04-22)


* --add edit card in Bo 54e5d2a
* --add option to force 3ds b126c15
* --add soft delete for card. It's change the status to Disabled ;-) ec6095e
* --bug in Bo payment Fixed 0b2320b



<a name="1.1.1"></a>
## 1.1.1 (2015-04-15)


* --add edit card in front 59092c5
* --add infos to object payment and display them on block info a16b7fd
* --add link to transaction détail TPP in view transaction Magento 9a320ce
* --add lock for refund if method == hipay_cc and ccType == bcmc 1b09ec8
* --add lock for refund if order is in status capture_requested 7ef9ffa
* --add semantic 4e12f35
* --add sort order by is_default in Abstract payment form 4a0bc5d
* --add url to order in cdata1 1c29419
* --Bo bugs fixed bd6ccb2
* --bugs fixed 9851a64
* --change order status of fraud challenged 043e3b1
* --emails for fraud added ce97445
* --evolution propres à magento added fa84283



<a name="1.1.0"></a>
# 1.1.0 (2015-04-09)


* --add statues: authorization_requested, expired,partial capture,partial 395e06a
* --clean form template b986cb6
* --manage cards for oneclick 1f578e1



<a name="1.0.9"></a>
## 1.0.9 (2015-04-05)


* --card created when user create oneclick 0f746d1



<a name="1.0.8"></a>
## 1.0.8 (2015-04-05)


* --acceptChallenge and DenyChallenge OK 0816f5e
* --add accept/deny fraude challenged ee2d07b
* --add rule to oneclick configuration c6cef23
* --email fraus implemented 2930b78
* --fraud email first commit 3582675
* --payment in BO OK 7264c99
* --split payment ok d27b471



<a name="1.0.7"></a>
## 1.0.7 (2015-01-30)


* --make selection of cctype sortable in configuration 495a2bb
* --remove directory design/frontend/default and add directory 9d97b89
* --split payment in progress 8f1282a



<a name="1.0.6"></a>
## 1.0.6 (2014-12-10)


* -added order status filter if hipay response is "Authorized" (116) but 266122c



<a name="1.0.5"></a>
## 1.0.5 (2014-11-25)


* -all methods are processed for clean orders pending 7f359ee
* -fixed bug: invoice not paid a7531ce
* -Modify how to handle status "capture requested" and "capture" for logic process b86f0a3
* -version 1.0.5 5e62d8b
* add basic-js template config.xml e2a1095
* add getOrderPlaceRedirectUrl dynamic ff12806
* add ip xforwardedFor 23d19ee
* add label to config 9dc1b80
* Add local payment method "Sofort API" 5280848
* add order validation with status "Capture Requested" 0e2f3ac
* add readme file a695493
* add rule 3ds 62c61c4
* Add Sofort Method ddc0945
* add storeId to request vault and gateway fdabd71
* Add translations 1110729
* BCMC for BE fixed ab26746
* bug duplicate class's method Fixed d27b3db
* bug in capture fixed a20cd41
* Bugs fixed: 830acf3
* clean xml comments e856359
* Code AMEX replaced by code "american-express" 4c03fe0
* compatibility with version under 1.7 fixed d95e871
* First commit 05f020c
* last release b475384
* local payments API integration Sisal, Yandex, Webmoney, P24 76f0962
* many bugs fixed 27aad05
* Merge branch 'master' of 88.191.127.239:allopass_hipay 8c8562a
* Merge branch 'master' of 88.191.127.239:allopass_hipay 88a494d
* Merge branch 'master' of 88.191.127.239:allopass_hipay 3b408a8
* new release dbe7b8c
* new templates and iframe styles ea13ef4
* order processed when ccType is Amex and status hipay for validate order is capture_requested (117) 5a5d940
* product name change HiPay Banking TPP to HiPay Fullservice c953217
* product name change HiPay Banking TPP to HiPay Fullservice b76c02f
* Remove BCMC from param gateway "payment_product_list" when country is "BE" 00b3668
* second update juan 8a2c3df
* TEST 6fe742c
* test control for create invoice cb5386b
* test push f997e12
* V 1.0.3 packaged 38ce8ec
* version 1.0.3 5447612
