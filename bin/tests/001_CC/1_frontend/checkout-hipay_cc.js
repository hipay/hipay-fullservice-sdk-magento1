/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/
casper.test.begin('Test Checkout HiPay Credit Card WITH ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink, function() {
        this.clear();
    });
    /* Choose an item on home */
    .then(function() {
        this.echo("Selecting item and its options...", "INFO");
        this.waitForSelector('img[alt="Lafayette Convertible Dress"]', function success() {
            this.click('img[alt="Lafayette Convertible Dress"]');
        }, function fail() {
            test.assertExists('img[alt="Lafayette Convertible Dress"]', "'Lafayette Convertible Dress' image exists");
        });
        this.waitForSelector("#swatch73 span", function success() {
            this.click("#swatch73 span");
        }, function fail() {
            test.assertExists("#swatch73 span", "Size button exists");
        });
        this.waitForSelector("#swatch27 img", function success() {
            this.click("#swatch27 img");
        }, function fail() {
            test.assertExists("#swatch27 img", "Color button exists");
        });
        test.info("Done");
    })
    /* add item and go to checkout */
    .then(function() {
        this.echo("Adding this item then, accessing to the checkout...", "INFO");
        this.waitForSelector("form#product_addtocart_form .add-to-cart-buttons button", function success() {
            this.click("form#product_addtocart_form .add-to-cart-buttons button");
            test.assertNotExists('.validation-advice', "Warning message not present for submitting formular");
            test.info('Item added to cart');
        }, function fail() {
            test.assertExists("form#product_addtocart_form .add-to-cart-buttons button", "Submit button exists");
        });
        this.waitForSelector(".cart-totals .checkout-types .btn-checkout", function success() {
            this.click(".cart-totals .checkout-types .btn-checkout");
            test.comment('Checkout cart');
        }, function fail() {
            this.echo(this.currentUrl);
            test.assertExists(".cart-totals .checkout-types .btn-checkout");
        });
    })
    //============================================================== //
    //===           CHECKOUT AS GUEST                            === //
    //============================================================== //
    this.waitForSelector("button#onepage-guest-register-button",
        function success() {
            this.click("button#onepage-guest-register-button");
        },
        function fail() {
            test.assertExists("button#onepage-guest-register-button");
        });

    this.waitForSelector("form#co-billing-form",
        function success() {
            this.fill('form#co-billing-form', {
                'billing[country_id]': 'US',
            }, false);
        },
        function fail() {
            test.assertExists("button#onepage-guest-register-button");
        });

    //============================================================== //
    //===           FILL BILLING OPERATION                       === //
    //============================================================== //
    this.waitForSelector("form#co-billing-form",
        function success() {
            this.fill('form#co-billing-form', {
                'billing[region_id]': '2',
                'billing[firstname]': 'TEST',
                'billing[lastname]': 'TEST',
                'billing[email]': 'email@yopmail.com',
                'billing[street][]': 'Rue de la paix',
                'billing[city]': 'PARIS',
                'billing[postcode]': '75000',
                'billing[telephone]': '0171000000'
            }, false);

            // Valid billing address
            this.evaluate(function (regionId, countryId) {
                billing.save();
            });
        },
        function fail() {
            test.assertExists("button#onepage-guest-register-button");
        });

    //============================================================== //
    //===           FILL SHIPPING METHOD                         === //
    //============================================================== //
    this.waitUntilVisible('#checkout-step-shipping_method', function () {
        this.fill('form#co-shipping-method-form', {
            'shipping_method': 'ups_GND',
        }, false);

        this.evaluate(function () {
            shippingMethod.save();
        });
    }, null, 20000);

    //============================================================== //
    //===           FILL STEP PAYMENT                            === //
    //============================================================== //
    this.waitUntilVisible('#checkout-step-payment',
        function success() {
            this.click("form#co-payment-form #p_method_hipay_cc");
            test.assertExists("form#co-payment-form");

            // Test Type VISA
            if (TYPE_CC == 'VI') {
                this.fill('form#co-payment-form', {
                    'payment[hipay_cc_cc_type]': 'VI',
                    'payment[hipay_cc_cc_exp_year]': '2020',
                    'payment[hipay_cc_cc_exp_month]': '2'
                }, false);
                this.sendKeys("form#co-payment-form input[name='payment[hipay_cc_cc_number]']", "4111111111111111");
                // Test Type CB
            } else if (TYPE_CC == 'CB') {
                this.fill('form#co-payment-form', {
                    'payment[hipay_cc_cc_type]': 'CB',
                    'payment[hipay_cc_cc_exp_year]': '2020',
                    'payment[hipay_cc_cc_exp_month]': '2'
                }, false);
                this.sendKeys("form#co-payment-form input[name='payment[hipay_cc_cc_number]']", "5234131094136942");
                // Test Type MC
            } else if (TYPE_CC == 'MC') {
                this.fill('form#co-payment-form', {
                    'payment[hipay_cc_cc_type]': 'MC',
                    'payment[hipay_cc_cc_exp_year]': '2020',
                    'payment[hipay_cc_cc_exp_month]': '2'
                }, false);

                this.sendKeys("form#co-payment-form input[name='payment[hipay_cc_cc_number]']", "5234131094136942");
            }

            this.sendKeys("form#co-payment-form input[name='payment[hipay_cc_cc_cid]']", "500");

            // Valid Payment
            this.evaluate(function () {
                payment.save();

            });

            // Test if errors occurs
            test.assertNotExists('.validation-advice');
            test.comment('Fill payment informations with type ' + TYPE_CC);
        },
        function fail() {
            test.assertExists("#checkout-step-payment");
        }
        , null, 10000);


    //============================================================== //
    //===           PLACE ORDER                           === //
    //============================================================== //
    this.waitUntilVisible('#checkout-step-review', function () {
        test.assertExists('button.btn-checkout');
        this.click('button.btn-checkout');
        test.comment('Place order with hipay_cc');
    }, null, 10000);

    //============================================================== //
    //===           CHECK ORDER SUCCESS                          === //
    //============================================================== //
    this.waitForUrl(BASE_URL + 'checkout/onepage/success/', function () {
        test.assertHttpStatus(200);
        test.assertExists('.checkout-onepage-success');
        test.pass('The order has been placed successfully with method hipay_cc');
    }, null, 20000);

    .run(function () {
        test.done();
    });
})
;