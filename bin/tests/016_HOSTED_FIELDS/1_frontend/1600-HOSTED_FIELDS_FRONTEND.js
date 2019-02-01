/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card Hosted Fields",
    currentBrandCC = typeCC;

casper.test.begin('Test Checkout ' + paymentType + ' with ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active HiPay CC payment method if default card type is not defined or is VISA */
        .then(function () {
            this.logToBackend();
            method.proceed(test, paymentType, "hostedfields");
        })
        .thenOpen(baseURL, function () {
            this.waitUntilVisible('div.footer', function success() {
                this.selectItemAndOptions();
            }, function fail() {
                test.assertVisible("div.footer", "'Footer' exists");
            }, 10000);
        }, 15000)
        .then(function () {
            this.addItemGoCheckout();
        })
        .then(function () {
            this.checkoutMethod();
        })
        .then(function () {
            this.billingInformation();
        })
        .then(function () {
            this.shippingMethod();
        })
        /* Fill steps payment */
        .then(function () {
            this.wait(10000, function () {
                this.fillStepPayment(true);
            });
        })
        .then(function () {
            this.orderReview(paymentType);
        })
        .then(function () {
            this.orderResult(paymentType);

        })
        .run(function () {
            test.done();
        });
});
