<?php
/*
Plugin Name: Welco
Description: Envoyez les colis à vos voisins pour ne jamais rater leur arrivé
Version: 1.1.3
Author: Welco
Author URI: http://welco.io/
*/


require('class-wc-welco-home.php');
require('class-wc-welco-welker.php');
require_once('Api.php');
require_once('welco-orders.php');
require_once('cron.php');
require_once(__DIR__."/template/admin-parameters.php");

wp_localize_script('ajax_main', 'ajax_js', array(
    'themeurl' => get_stylesheet_directory_uri(),
    'ajaxurl'  => admin_url('admin-ajax.php'),
    'siteurl'  => site_url()
));



add_action('woocommerce_checkout_order_processed', 'welco_action_checkout_order_processed', 10, 3);
function welco_action_checkout_order_processed( $order_id, $posted_data, $order ) {
    session_start();

    $shipping_method = @array_shift($order->get_shipping_methods());
    $shipping_method_id = $shipping_method['method_id'];


    if($shipping_method_id != "welco" && $shipping_method_id != "welco-home"){
        return;
    }

    if($_SESSION['welker_id'] != null) {
        update_post_meta($order_id, 'welker_id', sanitize_text_field($_SESSION['welker_id']));
        update_post_meta($order_id, 'welker_name', sanitize_text_field($_SESSION['welker_name']));
        update_post_meta($order_id, 'welker_prenom', sanitize_text_field($_SESSION['welker_prenom']));
        update_post_meta($order_id, 'welker_ville', sanitize_text_field($_SESSION['welker_ville']));
        update_post_meta($order_id, 'welker_mail', sanitize_text_field($_SESSION['welker_mail']));
        update_post_meta($order_id, 'welker_adresse', sanitize_text_field($_SESSION['welker_adresse']));
        update_post_meta($order_id, 'welker_tel', sanitize_text_field($_SESSION['welker_tel']));
        update_post_meta($order_id, 'welker_cp', sanitize_text_field($_SESSION['welker_cp']));
        update_post_meta($order_id, 'comment_etablishment', sanitize_text_field($_SESSION['comment_etablishment']));
        update_post_meta($order_id, 'comment_firstname', sanitize_text_field($_SESSION['comment_firstname']));
        update_post_meta($order_id, 'comment_lastname', sanitize_text_field($_SESSION['comment_lastname']));
        update_post_meta($order_id, 'comment_phone', sanitize_text_field($_SESSION['comment_phone']));
        $shippingDetail['comment']['etablishment'] = sanitize_text_field($_SESSION['comment_etablishment']);
        $shippingDetail['comment']['firstname'] = sanitize_text_field($_SESSION['comment_firstname']);
        $shippingDetail['comment']['lastname'] = sanitize_text_field($_SESSION['comment_lastname']);
        $shippingDetail['comment']['phone'] = sanitize_text_field($_SESSION['comment_phone']);
    }else{
        update_post_meta($order_id,'welco_home',true);
    }


    session_destroy();

}

add_action('woocommerce_order_status_processing', 'payment_complete_callback');
function payment_complete_callback($order_id)
{

    $order = wc_get_order($order_id);

    $shipping_method = @array_shift($order->get_shipping_methods());
    $shipping_method_id = $shipping_method['method_id'];


    if($shipping_method_id != "welco" && $shipping_method_id != "welco-home"){
        return;
    }

    $weight = 0;
    foreach ($order->get_items() as $item){
        $weight += $item->get_product()->get_weight();
    }


    $api = new ApiWelco();

    $postdata = [
        "ref" => $order_id,
        "shopper" => [
            "email" => $order->get_billing_email(),
            "phone" =>  $order->get_billing_phone(),
            "firstName" => $order->get_billing_first_name(),
            "lastName" => $order->get_billing_last_name(),
            "name" => $order->get_billing_first_name(). " ".$order->get_billing_last_name(),
            "address" => [
                "streetNumber" => "",
                "streetName" => $order->get_billing_address_1(),
                "additionnalAddress" => $order->get_billing_address_2(),
                "city" => $order->get_billing_city(),
                "country" => "FR",
                "primaryPostalCode" => $order->get_billing_postcode(),
            ]
        ],
        "package" => [
            "packageWeight" => $weight,
        ]
    ];

    if(get_post_meta(get_the_ID(), "welco_home")[0] == 1) {
        $response = $api->linkTo("", $postdata);
    }else {
        $response = $api->linkTo(get_post_meta($order_id, 'welker_id')[0], $postdata);
    }

    update_post_meta($order_id,'delivery',sanitize_text_field($response["delivery"]));
    $order->update_status(get_option("commandeEnAttenteWelco_welco"));
    welco_getCommandesWelcoApi();
}


