<?php

// Ajout de la méthode de livraison à domicile

add_action( 'woocommerce_shipping_init', 'welco_shipping_method_home' );
function welco_shipping_method_home() {
    if ( ! class_exists( 'WC_Welco_Shipping_Method_Home' ) ) {
        class WC_Welco_Shipping_Method_Home extends WC_Shipping_Method {
            public function __construct( $instance_id = 0 ) {
                $this->instance_id 	  = absint( $instance_id );
                $this->id                 = 'welco-home';//this is the id of our shipping method
                $this->method_title       = __( 'Livraison Welco à domicile', 'welco' );
                $this->method_description = __( 'Livraison Welco à domicile', 'welco' );
                //add to shipping zones list
                $this->supports = array(
                    'shipping-zones',
                    //'settings', //use this for separate settings page
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->title = __( 'Livraison Welco à domicile', 'welco' );
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

    add_filter( 'woocommerce_shipping_methods', 'add_welco_shipping_method_home' );
    function add_welco_shipping_method_home( $methods ) {
        $methods['welco-home'] = 'WC_Welco_Shipping_Method_Home';
        return $methods;
    }
}
