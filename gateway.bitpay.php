<?php
/*
Addon Name: BitPay payment plugin
Author: Rich Morgan
Author URI: https://bitpay.com
Gateway ID: bitpay
*/

class bitpay extends M_Gateway {

	var $gateway = 'bitpay';
	var $title = 'BitPay - Bitcoin payments';
	var $postaddress = 'https://location_of_the_bp_post_file';
	var $bpOptions = array(
			                   'apiKey' => '',
			                   'verifyPos' => true,
			                   'notificationEmail' => '',
			                   'notificationURL' => '',
			                   'redirectURL' => '',
			                   'currency' => 'BTC',
			                   'physical' => false,
			                   'fullNotifications' => true,
			                   'transactionSpeed' => 'low',
			                   'useLogging' => true,
	                      );
	
	function bitpay() {
		parent::M_Gateway();

		add_action('M_gateways_settings_' . $this->gateway, array(&$this, 'mysettings'));

		// If I want to override the transactions output - then I can use this action
		//add_action('M_gateways_transactions_' . $this->gateway, array(&$this, 'mytransactions'));

		if($this->is_active()) {
			// Subscription form gateway
			add_action('membership_purchase_button', array(&$this, 'display_subscribe_button'), 1, 3);

			// Payment return
			add_action('membership_handle_payment_return_' . $this->gateway, array(&$this, 'handle_bitpay_return'));
		}

	}
	
	function mysettings() {
		if ( !is_ssl() ) {
			echo '<div id="message" class="updated fade"><p>' . __('The BitPay plugin requires an SSL certificate to be installed on this domain', 'membership') . '</p></div>';
		}

		global $M_options;

		?>
		<table class="form-table">
		<tbody>
		  <tr valign="top">
				<th scope="row"><?php _e('API key', 'membership') ?></th>
				<td><input type="text" name="api_key" value="<?php esc_attr_e(get_option( $this->gateway . "_api_key", "" )); ?>" /></td>
			</tr>
		  <tr valign="top">
		  <th scope="row"><?php _e('Transaction speed', 'membership') ?></th>
		  <td><select name="transact_speed">
		  <option value="low" <?php if (get_option( $this->gateway . "_transact_speed" ) == 'slow') echo 'selected="selected"'; ?>><?php _e('Low - 1 hour (safest)', 'membership') ?></option>
		  <option value="medium" <?php if (get_option( $this->gateway . "_transact_speed" ) == 'medium') echo 'selected="selected"'; ?>><?php _e('Medium - 10 mins', 'membership') ?></option>
		  <option value="fast" <?php if (get_option( $this->gateway . "_transact_speed" ) == 'fast') echo 'selected="selected"'; ?>><?php _e('Fast - instant (riskiest)', 'membership') ?></option>
		  </select>
		  <br />
		  </td>
		  </tr>
		  <tr valign="top">
		  <th scope="row"><?php _e('Subscription button', 'membership') ?></th>
		  <?php
		  	$button = get_option( $this->gateway . "_bitpay_button", 'https://button_URL_goes_here' );
		  ?>
		  <td><input type="text" name="bitpay_button" value="<?php esc_attr_e($button); ?>" style='width: 40em;' />
		  <br />
		  </td>
		  </tr>
		</tbody>
		</table>
		<?php
	}

	function build_custom($user_id, $sub_id, $amount) {

		$custom = '';

		//fake:user:sub:key

		$custom = time() . ':' . $user_id . ':' . $sub_id . ':';
		$key = md5('MEMBERSHIP' . $amount);

		$custom .= $key;

		return $custom;

	}

