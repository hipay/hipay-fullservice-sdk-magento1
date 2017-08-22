/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : BELFIUS/DEXIA DIRECT NET
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Belfius / Dexia Direct Net";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "dexia");
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
    		this.click('#dt_method_hipay_dexia>input[name="payment[method]"]');
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill Belfius formular */
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.waitForUrl(/secure\.ogone/, function success() {
            this.click('input#submit1');
            this.waitForUrl(/netbanking_ACS/, function success() {
                this.click('input#btn_Accept');
                test.info("Done");
            }, function fail() {
                test.assertUrlMatch(/netbanking_ACS/, "Payment Ogone second page exists");
            });
        }, function fail() {
            test.assertUrlMatch(/orderstandard/, "Payment Ogone page exists");
        }, 15000 );
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});