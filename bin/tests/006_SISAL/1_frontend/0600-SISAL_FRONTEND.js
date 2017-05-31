/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SISAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Sisal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "sisalapi");
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
    .then(function() {
    	this.echo("Choosing payment method...", "INFO");
    	this.waitUntilVisible('#checkout-step-payment', function success() {
    		this.click('#dt_method_hipay_sisalapi>input[name="payment[method]"]');
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill Sisal formular */
    .then(function() {
    	this.echo("Filling payment method...", "INFO");
    	this.waitForUrl(/provider\/sisal/, function success() {
    		this.click('a#submit-button');
    		test.info("Done");
    	}, function fail() {
    		test.assertUrlMatch(/provider\/sisal/, "Payment page exists");
    	}, 10000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});