jQuery(document).ready(
	function($) {

		$(".tfdate").datepicker({
			dateFormat : 'yy-mm-dd',
			showOn : 'button'
		});

		// Invoice - button add item
		jQuery("#gp_invoice_button_add_item")
				.click(
						function(event) {

							var row = '<tr><td><input type="text" name="gp-invoice-desc[]" placeholder="Concepto" /></td>';
							row += '<td class="right"><input type="text" name="gp-invoice-cant[]" placeholder="Cantidad" /></td>';
							row += '<td class="right"><input type="text" name="gp-invoice-price[]" placeholder="Precio" /></td></tr>';

							event.preventDefault();
							var newRow = jQuery(row);
							jQuery('table.gp-invoice-items-table')
									.append(newRow);

						});

		// budget - button add item
		jQuery("#gp_budget_button_add_item")
				.click(
						function(event) {

							var row = '<tr><td><input type="text" name="gp-budget-desc[]" placeholder="Concepto" /></td>';
							row += '<td class="right"><input type="text" name="gp-budget-cant[]" placeholder="Cantidad" /></td>';
							row += '<td class="right"><input type="text" name="gp-budget-price[]" placeholder="Precio" /></td></tr>';

							event.preventDefault();
							var newRow = jQuery(row);
							jQuery('table.gp-budget-items-table')
									.append(newRow);

						});

		/**
		 * Upload logo button.
		 */
		$('#upload_image_button').click(
			function() {
				formfield = $('#upload_image').attr('name');
				tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
				return false;
			});

		window.send_to_editor = function(html) {
			imgurl = $('img', '<div>' + html + '</div>').attr('src');
			$('#upload_image').val(imgurl);
			tb_remove();
		}
	});