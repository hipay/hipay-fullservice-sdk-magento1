exports.proceed = function proceed(test) {
    /* connection to admin panel */
    casper.then(function() {
        this.echo("Connecting to admin panel...", "INFO");
        this.waitForSelector("#loginForm", function success() {
            this.fillSelectors('form#loginForm', {
                'input[name="login[username]"]': 'hipay',
                'input[name="login[password]"]': 'hipay123'
            }, false);
            this.click('.form-buttons input[type=submit]');
            this.waitForSelector(".header-top", function success() {
                test.info("Done");
            }, function fail() {
                test.assertExists(".error-msg", "Incorrect credentials !");
            }, 20000);
        }, function fail() {
            test.assertExists("#loginForm", "Admin login formular exists");
        });
    });
};