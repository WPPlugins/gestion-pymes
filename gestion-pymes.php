<?php
/**
 * gestion-pymes.php
 *
 * Copyright (c) 2011,2012 Antonio Blanco http://www.eggemplo.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco
 * @package gestion-pymes
 * @since gestion-pymes 1.0.0
 *
 * Plugin Name: Gestion-Pymes
 * Plugin URI: http://www.eggemplo.com
 * Description: SME management
 * Version: 1.3.3
 * Author: gestion-pymes
 * Author URI: http://www.gestion-pymes.com
 * Text Domain: gestion-pymes
 * Domain Path: /languages
 * License: GPLv3
 */
if (! defined ( 'GESTIONPYMES_CORE_DIR' )) {
	define ( 'GESTIONPYMES_CORE_DIR', WP_PLUGIN_DIR . '/gestion-pymes' );
}
define ( 'GESTIONPYMES_FILE', __FILE__ );

define ( 'GESTIONPYMES_PLUGIN_URL', plugin_dir_url ( GESTIONPYMES_FILE ) );

define ( 'GESTIONPYMES_DEFAULT_LOGO', GESTIONPYMES_PLUGIN_URL . "/images/default_logo.jpg" );

if (! defined ( 'GESTIONPYMES_CURRENCY' )) {
	define ( 'GESTIONPYMES_CURRENCY', '€' );
}
class GestionPymes_Plugin {
	private static $notices = array ();
	public static function init() {
		add_action ( 'init', array (
				__CLASS__,
				'wp_init' 
		) );
		add_action ( 'admin_notices', array (
				__CLASS__,
				'admin_notices' 
		) );

		add_action('admin_init', array ( __CLASS__, 'admin_init' ) );

	}
	public static function wp_init() {
		load_plugin_textdomain ( 'gestion-pymes', null, 'gestion-pymes/languages' );

		add_action ( 'admin_menu', array (
				__CLASS__,
				'admin_menu' 
		), 40 );

		// extensions
		require_once 'core/class-gestion-pymes.php';
		require_once 'core/class-post-types.php';
		require_once 'core/class-taxonomies.php';
		require_once 'core/class-gp-pdf-template.php';

		// styles & javascript
		add_action ( 'admin_enqueue_scripts', array (
				__CLASS__,
				'admin_enqueue_scripts' 
		) );
	}

	public static function admin_init() {

		add_filter( 'post_row_actions', array ( 'GestionPymesPostTypes' , 'post_row_actions'), 10, 2 );

		// Print actions
		add_action('admin_action_gp_print_invoice', array ( 'GestionPymesPostTypes' , 'admin_action_gp_print_invoice' ) );
		add_action('admin_action_gp_print_budget', array ( 'GestionPymesPostTypes' , 'admin_action_gp_print_budget' ) );

	}

	public static function admin_enqueue_scripts($page) {
		// css
		wp_register_style ( 'gp-admin-style', GESTIONPYMES_PLUGIN_URL . '/css/admin-style.css', array (), '1.2' );
		wp_register_style ( 'ui-datepicker', GESTIONPYMES_PLUGIN_URL . '/css/jquery-ui-1.8.16.custom.css', array (), '1.2' );
		wp_enqueue_style ( 'ui-datepicker' );
		wp_enqueue_style ( 'gp-admin-style' );

		// javascript
		wp_register_script ( 'gp-admin-script', GESTIONPYMES_PLUGIN_URL . '/js/admin-scripts.js', array (
				'jquery' 
		), '1.2', true );
		// load datepicker scripts for all
		wp_enqueue_script ( 'datepicker', GESTIONPYMES_PLUGIN_URL . 'js/jquery.ui.datepicker.min.js', array (
				'jquery',
				'jquery-ui-core' 
		) );
		wp_enqueue_script ( 'datepickers', GESTIONPYMES_PLUGIN_URL . 'js/datepickers.js', array (
				'jquery',
				'jquery-ui-core',
				'datepicker' 
		) );

		wp_enqueue_script ( 'media-upload' );
		wp_enqueue_script ( 'thickbox' );
		wp_enqueue_style ( 'thickbox' );

		wp_enqueue_script ( 'gp-admin-script' );
	}
	public static function admin_notices() {
		if (! empty ( self::$notices )) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_menu_page ( __ ( 'Gestion-Pymes' ), __ ( 'Gestion-Pymes' ), 'manage_options', 'gestionpymes', array (
				__CLASS__,
				'gestionpymes_menu_settings' 
		), GESTIONPYMES_PLUGIN_URL . '/images/settings.png' );
	}

