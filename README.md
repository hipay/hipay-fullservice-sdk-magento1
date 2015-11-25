# Module Hipay Full Service

## How to use this repository

### Get and change sources
If you want to do any changes follow this steps:

1.  Clone the repo
```
$ git clone git@gitlab.sirateck.com:magento-modules/allopass_hipay.git
```
  **Note:**  
  Don't forget to put your ssh public key into your gitlab account.  Else try with http connection:  
```
$ git clone http://gitlab.sirateck.com/magento-modules/allopass_hipay.git
```

2.  Checkout into `develop` branch
```
$ git checkout -b develop origin/develop
```
3.  Write your modifications and save your filess

4. Stage and commit your changes
```
// Stage all modifications
$ git add .
// Commit with message. For good practices see: https://github.com/ajoslin/conventional-changelog/blob/master/conventions/angular.md  
$ git commit -m "fix(hosted): Fix iframe size"
```
5.  Share your code
```
$ git push
```

6. Finally, send merge request

  Go to your **gitlab account** on this project and send a [merge request](http://gitlab.sirateck.com/magento-modules/allopass_hipay/merge_requests) to admin users.

### Build Magento package

You can easily build Magento package with composer.  
If you don't have composer see: https://getcomposer.org/.  
The following example take in consideration you have command `composer` available in your PATH environment.  
Instead you can use `composer.phar` directly but it is less convenient.  
`tar` command is also required.

**IMPORTANT**: Make sure you are in master branch (`$ git checkout master`)

1.  Install dependencies
In project's root run:
```
$ composer install
```

2.  Build package
```
$ composer package
```

If build package is successful you can see *tar.gz file* in `dist/`  and *Package xml file* in `dist/var/connect`.

# HiPay Fullservice Features

## Overview   

HiPay Fullservice is the first payment platform oriented towards merchants that responds to all matters related to online payment:  
transaction processing, risk management, relationship management with banks and acquirers, financial reconciliation or even international expansion.  


## Optimise your conversion rate

Through one single integration, Hipay offers all local and international payment methods,   
the latest generation tool for fighting fraud and innovative features for an optimised conversion rate!    


![alt mode de paiement](http://58b7509f0ca565bdd628-3b5a1171ec85e695d4ada5118e96496e.r14.cf1.rackcdn.com/files/535e59ffc254511e29000020/size_3_logo-paiements.png)


HiPay Fullservice offers innovative features to reduce shopping cart abandonment rates and optimise the purchasing process on your merchant sites.

## Our module provides you with different options to increase your sales:

### Activation of 3Ds upon request:
* Configure when, how and for whom you want to request 3D Secure.
* Module for fighting intelligent fraud – HiPay FPS.
* Equipped with the latest technological innovations in the field, our module provides you with optimum protection without blocking legitimate transactions.

### A payment page with your branding:  
* The payment page offered by HiPay Fullservice is completely customisable with your corporate identity, is available in 10 languages and is built using multi-screen logic   
(responsive design). All of these options guarantee a unique user experience and an optimal conversion rate.   

## Expand your business on an international scale

HiPay Fullservice meets the security and electronic payment requirements of cross-border online trade and through   
one single integration, offers the most favoured domestic and international payment solutions in each market  
(Sofort, Sisal Pay, American Express, Visa, MasterCard, Qiwi, Bancontact Mister Cash, Ideal, Przelewy 24, Paysafcard, Multibanco, Yandex, Webmoney, Belfiu etc.)

HiPay Fullservice allows your foreign customers to make purchases in their own currency. (More than 100 currencies available)

## Features to save you time in managing your transactions

### Management of user permissions:
* HiPay Fullservice allows you to create user accounts with specific permissions for each individual. A feature that allows you to ensure your tool for managing transactions is secure.

### Numerous payment options:
* 1-click payments...

### Management of multi-accounts:
* Make it easy for your staff with automated financial reconciliation and eliminate 80% of the manual work undertaken by your team.

## Flexible integration

### HiPay Fullservice offers a choice of 3 types of integration to best suit your business:
* **Host mode**: HiPay Fullservice hosts the payment page on its secure site. With this option you will benefit from getting a single point of contact,   
adjustable payment pages that are PCI-DSS standard. In addition, you can outsource the heavy security requirements necessary for accepting payments.
* **Iframe**: A hybrid solution where the buyer remains on the merchant site to make a payment, but the information is entered on an iframe hosted by HiPay Fullservice.   
You will only need an SSL certificate to reassure your customers.
* **API integration**: The payment page is hosted entirely on the merchant site. You will need a PCI-DSS certification to allow credit card numbers to be transferred through your servers.

## Our experts in on-line payments are here for you

### For more information about the module, please contact us at:

support.tpp @ hipay.com

Send us an email so that we can send you the **technical documentation**.

For more information, have a look at: http://www.hipayfullservice.com

### Our group:

HiPay, the electronic banking branch of the Himedia Group, offers publishers of digital content and e-tailers, the payment modes that are the most relevant for the development of their business.  
The **HiPay** solutions are framed by **two European licenses for Payment Institutions and Electronic Money Issuer**.

### Our clients:

Various large companies have already placed their trust in us: Promod, F&M, Sfrpay, etc.