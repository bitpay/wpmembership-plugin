# Description

Bitcoin payment plugin for WPMU Membership using the bitpay.com service.

[![Build Status](https://travis-ci.org/bitpay/wpmembership-plugin.svg)](https://travis-ci.org/bitpay/wpmembership-plugin)


## Quick Start Guide

To get up and running with our plugin quickly, see the GUIDE here: https://github.com/bitpay/wpmembership-plugin/blob/master/GUIDE.md


## Support

**BitPay Support:**

* [Github Issues](https://github.com/bitpay/wpmembership-plugin/issues)
  * Open an Issue if you are having issues with this plugin
* [Support](https://support.bitpay.com/)
  * Checkout the BitPay support site

**WPMU Membership Support:**

* [Homepage](https://premium.wpmudev.org/project/membership/)
* [Documentation](https://premium.wpmudev.org/project/membership/)
* [Forums](http://premium.wpmudev.org/forums/)


## Troubleshooting

The official BitPay API documentation should always be your first reference for development, errors and troubleshooting:
https://bitpay.com/downloads/bitpayApi.pdf

Some web servers have outdated root CA certificates and will cause this curl error: "SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed'".  The fix is to contact your hosting provider or server administrator and request a root CA cert update.

The log file is named 'bplog.txt' and can be found in the same directory as the plugin files. Checking this log file will give you exact responses from the BitPay network, in case of failures.

Check the version of this plugin agains the official repository to ensure you are using the latest version. Your issue might have been addressed in a newer version of the plugin: https://github.com/bitpay/wpmembership-plugin

If all else fails, send an email describing your issue *in detail* to support@bitpay.com and attach the bplog.txt file.

## Contribute

To contribute to this project, please fork and submit a pull request.

## License

The MIT License (MIT)

Copyright (c) 2011-2015 BitPay

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
