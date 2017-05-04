casper.test.begin('Test Magento Hipay Enterprise Config', function(test) {
	phantom.clearCookies();
	var fields = [],
		configsID = ["", "_moto", "_basket"];

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
    })
    .then(function() {
    	this.echo("Accessing to Hipay Enterprise menu and checking blocs menu...", "INFO");
    	this.waitForUrl(/admin\/dashboard/, function success() {
	    	this.click(x('//span[text()="Configuration"]'));
	    	this.waitForUrl(/admin\/system_config/, function success() {
	    		this.click(x('//span[contains(., "HiPay Enterprise")]'));
	    		this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
    				test.assertExists('div.section-config>div>a#hipay_hipay_api-head', "Normal configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_hipay_api' + configsID[1] + '-head', "MOTO configuration activated !");
    				test.assertExists('div.section-config>div>a#hipay_hipay' + configsID[2] + '-head', "Basket configuration activated !");
	    		}, function fail() {
	    			test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
	    		}, 10000);
	    	}, function fail() {
	    		test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
	    	}, 10000);
	    }, function fail() {
	    	test.assertUrlMatch(/admin\/dashboard/, "Dashboard admin page exists");
	    }, 10000);
    })
    .then(function() {
    	this.echo("Checking configurations menu fields...", "INFO");
    	this.each(configsID, function(self, config) {
    		if(config != "_basket")
				fields.push(this.getElementsAttribute('fieldset#hipay_hipay_api' + config + '>table>tbody>tr>td>input, fieldset#hipay_hipay_api' + config + '>table>tbody>tr>td>select', 'name'));
    		else
				fields.push(this.getElementsAttribute('fieldset#hipay_hipay' + config + '>table>tbody>tr>td>select', 'name'));
    	});
    	fields = concatTable(fields);
    	test.info(fields.length + " fields gotten !");
    })
    .then(function() {
    	this.each(fields, function(self, field, i) {
    		test.assert(field != "" && field != "undefined" && field != "null", "Field name n°" + (i+1) + " correct !");
    	});
    })
    .run(function() {
        test.done();
    });
});