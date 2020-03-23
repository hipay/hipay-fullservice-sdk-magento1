/**
 * Log to Prestashop Admin
 */
Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin');
    cy.get('#username').type("hipay");
    cy.get('#login').type('hipay123');
    cy.get('*[title="Login"]').click();

    cy.get('.adminhtml-dashboard-index');
});

Cypress.Commands.add("adminLogOut", () => {
    cy.visit('/admin');
    cy.get('.link-logout').click({force: true});
});

Cypress.Commands.add("goToHipayModuleAdmin", () => {
    cy.get('span').contains('Configuration').click({force:true});
    cy.get('span').contains('HiPay Enterprise').click({force:true});
});

Cypress.Commands.add("goToHipayModulePaymentMethodAdmin", () => {
    cy.get('span').contains('Configuration').click({force:true});
    cy.get('span').contains('Payment Methods').click({force:true});
});

Cypress.Commands.add("goToHipaySplitPaymentProfileAdmin", () => {
    cy.get('span').contains('Split Payment Profiles').click({force:true});
});

Cypress.Commands.add("goToHipaySplitPaymentAdmin", () => {
    cy.get('span').contains('Split payments').click({force:true});
});

Cypress.Commands.add("resetProductionConfigForm", () => {
    cy.get('#hipay_hipay_api-head').click();
    cy.get('#hipay_hipay_api_api_username').clear({force: true});
    cy.get('#hipay_hipay_api_api_password').clear({force: true});
    cy.get('#hipay_hipay_api_api_tokenjs_username').clear({force: true});
    cy.get('#hipay_hipay_api_api_tokenjs_publickey').clear({force: true});
    cy.get('#hipay_hipay_api_secret_passphrase').clear({force: true});

    cy.get('#hipay_hipay_api_moto-head').click();
    cy.get('#hipay_hipay_api_moto_api_username').clear({force: true});
    cy.get('#hipay_hipay_api_moto_api_password').clear({force: true});
    cy.get('#hipay_hipay_api_moto_secret_passphrase').clear({force: true});
});


/**
 *  Activate Payment Methods
 */
Cypress.Commands.add("activatePaymentMethods", (method) => {
    cy.get('#payment_hipay_' + method + '_active').then(($selectActive) => {
        if(!$selectActive.is(":visible")) {
            cy.get('#payment_hipay_' + method + '-head').click();
        }

        cy.get('#payment_hipay_' + method + '_active').select("Yes");
        cy.get('#payment_hipay_' + method + '_debug').select("Yes");
        cy.get('#payment_hipay_' + method + '_is_test_mode').select("Yes");
        cy.get('.content-header-floating *[title="Save Config"]').click();

        cy.get('.success-msg').contains('The configuration has been saved');
    });
});

Cypress.Commands.add("configurePaymentMethods", (method, field, value) => {
    cy.get('#payment_hipay_' + method + '_active').then(($selectActive) => {
        if(!$selectActive.is(":visible")) {
            cy.get('#payment_hipay_' + method + '-head').click();
        }

        cy.get('#payment_hipay_' + method + '_' + field).select(value);
        cy.get('.content-header-floating *[title="Save Config"]').click();

        cy.get('.success-msg').contains('The configuration has been saved');
    })
});

Cypress.Commands.add("activateOneClick", (method) => {
    cy.get('#payment_hipay_' + method + '_active').then(($selectActive) => {
        if(!$selectActive.is(":visible")) {
            cy.get('#payment_hipay_' + method + '-head').click();
        }

        cy.get('#payment_hipay_' + method + '_allow_use_oneclick').select("Yes");
        cy.get('.content-header-floating *[title="Save Config"]').click();

        cy.get('.success-msg').contains('The configuration has been saved');
    });
});

Cypress.Commands.add("selectPaymentProfile", (method, profile) => {
    cy.get('#payment_hipay_' + method + '_active').then(($selectActive) => {
        if(!$selectActive.is(":visible")) {
            cy.get('#payment_hipay_' + method + '-head').click();
        }

        cy.get('#payment_hipay_' + method + '_split_payment_profile').select(profile);
        cy.get('.content-header-floating *[title="Save Config"]').click();

        cy.get('.success-msg').contains('The configuration has been saved');
    });
});

Cypress.Commands.add("createPaymentProfile", (profileName, unit, delay, maxCycles) => {
    cy.get('body').then(($body) => {
        if(!$body.find(':contains("' + profileName + '")').length) {
            cy.get('.content-header-floating *[title="Add payment profile"]').click({force:true});

            cy.get('#name').clear().type(profileName);
            cy.get('#period_unit').select(unit);
            cy.get('#period_frequency').clear().type(delay);
            cy.get('#period_max_cycles').clear().type(maxCycles);

            cy.get('.content-header-floating *[title="Save"]').click({force:true});
            cy.get('.success-msg').contains('The payment profile has been saved.');
        }
    });
});


/**
 * Requests steps
 */
Cypress.Commands.add("getLastOrderRequest", (method) => {
    cy.getAllRequests(method).then((requests) => {
        requests.reverse();
        for (let request of requests) {
            if (request.orderid) {
                return request;
            }
        }

        return null;
    });
});

Cypress.Commands.add("getOrderRequest", (method, orderId) => {
    let goodOrder = null;
    let regex = new RegExp(orderId + ".*");
    cy.getAllRequests(method).then((requests) => {
        for (let request of requests) {
            if (request.orderid && request.orderid.toString().match(regex)) {
                goodOrder = request;
            }
        }

        return goodOrder;
    });
});


Cypress.Commands.add("getAllRequests", (method) => {
    cy.request('/var/log/payment_hipay_' + method + '.log')
        .then((text) => {
            var utils = require('./utils');

            let rawLogArray = text.body.split(/^20[0-9]{2}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2} DEBUG \([0-9]\): /gm);
            let logArray = [];
            for (let rawLog of rawLogArray) {
                if (rawLog !== "") {
                    let logJson = rawLog.replace(/\\/gm, '\\\\"')
                        .replace(/"/gm, '\\"')
                        .replace(/ => /gm, '": "')
                        .replace(/^\s*\[(.*)\]": /gm, '"$1": ')
                        .replace(/(.)$/gm, '$1",')
                        .replace(/\s*"?Array",\s*\(",/gm, '{')
                        .replace(/^\s*\)",\s*$/gm, '},')
                        .replace(/,\s*}/gm, '}')
                    logJson = logJson.substr(0, logJson.length - 1);

                    let log = utils.jsonParseDeep(logJson);
                    logArray.push(log);
                }
            }

            cy.log(logArray).then(() => {
                return logArray;
            });
        });
});

/**
 * Clients management
 */
Cypress.Commands.add("deleteClients", () => {
    cy.get('span').contains('Manage Customers').click({force: true});
    cy.get('body').then(($body) => {
        // synchronously query from body
        // to find which element was created
        if ($body.find('.massaction-checkbox').length) {
            cy.get("a").contains('Select All').click({force: true});
            cy.wait(2000);
            cy.get("#customerGrid_massaction-select").select('Delete');
            cy.get('*[title="Submit"]').click();
        } else {
            cy.log("No user found");
            return;
        }
    });
});