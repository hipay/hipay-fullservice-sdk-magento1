/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : PAYPAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise PayPal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC + ' and ' + countryPaypal, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    /* Active PayPal payment method if country for payment formular is US */
    .then(function() {
        if(countryPaypal == 'US') {
            authentification.proceed(test);
            method.proceed(test, paymentType, "paypalapi");
        }
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
        this.billingInformation(countryPaypal);
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
    	this.echo("Choosing payment method...", "INFO");
    	this.waitUntilVisible('#checkout-step-payment', function success() {
    		this.click('#dt_method_hipay_paypalapi>input[name="payment[method]"]');
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill PayPal formular */
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.waitForUrl(/sandbox\.paypal/, function success() {
            this.wait(5000, function() {
                this.withFrame('injectedUl', function() {
                    this.fillSelectors('form[name="login"]', {
                        'input[name="login_email"]': loginPaypal,
                        'input[name="login_password"]': passPaypal
                    }, true);
                    test.info("Credentials inserted");
                });
                this.waitForUrl(/checkout\/review/, function success() {
                    this.click('input#confirmButtonTop');
                    test.info("Done");
                }, function fail() {
                    test.assertUrlMatch(/checkout\/review/, "Payment review page exists");
                }, 10000);
            });
        }, function fail() {
            test.assertUrlMatch(/sandbox\.paypal/, "Payment page exists");
        }, 30000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});

/* Test it again with another country inside formular */
casper.then(function() {
    if(countryPaypal == 'US') {
        countryPaypal = 'FR';
        phantom.injectJs(pathHeader + "013_PAYPAL/1_frontend/1300-PAYPAL_FRONTEND.js");
    }
});