var realCurrentCurrency = currentCurrency;

casper.test.begin('Test Magento Using Order Currency For Transactions', function(test) {
    phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card",
        allowed = [];

    /* Choose current currency from Magento1 homepage */
    casper.setCurrency = function(currency, symbol) {
        this.echo("Changing current currency...", "INFO");
        this.waitForSelector('select#select-currency', function success() {
            var current = this.evaluate(function() { return document.querySelector('select#select-currency').value; });
            if(current.indexOf(currency) == -1) {
                var ind = this.evaluate(function(cur) { return __utils__.getElementByXPath('//select[@id="select-currency"]/option[contains(., "' + cur + '")]').index; }, currency);
                this.evaluate(function(index) {
                    var sel = document.querySelector('select#select-currency');
                    sel.selectedIndex = index;
                    sel.onchange();
                }, ind);
                this.waitForText(symbol, function success() {
                    test.info("'" + currency + "' currency done");
                }, function fail() {
                    test.assertTextExists(symbol, "Current currency is not " + currency);
                }, 10000);
            }
            else
                test.info("'" + currency + "' currency already done");
        }, function fail() {
            test.assertExists('select#select-currency', "'Currency' select field exists");
        }, 10000);    
    };
    /* Set order currency option via formular */
    casper.setUseOrderCurrency = function(state) {
        this.echo("Accessing Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                this.echo("Changing 'Use order currency for transactions' field...", "INFO");
                var valueOrderCurrency = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api][fields][currency_transaction][value]"]').value; });
                if(valueOrderCurrency == state)
                    test.info("Use order currency for transactions configuration already done");
                else {
                    this.fillSelectors("form#config_edit_form", {
                        'select[name="groups[hipay_api][fields][currency_transaction][value]"]': state
                    }, false);
                    this.click(x('//span[text()="Save Config"]'));
                    this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                        test.info("Use order currency for transactions Configuration done");
                    }, function fail() {
                        test.fail('Failed to apply Use order currency for transactions Configuration on the system');
                    }, 15000);
                }
            }, function fail() {
                test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
            }, 10000);
        }, function fail() {
            test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
        }, 10000);
    };

    /* Get all currencies from variable */
    casper.start(headlink + "admin/", function() {
        this.each(allowedCurrencies, function(self, allowedCurrency) {
            allowed.push(allowedCurrency["currency"]);
        });
    })
    .then(function() {
        if(realCurrentCurrency["currency"] == "EUR") {
        	authentification.proceed(test);
            method.proceed(test, paymentType, "cc");
            /* Active order currency option */
            this.then(function() {
                this.setUseOrderCurrency('1');
            });
            /* Choose different currencies via formular */
            this.thenClick(x('//span[contains(., "Currency Setup")]'), function() {
                this.echo("Changing 'Allowed Currencies' field...", "INFO");
                this.waitForUrl(/section\/currency/, function success() {
                    var currencies = this.getElementsAttribute('select[name="groups[options][fields][allow][value][]"]>option[selected]', 'value');
                    if(JSON.stringify(currencies) == JSON.stringify(allowed))
                        test.info("Allowed Currencies configuration already done");
                    else {
                        this.fillSelectors('form#config_edit_form', {
                            'select[name="groups[options][fields][allow][value][]"]': allowed
                        }, false);
                        this.click(x('//span[text()="Save Config"]'));
                        this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                            test.info("Allowed Currencies Configuration done");
                        }, function fail() {
                            test.fail('Failed to apply Allowed Currencies Configuration on the system');
                        }, 15000);
                    }
                }, function fail() {
                    test.assertUrlMatch(/section\/currency/, "Currency setup page exists");
                }, 10000);
            });
        }
    })
    /* Go to Magento1 homepage and choose current currency */
    .thenOpen(headlink, function() {
        this.setCurrency(realCurrentCurrency["currency"], realCurrentCurrency["symbol"]);
    })
    .then(function() {
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
    /* Fill HiPay CC fomular */
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
    /* Access to BO TPP */
    .thenOpen(urlBackend, function() {
        orderID = this.getOrderId();
        this.logToBackend();
    })
    .then(function() {
        this.selectAccountBackend("OGONE_DEV");
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
    /* Check order currency from BO TPP */
    .then(function() {
        this.echo("Checking order currency from BO TPP...", "INFO");
        this.waitForText('Order Total:', function success() {
            test.assertExists(x('//th[contains(., "Order Total")]/following-sibling::th[contains(., "' + realCurrentCurrency["currency"] + '")]'), "Order currency is correctly on '" + realCurrentCurrency["currency"] + "' !");
        }, function fail() {
            test.assertTextExists('Order Total:', "Order summary page exists");
        });
    })
    /* Set default current currency at Magento1 homepage */
    .then(function() {
        var lengthCurrencies = allowedCurrencies.length -1;
        if(realCurrentCurrency == allowedCurrencies[lengthCurrencies]) {
            this.thenOpen(headlink + "admin/", function() {
                authentification.proceed(test);
            });
            this.then(function() {
                this.setUseOrderCurrency('0');
            });
        }
    })
    .run(function() {
        test.done();
    });
});

/* Test it again with another currency */
casper.testOtherCurrency('013_ADMIN_MENUS/0_admin/1306-TEST_ORDER_CURRENCY_FOR_TRANSACTIONS.js');