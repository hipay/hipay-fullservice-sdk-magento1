var x = require('casper').selectXPath;
var BASE_URL = casper.cli.get('url');
var TYPE_CC = casper.cli.get('type-cc');
var URL_MAILCATCHER = casper.cli.get('url-mailcatcher');

// Require class utils for test
var payment = require('modules/step-payment');
var checkout = require('modules/step-checkout');
var authentification = require('modules/step-authentification');
var configuration = require('modules/step-configuration');
var mailcatcher = require('modules/step-mailcatcher');
var pay = require('modules/step-pay-hosted');

casper.on('remote.alert', function (message) {
    this.echo('alert message: ' + message);
});

casper.on('remote.message', function (msg) {
    this.echo('remote message caught: ' + msg);
});

casper.on('page.error', function (msg, trace) {
    this.echo('Error: ' + msg, 'ERROR');
    for (var i = 0; i < trace.length; i++) {
        var step = trace[i];
        this.echo('   ' + step.file + ' (line ' + step.line + ')', 'ERROR');
    }
});

/**********************************************************************************************
 *
 *                     TEST CHECKOUT IN ADMIN : HOSTED
 *
 *  To launch the test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/
casper.test.begin('ADMIN CHECKOUT MOTO HIPAY-HOSTED WITH ' + TYPE_CC + ' ON URL ' + BASE_URL,5, function (test) {
    casper.start(BASE_URL + 'admin');
    phantom.clearCookies();

    //============================================================== //
    //===           AUTHENTIFICATION                             === //
    //============================================================== //
    authentification.proceed(test);

    //============================================================== //
    //===           CONFIG                                       === //
    //============================================================== //
    configuration.proceedMotoSendMail(test, '1');

    //============================================================== //
    //===           ADD ARTICLE TO CART                           === //
    //============================================================== //
    checkout.proceed(test);

    casper.waitForSelector(x('//span[text()="Submit Order"]'), function () {
        this.click(x('//span[text()="Submit Order"]'));
    });

    //============================================================== //
    //===           CHECK MAIL AND CHECKOUT                      === //
    //============================================================== //
    casper.wait(5000,
        function () {
            mailcatcher.checkMail(test);
        }
    );

    casper.run(function () {
        test.done();
    });
})
;