add_action('wp_ajax_nopriv_ajax_action', 'welco_ajax_action');
add_action('wp_ajax_ajax_action', 'welco_ajax_action');
function welco_ajax_action(){
    session_start();
    $_SESSION['welker_name'] = sanitize_text_field($_POST['nomWelker']);
    $_SESSION['welker_prenom'] = sanitize_text_field($_POST['prenomWelker']);
    $_SESSION['welker_ville'] = sanitize_text_field($_POST['villeWelker']);

    die();
}

add_action('wp_ajax_nopriv_test_ajax', 'welco_test_ajax');
add_action('wp_ajax_test_ajax', 'welco_test_ajax');
function welco_test_ajax(){
    $params = [
        'welker_id' => sanitize_text_field($_GET['welker_id']),
        'welco_token' => urlencode(sanitize_text_field($_GET['welco_token'])),
        'welco_requested' => sanitize_text_field($_GET['welco_requested']),
        'welco_suggested' => sanitize_text_field($_GET['welco_suggested']),
        'comment' => sanitize_text_field($_GET['comment'])
    ];
    $result = welco_ajaxRegister($params);
    die();
}

add_action('wp_enqueue_scripts', 'welco_callback_for_setting_up_scripts');
function welco_callback_for_setting_up_scripts() {
    wp_register_style( 'namespace', plugins_url('/assets/style.css', __FILE__));
    wp_enqueue_style( 'namespace' );
    wp_enqueue_script( 'namespace', plugins_url('/assets/js/welco.js', __FILE__), array('jquery'));
    wp_enqueue_script( '', get_option("welcowidgeturl_welco").'/widget.js',array(),false,true);
}


function head_welco(){?>
    <script type="text/javascript">
        var WnG_MapBoxToken = '<?php echo esc_js(get_option("mapboxkey_welco"));?>';
        var welcoToken = '<?php echo esc_js(get_option("welcokey_welco"));?>';
        var WnG_container = "welcoContainer";
        var WnG_delay = 2;
        var WELCO_REQUESTED = -1;
        var WELCO_SUGGESTED = -2;
        var WnG_selected = {};
        var welcoBaseDir = "<?php echo esc_url(plugins_url('welco-woocommerce'), __FILE__); ?>"
        //var welcoCartId = "82";
        var WnG_merchantToken = "<?php echo esc_js(get_option("welcokey_welco"));?>";
        var WnG_startAddress = "Paris";
        var customer_first_name = "";
        var customer_email = "";
        var customer_phone = "";
        var customer_last_name = "";
        var customer_street_name = "";
        var customer_additional_address = "";
        var customer_postal_code = "";
        var customer_city = "";
        var welcoCartId = "";
    </script>
<?php }

add_action('wp_head','head_welco');

