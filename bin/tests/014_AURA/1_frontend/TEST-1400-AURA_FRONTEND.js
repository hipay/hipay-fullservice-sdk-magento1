/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : AURA
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "Aura";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    // phantom.clearCookies();

    casper.on('url.changed', function(url) {
        test.comment(url);
        test.info("cookie : " + this.evaluate(function() {
            return document.cookie;
        }));
    });

    casper.setCurrencySetup = function(currency) {
        if(currency == 'BRL')
            this.echo("Changing currency setup...", "INFO");
        else
            this.echo("Reinitializing currency setup...", "INFO");
        this.click(x('//span[contains(., "Currency Setup")]'));
        this.waitForUrl(/section\/currency/, function success() {
            this.fillSelectors('form#config_edit_form', {
                'select[name="groups[options][fields][base][value]"]': currency,
                'select[name="groups[options][fields][default][value]"]': currency,
                'select[name="groups[options][fields][allow][value][]"]': currency
            }, false);
            this.click(x('//span[text()="Save Config"]'));
            this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                test.info("Currency Setup Configuration done");
            }, function fail() {
                test.fail('Failed to apply Currency Setup Configuration on the system');
            }, 15000);
        }, function fail() {
            test.assertUrlMatch(/section\/currency/, "Currency Setup page exists");
        }, 10000);
    };

    casper.start(headlink + "admin/")
    // .then(function() {
    //     authentification.proceed(test);
    //     method.proceed(test, paymentType, "aura");
    // })
    // .then(function() {
    //     this.setCurrencySetup('BRL');
    // })
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
        this.billingInformation("BR");
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
    	this.echo("Choosing payment method and filling 'Payment Information' formular with " + typeCC + "...", "INFO");
    	this.waitUntilVisible('#checkout-step-payment', function success() {
            method="method_hipay_aura";
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method);
            } else {
                this.click('#dt_' + method +'>input[name="payment[method]"]');
            }

            this.fillSelectors("form#co-payment-form", {
                'input[name="payment[national_identification_number]"]': generatedCPF
            }, false);
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
        this.echo("Filling payment page...", "INFO");
        this.waitForUrl(/go_to_bank_cath/, function success() {
            test.info("go_to_bank page exists");
        }, function fail() {
            test.assertUrlMatch(/go_to_bank_cath/, "Payment redirected page exists");
        }, 15000);
        // this.waitForUrl(/payment$/, function success() {
        //     this.click('input[name="btnSubmit"]');
        //     this.waitUntilVisible(x('//h2[text()="Payment resume"]'), function success() {
        //         this.click('input#optStatusAccepted');
        //         this.click('input#btnConfirmPayment');
        //         this.waitForText('Transaction made!', function success() {
        //             this.click('input#new-sexy-button');
        //             test.info("Done");
        //         }, function fail() {
        //             test.assertTextExists('Transaction made!', "Transaction informations alert exists");
        //         });
        //     }, function fail() {
        //         test.assertVisible(x('//h2[text()="Payment resume"]'), "Modal window 'Payment resume' exists");
        //     });
        // }, function fail() {
        //     test.comment(this.currentUrl);
        //     this.thenOpen("https://sandbox.astropaycard.com/test_bank/payment", function() {
        //         this.wait(2000, function() {
        //             this.capture(pathErrors + 'ok.png');
        //         });
        //     });
        //     // test.assertUrlMatch(/payment$/, "AURA payment page exists");
        // }, 15000);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .thenOpen(headlink + 'admin/', function() {
        authentification.proceed(test);
    })
    .then(function() {
        this.setCurrencySetup('EUR');
    })
    .run(function() {
        test.done();
    });
});