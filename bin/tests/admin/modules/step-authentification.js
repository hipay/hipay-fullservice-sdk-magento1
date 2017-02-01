exports.proceed = function proceed(test) {

//============================================================== //
//===               CONNECT TO ADMIN PANEL                   === //
//============================================================== //
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
                    test.comment('Authentification');
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

}