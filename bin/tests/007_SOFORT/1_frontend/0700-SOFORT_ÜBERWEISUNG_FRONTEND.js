/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SOFOR ÜBERWEISUNG
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Sofort Überweisung";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "sofortapi");
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
    		this.click('#dt_method_hipay_sofortapi>input[name="payment[method]"]');
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Fill Sofort formular */
    .then(function() {
    	this.echo("Filling payment formular...", "INFO");
    	this.waitForUrl(/go\/select_country/, function success() {
    		this.fillSelectors('form#WizardForm', {
    			'select[name="data[MultipaysSession][sender_country_id]"]': "FR"
    		}, false);
            this.wait(2000, function() {
                this.fillSelectors('form#WizardForm', {
                    'select[name="data[MultipaysSession][SenderBank]"]': "00000"
                }, false);
                this.click("button.form-submitter");
                test.info("Bank and country selected");
                this.waitForUrl(/go\/login/, function success() {
                    this.fillSelectors('form#WizardForm', {
                        'input[name="data[BackendForm][LOGINNAME__USER_ID]"]': "00000",
                        'input[name="data[BackendForm][USER_PIN]"]': "123456789"
                    }, false);
                    this.click("button.form-submitter");
                    test.info("Credentials inserted");
                    this.waitForUrl(/go\/select_account/, function success() {
                        this.click('ul.radio-list>li:first-of-type>label');
                        this.click("button.form-submitter");
                        test.info("Account selected");
                        this.waitForUrl(/go\/provide_tan/, function success() {
                            this.fillSelectors('form#WizardForm', {
                                'input[name="data[BackendForm][TAN]"]': "12345"
                            }, false);
                            this.click("button.form-submitter");
                            test.info("TAN code inserted");
                        }, function fail() {
                            test.assertUrlMatch(/go\/provide_tan/, "Payment TAN page exists");
                        });
                    }, function fail() {
                        test.assertUrlMatch(/go\/select_account/, "Payment account page exists");
                    });
                }, function fail() {
                    test.assertUrlMatch(/go\/login/, "Payment login page exists");
                });
            });
    	}, function fail() {
    		test.assertUrlMatch(/go\/select_country/, "Payment country page exists");
    	}, 20000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});