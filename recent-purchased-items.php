<?php
/**
 * Plugin Name
 *
 * @package           RecentPurchasedItems
 * @author            Sangramsinh sardesai
 * @copyright         2023 Sangramsinh sardesai
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Recent Purchased Items
 * Plugin URI:        https://www.facebook.com/sangramsinh.sardesai
 * Description:       Recent Purchased Items list
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sangramsinh sardesai
 * Author URI:        https://profiles.wordpress.org/sangramsinh/
 * Text Domain:       recent-purchased-items
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */




/*
 * Add my new menu to the Admin Control Panel
 */
// Hook the 'admin_menu' action hook, run the function named 'mfp_Add_My_Admin_Link()'
add_action( 'admin_menu', 'rpi_add_admin_menu_link' );
// Add a new top level menu link to the ACP
function rpi_add_admin_menu_link()
{
	/*add_submenu_page(
        'options-general.php',
        'Recent Purchased Items',
        'Recent Purchased Items',
        'manage_options',
        'recentpurchaseditems',
        'dashboard_page' );*/
      add_submenu_page(
		'woocommerce', 
        'Recent Purchased Items', // Title of the page
        'Recent Purchased Items', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'recentpurchaseditems', 
		'rpi_dashboard_page'
    );
}


function rpi_recentproductsloop()
{
	ob_start();
	$userid = get_current_user_id();
	
	if($userid>0)
	{
			
			$args = array();
			$instance = array();

			
			$number_of_items_to_list = get_option('number_of_items_to_list');
			
			$number_of_items_to_list = isset( $number_of_items_to_list ) ? $number_of_items_to_list : '8';
			
			
			
						
			
			$args = array(
    'customer_id' => $userid,
);
$orders = wc_get_orders( array(
'numberposts' => 15,
'orderby' => 'date',
'order' => 'DESC',
'customer_id' => get_current_user_id(),
'status' => array('completed'),
) );

			
			//$out = null;
			if($orders){
				
				$product_ids = array();
				foreach ( $orders as $eachorder ) { 
					//setup_postdata($payment);
					$order_data = $eachorder->get_data(); // The Order data
					$order = new WC_Order($order_data['id']);
					$fname = $order->get_billing_first_name();
					$lname = $order->get_billing_last_name();							
					$date = $order->get_date_completed();
					$time = human_time_diff( strtotime($date), current_time('timestamp') );
					$user_id = $order->get_user_id();
					$products = $order->get_items();
										
					foreach($products as $product){
						$product_ids[] = $product['product_id'];
					}				
					
					
					
				}
				
				$product_ids = array_unique($product_ids);
				
				$product_ids =array_slice($product_ids,0,$number_of_items_to_list);
				
				 $args = array(
					'post_type' => 'product',
					'post__in'      => $product_ids
					);
				$loop = new WP_Query( $args );
				if ( $loop->have_posts() ) {
					echo '<div class="products-listing-items-wrapper woocommerce products-listing-grid"><h2 style="text-align: center;font-weight: 800" class="title">Recent Purchased Items</h2>';
					echo '<ul class="products products-loop row grid products-loop-column-4 mobile-portrait-1" data-column="4">';
					while ( $loop->have_posts() ) : $loop->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					echo '</ul>';
				} else {
					echo __( 'No products found' );
				}
				wp_reset_postdata();
				/*
				 * $out .= '<ul class="products products-loop row grid products-loop-column-3 ciyashop-products-shortcode mobile-col-1 mobile-portrait-1" data-column="3">';
				foreach($product_ids as $item_id)
				{
					
					$product = wc_get_product( $item_id );		
					wc_get_template_part( 'content', 'product' );
					$price = $product->get_price_html();
					$url = get_permalink($item_id);				
					$download = '<a href="'.$url.'">'.$product->get_title().'</a>';					
					$message = $item_id.$notification;
					$message = str_replace( '{fname}', $fname, $message );
					$message = str_replace( '{lname}', $lname, $message );
					$message = str_replace( '{product}', $download, $message );
					$message = str_replace( '{price}', $price, $message );
					$message = str_replace( '{time}', $time, $message );										
					$image = get_the_post_thumbnail( $item_id, array($img_size,$img_size), array('class' => 'alignleft') );
						$image = '<a href="'.$url.'">'.$image.'</a>';
					
					$out .=  '<li>'.$image.' '.$message.'</li>';
					
				}
				
				
				wp_reset_postdata();
				$out .= '</ul>';*/
				
				
			}
	}		
			return ob_get_contents();
			//return $out;
}
function rpi_recentitems_shortcode() { 
  

	return rpi_recentproductsloop();;
}
// register shortcode
add_shortcode('recentitems', 'rpi_recentitems_shortcode');

add_action( 'woocommerce_account_content', 'rpi_action_woocommerce_account_content' );
function rpi_action_woocommerce_account_content(  ) {
	
	if('on' != get_option('recentitems_on_dashboard'))
	{
		return true;
	}
    	rpi_recentproductsloop();

    //echo '<p>' . __("This is an addition…", "woocommerce") . '</p>';
}


function rpi_dashboard_page()
{
	?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
		        <div id="icon-themes" class="icon32"></div>  
		        <h2>Recent Purchased Items</h2>  
		         <div class="wrap">
        <h2>Recent Purchased Items Configure</h2>
        <form method="post" action="options.php">
            <?php settings_fields('recentpurchaseditems'); ?>
 
        <table class="form-table">
			<tr><td>ShortCode : [recentitems]</td>
			</tr>
            <tr>
                <th><label for="number_of_items_to_list">Number of items to list in block:</label></th>
 
                <td>
<input type = 'number'  min="1" max="15"  width="20" id="number_of_items_to_list" name="number_of_items_to_list" value="<?php echo esc_html(get_option('number_of_items_to_list')); ?>">
                </td>
            </tr>
 
            <tr>
                <th><label for="redirect_to_recentitems_dashboard">Recent items on dashboard:</label></th>
                <td>
				<?php
				$recentitems_on_dashboard = get_option('recentitems_on_dashboard');
				?>
				<input id="recentitems_on_dashboard" name="recentitems_on_dashboard" type="checkbox" <?php if ($recentitems_on_dashboard == 'on') { echo 'checked="checked"'; } ?> />

                </td>
            </tr>
 
        </table>
  
        <?php submit_button(); ?>
</form>
					 </div>
    
</div>

<?php
}
function recentpurchaseditems_plugin_register_settings() {
 
register_setting('recentpurchaseditems', 'number_of_items_to_list');
 
register_setting('recentpurchaseditems', 'recentitems_on_dashboard');
 
}
add_action('admin_init', 'recentpurchaseditems_plugin_register_settings');
?>