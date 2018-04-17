/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT LONG
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Oney Credit Long";

casper.test.begin('Test Checkout ' + paymentType, function(test) {

    casper.start(baseURL + "admin/")
     .then(function() {
         this.logToBackend();
         method.proceed(test, paymentType, "oneycreditlong");
     })
    .then(function() {
        this.activeAndFillBasket();
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
        this.billingInformation("FR");
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.choosingPaymentMethod('method_hipay_oneycreditlong');
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    .then(function() {
        this.echo("Wait for oney page", "INFO");
        this.wait("35000", function success() {
            test.comment(this.currentUrl);
            if (this.currentUrl.match('/oney/')) {
                test.info("Done");
                test.done();
            } else {
                test.fail("ONEY payment page exists");
            }
         }, function fail() {
             test.comment(this.currentUrl);
             test.assertUrlMatch(/scoach-merchant-ui/, "ONEY payment page exists");
         }, 20000);
    })
    .run(function() {
        test.done();
    });
});