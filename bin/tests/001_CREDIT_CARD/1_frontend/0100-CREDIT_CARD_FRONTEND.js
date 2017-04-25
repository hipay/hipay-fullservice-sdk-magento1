/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

/* Function which fills formular during Step Payment according to the card type and its number */
casper.fillFormPaymentInformation = function fillFormPaymentInformation(type, card) {
    this.fillSelectors('form#co-payment-form', {
        'select[name="payment[hipay_cc_cc_type]"]': type,
        'input[name="payment[hipay_cc_cc_number]"]': card,
        'select[name="payment[hipay_cc_cc_exp_month]"]': '2',
        'select[name="payment[hipay_cc_cc_exp_year]"]': '2020',
        'input[name="payment[hipay_cc_cc_cid]"]': '500'
    }, false);
};

var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        if(typeof casper.cli.get('type-cc') == "undefined" && typeCC == "VISA" || typeof casper.cli.get('type-cc') != "undefined") {
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
    /* fill steps payment */
    .then(function() {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + typeCC + "...", "INFO");
        this.waitUntilVisible('#checkout-step-payment', function success() {
            this.click('#dt_method_hipay_cc>input[name="payment[method]"]');
            if(typeCC == 'VISA')
                this.fillFormPaymentInformation('VI', cardsNumber[0]);
            else if(typeCC == 'CB' || typeCC == "MasterCard")
                this.fillFormPaymentInformation('MC', cardsNumber[1]);

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

casper.testOtherTypeCC('001_CREDIT_CARD/1_frontend/0100-CREDIT_CARD_FRONTEND.js');