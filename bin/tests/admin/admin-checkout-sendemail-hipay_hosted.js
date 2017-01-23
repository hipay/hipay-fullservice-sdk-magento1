var x = require('casper').selectXPath;
var BASE_URL = casper.cli.get('url');
var TYPE_CC = casper.cli.get('type-cc');


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
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/
casper.test.begin('ADMIN CHECKOUT HIPAY-CC WITH ' + TYPE_CC + ' ON URL ' + BASE_URL, function (test) {
    casper.start(BASE_URL + 'admin');
    phantom.clearCookies();

    // Connect to admin PANEL
    casper.waitForSelector("#loginForm",
        function success() {
            // Fill form for authentification
            this.fill('form#loginForm', {
                'login[username]': 'hipay',
                'login[password]': 'hipay123'
            }, false);

            // Valid form for authentification
            this.click('.form-buttons input[type=submit]');

            casper.waitForSelector(".header-top",
                function success() {
                    test.pass('AUTHENTIFICATION MAGENTO');
                },
                function fail() {
                    test.assertExists(".error-msg");
                }
                , 20000);
        },
        function fail() {
            test.assertExists("#loginForm");
        }
    );

    //============================================================== //
    //===               SELECT CUSTOMER AND STORE                === //
    //============================================================== //
    casper.then(
        function () {
            // Select Panel orders
            this.click(x('//span[text()="Orders"]'));
            casper.waitForSelector("td.form-buttons button.add",
                function success() {
                    // Enter in add orders workflow
                    this.click('td.form-buttons button.add');
                    casper.waitForSelector(x('//tr[@title=\'136\']'),
                        function success() {
                            // Select Customer Jane Doe
                            test.comment('Create an order for Jane Doe');
                            this.click(x('//tr[@title=\'136\']'));
                        },
                        function fail() {
                            test.assertExists(x('//tr[@title=\'136\']'));
                        }
                        , 20000);
                },
                function fail() {
                    test.assertExists(".error-msg");
                }
                , 20000);
        }
    );
    //============================================================== //
    //===                      SELECT PRODUCT                    === //
    //============================================================== //
    casper.then(
        function () {
            casper.waitForSelector(x('//input[@type="radio" and @id="store_2" and @class="radio"]'),
                function success() {
                    // Select store
                    test.comment('Select store French');
                    this.click(x('//input[@type="radio" and @id="store_2" and @class="radio"]'));

                    casper.waitForSelector(x('//span[text()="Add Products"]'),
                        function success() {
                            // Add Product
                            casper.then(function () {
                                    test.comment('Add product (Load Grid Product)');
                                    this.click(x('//span[text()="Add Products"]'));
                                }
                            );

                            casper.waitForSelector('#sales_order_create_search_grid_table tbody tr:first-child td:first-child',
                                function success() {
                                    // Select the last product in grid
                                    casper.then(function () {
                                            test.comment('Select one product 2');
                                            this.click('#sales_order_create_search_grid_table tbody tr:first-child input.checkbox');
                                        }
                                    );
                                }, function fail() {
                                    test.assertExists("#sales_order_create_search_grid_table tbody tr:first-child td:first-child");

                                });

                            casper.waitForSelector(x('//span[text()="Add Selected Product(s) to Order"]'),
                                function success() {
                                    // Select the last product in grid
                                    casper.then(function () {
                                            test.comment('Confirm product selection 3');
                                            this.click(x('//span[text()="Add Selected Product(s) to Order"]'));
                                        }
                                    );

                                    // Select the last product in grid
                                    casper.then(function () {
                                            test.comment('Load Shipping Method');
                                            this.click('#order-shipping-method-summary > a');
                                        }
                                    );
                                }, function fail() {
                                    test.assertExists(x('//span[text()="Add Selected Product(s) to Order"]'));
                                });
                        },
                        function fail() {
                            test.assertExists(".error-msg");
                        }
                    );
                },
                function fail() {
                    test.assertExists(".error-msg");
                }
                , 20000);

        }
    );

    //============================================================== //
    //===           SELECT SHIPPING METHOD ( UPS )               === //
    //============================================================== //
    casper.waitForSelector('#order-shipping-method-summary > a',
        function success() {
            // Select the last product in grid
            casper.then(function () {
                    test.comment('Load Shipping Method');
                    this.click('#order-shipping-method-summary > a');
                }
            );
            casper.waitForSelector(x('//input[@id="s_method_ups_3DS"]'),
                function success() {
                    // Select UPS 3DS
                    casper.then(function () {
                            test.comment('Select Shipping method');
                            this.click(x('//input[@id="s_method_ups_3DS"]'));
                        }
                    );
                }, function fail() {
                    test.assertExists(x('//input[@id="s_method_ups_3DS"]'));
                });
        }, function fail() {
            test.assertExists(x('//span[text()="Add Selected Product(s) to Order"]'));
        });

    //============================================================== //
    //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
    //============================================================== //
    casper.waitForSelector('.payment-methods',
        function success() {
            // Select the last product in grid
            test.comment('Load Shipping Method');
            // Valid Payment
            this.evaluate(function () {
                payment.switchMethod('hipay_hosted');
            });

            //============================================================== //
            //===           SELECT AND FILL PAYMENT METHOD ( UPS )       === //
            //============================================================== //
            casper.wait(5000,
                function (){
                    test.comment('Payment');
                    this.click(x('//span[text()="Submit Order"]'));
                    casper.wait(20000,function(){
                        test.assertExists('.error-msg');

                        this.evaluate(function () {
                            console.log(document.querySelector('.error-msg').innerHTML);
                        });
                        casper.waitForUrl('/https:\/\/stage-secure-gateway.hipay-tpp.com\/payment\/web\/pay/', function () {
                            test.assertHttpStatus(200);


                        }, null, 20000);
                    });

                }
            );

        }, function fail() {
            test.assertExists('.payment-methods');
        });



    casper.run(function () {
        test.done();
    });
})
;