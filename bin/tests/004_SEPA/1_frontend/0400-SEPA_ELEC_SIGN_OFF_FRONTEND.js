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

    casper.start(headlink + "admin/")
    /* Active SEPA payment method with electronic signature */
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "sdd", ['select[name="groups[hipay_sdd][fields][electronic_signature][value]"]', '1']);
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
            method="method_hipay_sdd";
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
    /* Fill SEPA payment formular */
    .then(function() {
    	this.echo("Filling payment formular...", "INFO");
    	this.waitForUrl(/payment\/pay\/reference/, function success() {
    		this.fillSelectors('#registrationform', {
    			'select[name="gender"]': "male",
    			'input[name="firstname"]': "TEST",
    			'input[name="lastname"]': "TEST",
    			'input[name="street"]': "Rue de la paix",
    			'input[name="zip"]': "75000",
    			'input[name="city"]': "PARIS",
    			'select[name="country"]': "GB",
    			'input[name="email"]': "email@yopmail.com"
    		}, false);
    		this.thenClick('input[name="bankaccountselection"]', function() {
    			this.fillSelectors('#registrationform', {
    				'input[name="iban"]': ibanNumber[0],
    				'input[name="bic"]': bicNumber[0]
    			}, false);
	    		this.click('body');
    			this.waitUntilVisible('div.ajaxsuccess', function success() {
    				test.assertNotVisible('div.ajaxerror', "Correct IBAN and BIC number");
	    			this.click('input#nyrosubmitfix');
    				test.info("Done");
                    this.echo("Submitting formular...", "INFO");
    			}, function fail() {
    				test.assertAllVisible('div.ajaxsuccess', "Succesful div block exists");
    			});
    		});
    	}, function fail() {
    		test.assertUrlMatch(/payment\/pay\/reference/, "Payment page exists");
    	}, 10000);
    })
    .then(function() {
        test.info("Done");
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});