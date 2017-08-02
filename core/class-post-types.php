<?php
class GestionPymesPostTypes {

	public static function init() {
		self::create_post_types();
		
		add_action( 'add_meta_boxes_gp_customer', array( __CLASS__,'add_meta_boxes_gp_customer' ) );
		add_action( 'add_meta_boxes_gp_budget', array( __CLASS__,'add_meta_boxes_gp_budget' ) );
		add_action( 'add_meta_boxes_gp_invoice', array( __CLASS__,'add_meta_boxes_gp_invoice' ) );

		add_action('save_post', array( __CLASS__, 'save_post' ), 1, 2 );

		// Add extra columns to invoices
		add_filter( 'manage_gp_invoice_posts_columns', array( __CLASS__, 'manage_gp_invoice_posts_columns' ) );
		add_filter( 'manage_gp_invoice_posts_custom_column', array( __CLASS__, 'manage_gp_invoice_posts_custom_column' ), 10, 3 );
		// Add extra columns to budgets
		add_filter( 'manage_gp_budget_posts_columns', array( __CLASS__, 'manage_gp_budget_posts_columns' ) );
		add_filter( 'manage_gp_budget_posts_custom_column', array( __CLASS__, 'manage_gp_budget_posts_custom_column' ), 10, 3 );
		// Add extra columns to customers
		add_filter( 'manage_gp_customer_posts_columns', array( __CLASS__, 'manage_gp_customer_posts_columns' ) );
		add_filter( 'manage_gp_customer_posts_custom_column', array( __CLASS__, 'manage_gp_customer_posts_custom_column' ), 10, 3 );
	}

	public static function post_row_actions ( $actions, $post ) {
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg( 'action', $current_url );

		switch ( $post->post_type ) { 
			case 'gp_invoice':
				$print_url = add_query_arg(
						array(
								'gp-invoice-id' => intval( $post->ID ),
								'action' => 'gp_print_invoice'
						),
						$current_url
						);
				$actions['gp_print_invoice'] =  '<a href="' . $print_url . '">' . __('Print', 'gestion-pymes' ) . '</a>';
				break;
			case 'gp_budget':
				$print_url = add_query_arg(
				array(
				'gp-budget-id' => intval( $post->ID ),
				'action' => 'gp_print_budget'
						),
						$current_url
						);
				$actions['gp_print_budget'] =  '<a href="' . $print_url . '">' . __('Print', 'gestion-pymes' ) . '</a>';
				break;
		}

		return $actions;
	}

	public static function admin_action_gp_print_invoice () {

		$post_id = isset( $_REQUEST['gp-invoice-id'] ) ? $_REQUEST['gp-invoice-id'] : null;

		if ( $post_id ) {
			include_once GESTIONPYMES_CORE_DIR . '/mpdf/mpdf.php';

			$invoice_number = get_post_meta( $post_id, '_gp_invoice_number', TRUE );

			$tableVar = GP_PDF_Template::getInvoiceContent( $post_id );
			$stylesheet = GP_PDF_Template::getInvoiceCSS( $post_id );

			$mpdf=new mPDF();

			$mpdf->SetHTMLFooter( GP_PDF_Template::getInvoiceFooterContent( $post_id ) );

			$mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHTML($tableVar,2);
			$mpdf->Output(__( 'invoice-', 'gestion-pymes' ) . $invoice_number . '.pdf','D');
		}
		exit;
	}

	public static function admin_action_gp_print_budget () {

		$post_id = isset( $_REQUEST['gp-budget-id'] ) ? $_REQUEST['gp-budget-id'] : null;

		if ( $post_id ) {
			include_once GESTIONPYMES_CORE_DIR . '/mpdf/mpdf.php';

			$budget_number = get_post_meta( $post_id, '_gp_budget_number', TRUE );

			$tableVar = GP_PDF_Template::getBudgetContent( $post_id );
			$stylesheet = GP_PDF_Template::getBudgetCSS( $post_id );

			$mpdf=new mPDF();

			$mpdf->SetHTMLFooter( GP_PDF_Template::getBudgetFooterContent( $post_id ) );

			$mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHTML($tableVar,2);
			$mpdf->Output( __( 'budget-', 'gestion-pymes' ) . $budget_number . '.pdf','D');
			exit;
		}
	}

	/**
	 * Invoice columns
	 * @param array $column_headers
	 * @return array
	 */
	public static function manage_gp_invoice_posts_columns( $column_headers ) {
		$column_headers = array (
				'number'   => __( 'Number', 'gestion-pymes' ),
				'title'    => __( 'Title', 'gestion-pymes' ),
				'customer' => __( 'Customer', 'gestion-pymes' ),
				'idate'    => __( 'Date', 'gestion-pymes' ),
				'total'    => __( 'Total', 'gestion-pymes' ) );
		return $column_headers;
	}
	
