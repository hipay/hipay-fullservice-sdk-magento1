exports.proceed = function proceed(test,that) {

    test.comment('Fill payment in hosted page');
    test.assertHttpStatus(200);

    that.fill('#form-payment', {
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

}


