exports.proceed = function proceed(test) {

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
                        }, 20000);
                }
            );


        }, function fail() {
            test.assertExists(x('//span[text()="Add Selected Product(s) to Order"]'));
        });


    casper.waitForSelector('#p_method_hipay_hosted',
        function success() {
            //============================================================== //
            //===           SELECT PAYMENT                              === //
            //============================================================== //
            payment.proceed(test);

        });


};