	/**
	 * Set invoice columns value
	 * @param string $column_name
	 * @param int $post_ID
	 */
	public static function manage_gp_invoice_posts_custom_column($column_name, $post_ID) {
		switch ( $column_name ) {
			case 'number':
				echo get_post_meta( $post_ID, '_gp_invoice_number', true );
				break;
			case 'title':
				echo get_post_meta( $post_ID, 'title', true );
				break;
			case 'customer':
				$customer_id = get_post_meta( $post_ID, '_gp_invoice_customer', true );
				$customer_name = get_post_meta( $customer_id, '_gp_customer_name', true );
				echo $customer_name;
				break;
			case 'idate':
				echo get_post_meta( $post_ID, '_gp_invoice_date', true );
				break;
			case 'total':
				$total = GestionPymes::getInvoiceTotal( $post_ID );
				echo $total . GESTIONPYMES_CURRENCY;
				break;
		}
	}

	/**
	 * Budget columns
	 * @param array $column_headers
	 * @return array
	 */
	public static function manage_gp_budget_posts_columns( $column_headers ) {
		$column_headers = array (
				'number' => __( 'Number', 'gestion-pymes' ),
				'title' => __( 'Title', 'gestion-pymes' ),
				'customer' => __( 'Customer', 'gestion-pymes' ),
				'idate' => __( 'Date', 'gestion-pymes' ),
				'total' => __( 'Total', 'gestion-pymes' ) );
		return $column_headers;
	}
	
	/**
	 * Set budget columns value
	 * @param string $column_name
	 * @param int $post_ID
	 */
	public static function manage_gp_budget_posts_custom_column($column_name, $post_ID) {
		switch ( $column_name ) {
			case 'number':
				echo get_post_meta( $post_ID, '_gp_budget_number', true );
				break;
			case 'title':
				echo get_post_meta( $post_ID, 'title', true );
				break;
			case 'customer':
				$customer_id = get_post_meta( $post_ID, '_gp_budget_customer', true );
				$customer_name = get_post_meta( $customer_id, '_gp_customer_name', true );
				echo $customer_name;
				break;
			case 'idate':
				echo get_post_meta( $post_ID, '_gp_budget_date', true );
				break;
			case 'total':
				$total = GestionPymes::getBudgetTotal( $post_ID );
				echo $total . GESTIONPYMES_CURRENCY;
				break;
		}
	}

	/**
	 * Customer columns
	 * @param array $column_headers
	 * @return array
	 */
	public static function manage_gp_customer_posts_columns( $column_headers ) {
		$column_headers = array (
				'title' => __( 'Title', 'gestion-pymes' ),
				'name' => __( 'Name', 'gestion-pymes' ),
				'email' => __( 'Email', 'gestion-pymes' ) );
		return $column_headers;
	}
	
	/**
	 * Set customer columns value
	 * @param string $column_name
	 * @param int $post_ID
	 */
	public static function manage_gp_customer_posts_custom_column($column_name, $post_ID) {
		switch ( $column_name ) {
			case 'title':
				echo get_post_meta( $post_ID, 'title', true );
				break;
			case 'name':
				$name = get_post_meta( $post_ID, '_gp_customer_name', true );
				$surname = get_post_meta( $post_ID, '_gp_customer_surname', true );
				echo $name . " " . $surname;
				break;
			case 'email':
				echo get_post_meta( $post_ID, '_gp_customer_email', true );
				break;
		}
	}

	public static function add_meta_boxes_gp_customer ( $post ) {
		add_meta_box( 
			'gp_customer_information',
			__( 'Information', 'gestion-pymes' ),
			array ( __CLASS__,'render_meta_box_gp_customer_information')
		);
		add_meta_box(
			'gp_customer_invoices',
			__( 'Invoices', 'gestion-pymes' ),
			array ( __CLASS__,'render_meta_box_gp_customer_invoices'),
			'gp_customer',
			'side'
		);
		add_meta_box(
			'gp_customer_budgets',
			__( 'Budgets', 'gestion-pymes' ),
			array ( __CLASS__,'render_meta_box_gp_customer_budgets'),
			'gp_customer',
			'side'
		);
	}

