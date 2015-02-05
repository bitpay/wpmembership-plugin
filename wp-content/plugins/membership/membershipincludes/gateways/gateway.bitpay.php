<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2011-2015 BitPay
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
 * Addon Name: BitPay payment plugin
 * Author:     Rich Morgan <integrations@bitpay.com>
 * Author URI: https://bitpay.com
 * Gateway ID: bitpay
 */

// Membership_Gateway is the name of the Gateway class in the newer versions, so we create an alias
// in order to reference it as the older name for version difference purposes
if (true === class_exists('Membership_Gateway')) {
    class_alias('Membership_Gateway', 'M_Gateway');
}

class bitpay extends M_Gateway
{
    public $gateway   = 'bitpay';
    public $title     = 'BitPay - Bitcoin payments';

    public $bpOptions = array(
                           'apiKey'            => '',
                           'verifyPos'         => true,
                           'notificationEmail' => '',
                           'notificationURL'   => '',
                           'redirectURL'       => '',
                           'currency'          => 'USD',
                           'physical'          => false,
                           'fullNotifications' => true,
                           'transactionSpeed'  => 'low',
                           'useLogging'        => true,
                        );

    /**
     * Public constructor method to initialize class properties.
     */
    public function __construct()
    {
        // Update to work with latest 3.5.x Membership version
        // and keep backward compatibility with older versions as well
        if (false === class_exists('Membership_Gateway')) {
            return;
        } else {
            parent::__construct();

            add_action('M_gateways_settings_' . $this->gateway, array(&$this, 'mysettings'));

            // If I want to override the transactions output - then I can use this action
            // add_action('M_gateways_transactions_' . $this->gateway, array(&$this, 'mytransactions'));

            if ($this->is_active()) {
                // Subscription form gateway
                add_action('membership_purchase_button', array(&$this, 'display_subscribe_button'), 1, 3);

                // Payment return
                add_action('membership_handle_payment_return_' . $this->gateway, array(&$this, 'handle_bitpay_return'));
            }
        }
    }

    /**
     * Register plugin hooks for subscription button,
     * settings and post-payment returning user.
     */
    public function bitpay()
    {
        // Update to work with latest 3.5.x Membership version
        // and keep backward compatibility with older versions as well
        if (true === class_exists('Membership_Gateway')) {
            return;
        } else {
            parent::M_Gateway();

            add_action('M_gateways_settings_' . $this->gateway, array(&$this, 'mysettings'));

            if ($this->is_active()) {
                // Subscription form gateway
                add_action('membership_purchase_button', array(&$this, 'display_subscribe_button'), 1, 3);

                // Payment return
                add_action('membership_handle_payment_return_' . $this->gateway, array(&$this, 'handle_bitpay_return'));
            }
        }
    }

    /**
     * Generates the gateway settings displayed
     * in the WPMU Membership admin panel.
     */
    public function mysettings()
    {
        global $M_options;

        $html_output       = '';
        $transaction_speed = '';
        $api_key_option    = '';
        $bpformURL         = '';
        $button            = '';

        $transaction_speed = get_option($this->gateway . '_transact_speed', 'low');
        $api_key_option    = esc_attr_e(get_option($this->gateway . '_api_key', ''));
        $bpformURL         = get_option($this->gateway . '_bitpay_formurl', 'https://your_website_url/wp-content/plugins/bitpay-form-helper/form.php');
        $button            = get_option( $this->gateway . '_bitpay_button', 'https://button_URL_goes_here' );

        // BitPay requires the use of SSL
        if (!is_ssl()) {
            $html_output .= '<div id="message" class="updated fade"><p>' . __('The BitPay plugin requires an SSL certificate to be installed on this domain.', 'membership') . '</p></div>';
        }

        $html_output .= '<table class="form-table"><tbody><tr valign="top"><th scope="row">' .
                        _e('API key', 'membership') .
                        '</th><td><input type="text" name="api_key" value="' .
                        $api_key_option .
                        '" /></td></tr><tr valign="top"><th scope="row">' .
                        _e('Transaction speed', 'membership') .
                        '</th><td><select name="transact_speed"><option value="low" ';

        if ($transaction_speed == 'low') {
            $html_output .= 'selected="selected"';
        }

        $html_output .= '>' . _e('Low ~ 1 hour (safest)', 'membership') . '</option><option value="medium" ';

        if ($transaction_speed == 'medium') {
            $html_output .= 'selected="selected"';
        }

        $html_output .= '>' . _e('Medium ~ 10 mins', 'membership') . '</option><option value="high" ';

        if ($transaction_speed == 'high') {
            $html_output .= 'selected="selected"';
        }

        $html_output .= '>' .
                        _e('High ~ instant (riskiest)', 'membership') .
                        '</option></select><br /></td></tr><tr valign="top"><th scope="row">' .
                        _e('Form Helper POST URL', 'membership') .
                        '</th><td><input type="text" name="bitpay_formurl" value="' .
                        esc_attr_e($bpformURL) .
                        ' style="width: 40em;" /><br /></td></tr><tr valign="top"><th scope="row">' .
                        _e('Subscription button', 'membership') .
                        '</th><td><input type="text" name="bitpay_button" value="' .
                        esc_attr_e($button) .
                        ' style="width: 40em;" /><br /></td></tr></tbody></table>';

        echo $html_output;
    }

