/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : PAYPAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise PayPal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        if(!paypalTestFRAddress) {
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
        this.billingInformation("FR");
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
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.waitForUrl(/sandbox\.paypal/, function success() {
            this.wait(5000, function() {
                this.withFrame('injectedUl', function() {
                    this.fillSelectors('form[name="login"]', {
                        'input[name="login_email"]': paypalLogin,
                        'input[name="login_password"]': paypalPass
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
        }, 25000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});

casper.then(function() {
    if(!paypalTestFRAddress) {
        paypalTestFRAddress = true;
        phantom.injectJs(pathHeader + "012_PAYPAL/1_frontend/1200-PAYPAL_FRONTEND.js");
    }
});