exports.proceed = function proceed(test) {

    casper.waitForSelector('input#payment-product-switcher-visa', function success() {
        test.comment('Fill payment in hosted page');
        test.assertHttpStatus(200);

        this.evaluate(function() {
            document.querySelector('input#payment-product-switcher-visa').click();
        });
        //============================================================== //
        this.wait(3000,
            function () {
                this.fillSelectors('#form-payment', {
                    'input[name="cardNumber"]': '4111111111111111',
                    'input[name="cardHolder"]': 'MC',
                    'select[name="cardExpiryMonth"]': '12',
                    'select[name="cardExpiryYear"]': '2020',
                    'input[name="cardSecurityCode"]': '500'
                }, false);
                this.click('#submit-button');
            }
        );
    }, function fail() {
        this.echo(this.currentUrl);
        test.assertExists('input#payment-product-switcher-visa');
    });
}


