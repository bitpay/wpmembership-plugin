<?php

/**
 *  Form helper for BitPay WPMU Membership payment gateway.
 *  Copyright (c) 2014, BitPay, Inc
 *  Written by Rich Morgan (rich@bitpay.com)
 **/
 
 require_once('bp_lib.php');
 
 global $bpOptions;
 
 if(isset($_POST['rcla'])) {
   $post = array();
   $bpOptions['apiKey'] = base64_decode(trim($_POST['rcla']));
   $opts = array('orderID', 'itemDesc', 'itemCode', 'notificationEmail', 'notificationURL', 'redirectURL', 
                 'posData', 'price', 'currency', 'physical', 'fullNotifications', 'transactionSpeed', 'buyerName', 
                 'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerEmail', 'buyerPhone');

    $opts = array_flip($opts);

    foreach($_POST as $key => $value) {
      if (array_key_exists($key, $opts))
        $post[$key] = $_POST[$key];
    }  
    
   $invresp = bpCreateInvoice($_POST['orderId'],$_POST['price'],$_POST['posData'],$post);
   
   if(isset($invresp['url']))
     header('Location: ' . $invresp['url']);
   else
     header('Location: ' . $_POST['redirectURL'] . '?error='. htmlentities($invresp['error']['message']));
 } else {
   die();
 }
