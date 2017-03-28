/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

/* Function which fills formular during Step Payment according to the card type and its number */
casper.fillFormPaymentInformation = function fillFormPaymentInformation(type, card) {
    this.fillSelectors('form#co-payment-form', {
        'select[name="payment[hipay_cc_cc_type]"]': type,
        'input[name="payment[hipay_cc_cc_number]"]': card,
        'select[name="payment[hipay_cc_cc_exp_month]"]': '2',
        'select[name="payment[hipay_cc_cc_exp_year]"]': '2020',
        'input[name="payment[hipay_cc_cc_cid]"]': '500'
    }, false);
};

var paymentType = "HiPay Credit Card"

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink)
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
            test.info("Done");
        }, function fail() {
            test.assertExists("#swatch27 img", "Color button exists");
        });
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
            test.info('Proceed to checkout');
        }, function fail() {
            test.assertExists(".cart-totals .checkout-types .btn-checkout", "Checkout button exists");
        });
    })
    /* Checkout as guest */
    .then(function() {
        this.echo("Choosing checkout method...", "INFO");
        this.waitForSelector("button#onepage-guest-register-button", function success() {
            this.click("button#onepage-guest-register-button");
            test.info("Done");
        },function fail() {
            test.assertExists("button#onepage-guest-register-button", "'Continue' button exists");
        });
    })
    /* fill billing operation */
    .then(function() {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("form#co-billing-form", function success() {
            this.fillSelectors('form#co-billing-form', {
                'input[name="billing[firstname]"]': 'TEST',
                'input[name="billing[lastname]"]': 'TEST',
                'input[name="billing[email]"]': 'email@yopmail.com',
                'input[name="billing[street][]"]': 'Rue de la paix',
                'input[name="billing[city]"]': 'PARIS',
                'input[name="billing[postcode]"]': '75000',
                'select[name="billing[country_id]"]': 'US',
                'input[name="billing[telephone]"]': '0171000000',
                'select[name="billing[region_id]"]': '2'
            }, false);
            this.click("div#billing-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertExists("form#co-billing-form", "'Billing Information' formular exists");
        });
    })
    /* fill shipping method */
    .then(function() {
        this.echo("Filling 'Shipping Method' formular...", "INFO");
        this.waitUntilVisible('div#checkout-step-shipping_method', function success() {
            this.click('input#s_method_ups_GND');
            this.click("div#shipping-method-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("form#co-shipping-method-form", "'Shipping Method' formular exists");
        }, 20000);
    })
    /* fill steps payment */
    .then(function() {
        this.echo("Filling 'Payment Information' formular with " + typeCC + "...", "INFO");
        this.waitUntilVisible('#checkout-step-payment', function success() {
            this.click('#dt_method_hipay_cc>input[name="payment[method]"]');
            if(typeCC == 'VISA')
                this.fillFormPaymentInformation('VI', cardsNumber[0]);
            else if(typeCC == 'CB')
                this.fillFormPaymentInformation('CB', cardsNumber[1]);
            else if(typeCC == 'MasterCard')
                this.fillFormPaymentInformation('MC', cardsNumber[1]);

            this.click("div#payment-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    /* place order */
    .then(function() {
        this.echo("Placing this order via " + paymentType + "...", "INFO");
        this.waitUntilVisible('#checkout-step-review', function success() {
            this.click('button.btn-checkout');
            test.info('Done');
        }, function fail() {
             test.assertVisible("#checkout-step-payment", "'Order Review' exists");
        }, 10000);
    })
    /* check order success */
    .then(function() {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(headlink + 'checkout/onepage/success/', function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('.checkout-onepage-success', "The order has been successfully placed with method " + paymentType + " !");
        }, function fail() {
            test.assertUrlMatch(/checkout\/onepage\/success/, "Checkout result page exists");
        }, 20000);
    })
    .run(function() {
        test.done();
    });
});