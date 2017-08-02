<?php
class GP_PDF_Template {
	public static function getInvoiceContent ( $invoice_id ) {
		
		$invoice_number = get_post_meta( $invoice_id, '_gp_invoice_number', true);
		$customer_id = get_post_meta( $invoice_id, '_gp_invoice_customer', true);
		
		if ( $customer_id !== false ) {
			$customer_name = get_post_meta( $customer_id, '_gp_customer_name', true);
			$customer_surname = get_post_meta( $customer_id, '_gp_customer_surname', true);
			$customer_cif = get_post_meta( $customer_id, '_gp_customer_cif', true);
			$customer_tlf = get_post_meta( $customer_id, '_gp_customer_tlf', true);
			$customer_email = get_post_meta( $customer_id, '_gp_customer_email', true);
			$customer_address = get_post_meta( $customer_id, '_gp_customer_address', true);
		}
		$logo = get_option( "gp_settings_image", GESTIONPYMES_DEFAULT_LOGO );
		if ( $logo == "" ) {
			$logo = GESTIONPYMES_DEFAULT_LOGO;
		}
		$output = "";
		$output .= '<page backtop="5mm" backbottom="14mm" backleft="14mm" backright="10mm">
		
		<table border="0" align="left" cellspacing="0px" cellpadding="5px"
				width="650">
				<thead>
				<tr>
				<td width="290"></td>
				<td width="30"></td>
				<td width="330"></td>
				</tr>
				</thead>
				<tbody>
				<tr>
				<td colspan="3"><img src="' . $logo . '" /></td>
				</tr>
		
				<tr style="height: 20px !important;">
				<td colspan="3">&nbsp;</td>
				</tr>
		
				<tr>
				<td>' . get_option( "gp_settings_name", "Gestion-Pymes" ) . '</td>
					<td></td>
					<td width="330"
						style="border-bottom: 1px solid #ccc;"><b>' . __( "Customer:", 'gestion-pymes' ) . '</b>
					</td>
				</tr>
				<tr>
					<td><b>CIF:</b>' . get_option( "gp_settings_cif", "" ) . '</td>
					<td></td>
						<td width="330"
							style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"></td>
				</tr>
				<tr>
					<td><b>Tlf:</b>' . get_option( "gp_settings_tlf", "" ) . '</td>
					<td></td>
					<td width="330"
						style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "Name:", 'gestion-pymes' ) . '</b>
						' . $customer_name . " " . $customer_surname . '
					</td>
				</tr>
				<tr>
					<td>' . get_option( "gp_settings_email", "" ) . '</td>
					<td></td>
					<td width="330"
						style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "VAT Number:", 'gestion-pymes' ) . '</b>
						' . $customer_cif . '
					</td>
				</tr>
				<tr valign="top">
					<td><b>Dirección:</b>' . get_option( "gp_settings_address", "" ) . '</td>
					<td></td>
					<td width="330" style="border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "Address:", 'gestion-pymes' ) . '</b>
						' . nl2br( $customer_address ) . '
					</td>
				
				</tr>
				<tr>
					 <td><h3>' . __( "INVOICE Nº: ", 'gestion-pymes' ) . $invoice_number . '</h3></td>
					<td></td>
					<td>' . __( "Date:", 'gestion-pymes' ) . get_post_meta( $invoice_id, '_gp_invoice_date', true) . '</td>
				</tr>
				
			</tbody>
		</table>
		';
		
		$output .= '<table align="left" cellspacing="0px" cellpadding="5px"
			width="650">
			<thead>
				<tr align="center" style="background:#ccc;">
					<td width="405">' . __( "Description", 'gestion-pymes' ) . '</td>
					<td width="66">' . __( "QTY", 'gestion-pymes' ) . '</td>
					<td width="91">' . __( "Unit price", 'gestion-pymes' ) . '</td>
					<td width="91">' . __( "Line total", 'gestion-pymes' ) . '</td>
				</tr>
			</thead>
			<tbody>';

		$descs_str = get_post_meta($invoice_id, 'gp-invoice-desc', true);
		$descs = maybe_unserialize( $descs_str );
		$cants_str = get_post_meta($invoice_id, 'gp-invoice-cant', true);
		$cants = maybe_unserialize( $cants_str );
		$prices_str = get_post_meta($invoice_id, 'gp-invoice-price', true);
		$prices = maybe_unserialize( $prices_str );

		if ( is_array( $descs ) ) {
			foreach ( $descs as $key => $value ) {
				$cant_value = isset($cants[$key])?$cants[$key]:'';
				$price_value = isset($prices[$key])?$prices[$key]:'';
				
				$str_price = ( $cant_value !== '' ) ? ( $price_value . __( " €", 'gestion-pymes' ) ) : '';
				$str_subtotal = ( $cant_value !== '' ) ? ( number_format(($cant_value * $price_value), 2) . __( " €", 'gestion-pymes' ) ) : '';
				if ( ( $value !== '' ) || ( $cant_value !== '' ) || ( $str_price !== '' ) || ( $str_subtotal !== '' ) ) {
					$output .= '<tr>
									<td width="405" style="border-bottom:1px solid #ccc;">' . $value . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $cant_value . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $str_price . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $str_subtotal . '</td>
								</tr>';
				}
			}
		}

		$output .= '</tbody>
		</table>';


		$subtotal = GestionPymes::getInvoiceSubtotal( $invoice_id );
		$tax_value = GestionPymes::getInvoiceTax( $invoice_id );
		$tax = get_post_meta($invoice_id, '_gp_invoice_tax', true);
		$irpf_value = GestionPymes::getInvoiceIRPF( $invoice_id );
		$irpf = get_post_meta($invoice_id, '_gp_invoice_irpf', true);
		$total = GestionPymes::getInvoiceTotal( $invoice_id );
		
		$output .= '<!-- Totales -->
			<table border="0" align="left" cellspacing="0px" width="650" style="margin-top:20px;">
			<thead>
				<tr>
					<td width="450"></td>
					<td width="100"></td>
					<td width="100"></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td>Subtotal:</td>
					<td align="right">' . number_format(round($subtotal, 2), 2) . __( " €", 'gestion-pymes' ) . '</td>
				</tr>
				<tr>
					<td></td>
					<td>' . get_option( "gp_settings_vat_name", "IVA" ) . " (" . $tax . '%):</td>
					<td align="right">' . number_format(round($tax_value, 2), 2) . __( " €", 'gestion-pymes' ) . '</td>
				</tr>';
		
				if ($irpf>0) {
					$output .= '
						<tr>
							<td></td>
							<td>-' . __("IRPF", 'gestion-pymes' ) . " (" . $irpf . '%):</td>
							<td align="right">- ' . number_format(round($irpf_value, 2), 2) . __(" €", 'gestion-pymes' ) . '</td>
						</tr>';
				}
				$output .= '
				<tr>
					<td></td>
					<td><b>TOTAL:</b></td>
					<td align="right"><b>' . number_format(round($total, 2), 2) . __(" €", 'gestion-pymes' ) . '</b></td>
				</tr>
			</tbody>
		</table>';

		$output .= '</page>';

		return $output;
	}

	public static function getInvoiceCSS ( $invoice_id ) {
		$css = '.footer { font-size: 85%; }';
		$css = apply_filters( 'gp_invoice_css', $css, $invoice_id );
		return $css;
	}

	public static function getBudgetContent ( $budget_id ) {

		$customer_id = get_post_meta( $budget_id, '_gp_budget_customer', true);

		if ( $customer_id !== false ) {
			$customer_name = get_post_meta( $customer_id, '_gp_customer_name', true);
			$customer_surname = get_post_meta( $customer_id, '_gp_customer_surname', true);
			$customer_cif = get_post_meta( $customer_id, '_gp_customer_cif', true);
			$customer_tlf = get_post_meta( $customer_id, '_gp_customer_tlf', true);
			$customer_email = get_post_meta( $customer_id, '_gp_customer_email', true);
			$customer_address = get_post_meta( $customer_id, '_gp_customer_address', true);
		}
		$logo = get_option( "gp_settings_image", GESTIONPYMES_DEFAULT_LOGO );
		if ( $logo == "" ) {
			$logo = GESTIONPYMES_DEFAULT_LOGO;
		}
		$output = "";
		$output .= '<page backtop="5mm" backbottom="14mm" backleft="14mm" backright="10mm">
		
		<table border="0" align="left" cellspacing="0px" cellpadding="5px"
				width="650">
				<thead>
				<tr>
				<td width="290"></td>
				<td width="30"></td>
				<td width="330"></td>
				</tr>
				</thead>
				<tbody>
				<tr>
				<td colspan="3"><img src="' . $logo . '" /></td>
				</tr>

				<tr style="height: 20px !important;">
				<td colspan="3">&nbsp;</td>
				</tr>

				<tr>
				<td>' . get_option( "gp_settings_name", "Gestion-Pymes" ) . '</td>
					<td></td>
					<td width="330"
						style="border-bottom: 1px solid #ccc;"><b>' . __( "Customer:", 'gestion-pymes' ) . '</b>
					</td>
				</tr>
				<tr>
					<td><b>CIF:</b>' . get_option( "gp_settings_cif", "" ) . '</td>
					<td></td>
						<td width="330"
							style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"></td>
				</tr>
				<tr>
					<td><b>Tlf:</b>' . get_option( "gp_settings_tlf", "" ) . '</td>
					<td></td>
					<td width="330"
						style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "Name:", 'gestion-pymes' ) . '</b>
						' . $customer_name . " " . $customer_surname . '
					</td>
				</tr>
				<tr>
					<td>' . get_option( "gp_settings_email", "" ) . '</td>
					<td></td>
					<td width="330"
						style="border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "VAT Number:", 'gestion-pymes' ) . '</b>
						' . $customer_cif . '
					</td>
				</tr>
				<tr valign="top">
					<td><b>Dirección:</b>' . get_option( "gp_settings_address", "" ) . '</td>
					<td></td>
					<td width="330" style="border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; border-right: 1px solid #ccc;"><b>' . __( "Address:", 'gestion-pymes' ) . '</b>
						' . nl2br( $customer_address ) . '
					</td>
				
				</tr>
				<tr>
					 <td><h3>' . __( "BUDGET Nº: ", 'gestion-pymes' ) . $budget_id . '</h3></td>
					<td></td>
					<td>' . __( "Date:", 'gestion-pymes' ) . get_post_meta( $budget_id, '_gp_budget_date', true) . '</td>
				</tr>
				
			</tbody>
		</table>
		';
		
		$output .= '<table align="left" cellspacing="0px" cellpadding="5px"
			width="650">
			<thead>
				<tr align="center" style="background:#ccc;">
					<td width="405">' . __( "Description", 'gestion-pymes' ) . '</td>
					<td width="66">' . __( "QTY", 'gestion-pymes' ) . '</td>
					<td width="91">' . __( "Unit price", 'gestion-pymes' ) . '</td>
					<td width="91">' . __( "Line total", 'gestion-pymes' ) . '</td>
				</tr>
			</thead>
			<tbody>';

		$descs_str = get_post_meta($budget_id, 'gp-budget-desc', true);
		$descs = maybe_unserialize( $descs_str );
		$cants_str = get_post_meta($budget_id, 'gp-budget-cant', true);
		$cants = maybe_unserialize( $cants_str );
		$prices_str = get_post_meta($budget_id, 'gp-budget-price', true);
		$prices = maybe_unserialize( $prices_str );

		if ( is_array( $descs ) ) {
			foreach ( $descs as $key => $value ) {
				$cant_value = isset($cants[$key])?$cants[$key]:'';
				$price_value = isset($prices[$key])?$prices[$key]:'';
				
				$str_price = ( $cant_value !== '' ) ? ( $price_value . __( " €", 'gestion-pymes' ) ) : '';
				$str_subtotal = ( $cant_value !== '' ) ? ( number_format(($cant_value * $price_value), 2) . __( " €", 'gestion-pymes' ) ) : '';
				if ( ( $value !== '' ) || ( $cant_value !== '' ) || ( $str_price !== '' ) || ( $str_subtotal !== '' ) ) {
					$output .= '<tr>
									<td width="405" style="border-bottom:1px solid #ccc;">' . $value . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $cant_value . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $str_price . '</td>
									<td  style="border-bottom:1px solid #ccc;" align="right">' . $str_subtotal . '</td>
								</tr>';
				}
			}
		}

		$output .= '</tbody>
		</table>';

		$subtotal = GestionPymes::getBudgetSubtotal( $budget_id );
		$tax_value = GestionPymes::getBudgetTax( $budget_id );
		$tax = get_post_meta($budget_id, '_gp_budget_tax', true);
		$irpf_value = GestionPymes::getBudgetIRPF( $budget_id );
		$irpf = get_post_meta($budget_id, '_gp_budget_irpf', true);
		$total = GestionPymes::getBudgetTotal( $budget_id );

		$output .= '<!-- Totales -->
			<table border="0" align="left" cellspacing="0px" width="650" style="margin-top:20px;">
			<thead>
				<tr>
					<td width="450"></td>
					<td width="100"></td>
					<td width="100"></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td>Subtotal:</td>
					<td align="right">' . number_format(round($subtotal, 2), 2) . __( " €", 'gestion-pymes' ) . '</td>
				</tr>
				<tr>
					<td></td>
					<td>' . get_option( "gp_settings_vat_name", "IVA" ) . " (" . $tax . '%):</td>
					<td align="right">' . number_format(round($tax_value, 2), 2) . __( " €", 'gestion-pymes' ) . '</td>
				</tr>';
		
				if ($irpf>0) {
					$output .= '
						<tr>
							<td></td>
							<td>-' . __("IRPF", 'gestion-pymes' ) . " (" . $irpf . '%):</td>
							<td align="right">- ' . number_format(round($irpf_value, 2), 2) . __(" €", 'gestion-pymes' ) . '</td>
						</tr>';
				}
				$output .= '
				<tr>
					<td></td>
					<td><b>TOTAL:</b></td>
					<td align="right"><b>' . number_format(round($total, 2), 2) . __(" €", 'gestion-pymes' ) . '</b></td>
				</tr>
			</tbody>
		</table>';

		$output .= '</page>';

		return $output;
	}

	public static function getBudgetCSS ( $budget_id ) {
		$css = '.footer { font-size: 85%; }';
		$css = apply_filters( 'gp_budget_css', $css, $budget_id );
		return $css;
	}

	/**
	 * Get the invoices html footer for pdf
	 * @param int $invoice_id
	 * @return string $footer
	 */
	public static function getInvoiceFooterContent ( $invoice_id ) {
		$footer = '<div class="footer">' . nl2br( get_option( "gp_settings_invoice_footer", '' ) ) . '</div>';
		$footer = apply_filters( 'gp_invoice_footer', $footer, $invoice_id );
		return $footer;
	}

	/**
	 * Get the budgets html footer for pdf
	 * @param int $budget_id
	 * @return string $footer
	 */
	public static function getBudgetFooterContent ( $budget_id ) {
		$footer = '<div class="footer">' . nl2br( get_option( "gp_settings_budget_footer", '' ) ) . '</div>';
		$footer = apply_filters( 'gp_invoice_footer', $footer, $budget_id );
		return $footer;
	}

}
?>