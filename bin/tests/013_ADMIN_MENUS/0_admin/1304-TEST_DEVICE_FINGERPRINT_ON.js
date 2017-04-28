casper.test.begin('Test Magento Device Fingerprint', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card",
        ioBB = "";

    casper.start(headlink + "admin/")
    // .then(function() {
    // 	authentification.proceed(test);
    // })
    // .then(function() {
    // 	this.echo("Accessing Hipay Enterprise menu...", "INFO");
    // 	this.click(x('//span[text()="Configuration"]'));
    // 	this.waitForUrl(/admin\/system_config/, function success() {
    // 		this.click(x('//span[contains(., "HiPay Enterprise")]'));
    //         test.info("Done");
    // 		this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
    // 			this.echo("Changing Device Fingerprint field...", "INFO");
    //             var valueFingerprint = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api][fields][fingerprint][value]"]').value; });
    //             if(valueFingerprint == 1)
    //                 test.info("Device Fingerprint configuration already done");
    //             else {
    //                 this.fillSelectors("form#config_edit_form", {
    //                     'select[name="groups[hipay_api][fields][fingerprint][value]"]': "1"
    //                 }, false);
    //                 this.click(x('//span[text()="Save Config"]'));
    //                 this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
    //                     test.info("Device Fingerprint Configuration done");
    //                 }, function fail() {
    //                     test.fail('Failed to apply Device Fingerprint Configuration on the system');
    //                 }, 15000);
    //             }
    // 		}, function fail() {
    // 			test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
    // 		}, 10000);
    // 	}, function fail() {
    // 		test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
    // 	}, 10000);
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
        this.echo("Checking 'ioBB' field inside checkout page...", "INFO");
        this.wait(3000, function() {
            ioBB = this.getElementAttribute('input#ioBB', 'value');
            test.comment(ioBB);
            test.assert(this.exists('input#ioBB') && ioBB != "", "'ioBB' field is present and not empty !");
        });
    })
    .then(function() {
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + typeCC + "...", "INFO");
        this.waitUntilVisible('#checkout-step-payment', function success() {
            this.click('#dt_method_hipay_cc>input[name="payment[method]"]');
            if(typeCC == 'VISA')
                this.fillFormPaymentHipayCC('VI', cardsNumber[0]);
            else if(typeCC == 'CB' || typeCC == "MasterCard")
                this.fillFormPaymentHipayCC('MC', cardsNumber[1]);

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
        this.orderResult(paymentType);
    })
    .thenOpen(urlbackend, function() {
        orderID = this.getOrderId();
        this.echo("Accessing and logging to TPP BackOffice...", "INFO");
        this.waitForUrl(/login/, function success() {
            this.fillSelectors('form', {
                'input[name="email"]': loginBackend,
                'input[name="password"]': passBackend
            }, true);
            test.info("Done");
        }, function fail() {
            test.assertUrlMatch(/login/, "Login page exists");
        });
    })
    .then(function() {
        this.echo("Selecting sub-account...", "INFO");
        this.waitForUrl(/dashboard/, function success() {
            this.thenClick('div#s2id_dropdown-merchant-input>a', function() {
                this.sendKeys('input[placeholder="Account name or API credential"]', "OGONE_DEV");
                this.wait(1000, function() {    
                    this.click(x('//span[contains(., "HIPAY_RE7_OGONE_DEV -")]'));
                });
            });
        }, function fail() {
            test.assertUrlMatch(/dashboard/, "dashboard page exists");
        });
    })
    .then(function() {
        this.waitForUrl(/maccount/, function success() {
            this.click('a.nav-transactions');
            test.info("Done");
        }, function fail() {
            test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
        });
    })
    .then(function() {
        this.echo("Finding order # " + orderID + " in order list...", "INFO");
        this.waitForUrl(/manage/, function success() {
            this.evaluate(function(ID) {
                document.querySelector('input#orderid').value = ID;
                document.querySelector('input[name="submitorderbutton"]').click();
            }, orderID);
            test.info("Done");
        }, function fail() {
            test.assertUrlMatch(/manage/, "Manage page exists");
        });
    })
    .then(function() {
        this.echo("Opening Customer Details...", "INFO");
        this.waitForSelector('a[href="#customer-details"]', function success() {
            this.thenClick('a[href="#customer-details"]', function() {
                this.wait(1000, function() {
                    var BOioBB = this.fetchText(x('//td[text()="Device Fingerprint"]/following-sibling::td/span')).split('.')[0];
                    test.info(ioBB);
                    test.comment(BOioBB);
                    test.assert(BOioBB != "" && ioBB.indexOf(BOioBB) != -1, "'ioBB' is correctly present into transaction details of BackOffice TPP !");
                });
            });
        }, function fail() {
            test.assertExists('a[href="#customer-details"]', "Customer Details tab exists");
        });
    })
    .run(function() {
        test.done();
    });
});