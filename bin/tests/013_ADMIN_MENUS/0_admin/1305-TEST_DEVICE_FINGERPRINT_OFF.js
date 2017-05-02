casper.test.begin('Test Magento Without Device Fingerprint', function(test) {
	phantom.clearCookies();
    var ioBB = "";

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
    })
    .then(function() {
        this.setDeviceFingerprint('0');
    })
    .thenOpen(headlink, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.waitForUrl(/checkout\/onepage/, function success() {
            this.echo("Checking 'ioBB' field NOT inside checkout page...", "INFO");
            test.assertDoesntExist('input#ioBB', "'ioBB' field is present and not empty !");
        }, function fail() {
            test.assertUrlMatch(/checkout\/onepage/, "Checkout page exists");
        }, 10000);
    })
    .run(function() {
        test.done();
    });
});