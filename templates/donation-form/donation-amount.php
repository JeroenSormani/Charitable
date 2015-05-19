<?php
/**
 * The template used to display the donation amount inputs.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

$campaign 			= $view_args[ 'campaign' ];
$suggested_donations = $campaign->get_suggested_donations();
$currency_helper 	= charitable()->get_currency_helper();

/**
 * @hook 	charitable_donation_form_before_donation_amount
 */
do_action( 'charitable_donation_form_before_donation_amount' ); ?>

<h3 class="charitable-form-header"><?php _e( 'Enter Donation Amount', 'charitable' ) ?></h3>

<?php 
if ( count( $suggested_donations ) ) : 
?>
<ul class="donation-amounts">
	<?php 	
	foreach ( $suggested_donations as $suggestion ) : 
		?>
		<li class="donation-amount suggested-donation-amount">
			<input type="radio" name="donation-amount" value="<?php echo $suggestion[ 'amount' ] ?>" /><?php 
			printf( '%s %s', 
				$currency_helper->get_monetary_amount( $suggestion[ 'amount' ] ), 
				strlen( $suggestion[ 'description' ] ) ? '- ' . $suggestion[ 'description' ] : ''
			) ?>
		</li>
		<?php 
	endforeach;
	?>
	<li class="donation-amount custom-donation-amount" data-charitable-toggle="custom-donation-amount-field">
		<input type="radio" name="donation-amount" value="custom" /><?php 
			_e( 'Enter custom amount', 'charitable' );
		?>
	</li>
</ul>

<?php endif ?>

<div id="custom-donation-amount-field" class="charitable-form-field">
	<label for="custom-donation-amount"><?php _e( 'Enter donation amount', 'charitable' ) ?></label>
	<input type="text" name="custom-donation-amount" placeholder="" />
</div>

<?php 
/**
 * @hook 	charitable_donation_form_after_donation_amount
 */
do_action( 'charitable_donation_form_after_donation_amount' ); ?>