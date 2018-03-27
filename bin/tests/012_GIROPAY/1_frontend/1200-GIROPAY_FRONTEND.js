/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : GIROPAY
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Giropay";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "giropay");
    })
    .thenOpen(baseURL, function() {
        this.waitUntilVisible('div.footer', function success() {
            this.selectItemAndOptions();
        }, function fail() {
            test.assertVisible("div.footer", "'Footer' exists");
        }, 10000);
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.checkoutMethod();
    })
    .then(function() {
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.choosingPaymentMethod('method_hipay_giropay');
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill GiroPay formular */
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.waitForUrl(/payment\/web\/pay/, function success() {
            this.fillSelectors('form#form-payment', {
                'input[name="issuer_bank_id"]': "TESTDETT421"
            }, true);
            this.waitForUrl(/customer-integration\.giropay/, function success() {
                this.fillSelectors("form:first-of-type", {
                    'input[name="account/addition[@name=benutzerkennung]"]': "sepatest1",
                    'input[name="ticket/pin"]': "12345"
                }, true);
                test.info("Credentials inserted");
                this.waitForSelector('input[name="weiterButton"]', function success() {
                    this.click('input[name="weiterButton"]');
                    this.waitForSelector('input[name="BezahlButton"]', function success() {
                        this.sendKeys('input[name="ticket/tan"]', "123456");
                        this.click('input[name="BezahlButton"]');
                        test.info("TAN code inserted");
                        this.waitForSelector('input[name="back2MerchantButton"]', function success() {
                            this.click('input[name="back2MerchantButton"]');
                            test.info("Done");
                        }, function fail() {
                            test.assertExists('input[name="back2MerchantButton"]', "Payment Giropay recap page exists");
                        }, 25000);
                    }, function fail() {
                        test.assertExists('input[name="BezahlButton"]', "Payment Giropay TAN page exists");
                    })
                }, function fail() {
                    test.assertExists('input[name="weiterButton"]', "Payment Giropay review page exists");
                }, 25000);
            }, function fail() {
                test.assertUrlMatch(/customer-integration\.giropay/, "Payment Giropay login page exists");
            },25000);
        }, function fail() {
            test.assertUrlMatch(/payment\/web\/pay/, "Payment page exists");
        }, 10000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});