	function single_button($pricing, $subscription, $user_id) {

		global $M_options;

		if(empty($M_options['paymentcurrency'])) {
			$M_options['paymentcurrency'] = 'USD';
		}

		$form = '';
		
	  $form .= '<form action="'. $postaddress .'" method="post">';
		$form .= '<input type="hidden" name="itemCode" value="' . $subscription->sub_id() . '">';
		$form .= '<input type="hidden" name="itemDesc" value="' . $subscription->sub_name() . '">';
		$form .= '<input type="hidden" name="price" value="' . number_format($pricing[0]['amount'], 2) . '">';
		$form .= '<input type="hidden" name="currency" value="' . $M_options['paymentcurrency'] .'">';
		$form .= '<input type="hidden" name="posData" value="' . $this->build_custom($user_id, $subscription->id, number_format($pricing[0]['amount'], 2)) .'">';
		$form .= '<input type="hidden" name="notificationURL" value="' . trailingslashit(get_option('home')) . 'paymentreturn/' . esc_attr($this->gateway) . '">';

		$button = get_option( $this->gateway . "_bitpay_button", 'https://button_URL_goes_here' );

		$form .= '<input type="image" name="submit" border="0" src="' . $button . '" alt="Bitcoin payments via BitPay">';
		$form .= '</form>';

		return $form;

	}

	/*
	function single_sub_button($pricing, $subscription, $user_id, $norepeat = false) {

		global $M_options;

		if(empty($M_options['paymentcurrency'])) {
			$M_options['paymentcurrency'] = 'USD';
		}

		$form = '';

		if($pricing[0]['type'] == 'indefinite') $pricing[0]['days'] = 365;
		
		$form .= '<form action="'. $postaddress .'" method="post">';
		$form .= '<input type="hidden" name="itemCode" value="' . $subscription->sub_id() . '">';
		$form .= '<input type="hidden" name="itemDesc" value="' . $subscription->sub_name() . '">';
		$form .= '<input type="hidden" name="price" value="' . number_format($pricing[0]['amount'], 2) . '">';
		$form .= '<input type="hidden" name="currency" value="' . $M_options['paymentcurrency'] .'">';
		$form .= '<input type="hidden" name="posData" value="' . $this->build_custom($user_id, $subscription->id, number_format($pricing[0]['amount'], 2)) .'">';
		$form .= '<input type="hidden" name="notificationURL" value="' . trailingslashit(get_option('home')) . 'paymentreturn/' . esc_attr($this->gateway) . '">';

		$button = get_option( $this->gateway . "_bitpay_button", 'https://button_URL_goes_here' );

		$form .= '<input type="image" name="submit" border="0" src="' . $button . '" alt="Bitcoin payments via BitPay">';
		$form .= '</form>';

		return $form;

	}
  */
	
