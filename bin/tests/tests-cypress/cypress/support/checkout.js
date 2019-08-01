/**
 * GO to Home
 */
Cypress.Commands.add("goToFront", () => {
    cy.visit('/');
});

Cypress.Commands.add("goToCart", () => {
    cy.visit('/checkout/cart/');
    cy.get('.method-checkout-cart-methods-onepage-bottom button.btn-checkout').click();
});

/**
 * Select just an item (Album) and add it to the cart
 */
Cypress.Commands.add("selectItemAndGoToCart", (qty) => {
    cy.selectShirtItem(qty);
    cy.goToCart();
});

Cypress.Commands.add("selectShirtItem", (qty) => {
    cy.selectItem(
        '/tori-tank-587.html',
        qty
    );
});

Cypress.Commands.add("selectMugItem", (qty) => {
    cy.selectItem(
        '/home-decor/decorative-accents/geometric-candle-holders.html',
        qty
    );
});

Cypress.Commands.add("selectVirtualItem", (qty) => {
    cy.selectItem(
        '/home-decor/books-music/alice-in-wonderland.html',
        qty
    );
});


Cypress.Commands.add("selectItem", (url, qty) => {
    cy.visit(url);

    if (qty !== undefined) {
        cy.get("#qty").clear({force: true});
        cy.get("#qty").type(qty);
    }

    cy.get('body').then(($body) => {
        // synchronously query from body
        // to find which element was created
        if ($body.find('#configurable_swatch_color').length) {
            cy.get('#configurable_swatch_color *:not(.not-available) .swatch-link:first').click();
        }

        if ($body.find('#configurable_swatch_size').length) {
            cy.get('#configurable_swatch_size *:not(.not-available) .swatch-link:first').click();
        }

        if ($body.find('#downloadable-links-list').length) {
            cy.get('#downloadable-links-list .product-downloadable-link:first').click();
        }
    });

    cy.get('.add-to-cart-buttons button.btn-cart').click();
});

Cypress.Commands.add("checkoutAsGuest", () => {
    cy.get('#login\\:guest').click();
    cy.get('#onepage-guest-register-button').click();
});

/**
 * Fill Billing from in checkout
 */
Cypress.Commands.add("fillBillingForm", (loggedIn, country) => {
    cy.server();
    cy.route('POST', '/checkout/onepage/saveBilling/').as('saveBilling');
    cy.route('GET', '/checkout/onepage/progress/?prevStep=billing').as('progressBilling');

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#billing\\:firstname').clear().type(customer.firstName);
        cy.get('#billing\\:lastname').clear().type(customer.lastName);

        if(!loggedIn) {
            cy.get('#billing\\:email').clear().type(customer.email);
        }

        cy.get('#billing\\:street1').clear().type(customer.streetAddress + "1");
        cy.get('#billing\\:city').clear().type(customer.city);
        cy.get('#billing\\:postcode').clear().type(customer.zipCode);
        cy.get('#billing\\:country_id').select(customer.country, {force: true});
        if (customer.state !== undefined) {
            cy.get('#billing\\:region_id').select(customer.state, {force: true});
        }
        cy.get('#billing\\:telephone').clear().type(customer.phone, {force: true});
        cy.get('button[onclick="billing.save()"]').click();

        cy.wait('@saveBilling');
        cy.wait('@progressBilling');
    });
});

/**
 * Fill Shipping from in checkout
 */
Cypress.Commands.add("fillShippingForm", (loggedIn, country) => {
    cy.server();
    cy.route('POST', '/checkout/onepage/saveShipping/').as('saveShipping');
    cy.route('GET', '/checkout/onepage/progress/?prevStep=shipping').as('progressShipping');

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#shipping\\:firstname').clear().type(customer.firstName);
        cy.get('#shipping\\:lastname').clear().type(customer.lastName);
        if(!loggedIn) {
            cy.get('#shipping\\:email').clear().type(customer.email);
        }

        cy.get('#shipping\\:street1').clear().type(customer.streetAddress);
        cy.get('#shipping\\:city').clear().type(customer.city);
        cy.get('#shipping\\:postcode').clear().type(customer.zipCode);
        cy.get('#shipping\\:country_id').select(customer.country, {force: true});
        if (customer.state !== undefined) {
            cy.get('#shipping\\:region_id').select(customer.state, {force: true});
        }
        cy.get('#shipping\\:telephone').clear().type(customer.phone, {force: true});
        cy.get('button[onclick="shipping.save()"]').click();

        cy.wait('@saveShipping');
        cy.wait('@progressShipping');
    });
});

/**
 * Fill Shipping from in checkout
 */
Cypress.Commands.add("selectShippingForm", (wrappingGift) => {
    cy.server();
    cy.route('POST', '/checkout/onepage/saveShippingMethod/').as('saveShippingMethod');
    cy.route('GET', '/checkout/onepage/progress/?prevStep=shipping_method\n').as('progressShippingMethod');

    cy.get('body').then(($body) => {
        if ($body.find('#s_method_flatrate_flatrate:visible').length) {
            cy.get('#s_method_flatrate_flatrate').click();
        }

        if (wrappingGift !== undefined) {
            cy.get('#allow_gift_messages').click();
            cy.get('#allow_gift_messages_for_order').click();
        }

        cy.get('#shipping-method-buttons-container > .button').click();
        cy.wait('@saveShippingMethod');
        cy.wait('@progressShippingMethod');
    });
});

/**
 * Check page for redirection sucess
 */
Cypress.Commands.add("checkOrderSuccess", () => {
    cy.url().should('include', '/checkout/onepage/success/');
});

Cypress.Commands.add("saveLastOrderId", () => {
    cy.get('.col-main > p:nth-child(4) > a:nth-child(1)').then(($a) => {
        cy.fixture('order').then((order) => {
            order.lastOrderId = $a.text();
            order.lastOrderLink = $a.attr('href');
            cy.writeFile('cypress/fixtures/order.json', order);
        });
    });
});

/**
 * Register
 */
Cypress.Commands.add("signIn", (country) => {
    cy.visit('/customer/account/create/');

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#firstname').type(customer.firstName);
        cy.get('#lastname').type(customer.lastName);
        cy.get('#email_address').type(customer.email);

        cy.get('#password').type(customer.password);
        cy.get('#confirmation').type(customer.password);

        cy.get('button[title="Register"]').click();
    });
});


/**
 * Log in
 */
Cypress.Commands.add("logIn", (country) => {
    cy.visit('/customer/account/login/');

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#email').type(customer.email);
        cy.get('#pass').type(customer.password);
        cy.get('#send2').click();

        cy.get('.welcome-msg').contains('Welcome, ' + customer.firstName + ' ' + customer.lastName + '!');
    });
});

/**
 * Check Authorization
 */
Cypress.Commands.add("checkAuthorizationStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :116");
    cy.get("#id_order_state_chosen span").contains("Paiement autorisé (HiPay)");
});

/**
 * Check Capture
 */
Cypress.Commands.add("checkCaptureStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :118");
    cy.get("#id_order_state_chosen span").contains("Paiement accepté");
});

/**
 * Check Refund
 */
Cypress.Commands.add("checkRefundStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :124");
    cy.get("#id_order_state_chosen span").contains("Remboursement demandé (HiPay)");
});