    /**
     * Creates the posData information in this function.
     *
     * @param  string $user_id
     * @param  string $sub_id
     * @param  string $amount
     * @return mixed $custom
     */
    public function build_custom($user_id, $sub_id, $amount)
    {
        $custom = '';
        $key    = '';

        // The format is: 
        // timestamp : user_id : sub_id : md5('MEMBERSHIP' + the amount)
        $custom = time() . ':' . $user_id . ':' . $sub_id . ':';
        $key    = md5('MEMBERSHIP' . $amount);

        $custom .= $key;

        return $custom;
    }

    /**
     * This is the form that is used on the subscription checkout page.
     * When the user clicks the BitPay button, it calls this form which, in turn,
     * calls the BitPay form helper in the wp-plugins directory.
     *
     * @param string $pricing
     * @param string $subscription
     * @param string $user_id
     */
    public function single_button($pricing, $subscription, $user_id)
    {
        global $M_options;

        $form        = '';
        $final_price = 0;

        $bpformURL = get_option($this->gateway . '_bitpay_formurl', 'https://your_website_url/wp-content/plugins/bitpay-form-helper/form.php');
        $button    = get_option( $this->gateway . '_bitpay_button', 'https://button_URL_goes_here' );

        if (true === class_exists('Membership_Gateway')) {
            // It is possible there is free trial set before the actual subscription takes place,
            // so we're going to find the first price that's > 0 and use that as our price.
            if ($pricing[0]['amount'] < 1) {
                foreach($pricing as $price_obj) {
                    if ($price_obj['amount'] >= 1) {
                        $final_price = $price_obj['amount'];
                        break;
                    }
                }
            } else {
                $final_price = $pricing[0]['amount'];
            }
        }

        // Using a default of 'USD' if no currecy has been configured.
        if (true === empty($M_options['paymentcurrency'])) {
            $M_options['paymentcurrency'] = 'USD';
        }
    
        $form .= '<form id="bitpay-form"  action="'. $bpformURL .'" method="post">' .
                 '<input type="hidden" name="orderID" value="' . $subscription->id . '">' .
                 '<input type="hidden" name="itemCode" value="' . $subscription->sub_id() . '">' .
                 '<input type="hidden" name="itemDesc" value="' . $subscription->sub_name() . '">' .
                 '<input type="hidden" name="price" value="' . number_format($pricing[0]['amount'], 2) . '">' .
                 '<input type="hidden" name="transactionSpeed" value="' . get_option($this->gateway . '_transact_speed') . '">' .
                 '<input type="hidden" name="currency" value="' . $M_options['paymentcurrency'] . '">' .
                 '<input type="hidden" name="rcla" value="' . base64_encode(get_option($this->gateway . '_api_key')) . '">' .
                 '<input type="hidden" name="posData" value="' . $this->build_custom($user_id, $subscription->id, number_format($pricing[0]['amount'], 2)) .'">' .
                 '<input type="hidden" name="notificationURL" value="' . trailingslashit(get_option('home')) . 'paymentreturn/' . esc_attr($this->gateway) . '">' .
                 '<input type="hidden" name="redirectURL" value="' . apply_filters('membership_return_url_' . $this->gateway, M_get_returnurl_permalink()) . '">' .
                 '<input type="image" name="submit" border="0" src="' . $button . '" alt="Bitcoin payments via BitPay">' .
                 '</form>';

        return $form;
    }

