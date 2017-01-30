var x = require('casper').selectXPath;
var BASE_URL = casper.cli.get('url');
var TYPE_CC = casper.cli.get('type-cc');
var admin   = require('admin-checkout');

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
 *                              TEST CHECKOUT IN ADMIN : HOSTED
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
    configuration.proceedMotoSendMail(test,'0');

    //============================================================== //
    //===           ADD ARTICLE TO CART                           === //
    //============================================================== //
    admin.checkout(test);

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
                        test.assertNotExists('.error-msg');

                        casper.waitForUrl('/payment\/web\/pay/', function () {
                            test.comment('Fill payment in hosted page');
                            test.assertHttpStatus(200);

                            this.fill('#form-payment', {
                                paymentproductswitcher: 'visa'
                            }, false);

                            //============================================================== //
                            casper.wait(5000,
                                function () {
                                    this.page.switchToChildFrame(0);
                                    this.fill('#tokenizerForm', {
                                        'tokenizerForm:cardNumber': '4111111111111111',
                                        'tokenizerForm:cardHolder': 'MC',
                                        'tokenizerForm:cardExpiryMonth': '12',
                                        'tokenizerForm:cardExpiryYear': '2020',
                                        'tokenizerForm:cardSecurityCode': '500',
                                    }, false);

                                    this.page.switchToParentFrame();
                                    this.click('#submit-button');
                                }, null);

                            casper.wait(100,
                                function () {
                                    this.page.switchToParentFrame();
                                    this.click('#submit-button');
                                }, null);

                        }, function fail() {
                            this.debugPage();
                        }, 10000);

                        casper.waitForUrl('/admin/sales_order/view/order_id/', function () {
                            test.assertHttpStatus(200);
                            test.assertNotExists('.error-msg');
                            test.pass('HOSTED PAYMENT SUCCESSFUL')
                        }, null, 5000);
                    });
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