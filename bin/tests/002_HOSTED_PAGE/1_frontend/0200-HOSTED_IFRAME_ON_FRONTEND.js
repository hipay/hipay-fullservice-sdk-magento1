/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Page";

casper.test.begin('Test Checkout ' + paymentType + ' with Iframe', function(test) {
	phantom.clearCookies();

	casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "hosted", ['select[name="groups[hipay_hosted][fields][display_iframe][value]"]', '1']);
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
    		this.click('#dt_method_hipay_hosted>input[name="payment[method]"]');
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
    	this.wait(5000, function() {
			this.withFrame(0, function() {
				pay.proceed(test, true);
			});
    	});
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});