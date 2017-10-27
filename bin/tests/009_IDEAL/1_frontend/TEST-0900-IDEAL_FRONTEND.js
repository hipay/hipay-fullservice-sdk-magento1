/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : IDEAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise iDeal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "ideal");
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
            method="method_hipay_ideal";
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method);
            } else {
                this.click('#dt_' + method +'>input[name="payment[method]"]');
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
    /* Fill IDeal formular */
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.waitForUrl(/payment\/web\/pay/, function success() {
            this.fillSelectors("form#form-payment", {
                'select[name="issuer_bank_id"]': "TESTNL99"
            }, true);
            this.waitForUrl(/paymentscreen\/ideal\/testmode/, function success() {
                this.click('label.radio:first-of-type');
                this.click('button.btn-primary');
                test.info("Done");
            }, function fail() {
                test.assertUrlMatch(/paymentscreen\/ideal\/testmode/, "Payment Mollie page exists");
            });
        }, function fail() {
            test.assertUrlMatch(/payment\/web\/pay/, "Payment page exists");
        }, 10000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});