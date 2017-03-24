exports.proceed = function proceed(test) {

    test.comment('Fill payment in hosted page');
    test.assertHttpStatus(200);

    casper.evaluate(function() {
        document.querySelector('input#payment-product-switcher-visa').click();
    });
    //============================================================== //
    casper.wait(3000,
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
}


