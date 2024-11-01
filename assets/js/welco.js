jQuery(function($) {
    $(document).ready(function () {
        $("<div id=\"welcoContainer\" style=\"min-height:586px; width:100%;display:none\"></div>").insertBefore('#payment');
        if ($('ul#shipping_method li').length >= 1 && $('ul#shipping_method li input:checked').val() == "welco") {
            displayWelco();
            gestionPopup();
        }

        $(document).on('change', '.shipping_method', function () {
            if ($('#shipping_method_0_welco:checked').length > 0) {
                displayWelco();
                gestionPopup();
            } else {
                $("#welcoContainer").hide();
                $("#payment #place_order").removeAttr('disabled');
            }
        });


        $('#billing_first_name').change(function () {
            customer_first_name = $('#billing_first_name').val();
        });
        $('#billing_last_name').change(function () {
            customer_last_name = $('#billing_last_name').val();
        });
        $('#billing_address_1').change(function () {
            customer_street_name = $('#billing_address_1').val();
        });
        $('#billing_postcode').change(function () {
            customer_postal_code = $('#billing_postcode').val();
        });
        $('#billing_address_2').change(function () {
            customer_additional_address = $('#billing_address_2').val();
        });
        $('#billing_city').change(function () {
            customer_city = $('#billing_city').val();
        });
        $('#billing_phone').change(function () {
            customer_phone = $('#billing_phone').val();
        });
        $('#billing_email').change(function () {
            customer_email = $('#billing_email').val();
        });
    });

    /**
     * Permet d'activer/désactiver le bouton pour valider le choix de livraison
     *
     * @param status
     */

    function welcoAllowOrder(status) {
        var submitName = 'test';

        var hookPayment = document.getElementById("HOOK_PAYMENT");
        if (status) {
            $("[name=" + submitName + "]").removeAttr("disabled");
            if (hookPayment && hookPayment.style) {
                hookPayment.style.display = "block";
            }
        } else {
            $("[name=" + submitName + "]").attr("disabled", "disabled");

            if (hookPayment && hookPayment.style) {
                hookPayment.style.display = "none";
            }
        }
    }

    /**
     * Affiche le contenu du Widget Welco s'il s'agit du mode de livraison choisit
     */
    function displayWelco() {
        if ($('ul#shipping_method li').length >= 1 && $('ul#shipping_method li input').val() == "welco") {
            var options = $("#shipping_method");
            if (options && 1) {
                $(".cart-collaterals #welcoContainer").show();
            }
            $("#welcoContainer").fadeIn("fast", function () {
                runWidgetWelco();
            });
            welcoAllowOrder(false);
            return;
            $("#welcoContainer").fadeOut("fast");
            welcoAllowOrder(true);
        } else {
            if ($('#shipping_method_0_welco:checked').length !== 0) {
                var checkedCarrier = $('#shipping_method_0_welco:checked').val();
                if (checkedCarrier === 'welco') {
                    var options = $("#shipping_method");
                    if (options && 1) {
                        $(".cart-collaterals #welcoContainer").show();
                    }
                    $("#welcoContainer").fadeIn("fast", function () {
                        runWidgetWelco();
                    });
                    welcoAllowOrder(false);
                    return;
                }
                $("#welcoContainer").fadeOut("fast");
                welcoAllowOrder(true);
            }
        }

    }

    /**
     * Init welco widget
     */
    function runWidgetWelco() {
        customer_first_name = $('#billing_first_name').val();
        customer_last_name = $('#billing_last_name').val();
        customer_street_name = $('#billing_address_1').val();
        customer_postal_code = $('#billing_postcode').val();
        customer_additional_address = $('#billing_address_2').val();
        customer_city = $('#billing_city').val();
        customer_phone = $('#billing_phone').val();
        customer_email = $('#billing_email').val();
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var welker = urlParams.get('welker');
        delete (WnG_startAddress);
        WnGWidget.run();
        if (Object.keys(WnG_selected).length === 0) {
            setTimeout(function () {
                jQuery('#payment #place_order').attr("disabled", "disabled");
            }, 1000);
        }
        document.addEventListener('wng_selection', function (e) {
            $("#payment #place_order").removeAttr('disabled');
            WnG_selected = e.welker;

            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                method: 'post',
                data: {
                    action: 'ajax_action',
                    nomWelker: WnG_selected.first_name,
                    prenomWelker: WnG_selected.last_name,
                    villeWelker: WnG_selected.city,
                    phone: WnG_selected.phone,
                    etablishment: WnG_selected.etablishment
                },
                success: function (response) {
                    console.log('success');
                },
                error: function (er) {
                    console.log('error');
                }
            });
            if (WnG_selected && WnG_selected._id) {
                welcoRegister(WnG_selected._id);
            } else {
                welcoAllowOrder(false);
            }
        });
        var ajaxRequest = null;

        function welcoRegister(welkerId) {
            if (welkerId) {
                var id = WnG_selected._id;
                var $selector = null;
                switch (id) {
                    case WELCO_REQUESTED:
                        $selector = $('.WnG_formWelco');
                        break;
                    case WELCO_SUGGESTED:
                        $selector = $('.WnG_formNeighbour');
                        break;
                }
                console.log(WnG_selected);
                if (ajaxRequest !== null) {
                    ajaxRequest.abort();
                    ajaxRequest = null;
                }
                ajaxRequest = $.ajax({
                    method: 'GET',
                    url: '/wp-admin/admin-ajax.php',
                    data: {
                        action: 'test_ajax',
                        'action_ajax_welco': 'welco_ajaxRegister',
                        //'welco_cart_id': welcoCartId,
                        'welker_id': welkerId,
                        'welco_token': welcoToken,
                        'comment': WnG_selected.data ? WnG_selected.data : null,
                        'welco_requested': WELCO_REQUESTED,
                        'welco_suggested': WELCO_SUGGESTED
                    },
                    dataType: 'json',
                    before: function () {
                        if (null !== $selector) {
                            $selector.find('.WnG_button').text('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-rolling" style="width: 40px;height: 40px;"><circle cx="50" cy="50" fill="none" stroke="#ff0090" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138" transform="rotate(215.876 50 50)"><animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 50;360 50 50" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"/></circle></svg>')
                        }
                    },
                    success: function (result) {
                        if (null !== $selector) {
                            console.log(result);
                            $selector.find('.WnG_button').text('Demande envoyée avec succès')
                            $selector.find('.WnG_button').addClass('WnG_notificationSuccess')
                        }
                        welcoAllowOrder(true);
                    },
                    error: function (er) {
                        welcoAllowOrder(false);
                    }
                });

            } else {
                welcoAllowOrder(false);
                alert('Votre welker n\'a pas été sauvegardé, merci d\'en sélectionner un autre.');
            }
        }
    }


    function gestionPopup() {
        $('.WnG_cardContent .WnG_card_link .WnG_moreInfo').removeAttr("href");
        $('#WnG_noNeighbours .WnG_cardContent .WnG_card_link .WnG_moreInfo').click(function () {
            $('#WnG_modalInfo2').css('display', 'block');
        });
        $('#WnG_modalInfo2 .WnG_modal-content-info .WnG_close').click(function () {
            $('#WnG_modalInfo2').css('display', 'none');
        });

        $('.WnG_cardContent .WnG_card_link .WnG_moreInfo').removeAttr("href");

        $('.WnG_propose-box .WnG_card .WnG_action').click(function () {
            $('.WnG_modal-propose').css('display', 'block');
        });

        $('.WnG_propose-box .WnG_card .WnG_cardContent div:not(.WnG_card_link)').click(function () {
            $('.WnG_modal-propose').css('display', 'block');
        });
        $('#WnG_modalPropose .WnG_modal-content .WnG_close').click(function () {
            $('.WnG_modal-propose').css('display', 'none');
        });

        $('.WnG_propose-box .WnG_cardContent .WnG_card_link .WnG_moreInfo').click(function () {
            $('#WnG_modalInfo').css('display', 'block');
        });
        $('#WnG_modalInfo .WnG_modal-content-info .WnG_close').click(function () {
            $('#WnG_modalInfo').css('display', 'none');
        });
    }
})




