<?php
/**
 * The model for Benefactor relationships between Charitable campaigns and products in 3rd party extensions (EDD, WooCommerce, etc).
 *
 * @package		Charitable/Classes/Charitable_Benefactor
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Benefactor' ) ) :

	/**
	 * Charitable_Benefactor
	 *
	 * @since 		1.0.0
	 */
	abstract class Charitable_Benefactor {

		/**
		 * Core benefactor record.
		 *
		 * @var 	Object
		 * @access  protected
		 */
		protected $benefactor;

		/**
		 * Create class object.
		 *
		 * @param 	mixed $benefactor Benefactor ID.
		 * @access 	public
		 * @since	1.0.0
		 */
		public function __construct( $benefactor ) {
			if ( ! is_object( $benefactor ) ) {
				$this->benefactor = charitable_get_table( 'benefactors' )->get( $benefactor );
			}
		}

		/**
		 * Return an object of type Charitable_Benefactor, given a benefactor record and an extension.
		 *
		 * @param 	Object $benefactor Benefactor object.
		 * @param 	string $extension  Extension generating the benefactor object.
		 * @return  Charitable_Benefactor
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_object( $benefactor, $extension ) {
			$class = apply_filters( 'charitable_benefactor_class_' . $extension, false );

			if ( ! class_exists( $class ) ) {
				charitable_get_deprecated()->doing_it_wrong(
					__METHOD__,
					__( 'Benefactor class does not exist for given extension.', 'charitable' ),
					'1.0.0'
				);
			}

			return new $class( $benefactor );
		}

		/**
		 * Display a short one-line summary of a benefactor (how much is contributed and from where).
		 *
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function __toString() {
			return apply_filters( 'charitable_benefactor_summary', $this->get_contribution_description(), $this );
		}

		/**
		 * Magic getter method.
		 *
		 * @param 	string $key Key of the field to get.
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function __get( $key ) {
			return isset( $this->benefactor->$key ) ? $this->benefactor->$key : null;
		}

		/**
		 * Return the details of the benefactor (i.e. the 3rd party extension).
		 *
		 * @return 	Object
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_benefactor() {
			return $this->benefactor;
		}

		/**
		 * Return a one-line description of the contribution.
		 *
		 * @return  string
		 * @access  public
		 * @abstract
		 * @since   1.0.0
		 */
		abstract public function get_contribution_description();

		/**
		 * Return the contribution as a nicely formatted amount.
		 *
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_contribution_amount() {
			if ( $this->benefactor->contribution_amount_is_percentage ) {
				$amount = apply_filters( 'charitable_benefactor_contribution_amount_percentage', $this->benefactor->contribution_amount . '%', $this->benefactor->contribution_amount, $this );
			} else {
				$amount = apply_filters( 'charitable_benefactor_contribution_amount_fixed', charitable_format_money( $this->benefactor->contribution_amount ), $this->benefactor->contribution_amount, $this );
			}

			return $amount;
		}

		/**
		 * Returns whether the benefit rule is active.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.2.0
		 */
		public function is_active() {
			return ! $this->is_expired;
		}

		/**
		 * Returns whether the benefit rule is active.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.4.6
		 */
		public function is_expired() {
			return '0000-00-00 00:00:00' != $this->benefactor->date_deactivated && strtotime( $this->benefactor->date_deactivated ) < time();
		}

		/**
		 * Return the benefit amount of a product based on the price, quantity and percent going to benefit.
		 *
		 * @param   float $price    Price of the item.
		 * @param   int   $quantity Number of items.
		 * @return  float
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function calculate_line_item_percent_contribution( $price, $quantity ) {
			return $price * $quantity * ( $this->benefactor->contribution_amount / 100 );
		}

		/**
		 * Return the benefit amount of a product based on the quantity and fixed amount going per item.
		 *
		 * @param   int $quantity The number of items.
		 * @return  float
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function calculate_line_item_fixed_contribution( $quantity = 1 ) {
			return $quantity * $this->benefactor->contribution_amount;
		}

		/**
		 * Returns whether the benefactor's benefit is applied once per cart.
		 *
		 * False means that more benefit is added for every applicable item in the cart.
		 *
		 * @return  boolean
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function benefit_is_per_cart() {
			return false === ( $this->benefactor->contribution_amount_is_per_item || $this->benefactor->contribution_amount_is_percentage );
		}
	}

endif;