	public static function add_meta_boxes_gp_invoice ( $post ) {
		add_meta_box(
				'gp_invoice_information',
				__( 'General Details', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_invoice_information')
		);
		add_meta_box(
				'gp_invoice_items',
				__( 'Invoice items', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_invoice_items')
		);
		add_meta_box(
				'gp_invoice_actions',
				__( 'Actions', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_invoice_actions'),
				'gp_invoice',
				'side'
		);
	}
	
	public static function add_meta_boxes_gp_budget ( $post ) {
		add_meta_box(
				'gp_budget_information',
				__( 'General Details', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_budget_information')
		);
		add_meta_box(
				'gp_budget_items',
				__( 'Budget items', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_budget_items')
		);
		add_meta_box(
				'gp_budget_actions',
				__( 'Actions', 'gestion-pymes' ),
				array ( __CLASS__,'render_meta_box_gp_budget_actions'),
				'gp_budget',
				'side'
		);
	}

	public static function render_meta_box_gp_customer_information () {
		global $post;
	
		$output = "";
		echo '<input type="hidden" name="gp_customer_information" id="gp_customer_information" value="' .
				wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
		// Get the location data if its already been entered
		$name = get_post_meta($post->ID, '_gp_customer_name', true);
		$surname = get_post_meta($post->ID, '_gp_customer_surname', true);
		$cif = get_post_meta($post->ID, '_gp_customer_cif', true);
		$tlf = get_post_meta($post->ID, '_gp_customer_tlf', true);
		$email = get_post_meta($post->ID, '_gp_customer_email', true);
		$address = get_post_meta($post->ID, '_gp_customer_address', true);
	
		 
		// Echo out the field
		$output .= '<p>' . __( "Name", 'gestion-pymes' ) . '<br><input type="text" name="_gp_customer_name" value="' . $name  . '" class="widefat" /></p>';
		$output .= '<p>' . __( "Surname", 'gestion-pymes' ) . '<br><input type="text" name="_gp_customer_surname" value="' . $surname  . '" class="widefat" /></p>';
		$output .= '<p>' . __( "VAT number", 'gestion-pymes' ) . '<br><input type="text" name="_gp_customer_cif" value="' . $cif  . '" class="widefat" /></p>';
		$output .= '<p>' . __( "Telephone", 'gestion-pymes' ) . '<br><input type="text" name="_gp_customer_tlf" value="' . $tlf  . '" class="widefat" /></p>';
		$output .= '<p>' . __( "Email", 'gestion-pymes' ) . '<br><input type="text" name="_gp_customer_email" value="' . $email  . '" class="widefat" /></p>';
		$output .= '<p>' . __( "Address", 'gestion-pymes' ) . '<br><textarea name="_gp_customer_address" class="widefat">' . $address . '</textarea></p>';
		echo $output;
	}
	
	/**
	 * Render the customer's invoices as a list.
	 */
	public static function render_meta_box_gp_customer_invoices () {
		global $post;
	
		$output = "<ul>";
	
		$invoices = GestionPymes::getCustomerInvoices ( $post->ID );
		if ( sizeof( $invoices ) > 0 ) {
			foreach ( $invoices as $invoice ) {
				$output .='<li><a href="' . get_edit_post_link( $invoice->ID ) . '">' . $invoice->post_title . '</a></li>';
			}
		}
	
		$output .= '</ul>';
		echo $output;
	}
	/**
	 * Render the customer's budget as a list.
	 */
	public static function render_meta_box_gp_customer_budgets () {
		global $post;
	
		$output = "<ul>";
	
		$budgets = GestionPymes::getCustomerBudgets ( $post->ID );
		if ( sizeof( $budgets ) > 0 ) {
			foreach ( $budgets as $budget ) {
				$output .='<li><a href="' . get_edit_post_link( $budget->ID ) . '">' . $budget->post_title . '</a></li>';
			}
		}
	
		$output .= '</ul>';
		echo $output;
	}
	
	public static function render_meta_box_gp_invoice_information () {
		global $post; 

		$output = "";
	    echo '<input type="hidden" name="gp_invoice_information" id="gp_invoice_information" value="' .  
	    wp_create_nonce( plugin_basename(__FILE__) ) . '" />'; 
	    
	    // Get the location data if its already been entered
	    $num = get_post_meta($post->ID, '_gp_invoice_number', true);
	    $date = get_post_meta($post->ID, '_gp_invoice_date', true);
	    $tax = get_post_meta($post->ID, '_gp_invoice_tax', true);
	    $irpf = get_post_meta($post->ID, '_gp_invoice_irpf', true);
	    $customerID = get_post_meta($post->ID, '_gp_invoice_customer', true);
	     
	    $args = array( 'post_type' => 'gp_customer', 'posts_per_page' => -1, 'post_status' =>'publish' );
	    $allCustomers = get_posts( $args );
	    
	    $select = '<select name="_gp_invoice_customer" class="widefat">';
	    if ( is_array( $allCustomers ) && ( sizeof( $allCustomers )>0 ) ) {
	    	foreach ( $allCustomers as $customer ) {
	    		$selected = "";
	    		if ( $customer->ID == $customerID ) {
	    			$selected = "selected";
	    		}
	    		$select .= '<option value="' . $customer->ID . '" ' . $selected . ' >' . get_the_title( $customer->ID ) . '</option>';
	    	}
	    } else {
	    	$select .= '<option value="">' . __( 'Select one', 'gestion-pymes' ) . '</option>';
	    }
	    $select .= '</select>';
	    
	    $selectTax = '<select name="_gp_invoice_tax" class="widefat">';
	    $taxes = array();
	    $taxes[] = get_option( "gp_settings_vat_1", "0" );
	    $taxes[] = get_option( "gp_settings_vat_2", "0" );
	    $taxes[] = get_option( "gp_settings_vat_3", "0" );
	    foreach ( $taxes as $value ) {
	    	$selected = "";
	    	if ( $value == $tax ) {
	    		$selected = "selected";
	    	}
	    	$selectTax .= '<option value="' . $value . '" ' . $selected . ' >' . $value . '</option>';
	    }
	    $selectTax .= '</select>';
	     
	    // Echo out the field
	    $output .= '<p>' . __( "Number", 'gestion-pymes' ) . '<br><input type="text" name="_gp_invoice_number" value="' . $num  . '" class="" /></p>';
	    $output .= '<p>' . __( "Date", 'gestion-pymes' ) . '<br><input type="text" readonly class="tfdate" name="_gp_invoice_date" value="' . $date  . '" class="widefat" /></p>';
	    $output .= '<p>' . __( "TAX", 'gestion-pymes' ) . '<br>' . $selectTax . '</p>';
	    $output .= '<p>' . __( "IRPF", 'gestion-pymes' ) . '<br><input type="text" name="_gp_invoice_irpf" value="' . $irpf  . '" class="widefat" /></p>';
	    $output .= '<p>' . __( "Customer", 'gestion-pymes' ) . '<br>' . $select . '</p>';
	    echo $output;
	     
	}
	
	public static function render_meta_box_gp_invoice_items () {
		global $post;
		
		$descs = get_post_meta($post->ID, 'gp-invoice-desc', true);
		$desc = maybe_unserialize( $descs );
		$cants = get_post_meta($post->ID, 'gp-invoice-cant', true);
		$cant = maybe_unserialize( $cants );
		$prices = get_post_meta($post->ID, 'gp-invoice-price', true);
		$price = maybe_unserialize( $prices );
		
		$output = "";
		$output .= '<div class="table-responsive">
						<table class="gp-invoice-items-table">
							<thead>
								<tr>
									<th class="gp-invoice-table-desc">Descripción</th>
									<th class="gp-invoice-table-cant">Cantidad</th>
									<th class="gp-invoice-table-price">Precio</th>
								</tr>
							</thead>
							<tbody>';
		
		if ( is_array( $desc ) ) {
			foreach ( $desc as $key => $value ) {
				$cant_value = isset($cant[$key])?$cant[$key]:"";
				$price_value = isset($price[$key])?$price[$key]:"";
				$output .= '<tr>
					<td><input type="text" name="gp-invoice-desc[]" value="' . $value . '" /></td>
					<td class="right"><input type="text" name="gp-invoice-cant[]" value="' . $cant_value . '" /></td>
					<td class="right"><input type="text" name="gp-invoice-price[]" value="' . $price_value . '" /></td>
				</tr>';
			}
		} else {
			$output .= '<tr>
							<td><input type="text" name="gp-invoice-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-invoice-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-invoice-price[]" placeholder="Precio" /></td>
						</tr>';
			$output .= '<tr>
							<td><input type="text" name="gp-invoice-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-invoice-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-invoice-price[]" placeholder="Precio" /></td>
						</tr>';
			$output .= '<tr>
							<td><input type="text" name="gp-invoice-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-invoice-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-invoice-price[]" placeholder="Precio" /></td>
						</tr>';
		}
		$output .= '</tbody>
				</table>
			</div>';
		
		// button
		$output .= '<div style="clear:both;"></div>';
		$output .= '<button id="gp_invoice_button_add_item" class="button button-primary button-large" onclick="return false;">' . __( "Añadir línea", 'gestionpymes' ) . '</button>';
		echo $output;
	}
	
	public static function render_meta_box_gp_invoice_actions () {
		global $post;

		$output = "";
		$output .= '<form method="post" action="">';
		$output .= '<input type="hidden" name="gp-invoice-id" value="' . $post->ID . '" />';
		$output .= '<input type="submit" name="gp-invoice-print" class="button button-primary button-large" value="' . __( "Print", 'gestion-pymes' ) . '" />';
		$output .= '</form>';

		echo $output;
	}
	
	public static function render_meta_box_gp_budget_information () {
		global $post; 

		$output = "";
	    echo '<input type="hidden" name="gp_budget_information" id="gp_budget_information" value="' .  
	    wp_create_nonce( plugin_basename(__FILE__) ) . '" />'; 
		
		// Get the location data if its already been entered 
	    $num = get_post_meta($post->ID, '_gp_budget_number', true); 
	    $date = get_post_meta($post->ID, '_gp_budget_date', true); 
	    $tax = get_post_meta($post->ID, '_gp_budget_tax', true);
	    $irpf = get_post_meta($post->ID, '_gp_budget_irpf', true);
	    $customerID = get_post_meta($post->ID, '_gp_budget_customer', true);
	    
	    $args = array( 'post_type' => 'gp_customer', 'posts_per_page' => -1, 'post_status' =>'publish' );
	    $allCustomers = get_posts( $args );
	     
	    $select = '<select name="_gp_budget_customer" class="widefat">';
	    if ( is_array( $allCustomers ) && ( sizeof( $allCustomers )>0 ) ) {
	    	foreach ( $allCustomers as $customer ) {
	    		$selected = "";
	    		if ( $customer->ID == $customerID ) {
	    			$selected = "selected";
	    		}
	    		$select .= '<option value="' . $customer->ID . '" ' . $selected . ' >' . get_the_title( $customer->ID ) . '</option>';
	    	}
	    } else {
	    	$select .= '<option value="">' . __( 'Select one', 'gestion-pymes' ) . '</option>';
	    }
	    $select .= '</select>';
	    
	    $selectTax = '<select name="_gp_budget_tax" class="widefat">';
	    $taxes = array();
	    $taxes[] = get_option( "gp_settings_vat_1", "0" );
	    $taxes[] = get_option( "gp_settings_vat_2", "0" );
	    $taxes[] = get_option( "gp_settings_vat_3", "0" );
	    foreach ( $taxes as $value ) {
	    	$selected = "";
		    if ( $value == $tax ) {
		    	$selected = "selected";
		    }
		    $selectTax .= '<option value="' . $value . '" ' . $selected . ' >' . $value . '</option>';
	    }
		$selectTax .= '</select>';
	     
	    // Echo out the field 
	    $output .= '<p>' . __( "Number", 'gestion-pymes' ) . '<br><input type="text" name="_gp_budget_number" value="' . $num  . '" class="" /></p>'; 
	    $output .= '<p>' . __( "Date", 'gestion-pymes' ) . '<br><input type="text" readonly class="tfdate" name="_gp_budget_date" value="' . $date  . '" class="widefat" /></p>'; 
	    $output .= '<p>' . __( "TAX", 'gestion-pymes' ) . '<br>' . $selectTax . '</p>'; 
	    $output .= '<p>' . __( "IRPF", 'gestion-pymes' ) . '<br><input type="text" name="_gp_budget_irpf" value="' . $irpf  . '" class="widefat" /></p>'; 
	    $output .= '<p>' . __( "Customer", 'gestion-pymes' ) . '<br>' . $select . '</p>'; 
	    echo $output;
	}
	
	public static function render_meta_box_gp_budget_items () {
		global $post;
		
		$descs = get_post_meta($post->ID, 'gp-budget-desc', true);
		$desc = maybe_unserialize( $descs );
		$cants = get_post_meta($post->ID, 'gp-budget-cant', true);
		$cant = maybe_unserialize( $cants );
		$prices = get_post_meta($post->ID, 'gp-budget-price', true);
		$price = maybe_unserialize( $prices );
		
		$output = "";
		$output .= '<div class="table-responsive">
						<table class="gp-budget-items-table">
							<thead>
								<tr>
									<th class="gp-budget-table-desc">Descripción</th>
									<th class="gp-budget-table-cant">Cantidad</th>
									<th class="gp-budget-table-price">Precio</th>
								</tr>
							</thead>
							<tbody>';
		
		if ( is_array( $desc ) ) {
			foreach ( $desc as $key => $value ) {
				$cant_value = isset($cant[$key])?$cant[$key]:"";
				$price_value = isset($price[$key])?$price[$key]:"";
				$output .= '<tr>
					<td><input type="text" name="gp-budget-desc[]" value="' . $value . '" /></td>
					<td class="right"><input type="text" name="gp-budget-cant[]" value="' . $cant_value . '" /></td>
					<td class="right"><input type="text" name="gp-budget-price[]" value="' . $price_value . '" /></td>
				</tr>';
			}
		} else {
			$output .= '<tr>
							<td><input type="text" name="gp-budget-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-budget-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-budget-price[]" placeholder="Precio" /></td>
						</tr>';
			$output .= '<tr>
							<td><input type="text" name="gp-budget-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-budget-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-budget-price[]" placeholder="Precio" /></td>
						</tr>';
			$output .= '<tr>
							<td><input type="text" name="gp-budget-desc[]" placeholder="Concepto" /></td>
							<td class="right"><input type="text" name="gp-budget-cant[]" placeholder="Cantidad" /></td>
							<td class="right"><input type="text" name="gp-budget-price[]" placeholder="Precio" /></td>
						</tr>';
		}
		$output .= '</tbody>
				</table>
			</div>';
		
		// button
		$output .= '<div style="clear:both;"></div>';
		$output .= '<button id="gp_budget_button_add_item" class="button button-primary button-large" onclick="return false;">' . __( "Añadir línea", 'gestion-pymes' ) . '</button>';
		echo $output;
	}
	
	public static function render_meta_box_gp_budget_actions () {
		global $post;
	
		$output = "";
		$output .= '<div class="gp-actions">';
		$output .= '<form method="post" action="">';
		$output .= '<input type="hidden" name="gp-budget-id" value="' . $post->ID . '" />';
		$output .= '<input type="submit" name="gp-budget-print" class="button button-primary button-large" value="' . __( "Print", 'gestion-pymes' ) . '" />';
		$output .= '<input type="submit" name="gp-budget-to-invoice" class="button button-primary button-large" value="' . __( "To Invoice", 'gestion-pymes' ) . '" />';
		$output .= '</form>';
		$output .= '</div>';
		
		$output .= '<div style="clear:both;"></div>';
		
		echo $output;
	}
	
	/*
	 * saving post ....
	 */
	public static function save_post ( $post_id, $post ) {
	
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}
	
		if ( $post->post_type == 'revision' ) {
			return; // Don't store custom data twice
		}
		
		switch($post->post_type) { // Do different things based on the post type
			case "gp_customer":
				if ( (!isset( $_POST['gp_customer_information'] ) ) || ( !wp_verify_nonce( $_POST['gp_customer_information'], plugin_basename(__FILE__) ) ) ) {
					return $post->ID;
				}
				// Name
				if ( isset( $_POST['_gp_customer_name'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_customer_name'] );
					if ( get_post_meta( $post->ID, '_gp_customer_name', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_name', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_name', $value );
					}
				}
				// Surname
				if ( isset( $_POST['_gp_customer_surname'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_customer_surname'] );
					if ( get_post_meta($post->ID, '_gp_customer_surname', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_surname', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_surname', $value );
					}
				}
				// cif
				if ( isset( $_POST['_gp_customer_cif'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_customer_cif'] );
					if ( get_post_meta($post->ID, '_gp_customer_cif', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_cif', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_cif', $value );
					}
				}
				// Telephone
				if ( isset( $_POST['_gp_customer_tlf'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_customer_tlf'] );
					if ( get_post_meta($post->ID, '_gp_customer_tlf', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_tlf', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_tlf', $value );
					}
				}
				// email
				if ( isset( $_POST['_gp_customer_email'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_customer_email'] );
					if ( get_post_meta($post->ID, '_gp_customer_email', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_email', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_email', $value );
					}
				}
				// Address
				if ( isset( $_POST['_gp_customer_address'] ) ) {
					$value = esc_textarea( $_POST['_gp_customer_address'] );
					if ( get_post_meta($post->ID, '_gp_customer_address', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_customer_address', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_customer_address', $value );
					}
				}
				break;

			case "gp_invoice":
				if ( ( isset( $_POST['gp-invoice-print'] ) ) && ( isset( $_POST['gp-invoice-id'] ) ) ) {

					include_once GESTIONPYMES_CORE_DIR . '/mpdf/mpdf.php';

					$invoice_number = get_post_meta( $post->ID, '_gp_invoice_number', TRUE );

					$tableVar = GP_PDF_Template::getInvoiceContent( $_POST['gp-invoice-id'] );
					$stylesheet = GP_PDF_Template::getInvoiceCSS( $_POST['gp-invoice-id'] );

					$mpdf=new mPDF();

					$mpdf->SetHTMLFooter( GP_PDF_Template::getInvoiceFooterContent( $_POST['gp-invoice-id'] ) );

					$mpdf->WriteHTML($stylesheet,1);
					$mpdf->WriteHTML($tableVar,2);
					$mpdf->Output(__( 'invoice-', 'gestion-pymes' ) . $invoice_number . '.pdf','D');
					exit;
				}
				if ( (!isset( $_POST['gp_invoice_information'] ) ) || ( !wp_verify_nonce( $_POST['gp_invoice_information'], plugin_basename(__FILE__) ) ) ) {
					return $post->ID;
				}
				// Number
				if ( isset( $_POST['_gp_invoice_number'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_invoice_number'] );
					if ( get_post_meta( $post->ID, '_gp_invoice_number', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_invoice_number', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_invoice_number', $value );
					}
				}
				// Date
				if ( isset( $_POST['_gp_invoice_date'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_invoice_date'] );
					$value = sanitize_text_field( apply_filters('gestion_pymes_invoice_date', $value) );
					if ( get_post_meta( $post->ID, '_gp_invoice_date', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_invoice_date', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_invoice_date', $value );
					}
				}
				// TAX
				if ( isset( $_POST['_gp_invoice_tax'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_invoice_tax'] );
					if ( get_post_meta( $post->ID, '_gp_invoice_tax', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_invoice_tax', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_invoice_tax', $value );
					}
				}
				// IRPF
				if ( isset( $_POST['_gp_invoice_irpf'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_invoice_irpf'] );
					if ( get_post_meta( $post->ID, '_gp_invoice_irpf', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_invoice_irpf', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_invoice_irpf', $value );
					}
				}
				// Customer
				if ( isset( $_POST['_gp_invoice_customer'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_invoice_customer'] );
					if ( get_post_meta( $post->ID, '_gp_invoice_customer', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_invoice_customer', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_invoice_customer', $value );
					}
				}
				// items
				if ( isset( $_POST['gp-invoice-desc'] ) ) {
					$descriptions = array();
					foreach ( $_POST['gp-invoice-desc'] as $description ) {
						$descriptions[] = sanitize_text_field( $description );
					}
					$values = maybe_serialize( $descriptions );
					update_post_meta( $post->ID, 'gp-invoice-desc', $values );
				}
				if ( isset( $_POST['gp-invoice-cant'] ) ) {
					$cants = array();
					foreach ( $_POST['gp-invoice-cant'] as $cant ) {
						$cants[] = sanitize_text_field( $cant );
					}
					$values = maybe_serialize( $cants );
					update_post_meta( $post->ID, 'gp-invoice-cant', $values );
				}
				if ( isset( $_POST['gp-invoice-price'] ) ) {
					$prices = array();
					foreach ( $_POST['gp-invoice-price'] as $price ) {
						$prices[] = sanitize_text_field( $price );
					}
					$values = maybe_serialize( $prices );
					update_post_meta( $post->ID, 'gp-invoice-price', $values );
				}
				break;

			case "gp_budget":
				if ( ( isset( $_POST['gp-budget-print'] ) ) && ( isset( $_POST['gp-budget-id'] ) ) ) {

					include_once GESTIONPYMES_CORE_DIR . '/mpdf/mpdf.php';

					$budget_number = get_post_meta( $post->ID, '_gp_budget_number', TRUE );

					$tableVar = GP_PDF_Template::getBudgetContent( $_POST['gp-budget-id'] );
					$stylesheet = GP_PDF_Template::getBudgetCSS( $_POST['gp-budget-id'] );

					$mpdf=new mPDF();

					$mpdf->SetHTMLFooter( GP_PDF_Template::getBudgetFooterContent( $_POST['gp-budget-id'] ) );

					$mpdf->WriteHTML($stylesheet,1);
					$mpdf->WriteHTML($tableVar,2);
					$mpdf->Output( __( 'budget-', 'gestion-pymes' ) . $budget_number . '.pdf','D');
					exit;
				}
				// to Invoice
				if ( ( isset( $_POST['gp-budget-to-invoice'] ) ) && ( isset( $_POST['gp-budget-id'] ) ) ) {
					$invoice_id = GestionPymes::budgetToInvoice( $_POST['gp-budget-id'] );
					wp_redirect( admin_url( 'post.php?post=' . $invoice_id .'&action=edit' ) );
					exit;
				}

				if ( (!isset( $_POST['gp_budget_information'] ) ) || ( !wp_verify_nonce( $_POST['gp_budget_information'], plugin_basename(__FILE__) ) ) ) {
					return $post->ID;
				}
				// Number
				if ( isset( $_POST['_gp_budget_number'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_budget_number'] );
					if ( get_post_meta( $post->ID, '_gp_budget_number', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_budget_number', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_budget_number', $value );
					}
				}
				// Date
				if ( isset( $_POST['_gp_budget_date'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_budget_date'] );
					$value = sanitize_text_field( apply_filters('gestion_pymes_budget_date', $value) );
					if ( get_post_meta( $post->ID, '_gp_budget_date', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_budget_date', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_budget_date', $value );
					}
				}
				// TAX
				if ( isset( $_POST['_gp_budget_tax'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_budget_tax'] );
					if ( get_post_meta( $post->ID, '_gp_budget_tax', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_budget_tax', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_budget_tax', $value );
					}
				}
				// IRPF
				if ( isset( $_POST['_gp_budget_irpf'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_budget_irpf'] );
					if ( get_post_meta( $post->ID, '_gp_budget_irpf', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_budget_irpf', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_budget_irpf', $value );
					}
				}
				// Customer
				if ( isset( $_POST['_gp_budget_customer'] ) ) {
					$value = sanitize_text_field( $_POST['_gp_budget_customer'] );
					if ( get_post_meta( $post->ID, '_gp_budget_customer', FALSE ) ) { // If the custom field already has a value
						update_post_meta( $post->ID, '_gp_budget_customer', $value );
					} else { // If the custom field doesn't have a value
						add_post_meta( $post->ID, '_gp_budget_customer', $value );
					}
				}
				// items
				if ( isset( $_POST['gp-budget-desc'] ) ) {
					$descriptions = array();
					foreach ( $_POST['gp-budget-desc'] as $description ) {
						$descriptions[] = sanitize_text_field( $description );
					}
					$values = maybe_serialize( $descriptions );
					update_post_meta( $post->ID, 'gp-budget-desc', $values );
				}
				if ( isset( $_POST['gp-budget-cant'] ) ) {
					$cants = array();
					foreach ( $_POST['gp-budget-cant'] as $cant ) {
						$cants[] = sanitize_text_field( $cant );
					}
					$values = maybe_serialize( $cants );
					update_post_meta( $post->ID, 'gp-budget-cant', $values );
				}
				if ( isset( $_POST['gp-budget-price'] ) ) {
					$prices = array();
					foreach ( $_POST['gp-budget-price'] as $price ) {
						$prices[] = sanitize_text_field( $price );
					}
					$values = maybe_serialize( $prices );
					update_post_meta( $post->ID, 'gp-budget-price', $values );
				}
				break;
		}
	}

	/*
	 * Create the custom post-types.
	 */
	public static function create_post_types () {
		// Customers - gp-customer
		$labels = array(
				'name'               => __( 'Customers', 'gestion-pymes' ),
				'singular_name'      => __( 'Customer', 'gestion-pymes' ),
				'menu_name'          => __( 'Customers', 'gestion-pymes' ),
				'name_admin_bar'     => __( 'Customer', 'gestion-pymes' ),
				'add_new'            => __( 'Add New', 'gestion-pymes' ),
				'add_new_item'       => __( 'Add New Customer', 'gestion-pymes' ),
				'new_item'           => __( 'New Customer', 'gestion-pymes' ),
				'edit_item'          => __( 'Edit Customer', 'gestion-pymes' ),
				'view_item'          => __( 'View Customer', 'gestion-pymes' ),
				'all_items'          => __( 'All Customers', 'gestion-pymes' ),
				'search_items'       => __( 'Search Customers', 'gestion-pymes' ),
				'parent_item_colon'  => __( 'Parent Customers:', 'gestion-pymes' ),
				'not_found'          => __( 'No customers found.', 'gestion-pymes' ),
				'not_found_in_trash' => __( 'No customers found in Trash.', 'gestion-pymes' )
		);
		$args = array(
				'labels'             => $labels,
				'description'        => __( 'Customers/clients.', 'gestion-pymes' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'customer' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 100,
				'supports'           => array( 'title' ),
				'menu_icon'          => GESTIONPYMES_PLUGIN_URL . '/images/customers.png'
				);
		register_post_type( 'gp_customer', $args );

		// Invoices - gp-invoice
		$labels = array(
				'name'               => __( 'Invoices', 'gestion-pymes' ),
				'singular_name'      => __( 'Invoice', 'gestion-pymes' ),
				'menu_name'          => __( 'Invoices', 'gestion-pymes' ),
				'name_admin_bar'     => __( 'Invoice', 'gestion-pymes' ),
				'add_new'            => __( 'Add New', 'gestion-pymes' ),
				'add_new_item'       => __( 'Add New Invoice', 'gestion-pymes' ),
				'new_item'           => __( 'New Invoice', 'gestion-pymes' ),
				'edit_item'          => __( 'Edit Invoice', 'gestion-pymes' ),
				'view_item'          => __( 'View Invoice', 'gestion-pymes' ),
				'all_items'          => __( 'All Invoices', 'gestion-pymes' ),
				'search_items'       => __( 'Search Invoices', 'gestion-pymes' ),
				'parent_item_colon'  => __( 'Parent Invoices:', 'gestion-pymes' ),
				'not_found'          => __( 'No invoices found.', 'gestion-pymes' ),
				'not_found_in_trash' => __( 'No invoices found in Trash.', 'gestion-pymes' )
		);
		$args = array(
				'labels'             => $labels,
				'description'        => __( 'Invoices....', 'gestion-pymes' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'invoice' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 100,
				'supports'           => array( 'title' ),
				'menu_icon'          => GESTIONPYMES_PLUGIN_URL . '/images/invoices.png'
		);
		register_post_type( 'gp_invoice', $args );

		// budgets - gp-budget
		$labels = array(
				'name'               => __( 'Budgets', 'gestion-pymes' ),
				'singular_name'      => __( 'Budget', 'gestion-pymes' ),
				'menu_name'          => __( 'Budgets', 'gestion-pymes' ),
				'name_admin_bar'     => __( 'Budget', 'gestion-pymes' ),
				'add_new'            => __( 'Add New', 'gestion-pymes' ),
				'add_new_item'       => __( 'Add New budget', 'gestion-pymes' ),
				'new_item'           => __( 'New budget', 'gestion-pymes' ),
				'edit_item'          => __( 'Edit budget', 'gestion-pymes' ),
				'view_item'          => __( 'View budget', 'gestion-pymes' ),
				'all_items'          => __( 'All Budgets', 'gestion-pymes' ),
				'search_items'       => __( 'Search Budgets', 'gestion-pymes' ),
				'parent_item_colon'  => __( 'Parent Budgets:', 'gestion-pymes' ),
				'not_found'          => __( 'No budgets found.', 'gestion-pymes' ),
				'not_found_in_trash' => __( 'No budgets found in Trash.', 'gestion-pymes' )
		);
		$args = array(
				'labels'             => $labels,
				'description'        => __( 'Budgets ....', 'gestion-pymes' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'budget' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 100,
				'supports'           => array( 'title' ),
				'menu_icon'          => GESTIONPYMES_PLUGIN_URL . '/images/budgets.png'
		);
		register_post_type( 'gp_budget', $args );
	}
}

GestionPymesPostTypes::init();
?>