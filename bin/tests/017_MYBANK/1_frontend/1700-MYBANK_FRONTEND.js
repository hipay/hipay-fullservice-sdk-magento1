/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : MyBank
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise MyBank";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function (test) {
    phantom.clearCookies();

    var data = "",
        hash = "",
        output = "",
        notif117 = true,
        reload = false,
        orderID = casper.getOrderId();

    casper.start(baseURL + "admin/")
        .then(function () {
            this.logToBackend();
            method.proceed(test, paymentType, "mybankapi");
        })
        .thenOpen(baseURL, function () {
            this.selectItemAndOptions();
        })
        .then(function () {
            this.addItemGoCheckout();
        })
        .then(function () {
            this.checkoutMethod();
        })
        .then(function () {
            this.billingInformation('IT');
        })
        .then(function () {
            this.shippingMethod();
        })
        .then(function () {
            this.choosingPaymentMethod('method_hipay_mybankapi');
        })
        .then(function () {
            this.orderReview(paymentType);
        })
        /* Fill IDeal formular */
        .then(function () {
            this.fillPaymentFormularByPaymentProduct("mybank");
        })
        .then(function () {
            this.orderResult(paymentType);
        })
        .thenOpen(urlBackend, function () {
            this.logToHipayBackend(loginBackend, passBackend);
        })
        .then(function () {
            this.selectAccountBackend("OGONE_DEV");
        })
        .then(function () {
            cartID = casper.getOrderId();
            orderID = casper.getOrderId();
            this.processNotifications(true, false, true, false, "OGONE_DEV");
        })
        /* Open Magento admin panel and access to details of this order */
        .thenOpen(baseURL + "admin/", function () {
            this.logToBackend();
            this.waitForSelector(x('//span[text()="Orders"]'), function success() {
                this.echo("Checking status notifications from Magento server...", "INFO");
                this.click(x('//span[text()="Orders"]'));
                this.waitForSelector(x('//td[contains(., "' + orderID + '")]'), function success() {
                    this.click(x('//td[contains(., "' + orderID + '")]'));
                    this.waitForSelector('div#order_history_block', function success() {
                        /* Check notification with code 116 from Magento server */
                        this.checkNotifMagento("116");
                    }, function fail() {
                        test.assertExists('div#order_history_block', "History block of this order exists");
                    });
                }, function fail() {
                    test.assertExists(x('//td[contains(., "' + orderID + '")]'), "Order # " + orderID + " exists");
                });
            }, function fail() {
                test.assertExists(x('//span[text()="Orders"]'), "Order tab exists");
            });
        })
        /* Idem Notification with code 116 */
        .then(function () {
            this.checkNotifMagento("117");
        })
        /* Idem Notification with code 117 */
        .then(function () {
            this.checkNotifMagento("118");
        })
        .run(function () {
            test.done();
        });
});
