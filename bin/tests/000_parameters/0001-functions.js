/* Return 1D array from multiple dimensional array */
function concatTable(arrToConvert) {
    var newArr = [];
    for(var i = 0; i < arrToConvert.length; i++)
    {
        newArr = newArr.concat(arrToConvert[i]);
    }
    return newArr;
};
/* return random number between 2 specific numbers */
function randNumbInRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

casper.test.begin('Functions', function(test) {
	/* For each fails, show current successful tests and show current URL and capture image */
    var img = 0;
	test.on('fail', function() {
        img++;
		casper.echo("URL: " + casper.currentUrl, "WARNING");
		casper.capture(pathErrors + 'fail' + img + '.png');
		test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
		casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
	});

    /*casper.on('remote.message', function(message) {
        this.echo('remote message caught: ' + message);
    });

    casper.on('step.complete', function() {
    	    this.echo(Date.now()-start + "ms");
       	start = Date.now();
    });*/
    
    /* Fill HiPayCC formular */
    casper.fillFormPaymentHipayCC = function(type, card) {
        this.fillSelectors('form#co-payment-form', {
            'select[name="payment[hipay_cc_cc_type]"]': type,
            'input[name="payment[hipay_cc_cc_number]"]': card,
            'select[name="payment[hipay_cc_cc_exp_month]"]': '2',
            'select[name="payment[hipay_cc_cc_exp_year]"]': '2020',
            'input[name="payment[hipay_cc_cc_cid]"]': '500'
        }, false);
    };
	/* Choose first item at home page */
	casper.selectItemAndOptions = function() {
        this.echo("Selecting item and its options...", "INFO");
        this.waitForSelector('div.widget-products>ul>li:first-of-type>a>img', function success() {
            this.click('div.widget-products>ul>li:first-of-type>a>img');
        }, function fail() {
            var altImg = this.getElementAttribute('div.widget-products>ul>li:first-of-type>a>img', 'alt');
            test.assertExists('div.widget-products>ul>li:first-of-type>a>img', "'" + altImg + "' image exists");
        });
        this.waitForSelector(x('//ul[@id="configurable_swatch_size"]/li[not(contains(@class, "not-available"))]'), function success() {
            this.click(x('//ul[@id="configurable_swatch_size"]/li[not(contains(@class, "not-available"))][position()=1]/a/span'));
        }, function fail() {
            test.assertExists(x('//ul[@id="configurable_swatch_size"]/li[not(contains(@class, "not-available"))]'), "Size button exists");
        });
        this.waitForSelector(x('//ul[@id="configurable_swatch_color"]/li[not(contains(@class, "not-available"))]'), function success() {
            this.click(x('//ul[@id="configurable_swatch_color"]/li[not(contains(@class, "not-available"))][position()=1]/a/span'));
            test.info("Done");
        }, function fail() {
            test.assertExists(x('//ul[@id="configurable_swatch_color"]/li[not(contains(@class, "not-available"))]'), "Color button exists");
        });
	};
	/* Add item and go to checkout */
	casper.addItemGoCheckout = function() {
        this.echo("Adding this item then, accessing to the checkout...", "INFO");
        this.waitForSelector("form#product_addtocart_form .add-to-cart-buttons button", function success() {
            this.click("form#product_addtocart_form .add-to-cart-buttons button");
            test.info('Item added to cart');
        }, function fail() {
        	test.assertNotExists('.validation-advice', "Warning message not present on submitting formular");
            test.assertExists("form#product_addtocart_form .add-to-cart-buttons button", "Submit button exists");
        });
        this.waitForSelector(".cart-totals .checkout-types .btn-checkout", function success() {
            this.click(".cart-totals .checkout-types .btn-checkout");
            test.info('Proceed to checkout');
        }, function fail() {
            test.assertExists(".cart-totals .checkout-types .btn-checkout", "Checkout button exists");
        }, 7500);
	};
	/* Checkout as guest */
	casper.checkoutMethod = function() {
        this.echo("Choosing checkout method...", "INFO");
        this.waitForSelector("button#onepage-guest-register-button", function success() {
            this.click("button#onepage-guest-register-button");
            test.info("Done");
        },function fail() {
            test.assertExists("button#onepage-guest-register-button", "'Continue' button exists");
        }, 10000);
	};
	/* Fill billing operation */
	casper.billingInformation = function(country) {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("form#co-billing-form", function success() {
            var street = '1249 Tongass Avenue, Suite B', city = 'Ketchikan', cp = '99901', region = '2';
            switch(country) {
                case "FR":
                    street = 'Rue de la paix'; city = 'PARIS'; cp = '75000'; region = '257';
                    test.comment("French Address");
                    break;
                case "BR":
                    test.comment("Brazilian Address");
                    break;
                default:
                    country = 'US';
                    test.comment("US Address");
            }
            this.fillSelectors('form#co-billing-form', {
                'input[name="billing[firstname]"]': 'TEST',
                'input[name="billing[lastname]"]': 'TEST',
                'input[name="billing[email]"]': 'email@yopmail.com',
                'input[name="billing[street][]"]': street,
                'input[name="billing[city]"]': city,
                'input[name="billing[postcode]"]': cp,
                'select[name="billing[country_id]"]': country,
                'input[name="billing[telephone]"]': '0171000000'
            }, false);
            if(this.visible('select[name="billing[region_id]"]')) {
                this.fillSelectors('form#co-billing-form', {
                    'select[name="billing[region_id]"]': region
                }, false);
            }
            this.click("div#billing-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertExists("form#co-billing-form", "'Billing Information' formular exists");
        });
	};
	/* Fill shipping method */
	casper.shippingMethod = function() {
	    this.echo("Filling 'Shipping Method' formular...", "INFO");
	    this.waitUntilVisible('div#checkout-step-shipping_method', function success() {
	        this.click('input#s_method_flatrate_flatrate');
	        this.click("div#shipping-method-buttons-container>button");
	        test.info("Done");
	    }, function fail() {
	        test.assertVisible("form#co-shipping-method-form", "'Shipping Method' formular exists");
	    }, 35000);
	};
	/* Place order */
	casper.orderReview = function(paymentType) {
        this.echo("Placing this order via " + paymentType + "...", "INFO");
        this.waitUntilVisible('#checkout-step-review', function success() {
            this.click('button.btn-checkout');
            test.info('Done');
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Order Review' exists");
        }, 15000);
	};
    /* Log to BO TPP */
    casper.logToBackend = function() {
        this.echo("Accessing and logging to TPP BackOffice...", "INFO");
        this.waitForUrl(/login/, function success() {
            this.fillSelectors('form', {
                'input[name="email"]': loginBackend,
                'input[name="password"]': passBackend
            }, true);
            if(loginBackend != "" && passBackend != "")
                test.info("Done");
            else
                this.echo("WARNING: No Backend credentials available !", "WARNING");
        }, function fail() {
            test.assertUrlMatch(/login/, "Login page exists");
        });
    };
    /* Select account for test from BO TPP */
    casper.selectAccountBackend = function(name) {
        this.waitForUrl(/dashboard/, function success() {
            if(this.exists('div#s2id_dropdown-merchant-input>a')) {
                this.echo("Selecting sub-account...", "INFO");
                this.echo('URL match 1');
                this.thenClick('div#s2id_dropdown-merchant-input>a', function() {
                    this.sendKeys('input[placeholder="Account name or API credential"]', name);
                    this.wait(1000, function() {    
                        this.click(x('//span[contains(., "HIPAY_RE7_' + name + ' -")]'));
                    });
                });
            }
            else {
                this.echo("Selecting account "  + name + " with old backend ", "INFO");
                if(this.exists(x('//td[contains(., "HIPAY_RE7_' + name + '")]/preceding-sibling::td[@class="account-number"]/a'))) {
                    this.thenClick('div#fs-account-navigation>div>a', function() {
                        this.thenClick(x('//li/a[text()="Test"]'), function() {
                            this.thenClick(x('//td[contains(., "HIPAY_RE7")]/i'), function() {
                                this.click(x('//td[contains(., "HIPAY_RE7_' + name + '")]/preceding-sibling::td[@class="account-number"]/a'));
                            });
                        });
                    });
                } else {
                    this.echo('Account is not listed "HIPAY_RE7_' + name + '"', "ERROR");
                }
            }
        }, function fail() {
            test.assertUrlMatch(/dashboard/, "dashboard page exists");
        },
        25000);
    };
	/* Get order ID, if it exists, after purchase, and set it in variable */
	casper.setOrderId = function(pending) {
		if(pending)
			orderID = this.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
		else {
			var text = this.fetchText(x('//p[contains(., "Your order # is:")]')).split(':')[1];
			orderID = text.substring(1, text.length - 1);
		}
		test.info("Order ID : " + orderID);
	};
    /* Get order ID variable value */
	casper.getOrderId = function() {
        if(typeof order == "undefined" || order == "")
            return orderID;
        else
            return order;
	};
	/* Check order result */
	casper.orderResult = function(paymentType) {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(/checkout\/onepage\/success/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('.checkout-onepage-success', "The order has been successfully placed with method " + paymentType + " !");
            this.setOrderId(false);
        }, function fail() {
        	this.echo("Success payment page doesn't exists. Checking for pending payment page...", 'WARNING');
        	this.waitForUrl(/hipay\/checkout\/pending/, function success() {
        		this.warn("OK. This order is in pending");
        		test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            	test.assertExists('.hipay-checkout-pending', "The order has been successfully pended with method " + paymentType + " !");
            	this.setOrderId(true);
	        }, function fail() {
            	test.assertUrlMatch(/hipay\/checkout\/pending/, "Checkout result page exists");
    	    }, 50000);
        }, 50000);
	};
    /* Test file again with another card type */
    casper.testOtherTypeCC = function(file) {
        casper.then(function() {  
            if(typeof this.cli.get('type-cc') == "undefined") {
                if(typeCC == "VISA") {
                    typeCC = "MasterCard";
                    phantom.injectJs(pathHeader + file);
                }
                else if(typeCC == "MasterCard")
                    typeCC = "VISA"; // retour du typeCC à la normale --> VISA pour la suite des tests
            }
        });
    };
    /* Test file again with another currency */
    casper.testOtherCurrency = function(file) {
        casper.then(function() {
            if(currentCurrency == allowedCurrencies[0]) {
                currentCurrency = allowedCurrencies[1];
                phantom.injectJs(pathHeader + file);
            }
            else if(currentCurrency == allowedCurrencies[1])
                currentCurrency = allowedCurrencies[0]; // retour du currency à la normale --> EURO pour la suite des tests
        });
    };
    /* Configure HiPay Enterprise options via formular */
    casper.fillFormHipayEnterprise = function(credentials, moto) {
        var stringMoto = "";
        if(moto)
            stringMoto = " MOTO";
        if(credentials == "blabla")
            this.echo("Editing Credentials" + stringMoto + " configuration with bad credentials...", "INFO");
        else
            this.echo("Reinitializing Credentials" + stringMoto + " configuration...", "INFO");
        if(moto)
            this.fillSelectors("form#config_edit_form", { 'input[name="groups[hipay_api_moto][fields][api_username_test][value]"]': credentials }, false);
        else
            this.fillSelectors("form#config_edit_form", { 'input[name="groups[hipay_api][fields][api_username_test][value]"]': credentials }, false);
        this.click(x('//span[text()="Save Config"]'));
        this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
            test.info("HiPay Enterprise credentials configuration done");
        }, function fail() {
            test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
        },20000);
    };
    /* Configure Device Fingerprint options via formular */
    casper.setDeviceFingerprint = function(state) {
        this.echo("Accessing Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                this.echo("Changing 'Device Fingerprint' field...", "INFO");
                var valueFingerprint = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api][fields][fingerprint][value]"]').value; });
                if(valueFingerprint == state)
                    test.info("Device Fingerprint configuration already done");
                else {
                    this.fillSelectors("form#config_edit_form", {
                        'select[name="groups[hipay_api][fields][fingerprint][value]"]': state
                    }, false);
                    this.click(x('//span[text()="Save Config"]'));
                    this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                        test.info("Device Fingerprint Configuration done");
                    }, function fail() {
                        test.fail('Failed to apply Device Fingerprint Configuration on the system');
                    }, 15000);
                }
            }, function fail() {
                test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
            }, 10000);
        }, function fail() {
            test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
        }, 10000);
    };

	casper.echo('Fonctions chargées !', 'INFO');
	test.info("Based URL: " + headlink);
    test.done();
});