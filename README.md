bitpay/wpmembership-plugin
==========================

# Installation

<em><strong>Note:</strong> To use this plugin, your server must have the PHP cURL module installed, Apache mod_rewrite enabled and 'AllowOverrides' set to 'All' in your Apache configuration file (if you're using Apache) for your website files directory.  If you are unsure how to check or configure these items, contact your server administrator or webhosting provider support for assistance.</em>

After you have downloaded the latest plugin zip/tarball file from file from https://github.com/bitpay/wpmembership-plugin/releases/latest unzip this archive and copy the folders including their contents into your Wordpress plugins directory.

There are two folders inside this plugin zip file which contain the one WPMU Membership payment gateway file and three form helper files.  The structure is as follows:
<pre>
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
</pre>


# Configuration

1. Create an API key at bitpay.com by clicking My Account > API Access Keys > Add New API Key.<br />
2. Log into your Wordpress admin area and click Membership > Gateways.<br />
a. Enter your API key from step 1.<br />
b. Select a transaction speed.  The high speed will send a confirmation as soon as a transaction is received in the bitcoin network (usually a few seconds).  A medium speed setting will typically take 10 minutes.  The low speed setting usually takes around 1 hour.  See the bitpay.com merchant documentation for a full description of the transaction speed settings: https://bitpay.com/downloads/bitpayApi.pdf<br />
c. Enter the complete URL for the form.php file in the bitpay-form-helper directory on your server.<br />
d. Enter the complete URL for the graphical button you would like to use on the membership checkout page.<br />
e. Click the Save button to save your settings.


# Usage

When a member chooses the Bitcoin payment method and places their order, they will be redirected to bitpay.com to pay.  Bitpay will send a notification to your server which this plugin handles.  Then the customer will be redirected to an order summary page.

The membership subscription is instantly activated once the user pays their invoice. If you wish to change this behavior to keep the subscription in a "processing" state until a completed IPN message is received, open the gateway.bitpay.php file in your favorite editor and scroll down to the handle_bitpay_return() function.  Inside this function, there is a switch/case block which contains the code that handles the IPN status messages and performs the necessary subscription management.  The first edit you'll need to make is to remove or comment out the 'paid' case statement.  Then scroll further down and uncomment the 'paid' case block that will keep the subscription in the desired processing state.  These are the only two changes required to activate 'processing' state handling.

In the event any payment is determined to be invalid, the subscription will be marked as denied for this member's record.

<em><strong>Note:</strong> This extension does not provide a means of automatically pulling a current BTC exchange rate for presenting BTC prices to shoppers. The invoice automatically displays the correctly converted bitcoin amount as determined by BitPay.</em>

# Support

## BitPay Support
* [Github Issues](https://github.com/bitpay/wpmembership-plugin/issues)
  * Open an Issue if you are having issues with this plugin
* [Support](https://support.bitpay.com/)
  * Checkout the BitPay support site

## WPMU Membership Support
* [Homepage](https://premium.wpmudev.org/project/membership/)
* [Documentation](https://premium.wpmudev.org/project/membership/)
* [Forums](http://premium.wpmudev.org/forums/)


# Troubleshooting

The official BitPay API documentation should always be your first reference for development, errors and troubleshooting:
https://bitpay.com/downloads/bitpayApi.pdf

Some web servers have outdated root CA certificates and will cause this curl error: "SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed'".  The fix is to contact your hosting provider or server administrator and request a root CA cert update.

The log file is named 'bplog.txt' and can be found in the same directory as the plugin files. Checking this log file will give you exact responses from the BitPay network, in case of failures.

Check the version of this plugin agains the official repository to ensure you are using the latest version. Your issue might have been addressed in a newer version of the plugin: https://github.com/bitpay/wpmembership-plugin

If all else fails, send an email describing your issue *in detail* to support@bitpay.com and attach the bplog.txt file.

# Contribute

To contribute to this project, please fork and submit a pull request.

# License

The MIT License (MIT)

Copyright (c) 2011-2015 BitPay

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
