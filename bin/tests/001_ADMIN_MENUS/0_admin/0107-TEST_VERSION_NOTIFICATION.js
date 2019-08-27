casper.test.begin('Test Notification on Magento', function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            if (this.visible('p[class="bugs"]')) {
                test.done();
            }
        })
        .thenOpen(baseURL + "admin/", function () {
            this.logToBackend();
        })
        .then(function () {
            test.assertExists('div.notification-global', 'Notification is found');
            this.click("li.level0:nth-child(9) > ul:nth-child(2) > li:nth-child(2) > a:nth-child(1)");
        })
        .then(function (){
            test.assertExists('a[href^="https://github.com/hipay/hipay-fullservice-sdk-magento1/releases/tag/"]', 'Full notification is found');
            this.click('a.link-logout');
        })
        .then(function () {
            this.logToBackend();
        })
        .then(function () {
            test.assertExists('#message-popup-window', 'Notification popup is found');
            test.assertMatch(this.fetchText('p.message-text'), /HiPay enterprise .* available!/, 'Notification message is OK');
        })
        .run(function () {
            test.done();
        });
});