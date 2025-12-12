add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_outof_stock_check', 9, 5 );

function woocommerce_outof_stock_check() {
//echo 'script running '.product;
//echo get_the_ID();
product_id = get_the_ID();
//print_r(product);
//wp db connection
global wpdb;
// Custom alert message
// message = 'Product added to cart: ' . product_id;
// Display an alert using JavaScript
//echo "scriptalert('message');/script";
//get the product by the id
product = wc_get_product( product_id);
sku = product-get_sku();

product-set_stock_quantity(1);
product-save();
//check if this sku is already existing in the sku_stock table
sql = "SELECT * FROM `Sold_product` WHERE `sku` = 'sku'";
//echo "scriptalert('sql');/script";
output = wpdb-get_results(sql);
echo output;
if(output){
//echo 'out of stock';
product-set_stock_quantity(0);
product-save();

}

return true;
}