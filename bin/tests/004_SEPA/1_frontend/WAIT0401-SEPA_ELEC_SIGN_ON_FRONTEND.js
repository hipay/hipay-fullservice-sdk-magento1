/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType + ' with Electronic Signature', function(test) {
	phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active SEPA payment method without electronic signature */
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "sdd", ['select[name="groups[hipay_sdd][fields][electronic_signature][value]"]', '0']);
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
    /* Choose and fill payment formular */
    .then(function() {
        this.choosingPaymentMethod('method_hipay_sdd');
    })
    .then(function() {
        this.echo("Filling payment formular sdd ", "INFO");
        this.fillSelectors('form#co-payment-form', {
            'select[name="payment[cc_gender]"]': "M",
            'input[name="payment[cc_firstname]"]': "TEST",
            'input[name="payment[cc_lastname]"]': "TEST",
            'input[name="payment[cc_iban]"]': ibanNumber.gb,
            'input[name="payment[cc_code_bic]"]': bicNumber.gb,
            'input[name="payment[cc_bank_name]"]': "BANK TEST"
        }, false);
        this.click("div#payment-buttons-container>button");
        test.info("Done");
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