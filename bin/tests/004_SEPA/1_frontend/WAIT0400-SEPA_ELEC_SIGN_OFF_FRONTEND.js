/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType + ' without Electronic Signature', function(test) {
	phantom.clearCookies();

    casper.start(baseURL + "admin/")
    /* Active SEPA payment method with electronic signature */
    .then(function() {
        this.logToBackend();
        method.proceed(test, paymentType, "sdd", ['select[name="groups[hipay_sdd][fields][electronic_signature][value]"]', '1']);
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
        this.choosingPaymentMethod('method_hipay_sdd');
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill SEPA payment formular */
    .then(function() {
		this.fillPaymentFormularByPaymentProduct("sdd");
    })
    .then(function() {
        test.info("Done");
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});