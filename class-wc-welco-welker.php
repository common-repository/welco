<?php
add_action( 'woocommerce_shipping_init', 'welco_shipping_method' );
function welco_shipping_method() {
    if ( ! class_exists( 'WC_Welco_Shipping_Method' ) ) {
        class WC_Welco_Shipping_Method extends WC_Shipping_Method {
            public function __construct( $instance_id = 0 ) {
                $this->instance_id 	  = absint( $instance_id );
                $this->id                 = 'welco';//this is the id of our shipping method
                $this->method_title       = __( 'Livraison Welco', 'welco' );
                $this->method_description = __( 'Livraison Welco', 'welco' );
                //add to shipping zones list
                $this->supports = array(
                    'shipping-zones',
                    //'settings', //use this for separate settings page
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->title = __( 'Livraison Welco', 'welco' );
                $this->enabled = 'yes';
                $this->init();
            }

            function init() {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            function init_form_fields() {
                $this->instance_form_fields = array(

                    'title' => array(
                        'title' => __( 'Titre', 'welco' ),
                        'type' => 'text',
                        'description' => __( 'Title affiché sur le site', 'welco' ),
                        'default' => __( 'Expédition Welco', 'welco' )
                    ),

                    'cost' => array(
                        'title' => __( 'Coût (€)', 'welco' ),
                        'type' => 'number',
                        'description' => __( 'Coût de l\'expédition', 'welco' ),
                        'default' => 4
                    ),
                );
            }

            public function calculate_shipping( $package = array()) {
                $intance_settings =  $this->instance_settings;
                $this->add_rate( array(
                        'id'      => $this->id,
                        'label'   => $intance_settings['title'],
                        'cost'    => $intance_settings['cost'],
                        'package' => $package,
                        'taxes'   => false,
                    )
                );
            }
        }
    }

    add_filter( 'woocommerce_shipping_methods', 'add_welco_shipping_method' );
    function add_welco_shipping_method( $methods ) {
        $methods['welco'] = 'WC_Welco_Shipping_Method';
        return $methods;
    }
}

// Register new status
function register_en_attente_welco_order_status() {
    register_post_status( 'wc-attente-welco', array(
        'label'                     => 'En attente welco',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'En attente welco (%s)', 'En attente welco (%s)' )
    ) );
}
add_action( 'init', 'register_en_attente_welco_order_status' );


// Add to list of WC Order statuses
function add_en_attente_welco_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-attente-welco'] = 'En attente welco';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_en_attente_welco_to_order_statuses' );


// ajout status en cours de préparation WELCO
function register_en_cours_de_preparation_welco_order_status() {
    register_post_status( 'wc-en-cours-welco', array(
        'label'                     => 'En cours de préparation welco',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'En cours de préparation (%s)', 'En cours de préparation (%s)' )
    ) );
}
add_action( 'init', 'register_en_cours_de_preparation_welco_order_status' );

function add_en_cours_de_preparation_welco_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-en-cours-welco'] = 'en cours de préparation';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_en_cours_de_preparation_welco_to_order_statuses' );

// ajout status commande expédiée WELCO
function register_expediee_welco_order_status() {
    register_post_status( 'wc-expediee-welco', array(
        'label'                     => 'Commande expédiée welco',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Commande expédiée (%s)', 'Commandes expédiées (%s)' )
    ) );
}
add_action( 'init', 'register_expediee_welco_order_status' );

function add_expediee_welco_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-expediee-welco'] = 'Commande expédiée';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_expediee_welco_to_order_statuses' );

// ajout status commande livrée WELCO
function register_livree_welco_order_status() {
    register_post_status( 'wc-livree-welco', array(
        'label'                     => 'En cours de préparation welco',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Commande livrée (%s)', 'Commandes livrées (%s)' )
    ) );
}
add_action( 'init', 'register_livree_welco_order_status' );

function add_livree_welco_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-livree-welco'] = 'Commande livrée';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_livree_welco_to_order_statuses' );
