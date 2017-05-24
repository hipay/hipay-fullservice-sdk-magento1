/**********************************************************************************************
 *
 *                              TEST CHECKOUT IN ADMIN : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/
var paymentType = "HiPay Enterprise Hosted Page";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(headlink + 'admin')
    .then(function() {
        /* authentification */
        authentification.proceed(test);
        /* configuration */
        configuration.proceedMotoSendMail(test, '1');
        /* payment method activation */
        method.proceed(test, paymentType, "hosted");
        /* selection item and adding it to cart */
        checkout.proceed(test, paymentType, "hosted");
    })
    /* submit created order */
    .then(function() {
        this.echo("Submitting order...", "INFO");
        this.waitForSelector(x('//span[text()="Submit Order"]'), function success() {
            this.click(x('//span[text()="Submit Order"]'));
            test.info("Done");
        }, function fail() {
            test.assertExists(x('//span[text()="Submit Order"]'), "Submit order button exists");
        });
    })
    /* check mail and checkout */
    .then(function() {
        mailcatcher.checkMail(test, paymentType);
    })
    .run(function() {
        test.done();
    });
});