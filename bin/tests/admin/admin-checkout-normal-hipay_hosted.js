var x = require('casper').selectXPath;
var BASE_URL = casper.cli.get('url');
var TYPE_CC = casper.cli.get('type-cc');

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
 *                              TEST CHECKOUT IN ADMIN : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/
casper.test.begin('ADMIN CHECKOUT NORMAL HIPAY-HOSTED WITH ' + TYPE_CC + ' ON URL ' + BASE_URL,5, function (test) {
    casper.start(BASE_URL + 'admin');
    phantom.clearCookies();

    //============================================================== //
    //===           AUTHENTIFICATION                             === //
    //============================================================== //
    authentification.proceed(test);

    //============================================================== //
    //===           CONFIG                                       === //
    //============================================================== //
    configuration.proceedMotoSendMail(test, '0');

    //============================================================== //
    //===           ADD ARTICLE TO CART                           === //
    //============================================================== //
    checkout.proceed(test);

    //============================================================== //
    //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
    //============================================================== //
    casper.waitForSelector('#p_method_hipay_hosted',
        function success() {
            //============================================================== //
            //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
            //============================================================== //
            casper.wait(10000,
                function () {
                    test.comment('Payment');
                    this.click(x('//span[text()="Submit Order"]'));
                    casper.wait(10000, function () {
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
                            test.fail('Submit Error')
                        }, 10000);

                        casper.waitForUrl('/admin/sales_order/view/order_id/', function () {
                            test.assertHttpStatus(200);
                            test.assertNotExists('.error-msg');
                            test.pass('HOSTED PAYMENT SUCCESSFUL')
                        }, null, 15000);
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