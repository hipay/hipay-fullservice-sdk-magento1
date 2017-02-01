exports.proceed = function proceed(test, that) {

//============================================================== //
//===               SELECT PAYMENT                           === //
//============================================================== //
    casper.then(
        function () {
            // Select the last product in grid
            test.comment('Load Payment Method');
            // Valid Payment
            this.click('#p_method_hipay_hosted');

            this.evaluate(function () {
                payment.switchMethod('hipay_hosted');
            });
        }
    );
};

