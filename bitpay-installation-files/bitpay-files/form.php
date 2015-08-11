<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2011-2014 BitPay
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Written by Rich Morgan (rich@bitpay.com)
 */

 require_once('bp_lib.php');

 global $bpOptions;

 if(isset($_POST['rcla'])) {
   $post = array();
   $bpOptions['apiKey'] = base64_decode(trim($_POST['rcla']));
   $opts = array('orderID', 'itemDesc', 'itemCode', 'notificationEmail', 'notificationURL', 'redirectURL',
                 'currency', 'physical', 'fullNotifications', 'transactionSpeed', 'buyerName',
                 'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerEmail', 'buyerPhone');

    $opts = array_flip($opts);

    foreach($_POST as $key => $value) {
      if (array_key_exists($key, $opts))
        $post[$key] = $_POST[$key];
    }

   $invresp = bpCreateInvoice($_POST['orderId'],$_POST['price'],substr($_POST['posData'],0,99),$post, $_POST['network']);

   if(isset($invresp['url']))
     header('Location: ' . $invresp['url']);
   else
     bplog($invresp['error']['message']);
     echo('BitPay Transaction Error:<br />"' . $invresp['error']['message'] . '"<br />Please contact the site administrator');
     die();
 } else {
   die();
 }