add_action( 'woocommerce_admin_order_data_after_billing_address', 'welco_my_custom_checkout_field_display_admin_order_meta', 10, 1 );
function welco_my_custom_checkout_field_display_admin_order_meta( $order ){
    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    if(isset(get_post_meta($order_id, 'welker_id')[0])){
        echo '<p><strong>'.__('Nom voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_name', true ) . '</p>';
        echo '<p><strong>'.__('Prénom voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_prenom', true ) . '</p>';
        echo '<p><strong>'.__('Mail voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_mail', true ) . '</p>';
        echo '<p><strong>'.__('Tel voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_tel', true ) . '</p>';
        echo '<p><strong>'.__('Adresse voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_adresse', true ) . '</p>';
        echo '<p><strong>'.__('Ville voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_ville', true ) . '</p>';
        echo '<p><strong>'.__('Code postal voisin (Livraison Welco)').':</strong> ' . get_post_meta( $order_id, 'welker_cp', true ) . '</p>';
        echo '<p><strong>'.__('Établissement voisin (Livraison Welco - Voisin suggéré)').':</strong> ' . get_post_meta( $order_id, 'comment_etablishment', true ) . '</p>';
        echo '<p><strong>'.__('Nom voisin (Livraison Welco - Voisin suggéré)').':</strong> ' . get_post_meta( $order_id, 'comment_firstname', true ) . '</p>';
        echo '<p><strong>'.__('Prénom voisin (Livraison Welco - Voisin suggéré)').':</strong> ' . get_post_meta( $order_id, 'comment_lastname', true ) . '</p>';
        echo '<p><strong>'.__('Tel voisin (Livraison Welco - Voisin suggéré)').':</strong> ' . get_post_meta( $order_id, 'comment_phone', true ) . '</p>';
    }

    $shipping_method = @array_shift($order->get_shipping_methods());
    $shipping_method_id = $shipping_method['method_id'];

    if($shipping_method_id == "welco-home"){
        echo '<p><strong>'.__('delivery').':</strong> ' . get_post_meta( $order_id, 'delivery', true ) . '</p>';
    }

}


function welco_ajaxRegister($params)
{
    /*global $woocommerce;
    $cart = $woocommerce->cart->get_cart();
    $cart = WC()->cart;*/
    $welkerId = $params['welker_id'];
    $result = [];
    $isUpdateNeeded = false;
    $shippingDetail = [];
    $api = new ApiWelco();
    if (empty($welkerId)) {
        return ['response' => 'empty welker'];
    }
    //$ps_address = new \Address($cart->id_address_delivery);
   // $shippingDetail['comment_etablishment'] = $params['comment']['etablishment'];
    if(!empty($params['comment'])){
        $shippingDetail['comment_etablishment'] = $params['comment']['etablishment'];
        $shippingDetail['comment_firstname'] = $params['comment']['lastname'];
        $shippingDetail['comment_lastname'] = $params['comment']['firstname'];
        $shippingDetail['comment_phone'] = $params['comment']['phone'];
    }

    if ($params['welco_requested'] == (int) $welkerId || $params['welco_suggested'] == (int) $welkerId) {
        if($params['welco_suggested'] == (int) $welkerId){
        }
        else{
            $shippingDetail['shop_name'] = '';
        }

        //$shippingDetail['id_country'] = $ps_address->id_country;
        $result['response'] = 'success';
        $isUpdateNeeded = true;
    }
    else {
        $response = $api->getWelker($welkerId);
        if (!empty($response) && 'success' === $response['result']) {
            $data = $response['data'];
            $shippingDetail['shop_name'] = $data['last_name'].' '.$data['first_name'];
            $shippingDetail['address1'] = $data['street_number'].' '.$data['street_name'];
            $shippingDetail['address2'] = $data['additional_address'];
            $shippingDetail['postal_code'] = $data['postal_code'];
            $shippingDetail['city'] = $data['city'];
            $shippingDetail['gsm'] = $data['phone'];
            $shippingDetail['mail'] = $data['email'];
            //$shippingDetail['id_country'] = $ps_address->id_country;
            $result['response'] = 'success';
            $isUpdateNeeded = true;
        }
        else {
            return ['response' => 'getWelker : empty or not success'];
        }
    }
    if ($isUpdateNeeded) {
        session_start();

        $_SESSION['welker_mail'] = $shippingDetail['mail'];
        $_SESSION['welker_tel'] = $shippingDetail['gsm'];
        $_SESSION['welker_adresse'] = $shippingDetail['address1'];
        $_SESSION['welker_cp'] = $shippingDetail['postal_code'];
        $_SESSION['comment_etablishment'] = $shippingDetail['comment_etablishment'];
        $_SESSION['comment_firstname'] = $shippingDetail['comment_firstname'];
        $_SESSION['comment_lastname'] = $shippingDetail['comment_lastname'];
        $_SESSION['comment_phone'] =  $shippingDetail['comment_phone'];
        $_SESSION['welker_id'] = $welkerId;
    }

    echo $welkerId;


    return $result;
}














