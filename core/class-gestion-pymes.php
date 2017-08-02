<?php
class GestionPymes {
	
	/* CUSTOMERS */
	
	public static function getCustomerInvoices ( $customer_id ) {
		/* Query args. */
		$args = array(
				'post_type' => 'gp_invoice',
				'posts_per_page' => -1,
				'meta_key' => '_gp_invoice_customer',
				'meta_value' => $customer_id
		);
		
		$invoices = get_posts( $args );
		return $invoices;
	}
	
	public static function getCustomerBudgets ( $customer_id ) {
		/* Query args. */
		$args = array(
				'post_type' => 'gp_budget',
				'posts_per_page' => -1,
				'meta_key' => '_gp_budget_customer',
				'meta_value' => $customer_id
		);
		
		$budgets = get_posts( $args );
		return $budgets;
	}
	
	/* INVOICES */
	public static function getInvoiceSubtotal ( $invoice_id ) {
		
		$cants_str = get_post_meta($invoice_id, 'gp-invoice-cant', true);
		$cants = maybe_unserialize( $cants_str );
		$prices_str = get_post_meta($invoice_id, 'gp-invoice-price', true);
		$prices = maybe_unserialize( $prices_str );
		
		$result = 0;
		if ( is_array( $cants ) ) {
			foreach ( $cants as $key => $cant_value ) {
				$price_value = isset($prices[$key])?$prices[$key]:"";
				
				if ( is_numeric( $cant_value ) && is_numeric( $price_value ) ) {
					$result += $cant_value * $price_value;
				}
			}
		}
		
		return $result;
	}
	
	public static function getInvoiceTax ( $invoice_id ) {
		$iva = get_post_meta($invoice_id, '_gp_invoice_tax', true);
		return self::getInvoiceSubtotal($invoice_id) * ($iva/100);
	}
	
	public static function getInvoiceIRPF ( $invoice_id ) {
		$irpf = get_post_meta($invoice_id, '_gp_invoice_irpf', true);
		return self::getInvoiceSubtotal($invoice_id) * ($irpf/100);
	}
	
	public static function getInvoiceTotal ( $invoice_id ) {
		return self::getInvoiceSubtotal($invoice_id) + self::getInvoiceTax($invoice_id) - self::getInvoiceIRPF($invoice_id);
	}

	
	/* BUDGETS */
	public static function getBudgetSubtotal ( $budget_id ) {
	
		$cants_str = get_post_meta($budget_id, 'gp-budget-cant', true);
		$cants = maybe_unserialize( $cants_str );
		$prices_str = get_post_meta($budget_id, 'gp-budget-price', true);
		$prices = maybe_unserialize( $prices_str );
	
		$result = 0;
		if ( is_array( $cants ) ) {
			foreach ( $cants as $key => $cant_value ) {
				$price_value = isset($prices[$key])?$prices[$key]:"";
	
				if ( is_numeric( $cant_value ) && is_numeric( $price_value ) ) {
					$result += $cant_value * $price_value;
				}
			}
		}
	
		return $result;
	}
	
	public static function getBudgetTax ( $budget_id ) {
		$iva = get_post_meta($budget_id, '_gp_budget_tax', true);
		return self::getBudgetSubtotal($budget_id) * ($iva/100);
	}
	
	public static function getBudgetIRPF ( $budget_id ) {
		$irpf = get_post_meta($budget_id, '_gp_budget_irpf', true);
		return self::getBudgetSubtotal($budget_id) * ($irpf/100);
	}
	
	public static function getBudgetTotal ( $budget_id ) {
		return self::getBudgetSubtotal($budget_id) + self::getBudgetTax($budget_id) - self::getBudgetIRPF($budget_id);
	}
	
	public static function budgetToInvoice ( $budget_id ) {
	
		// Number
		$budget_num = get_post_meta( $budget_id, '_gp_budget_number', true);

		// Date
		$budget_date = get_post_meta( $budget_id, '_gp_budget_date', true);

		// TAX
		$budget_tax = get_post_meta( $budget_id, '_gp_budget_tax', true);

		// IRPF
		$budget_irpf = get_post_meta( $budget_id, '_gp_budget_irpf', true);

		// Customer
		$budget_customer = get_post_meta( $budget_id, '_gp_budget_customer', true);
		
		$post_id = wp_insert_post(array (
				'post_type' => 'gp_invoice',
				'post_title' => __( 'Invoice from budget ', 'gestion-pymes' ) . $budget_num,
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
		
		));
		
		
		if ( $post_id ) {
			// Date
			update_post_meta( $post_id, '_gp_invoice_date', $budget_date );
			// TAX
			update_post_meta( $post_id, '_gp_invoice_tax', $budget_tax );
			// Date
			update_post_meta( $post_id, '_gp_invoice_irpf', $budget_irpf );
			// Date
			update_post_meta( $post_id, '_gp_invoice_customer', $budget_customer );
			
			// items
			$cants_str = get_post_meta($budget_id, 'gp-budget-cant', true);
			$cants = maybe_unserialize( $cants_str );
			$prices_str = get_post_meta($budget_id, 'gp-budget-price', true);
			$prices = maybe_unserialize( $prices_str );
			$descs_str = get_post_meta($budget_id, 'gp-budget-desc', true);
			$descs = maybe_unserialize( $descs_str );
			
			update_post_meta( $post_id, 'gp-invoice-cant', $cants_str );
			update_post_meta( $post_id, 'gp-invoice-price', $prices_str );
			update_post_meta( $post_id, 'gp-invoice-desc', $descs_str );
		}
		
		return $post_id;
	}
}