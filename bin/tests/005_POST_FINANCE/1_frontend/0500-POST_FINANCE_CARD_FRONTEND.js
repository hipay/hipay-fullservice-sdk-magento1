/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : POST FINANCE CARD
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Fullservice PostFinance Card";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "postfinancecardapi");
    })
    .thenOpen(headlink, function() {
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
        this.choosingPaymentMethod('method_hipay_postfinancecardapi');
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill Post Finance formular */
    .then(function() {
    	this.echo("Filling payment method...", "INFO");
    	this.waitForUrl(/secure\.ogone/, function success() {
    		this.click('input#btn_Accept');
    		test.info("Done");
    	}, function fail() {
    		test.assertUrlMatch(/secure\.ogone/, "Payment page exists");
    	}, 10000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});