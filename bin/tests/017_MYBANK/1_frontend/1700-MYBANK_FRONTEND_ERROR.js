/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : MyBank
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise MyBank";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "mybankapi");
    })
    .thenOpen(baseURL, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.checkoutMethod();
    })
    .then(function() {
        this.billingInformation('FR');
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        test.assertDoesntExist('input#p_method_hipay_mybankapi');
    })
    .run(function() {
        test.done();
    });
});
