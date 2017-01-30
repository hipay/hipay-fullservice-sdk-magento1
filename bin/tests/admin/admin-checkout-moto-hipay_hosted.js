var x = require('casper').selectXPath;
var BASE_URL = casper.cli.get('url');
var TYPE_CC = casper.cli.get('type-cc');

// Require class utils for test
var admin = require('admin-checkout');
var authentification = require('admin-authentification');
var configuration = require('admin-configuration');
var mailcatcher = require('check-mailcatcher');

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
 *                            test.pass('HOSTED PAYMENT SUCCESSFUL')
 *                     TEST CHECKOUT IN ADMIN : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/
casper.test.begin('ADMIN CHECKOUT HIPAY-CC WITH ' + TYPE_CC + ' ON URL ' + BASE_URL, function (test) {
    casper.start(BASE_URL + 'admin');
    phantom.clearCookies();


    //============================================================== //
    //===           AUTHENTIFICATION                             === //
    //============================================================== //
    authentification.proceed(test);

    //============================================================== //
    //===           CONFIG                             === //
    //============================================================== //
    configuration.proceedMotoSendMail(test, '1');

    //============================================================== //
    //===           ADD ARTICLE TO CART                           === //
    //============================================================== //
    admin.proceed(test);


    //============================================================== //
    //===                       CHECK MAIL AND CHECKOUT         === //
    //============================================================== //
    casper.wait(5000,
        function () {
            mailcatcher.checkMail(test);
        }
    );

    //============================================================== //
    //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
    //============================================================== //
    casper.waitForSelector('#p_method_hipay_hosted',
        function success() {
            // Select the last product in grid
            test.comment('Load Payment Method');
            // Valid Payment
            this.click('#p_method_hipay_hosted');

            this.evaluate(function () {
                payment.switchMethod('hipay_hosted');
            });

            //============================================================== //
            //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
            //============================================================== //
            casper.wait(5000,
                function () {
                    test.comment('Payment');
                    this.click(x('//span[text()="Submit Order"]'));
                    casper.wait(5000, function () {

                        casper.waitForUrl('/admin/sales_order/view/order_id/', function () {
                            test.assertHttpStatus(200);
                            test.assertNotExists('.error-msg');
                        }, null, 5000);
                    });
                }
            );

            //============================================================== //
            //===                       CHECK MAIL AND CHECKOUT         === //
            //============================================================== //
            casper.wait(5000,
                function () {
                    mailcatcher.checkMail(test);
                }
            );

        }, function fail() {
            test.assertExists('#p_method_hipay_hosted');
        });

    casper.run(function () {
        test.done();
    });
})
;