    /**
     * This function is not used at this time
     * but is reserved for future functionality.
     *
     * @param string $pricing
     * @param string $subscription
     * @param string $user_id
     * @param bool   $norepeat
     */
    public function single_sub_button($pricing, $subscription, $user_id, $norepeat = false)
    {
        global $M_options;
    }

    /**
     * This function is not used at this time
     * but is reserved for future functionality.
     *
     * @param string $pricing
     * @param string $subscription
     * @param string $user_id
     */
    function complex_sub_button($pricing, $subscription, $user_id)
    {
        global $M_options;
    }

    /**
     * Create the subscription button.
     *
     * @param  string $pricing
     * @param  string $subscription
     * @param  string $user_id
     * @return mixed
     */
    public function build_subscribe_button($subscription, $pricing, $user_id)
    {
        if (false === empty($pricing)) {
            // check to make sure there is a price in the subscription
            // we don't want to display free ones for a payment system
            $free = true;

            foreach ($pricing as $key => $price) {
                if (false === empty($price['amount']) && $price['amount'] > 0 ) {
                    $free = false;
                }
            }

            if ($free == false) {
                if (count($pricing) == 1) {
                    // A basic price or a single subscription
                    if (true === in_array($pricing[0]['type'], array('indefinite','finite'))) {
                        // one-off payment
                        // return $this->single_sub_button($pricing, $subscription, $user_id, true);
                        return $this->single_button($pricing, $subscription, $user_id);
                    } else {
                        // simple subscription
                        // return $this->single_sub_button($pricing, $subscription, $user_id);
                        return $this->single_button($pricing, $subscription, $user_id);
                    }
                } else {
                    // something much more complex
                    //return $this->complex_sub_button($pricing, $subscription, $user_id);
                    return $this->single_button($pricing, $subscription, $user_id);
                }
            }
        }
    }

    /**
     * Echo the subscription button to the page.
     *
     * @param  string $pricing
     * @param  string $subscription
     * @param  string $user_id
     */
    public function display_subscribe_button($subscription, $pricing, $user_id)
    {
        echo $this->build_subscribe_button($subscription, $pricing, $user_id);
    }

    /**
     * Save changes to the plugin parameters.
     *
     * @return bool
     */
    public function update()
    {
        if (true === isset($_POST['api_key'])) {
            update_option($this->gateway . '_api_key', $_POST[ 'api_key' ]);
            update_option($this->gateway . '_transact_speed', $_POST['transact_speed']);
            update_option($this->gateway . '_bitpay_button', $_POST['bitpay_button']);
            update_option($this->gateway . '_bitpay_formurl', $_POST['bitpay_formurl']);
        }

        // default action is to return true
        return true;
    }

