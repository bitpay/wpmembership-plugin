# Using the BitPay plugin for WPMU Membership

## Prerequisites
You must have a BitPay merchant account to use this plugin.  It's free to [sign-up for a BitPay merchant account](https://bitpay.com/start).


## Installation

**Note:** To use this plugin, your server must have the PHP cURL module installed, Apache mod_rewrite enabled and 'AllowOverrides' set to 'All' in your Apache configuration file (if you're using Apache) for your website files directory.  If you are unsure how to check or configure these items, contact your server administrator or webhosting provider support for assistance.

After you have downloaded the latest plugin zip/tarball file from file from https://github.com/bitpay/wpmembership-plugin/releases/latest unzip this archive and copy the folders including their contents into your Wordpress plugins directory.

There are two folders inside this plugin zip file which contain the one WPMU Membership payment gateway file and three form helper files.  The structure is as follows:

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
  * Enter the complete URL for the form.php file in the bitpay-form-helper directory on your server.
  * Enter the complete URL for the graphical button you would like to use on the membership checkout page.
  * Click the Save button to save your settings.


## Usage

When a member chooses the Bitcoin payment method and places their order, they will be redirected to bitpay.com to pay.  Bitpay will send a notification to your server which this plugin handles.  Then the customer will be redirected to an order summary page.

The membership subscription is instantly activated once the user pays their invoice. If you wish to change this behavior to keep the subscription in a "processing" state until a completed IPN message is received, open the gateway.bitpay.php file in your favorite editor and scroll down to the handle_bitpay_return() function.  Inside this function, there is a switch/case block which contains the code that handles the IPN status messages and performs the necessary subscription management.  The first edit you'll need to make is to remove or comment out the 'paid' case statement.  Then scroll further down and uncomment the 'paid' case block that will keep the subscription in the desired processing state.  These are the only two changes required to activate 'processing' state handling.

In the event any payment is determined to be invalid, the subscription will be marked as denied for this member's record.

**Note:** This extension does not provide a means of automatically pulling a current BTC exchange rate for presenting BTC prices to shoppers. The invoice automatically displays the correctly converted bitcoin amount as determined by BitPay.

