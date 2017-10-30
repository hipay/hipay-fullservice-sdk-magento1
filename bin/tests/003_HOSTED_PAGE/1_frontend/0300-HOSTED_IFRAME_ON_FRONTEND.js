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
    /* Active Hosted payment method with display iframe */
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "hosted", ['select[name="groups[hipay_hosted][fields][display_iframe][value]"]', '1']);
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
    	this.echo("Choosing payment method...", "INFO");
    	this.waitUntilVisible('#checkout-step-payment', function success() {
            method_hipay="method_hipay_hosted";
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method_hipay);
            } else {
                this.click('#dt_' + method_hipay +'>input[name="payment[method]"]');
            }
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill payment formular inside iframe */
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