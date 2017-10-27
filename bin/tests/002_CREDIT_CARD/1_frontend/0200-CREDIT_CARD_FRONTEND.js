/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    realTypeCC = typeCC;

casper.test.begin('Test Checkout ' + paymentType + ' with ' + realTypeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    /* Active HiPay CC payment method if default card type is not defined or is VISA */
    .then(function() {
        if(typeof casper.cli.get('type-cc') == "undefined" && realTypeCC == "VISA" || typeof casper.cli.get('type-cc') != "undefined") {
            authentification.proceed(test);
            method.proceed(test, paymentType, "cc");
        }
    })
    .thenOpen(headlink, function() {
        this.selectItemAndOptions();
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
    /* Fill steps payment */
    .then(function() {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + realTypeCC + "...", "INFO");
        this.waitUntilVisible('#checkout-step-payment', function success() {
            method_hipay="method_hipay_cc";
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method_hipay);
            } else {
                this.click('#dt_' + method_hipay +'>input[name="payment[method]"]');
            }

            if(realTypeCC == 'VISA')
                this.fillFormPaymentHipayCC('VI', cardsNumber[0]);
            else if(realTypeCC == 'CB' || realTypeCC == "MasterCard")
                this.fillFormPaymentHipayCC('MC', cardsNumber[1]);

            this.click("div#payment-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});

/* Test it again with another card type */
casper.testOtherTypeCC('002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js');