	/*
	function complex_sub_button($pricing, $subscription, $user_id) {

		global $M_options;

		if(empty($M_options['paymentcurrency'])) {
			$M_options['paymentcurrency'] = 'USD';
		}

		$form = '';

		$form .= '<form action="'. $postaddress .'" method="post">';
		$form .= '<input type="hidden" name="itemCode" value="' . $subscription->sub_id() . '">';
		$form .= '<input type="hidden" name="itemDesc" value="' . $subscription->sub_name() . '">';
		$form .= '<input type="hidden" name="price" value="' . number_format($pricing[0]['amount'], 2) . '">';
		$form .= '<input type="hidden" name="currency" value="' . $M_options['paymentcurrency'] .'">';
		$form .= '<input type="hidden" name="posData" value="' . $this->build_custom($user_id, $subscription->id, number_format($pricing[0]['amount'], 2)) .'">';
		$form .= '<input type="hidden" name="notificationURL" value="' . trailingslashit(get_option('home')) . 'paymentreturn/' . esc_attr($this->gateway) . '">';

		// complex bits here
		$count = 1;
		$ff = array();
		foreach((array) $pricing as $key => $price) {

			switch($price['type']) {

				case 'finite':	if(empty($price['amount'])) $price['amount'] = '0';
								if($count < 3) {
									$ff['a' . $count] = number_format($price['amount'], 2, '.' , '');
									$ff['p' . $count] = $price['period'];
									$ff['t' . $count] = strtoupper($price['unit']);
								} else {
									// Or last finite is going to be the end of the subscription payments
									$ff['a3'] = number_format($price['amount'], 2, '.' , '');
									$ff['p3'] = $price['period'];
									$ff['t3'] = strtoupper($price['unit']);
									$ff['src'] = '0';
								}
								$count++;
								break;

				case 'indefinite':
								if(empty($price['amount'])) $price['amount'] = '0';

								if($price['amount'] == '0') {
									// The indefinite rule is free, we need to move any previous
									// steps up to this one as we can't have a free a3
									if( isset($ff['a2']) && $ff['a2'] != '0.00' ) {
										// we have some other earlier rule so move it up
										$ff['a3'] = $ff['a2'];
										$ff['p3'] = $ff['p2'];
										$ff['t3'] = $ff['t2'];
										unset($ff['a2']);
										unset($ff['p2']);
										unset($ff['t2']);
										$ff['src'] = '0';
									} elseif( isset($ff['a1']) && $ff['a1'] != '0.00' ) {
										$ff['a3'] = $ff['a1'];
										$ff['p3'] = $ff['p1'];
										$ff['t3'] = $ff['t1'];
										unset($ff['a1']);
										unset($ff['p1']);
										unset($ff['t1']);
										$ff['src'] = '0';
									}
								} else {
									$ff['a3'] = number_format($price['amount'], 2, '.' , '');
									$ff['p3'] = 1;
									$ff['t3'] = 'Y';
									$ff['src'] = '0';
								}
								break;
				case 'serial':
								if(empty($price['amount'])) $price['amount'] = '0';

								if($price['amount'] == '0') {
									// The serial rule is free, we need to move any previous
									// steps up to this one as we can't have a free a3
									if( isset($ff['a2']) && $ff['a2'] != '0.00' ) {
										// we have some other earlier rule so move it up
										$ff['a3'] = $ff['a2'];
										$ff['p3'] = $ff['p2'];
										$ff['t3'] = $ff['t2'];
										unset($ff['a2']);
										unset($ff['p2']);
										unset($ff['t2']);
										$ff['src'] = '1';
									} elseif( isset($ff['a1']) && $ff['a1'] != '0.00' ) {
										$ff['a3'] = $ff['a1'];
										$ff['p3'] = $ff['p1'];
										$ff['t3'] = $ff['t1'];
										unset($ff['a1']);
										unset($ff['p1']);
										unset($ff['t1']);
										$ff['src'] = '1';
									}
								} else {
									$ff['a3'] = number_format($price['amount'], 2, '.' , '');
									$ff['p3'] = $price['period'];
									$ff['t3'] = strtoupper($price['unit']);
									$ff['src'] = '1';
								}

								break;
			}
		}

		if(!empty($ff)) {
			foreach($ff as $key => $value) {
				$form .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
			}
		}

		$form .= '<input type="hidden" name="custom" value="' . $this->build_custom($user_id, $subscription->id, $ff['a3']) .'">';

		// Remainder of the easy bits

		$form .= '<input type="hidden" name="return" value="' . get_option('home') . '">';
		$form .= '<input type="hidden" name="cancel_return" value="' . get_option('home') . '">';


		$form .= '<input type="hidden" name="lc" value="' . esc_attr(get_option( $this->gateway . "_bitpay_site" )) . '">';
		$form .= '<input type="hidden" name="notify_url" value="' . trailingslashit(get_option('home')) . 'paymentreturn/' . esc_attr($this->gateway) . '">';

		$button = get_option( $this->gateway . "_bitpay_button", 'https://button_URL_goes_here' );

		$form .= '<!-- Display the payment button. --> <input type="image" name="submit" border="0" src="' . $button . '" alt="Bitcoin payments via BitPay">';
		$form .= '</form>';

		return $form;

	}
  */
	
	
	function build_subscribe_button($subscription, $pricing, $user_id) {

		if(!empty($pricing)) {

			// check to make sure there is a price in the subscription
			// we don't want to display free ones for a payment system
			$free = true;
			foreach($pricing as $key => $price) {
				if(!empty($price['amount']) && $price['amount'] > 0 ) {
					$free = false;
				}
			}

			if(!$free) {
				if(count($pricing) == 1) {
					// A basic price or a single subscription
					if(in_array($pricing[0]['type'], array('indefinite','finite'))) {
						// one-off payment
						//return $this->single_sub_button($pricing, $subscription, $user_id, true);
						return $this->single_button($pricing, $subscription, $user_id);
					} else {
						// simple subscription
						//return $this->single_sub_button($pricing, $subscription, $user_id);
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

	function display_subscribe_button($subscription, $pricing, $user_id) {
		echo $this->build_subscribe_button($subscription, $pricing, $user_id);

	}

	function update() {
		if(isset($_POST['api_key'])) {
			update_option( $this->gateway . "_api_key", $_POST[ 'api_key' ] );
			update_option( $this->gateway . "_transact_speed", $_POST[ 'transact_speed' ] );
			update_option( $this->gateway . "_bitpay_button", $_POST[ 'bitpay_button' ] );
		}

		// default action is to return true
		return true;

	}

	function bpHash($data, $key) {	
		try {
			$hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
			return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
		} catch (Exception $e) {
			return 'Error: ' . $e->getMessage();
		}
	}
	
	// IPN handling
	function handle_bitpay_return() {

		try {
			$post = file_get_contents("php://input");
		
			if (!$post)
				return 'No post data';
		
			$response = json_decode($post, true);
		
			if (is_string($response))
				return $response; // error
		
			if (!array_key_exists('posData', $response))
				return 'No posData';
		
			$posData = json_decode($response['posData'], true);
		
			if($bpOptions['verifyPos'] and $posData['hash'] != bpHash(serialize($posData['posData']), $bpOptions['apiKey']))
				return 'Authentication failed (bad hash)';
		
			$response['posData'] = $posData['posData'];

		} catch (Exception $e) {
			if($bpOptions['useLogging'])
				bpLog('Error: ' . $e->getMessage());
			return array('error' => $e->getMessage());
		}

		
		if (isset($response['status'])) {

			switch ($response['status']) {

				case 'new':
					break;

				case 'complete':
				case 'confirmed':
					// payment has been confirmed or marked complete
					$amount = $response['price'];
					$currency = $response['currency'];
					$timestamp = $response['currentTime'];

					$this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], '');

					do_action('membership_payment_processed', $user_id, $sub_id, $amount, $currency, $response['id']);
					
					// create_subscription
					$member = new M_Membership($user_id);
					if($member) {
						$member->create_subscription($sub_id);
					}
					
					do_action('membership_payment_subscr_signup', $user_id, $sub_id);
					
					break;

				case 'invalid':
					// payment has been deemed invalid. bad transaction!
					$note = 'This payment has been marked as invalid. Do not process membership!';
					$amount = $response['price'];
					$currency = $response['currency'];
					$timestamp = $response['currentTime'];

					$this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);

					$member = new M_Membership($user_id);
					if($member) {
						$member->expire_subscription($sub_id);
						$member->deactivate();
					}

					do_action('membership_payment_denied', $user_id, $sub_id, $amount, $currency, $response['id']);
					break;

				case 'paid':
					// payment has been made but confirmation pending
					$pending_str = 'BitPay payment received. Awaiting confirmation.';
					$reason = 'paid';
					$note = $pending_str;
					$amount = $response['price'];
					$currency = $response['currency'];
					$timestamp = $response['currentTime'];

					$this->record_transaction($user_id, $sub_id, $amount, $currency, $timestamp, $response['id'], $response['status'], $note);

					do_action('membership_payment_pending', $user_id, $sub_id, $amount, $currency, $response['id']);
					break;

				default:
					// case: various error cases
			}

		} else {
			// Did not find expected POST variables. Possible access attempt from a non BitPay site.
			header('Status: 404 Not Found');
			echo 'Error: Missing POST variables. Identification is not possible.';
			exit;
		}
	}

}

?>