	public static function gestionpymes_menu_settings() {

		// if submit
		if ((isset ( $_POST ['_gp_settings_name'] )) && (wp_verify_nonce ( $_POST ["gp-settings"], "gp-settings" ))) {

			// image
			add_option ( "gp_settings_image", sanitize_text_field ( $_POST ["_gp_settings_image"] ) );
			update_option ( "gp_settings_image", sanitize_text_field ( $_POST ["_gp_settings_image"] ) );
			// name
			add_option ( "gp_settings_name", sanitize_text_field ( $_POST ["_gp_settings_name"] ) );
			update_option ( "gp_settings_name", sanitize_text_field ( $_POST ["_gp_settings_name"] ) );
			// VAT number
			add_option ( "gp_settings_cif", sanitize_text_field ( $_POST ["_gp_settings_cif"] ) );
			update_option ( "gp_settings_cif", sanitize_text_field ( $_POST ["_gp_settings_cif"] ) );
			// email
			add_option ( "gp_settings_email", sanitize_text_field ( $_POST ["_gp_settings_email"] ) );
			update_option ( "gp_settings_email", sanitize_text_field ( $_POST ["_gp_settings_email"] ) );
			// tlf
			add_option ( "gp_settings_tlf", sanitize_text_field ( $_POST ["_gp_settings_tlf"] ) );
			update_option ( "gp_settings_tlf", sanitize_text_field ( $_POST ["_gp_settings_tlf"] ) );
			// name
			add_option ( "gp_settings_address", sanitize_text_field ( $_POST ["_gp_settings_address"] ) );
			update_option ( "gp_settings_address", sanitize_text_field ( $_POST ["_gp_settings_address"] ) );

			// VAT name
			add_option ( "gp_settings_vat_name", sanitize_text_field ( $_POST ["_gp_settings_vat_name"] ) );
			update_option ( "gp_settings_vat_name", sanitize_text_field ( $_POST ["_gp_settings_vat_name"] ) );
			// vat 1
			add_option ( "gp_settings_vat_1", sanitize_text_field ( $_POST ["_gp_settings_vat_1"] ) );
			update_option ( "gp_settings_vat_1", sanitize_text_field ( $_POST ["_gp_settings_vat_1"] ) );
			// vat 2
			add_option ( "gp_settings_vat_2", sanitize_text_field ( $_POST ["_gp_settings_vat_2"] ) );
			update_option ( "gp_settings_vat_2", sanitize_text_field ( $_POST ["_gp_settings_vat_2"] ) );
			// vat 3
			add_option ( "gp_settings_vat_3", sanitize_text_field ( $_POST ["_gp_settings_vat_3"] ) );
			update_option ( "gp_settings_vat_3", sanitize_text_field ( $_POST ["_gp_settings_vat_3"] ) );

			// Invoices footer
			update_option ( "gp_settings_invoice_footer", esc_textarea ( $_POST ["_gp_settings_invoice_footer"] ) );

			// Budgets footer
			update_option ( "gp_settings_budget_footer", esc_textarea ( $_POST ["_gp_settings_budget_footer"] ) );
		}
		?>
<h2><?php echo __( 'Gestion Pymes', 'gestion-pymes' ); ?></h2>

<form method="post" action="">
	<div class="gp-container">
		<h3><?php echo __( "Company", 'gestion-pymes' );?></h3>
			<?php
		if (get_option ( "gp_settings_image", "" ) !== "") {
			echo '<div><img class="gp-settings-logo" src="' . get_option ( "gp_settings_image" ) . '" alt="Logo" /></div>';
		}
		?>
			<p>
			<label><?php echo __( "Image", 'gestion-pymes' );?></label><input
				id="upload_image" type="text" size="36" name="_gp_settings_image"
				value="<?php echo get_option( "gp_settings_image" ); ?>" /> <input
				id="upload_image_button" type="button"
				value="<?php echo __( "Upload Image", 'gestion-pymes' );?>"
				class="button" />
		</p>
		<p>
			<label><?php echo __( "Name", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_name"
				value="<?php echo get_option( "gp_settings_name" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "VAT number", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_cif"
				value="<?php echo get_option( "gp_settings_cif" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "Email", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_email"
				value="<?php echo get_option( "gp_settings_email" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "Phone", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_tlf"
				value="<?php echo get_option( "gp_settings_tlf" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "Address", 'gestion-pymes' );?></label>
			<textarea name="_gp_settings_address"><?php echo get_option( "gp_settings_address" ); ?></textarea>
		</p>
	</div>

	<hr>

	<div class="gp-container">
		<h3><?php echo __( "VAT", 'gestion-pymes' );?></h3>
		<p>
			<label><?php echo __( "VAT name", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_vat_name"
				value="<?php echo get_option( "gp_settings_vat_name" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "VAT 1 (%)", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_vat_1"
				value="<?php echo get_option( "gp_settings_vat_1" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "VAT 2 (%)", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_vat_2"
				value="<?php echo get_option( "gp_settings_vat_2" ); ?>" />
		</p>
		<p>
			<label><?php echo __( "VAT 3 (%)", 'gestion-pymes' );?></label> <input
				type="text" name="_gp_settings_vat_3"
				value="<?php echo get_option( "gp_settings_vat_3" ); ?>" />
		</p>
	</div>

	<hr>

	<div class="gp-container">
		<h3><?php echo __( "PDF templates", 'gestion-pymes' );?></h3>
		<p>
			<label><?php echo __( "Invoice footer", 'gestion-pymes' );?></label>
		</p>
		<textarea name="_gp_settings_invoice_footer" class="widefat"><?php echo get_option( "gp_settings_invoice_footer" ); ?></textarea>
		<p>
			<label><?php echo __( "Budget footer", 'gestion-pymes' );?></label>
		</p>
		<textarea name="_gp_settings_budget_footer" class="widefat"><?php echo get_option( "gp_settings_budget_footer" ); ?></textarea>
	</div>

	<hr>

	<div class="gp-container">
		<?php
		wp_nonce_field ( 'gp-settings', 'gp-settings' )?>
			<input type="submit"
			value="<?php echo __( "Save", 'gestion-pymes' );?>"
			class="button button-primary button-large" />
	</div>
</form>
<?php 
	}

}
GestionPymes_Plugin::init();
