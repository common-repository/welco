<?php
add_action( 'admin_menu', 'welco_add_admin_page');
function welco_add_admin_page() {
    add_menu_page(
        'Welco', //Page Title
        'welco', //Menu Title
        'manage_options', //Capability
        'commandes-welco-parent', //Page slug
        'welco_admin_parameters_page_html' //Callback to print html
    );

    add_submenu_page(
        'commandes-welco-parent',
        'Commandes welco',
        'Commandes welco',
        'manage_options',
        'commandes-welco',
        'welco_admin_page_html'
    );
}

add_action('admin_enqueue_scripts', 'welco_import_css');
function welco_import_css() {
    wp_register_style( 'css-welco-orders', plugins_url('/assets/style.css', __FILE__));
    wp_enqueue_style( 'css-welco-orders' );
}
function welco_admin_page_html() {?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <button class="btn-cmd-welco" type="button" id="refresh">Rafaichir <div style="display:none ;" class="lds-ring"><div></div><div></div><div></div><div></div></div></button>
        <div id="tableau"><?php welco_getCommandesWelcoWoocommerce(); ?></div>
        <div class="welco-btn-footer">
            <button class="btn-cmd-welco" id="btn-print-update-all"> Imprimer les étiquettes et mettre à jour statut</button>
            <button class="btn-cmd-welco" id="btn-print-all"> Imprimer les étiquettes</button>
        </div>
        <script>
            let ajaxUrl = "<?php echo admin_url( 'admin-ajax.php' ) ?>";
            jQuery(function($){
                $('#refresh').on('click', function() {
                    jQuery('.lds-ring').css("display","block")
                    $.ajax({
                        method: "POST",
                        url: ajaxUrl,
                        data: { action: "refresh_welco" }
                    })
                        .done(function( msg ) {
                            $("#tableau").html(msg);
                            jQuery('.lds-ring').css("display","none")
                        });
                });
            })
            jQuery(function($){$(document).ready(function() {
                let imgs = [];
                $("#btn-print-all").click(function() {
                    welco_printEtiquette();
                });
                $("#btn-print-update-all").click(function() {
                    welco_printEtiquette();
                    let ids = []
                    $.each($("input[name='printEtiquette']:checked"), function() {
                        ids.push($(this).val());
                    });
                    $.ajax({
                        method: "POST",
                        url: ajaxUrl,
                        data: { action: "update_welco", ids: ids }
                    })
                        .done(function( msg ) {
                            $("#tableau").html(msg);
                        });
                });
                $("#select-all").change(function() {
                    if($("#select-all").prop('checked')){
                        $.each($("input[name='printEtiquette']"), function() {
                            $(this).prop("checked","checked")
                        });
                    }else{
                        $.each($("input[name='printEtiquette']"), function() {
                            $(this).prop("checked",false)
                        });
                    }
                });
                $("input[name='printEtiquette']").change(function (){
                    if(!$(this).prop('checked') &&  $("#select-all").prop('checked')){
                        $(this).prop('checked',false)
                        $("#select-all").prop('checked',false)
                    }else{
                        if($("input[name='printEtiquette']:checked").length == $("input[name='printEtiquette']").length){
                            $("#select-all").prop('checked',true)
                        }
                    }
                });
                function welco_printEtiquette(){
                    let nodeList = document.querySelectorAll(".order_checkbox:checked");
                    let selected = [];
                    nodeList.forEach(function(el) {
                        let img = el.closest('tr').querySelector('img');
                        if(img) {
                            imgs.push(img)
                        }
                    });
                    if (imgs.length <= 0) {
                        return alert('Aucune commande sélectionnée.');
                    }
                    let popup = window.open();
                    for (let i = 0; i < imgs.length; i++) {
                        let img = imgs[i].cloneNode();
                        let wrapper = popup.document.createElement('div');
                        wrapper.style.pageBreakAfter = 'always';
                        wrapper.style.pageBreakBefore = 'always';
                        wrapper.appendChild(img);
                        popup.document.body.appendChild(wrapper);
                    }
                    popup.focus(); //required for IE
                    popup.print();
                    imgs = [];
                }
            });})
        </script>
    </div>
    <?php
}
function welco_getCommandesWelcoWoocommerce(){
    $etatCommande= ['attente-welco'=> 'En attente Welco', 'en-cours-welco'=> 'En cours de préparation Welco','expediee-welco'=>'Commande expédiée','processing'=> 'En cours',
        'on-hold' => 'En attente', 'pending'=> 'Attente paiement', 'completed' => 'Terminée', 'cancelled' => 'Annulée', 'refunded' => 'Remboursée', 'failed'=>'Échouée'];
    $result = welco_wp_query();


    ?>
            <table class="table-welco" >
                <tr>
                    <th><input id="select-all" type="checkbox" name="select-all"></th>
                    <th>ID commande</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Adresse de livraison</th>
                    <th>Type</th>
                    <th>Etiquette</th>
                </tr>
                    <?php while ( $result->have_posts() ) {
                        $result->the_post();
                        $shipper = get_post_meta(get_the_ID());
                        $order= new WC_Order(get_the_ID());
                        $urlImage = wp_upload_dir()['baseurl']."/welco/images/".get_the_ID()."-".get_post_meta( get_the_ID(), 'delivery',  false )[0].".png";
                        ?>
                    <tr>
                        <td><input type="checkbox" class="order_checkbox" name="printEtiquette" value="<?php echo get_the_ID(); ?>"></td>
                        <td><?php echo esc_html(get_the_ID()); ?></td>
                        <td><?php echo esc_html($order->get_billing_last_name()." ". $order->get_billing_first_name());?></td>
                        <td><?php echo esc_html($etatCommande[$order->get_status()]); ?></td>
                        <?php if(get_post_meta(get_the_ID(), "welco_home")[0] == 1):  // afficher la bonne adresse en fonction de domicile ou welker ?>
                            <td><?php echo esc_html($order->get_billing_first_name())?> <?php echo esc_html($order->get_billing_last_name())?> <?php echo esc_html($order->get_billing_address_1())?> <?php echo esc_html($order->get_billing_address_2())?> <?php echo esc_html($order->get_billing_postcode())?> <?php echo esc_html($order->get_billing_city())?></td>
                        <?php else: ?>
                            <td><?php echo esc_html($shipper["welker_name"][0])?> <?php echo esc_html($shipper["welker_prenom"][0])?> <?php echo esc_html($shipper["welker_adresse"][0])?> <?php echo esc_html($shipper["welker_cp"][0])?> <?php echo esc_html($shipper["welker_ville"][0])?></td>
                        <?php endif; ?>

                        <?php if(get_post_meta(get_the_ID(), "welco_home")[0] == 1):  // afficher en fonction de si c'est welco ou home ?>
                            <td>Domicile</td>
                        <?php else: ?>
                            <td>Welker</td>
                        <?php endif; ?>
                        <!--<td><?php //$order->get_status() === $myFields["commandeEnAttenteWelco"]? '': '<a href="'.$urlImage.'" target="_blank"><img style="max-width : 100%;" src="'.$urlImage.'" alt=""></a>'; ?></td>-->
                        <td><?php if($order->get_status() != get_option("commandeEnAttenteWelco_welco")){ ?>
                            <a href="<?php echo esc_url($urlImage); ?>" target="_blank"><img style="max-width : 100%;" src="<?php echo esc_url($urlImage); ?>" alt=""></a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
    <?php
}
function welco_getCommandesWelcoApi(){
        $api = new ApiWelco();
    $result = welco_wp_query();
    $path = __DIR__.'/../../uploads/welco/images/';
    if(!file_exists($path)){
        mkdir("$path",0777,true) ;
    }
    while ( $result->have_posts() ) { // parcours des commandes en gestion
        $result->the_post();
        $order= new WC_Order(get_the_ID());
        $weight = 0;
        foreach ($order->get_items() as $item){ // on calcule le poids de la commande
            $weight += $item->get_product()->get_weight();
        }
        $idCommande = get_the_ID();
        $delivery = get_post_meta( get_the_ID(), 'delivery',  false );

        $dataApi = $api->getShipmentTrace($delivery[0]); // on va chercher l'étape de la commande dans l'API
        if(isset($dataApi['lastStatus'])){
           if ($dataApi['lastStatus'] === 'SHIPPING_NOTCREATED'){ // si on a pas encore créé la commande dans l'API et qu'on a pas l'étiquette
               $order->update_status(get_option("commandeStatusPretPourExpedition_welco"));
               $resultEtiquette = $api->createShipment([
                  "deliveryId" => $delivery[0],
                   "ref" =>get_the_ID(),
                   "shipper"=>  [
                       "name" => get_option( "boutiquename_welco"),
                       "phone" =>  get_option("tel_welco"),
                       "address" => [
                           "streetName" => get_option("adresse_welco"),
                           "city" => get_option("ville_welco"),
                           "country" => "FR",
                           "primaryPostalCode" => get_option("cp_welco")
                       ]
                   ],
                   "package" => [
                       "packageWeight" => $weight
                   ]
               ]);
               $imageName = $idCommande.'-'.$delivery[0].'.png';
               file_put_contents( $path.$imageName, base64_decode($resultEtiquette['label'])); // on sauvegarde l'étiquette

               if(isset($resultEtiquette['welker'])){
                   update_post_meta($idCommande,'welker_name',sanitize_text_field($resultEtiquette['welker']['last_name']));
                   update_post_meta($idCommande,'welker_prenom',sanitize_text_field($resultEtiquette['welker']['first_name']));
                   update_post_meta($idCommande,'welker_ville',sanitize_text_field($resultEtiquette['welker']['city']));
                   update_post_meta($idCommande,'welker_adresse',sanitize_text_field($resultEtiquette['welker']['street_name']));
                   update_post_meta($idCommande,'welker_tel',sanitize_text_field($resultEtiquette['welker']['phone']));
                   update_post_meta($idCommande,'welker_cp',sanitize_text_field($resultEtiquette['welker']['postal_code']));
                   if(isset($resultEtiquette['welker']['mail']))
                       update_post_meta($idCommande,'welker_mail',sanitize_text_field($resultEtiquette['welker']['mail']));
               }
            }
            if ($dataApi['lastStatus'] === 'SHIPPING_REQUESTED' && get_post_status() === get_option("commandeStatusPretPourExpedition_welco")){
                $order->update_status(get_option("commandeStatutExpediee_welco"));
            }
            if ($dataApi['lastStatus'] === 'SHIPPING_DELIVERED'){
                $order->update_status(get_option("commandeStatutLivree_welco"));
            }
        }
    }
}
add_action('wp_ajax_refresh_welco', 'refresh_welco');
add_action('wp_ajax_nopriv_refresh_welco', 'refresh_welco');
function refresh_welco(){
    welco_getCommandesWelcoApi();
    welco_getCommandesWelcoWoocommerce();
    die;
}

add_action('wp_ajax_update_welco', 'update_welco');
add_action('wp_ajax_nopriv_update_welco', 'update_welco');
function update_welco(){
    $post_id = $_POST['ids'];



        foreach ($post_id as $id) {
            $order = new WC_Order($id);
            if (("wc-" . $order->get_status()) === get_option("commandeStatusPretPourExpedition_welco"))
                $order->update_status(get_option("commandeStatutExpediee_welco"));
        }
    welco_getCommandesWelcoWoocommerce();
    die;
}
function welco_wp_query(){



    $args = [
        'post_type'=>'shop_order',
        'post_status' => [get_option("commandeEnAttenteWelco_welco"), get_option("commandeStatusPretPourExpedition_welco")],
        'posts_per_page' => '-1',
        'meta_query'=>[
            [
                'key' => 'delivery',
                'compare'=> '!=',
                'value' => ''
            ],
        ]
    ];

    return new WP_Query( $args );
}

function welco_idShipping(){
    $zones = WC_Shipping_Zones::get_zones();
    foreach($zones as $zone){
        foreach($zone as $method){
            if(is_array($method)){
                foreach ($method as $m){
                    if(is_a($m,'WC_Welco_Shipping_Method')){
                        if($m->id == 'welco'){
                            $idShipping = $m->get_instance_id();
                        }
                    }
                }
            }
        }
    }
    return $idShipping;
}

function multiple_welco_idShipping($datas){
    $shipping = [];
    $zones = WC_Shipping_Zones::get_zones();
    foreach($zones as $zone){
        foreach($zone as $method){
            if(is_array($method)){
                foreach ($method as $m){
                    foreach ($datas as $data){
                        if(is_a($m,$data["methode"])){
                            if($m->id == $data["id"]){
                                $shipping[] = $m->get_instance_id();
                            }
                        }
                    }
                }
            }
        }
    }
    return $shipping;
}