    /**
     * Hash and encode the posData.
     *
     * @param  string $data
     * @param  string $key
     * @return string
     */
    public function bpHash($data, $key)
    {    
        try {
            $hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
            return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * IPN handling here
     *
     * @return string
     */
    public function handle_bitpay_return()
    {
        try {
            $post = file_get_contents("php://input");

            if (!$post) {
                return 'No post data';
            }

            $response = json_decode($post, true);

            if (true === is_string($response)) {
                // error
                return $response;
            }

            if (false === array_key_exists('posData', $response)) {
                return 'No posData';
            }

            $posData = json_decode($response['posData'], true);
    
            if ($bpOptions['verifyPos'] && $posData['hash'] != bpHash(serialize($posData['posData']), $bpOptions['apiKey'])) {
                return 'Authentication failed (bad hash)';
            }

            $response['posData'] = $posData['posData'];

        } catch (Exception $e) {
            if ($bpOptions['useLogging']) {
                bpLog('Error: ' . $e->getMessage());
            }

            return array('error' => $e->getMessage());
        }
    
        if (isset($response['status'])) {
            switch ($response['status']) {
                case 'new':
                    // invoice just created, skip
                    break;

                case 'paid':
                case 'complete':
                case 'confirmed':
                    // payment has been paid, confirmed or marked complete
                    $note     = 'Payment confirmed! BitPay Invoice ID: ' . $response['id'];
                    $amount   = $response['price'];
                    $currency = $response['currency'];

                    list($timestamp, $user_id, $sub_id, $key) = explode(':', $response['posData']);

                    // Update to work with latest 3.5.x Membership version
                    // and keep backward compatibility with older versions as well
                    if (false === class_exists('Membership_Gateway')) {
                        $isDuplicate = $this->duplicate_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                    } else {
                        $isDuplicate = $this->_check_duplicate_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                    }

                    if (!$isDuplicate) {
                        // Update to work with latest 3.5.x Membership version
                        // and keep backward compatibility with older versions as well
                        if (false === class_exists('Membership_Gateway')) {
                            $this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                        } else {
                            $this->_record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                        }

                        do_action('membership_payment_processed', $user_id, $sub_id, $amount, $currency, $response['id']);

                        // create_subscription
                        $member = new M_Membership($user_id);

                        if (true === isset($member)) {
                            $member->create_subscription($sub_id, $this->gateway);
                        }

                        do_action('membership_payment_subscr_signup', $user_id, $sub_id);
                    }
                    break;

                case 'invalid':
                    // payment has been deemed invalid. bad transaction!
                    $note     = 'This payment has been marked as invalid. Do not process membership! BitPay Invoice ID: ' . $response['id'];
                    $amount   = $response['price'];
                    $currency = $response['currency'];

                    list($timestamp, $user_id, $sub_id, $key) = explode(':', $response['posData']);

                    // Update to work with latest 3.5.x Membership version
                    // and keep backward compatibility with older versions as well
                    if (false === class_exists('Membership_Gateway')) {
                        $this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                    } else {
                        $this->_record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                    }

                    $member = new M_Membership($user_id);

                    if (true === isset($member)) {
                        $member->expire_subscription($sub_id);
                        $member->deactivate();
                    }

                    do_action('membership_payment_denied', $user_id, $sub_id, $amount, $currency, $response['id']);
                    break;

                /**
                 * Since we want instant membership activation, the paid status is combined with the confirmed
                 * and completed statuses above. In the future if you want to change that, remove the paid: switch
                 * above and uncomment this code:
                 *
                 * case 'paid':
                 *   // payment has been made but confirmation pending
                 *   $pending_str = 'BitPay payment received. Awaiting confirmation. BitPay Invoice ID: ' . $response['id'];
                 *   $reason      = 'paid';
                 *   $note        = $pending_str;
                 *   $amount      = $response['price'];
                 *   $currency    = $response['currency'];
                 *   $timestamp   = $response['currentTime'];
                 *
                 *    // Update to work with latest 3.5.x Membership version
                 *    // and keep backward compatibility with older versions as well
                 *   if (false === class_exists('Membership_Gateway')) {
                 *       $this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                 *   } else {
                 *       $this->_record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);
                 *   }
                 *
                 *   do_action('membership_payment_pending', $user_id, $sub_id, $amount, $currency, $response['id']);
                 *   break;
                 */

                default:
                    // case: various error cases
                    break;
            }

        } else {
            // Did not find expected POST variables. Possible access attempt from a non BitPay site.
            error_log('[ERROR] In wp-content/plugins/membership/membershipincludes/gateways/gateway.bitpay.php::handle_bitpay_return(): Missing POST variables. Identification is not possible.';
            header('Status: 404 Not Found');
            exit;
        }
    }
}

// Update to work with latest 3.5.x Membership version
// and keep backward compatibility with older versions as well
if (false === class_exists('Membership_Gateway')) {
    M_register_gateway('bitpay', 'bitpay');
} else {
    Membership_Gateway::register_gateway('bitpay', 'bitpay');
}

?>
