# Using the BitPay plugin for WPMU Membership

## Prerequisites

* Last Version Tested: 3.5.x

You must have a BitPay merchant account to use this plugin.  It's free to [sign-up for a BitPay merchant account](https://bitpay.com/start).

## Installation
**Note:** To use this plugin, your server must have the PHP cURL module installed, Apache mod_rewrite enabled and 'AllowOverrides' set to 'All' in your Apache configuration file (if you're using Apache) for your website files directory.  If you are unsure how to check or configure these items, contact your server administrator or webhosting provider support for assistance.

Download our [latest release](https://github.com/bitpay/wpmembership-plugin/releases/latest) from the releases page. To extract the tar.gz file, use the following command:
```
tar -zxpvf wpmembership-plugin-1.4.0.tar.gz
```
Move the contents of bitpay-installation-files (bitpayinstall.php and bitpay-files) into your wordpress root directory.

### Using the bitpayinstall.php script
After moving the files, go to your browser and type in the url path to your wordpress directory, adding a /bitpayinstall.php at the end. For example, if my document root is already set to start inside of the wordpress root folder, I would type `http://mydomain.com/bitpayinstall.php` into my browser url bar. Follow the directions and fix any necessary permission errors. The install script will determine the architecture of your wordpress folder and automatically install the files in their respective locations.

**NOTE** The install script will not work without 777 permissions on bitpay-files. If you extracted using the -zxpvf (-p tag specifically) by default the permissions should be 777. On the case that it isn't, the install-script will prompt you to set permissions before continuing.

### Manual installation.
If you do not wish to use the installer script, or if you find difficulty using the installer script, you may manually move the files inside bitpay-files into their respective locations. As of Membership 4.x.x, there are two architectures and the files will be installed in the following folders based on Membership version:

For Membership versions after 4.x.x
```
 wp-content/
            plugins/
                    bitpay-form-helper/
                                       bp_lib.php
                                       bp_options.php
                                       form.php
                    membership/
                               app_old/
                                       membershipincludes/
                                                          gateways/
                                                                   gateway.bitpay.php
```

For Membership version before 4.x.x
```
 wp-content/
            plugins/
                    bitpay-form-helper/
                                       bp_lib.php
                                       bp_options.php
                                       form.php
                    membership/
                               membershipincludes/
                                                  gateways/
                                                           gateway.bitpay.php
```

## Configuration
* Create an API key at bitpay.com by clicking My Account > API Access Keys > Add New API Key.
* Log into your Wordpress admin area and click Membership > Gateways.
* Enter your API key from step 1.
* Select a transaction speed.  The high speed will send a confirmation as soon as a transaction is received in the bitcoin network (usually a few seconds).  A medium speed setting will typically take 10 minutes.  The low speed setting usually takes around 1 hour.  See the bitpay.com merchant documentation for a full description of the transaction speed settings: https://bitpay.com/downloads/bitpayApi.pdf
* Select a network. Use livenet for live bitcoin and testnet for fake bitcoin.
* Enter the complete URL for the form.php file in the bitpay-form-helper directory on your server. Be sure to check whether your site is using https or http.
* Enter the complete URL for the graphical button you would like to use on the membership checkout page.
* Click the Save button to save your settings.


## Usage
When a member chooses the Bitcoin payment method and places their order, they will be redirected to bitpay.com to pay.  Bitpay will send a notification to your server which this plugin handles.  Then the customer will be redirected to an order summary page.

The membership subscription is instantly activated once the user pays their invoice. If you wish to change this behavior to keep the subscription in a "processing" state until a completed IPN message is received, open the gateway.bitpay.php file in your favorite editor and scroll down to the handle_bitpay_return() function.  Inside this function, there is a switch/case block which contains the code that handles the IPN status messages and performs the necessary subscription management.  The first edit you'll need to make is to remove or comment out the 'paid' case statement.  Then scroll further down and uncomment the 'paid' case block that will keep the subscription in the desired processing state.  These are the only two changes required to activate 'processing' state handling.

In the event any payment is determined to be invalid, the subscription will be marked as denied for this member's record.

**Note:** This extension does not provide a means of automatically pulling a current BTC exchange rate for presenting BTC prices to shoppers. The invoice automatically displays the correctly converted bitcoin amount as determined by BitPay.
