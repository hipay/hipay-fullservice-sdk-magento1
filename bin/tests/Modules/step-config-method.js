exports.proceed = function proceed(test, method, nameField, option) {
	/* Payment method configuration */
	casper.then(function() {
		this.echo("Configuring payment method: " + method + "...", "INFO");
		this.waitForSelector(x('//span[text()="Configuration"]'), function success() {
			this.click(x('//span[text()="Configuration"]'));
			this.waitForSelector(x('//span[contains(.,"Payment Methods")]'), function success() {
				this.click(x('//span[contains(.,"Payment Methods")]'));
				this.waitForSelector(x('//a[text()="' + method + '"]'), function success() {
					this.click(x('//a[text()="' + method + '"]'));
                    this.waitUntilVisible('select[name="groups[hipay_' + nameField + '][fields][active][value]"]', function success() {
                        var fill = {};
                        fill['select[name="groups[hipay_' + nameField +  '][fields][active][value]"]'] = "1";
                        fill['select[name="groups[hipay_' + nameField +  '][fields][debug][value]"]'] = "1";
                        fill['select[name="groups[hipay_' + nameField +  '][fields][is_test_mode][value]"]'] = "1";
                        if(typeof option != 'undefined')
                            fill[option[0]] = option[1];
                        this.fillSelectors('form#config_edit_form', fill, false);
                        this.click(x('//span[text()="Save Config"]'));
                        this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                    	    test.info(method + " Configuration done");
                        }, function fail() {
                            test.fail('Failed to apply ' + method + ' Configuration on the system');
                        }, 15000);
                    }, function fail() {
                        test.assertVisible('select[name="groups[hipay_' + nameField + '][fields][active][value]"]', "'Enabled' select exists");
                    });
				}, function fail() {
                    test.assertExists(x('//a[text()="' + method + '"]'), method + " payment method exists");
                });
			}, function fail() {
                test.assertExists(x('//span[contains(.,"Payment Methods")]'), "Payment Methods menu exists");
            }, 20000);
		}, function fail() {
            test.assertExists(x('//span[text()="Configuration"]'), "Configuration menu exists");
        }, 20000);
	});
};