/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SOFOR ÜBERWEISUNG
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Sofort Überweisung";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL + "admin/")
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "sofortapi");
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
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.choosingPaymentMethod('method_hipay_sofortapi');
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill Sofort formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("sofort-uberweisung");
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});