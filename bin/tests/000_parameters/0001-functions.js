casper.test.begin('Functions', function(test) {
	var orderID = 0,
        img = 0,
        pathErrors = "bin/tests/errors/";
	/* Show current number of successful tests */
	test.on('fail', function() {
        img++;
		casper.echo("URL: " + casper.currentUrl, "WARNING");
		casper.capture(pathErrors + 'fail' + img + '.png');
		test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
		casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
	});
	// casper.on('step.complete', function() {
	// 	this.echo(Date.now()-start + "ms");
 //    	start = Date.now();
	// });
	/* Choose an item at home page */
	casper.selectItemAndOptions = function() {
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
	};
	/* add item and go to checkout */
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
        });
	};
	/* Checkout as guest */
	casper.checkoutMethod = function() {
        this.echo("Choosing checkout method...", "INFO");
        this.waitForSelector("button#onepage-guest-register-button", function success() {
            this.click("button#onepage-guest-register-button");
            test.info("Done");
        },function fail() {
            test.assertExists("button#onepage-guest-register-button", "'Continue' button exists");
        });
	};
	/* fill billing operation */
	casper.billingInformation = function() {
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
	};
	/* fill shipping method */
	casper.shippingMethod = function() {
	    this.echo("Filling 'Shipping Method' formular...", "INFO");
	    this.waitUntilVisible('div#checkout-step-shipping_method', function success() {
	        this.click('input#s_method_flatrate_flatrate');
	        this.click("div#shipping-method-buttons-container>button");
	        test.info("Done");
	    }, function fail() {
	        test.assertVisible("form#co-shipping-method-form", "'Shipping Method' formular exists");
	    }, 20000);
	};
	/* place order */
	casper.orderReview = function(paymentType) {
        this.echo("Placing this order via " + paymentType + "...", "INFO");
        this.waitUntilVisible('#checkout-step-review', function success() {
            this.click('button.btn-checkout');
            test.info('Done');
        }, function fail() {
        	this.capture('ok.png');
            test.assertVisible("#checkout-step-payment", "'Order Review' exists");
        }, 10000);
	};
	/* Get order ID after purchase */
	casper.setOrderId = function(pending) {
		if(pending)
			orderID = this.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
		else {
			var text = this.fetchText(x('//p[contains(., "Your order # is:")]')).split(':')[1];
			orderID = text.substring(1, text.length - 1);
		}
		test.info("Order ID : " + orderID);
	};
	casper.getOrderId = function() {
		return orderID;
	};
	/* check order success */
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
        	});
        }, 25000);
	};

	casper.echo('Fonctions chargées !', 'INFO');
	test.info("Based URL: " + headlink);
    test.done();
});