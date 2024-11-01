<?php
add_action('cron-welco',function (){

    if(get_option("majAutoCron") === 'yes'){
        welco_getCommandesWelcoApi();
    }

});

//TODO a commenter pour la prod juste là pour les test
/*add_filter('cron_schedules', function ($schedules){
    $schedules['ten_seconds']=[
        'interval' => 10,
        'display' => __('toutes les 10 secondes','plugin')
    ];
    return $schedules;
});*/

// pour supprimer le cron d'une heure pour les tests

/*if($timestamp = wp_next_scheduled('cron-welco')){
    wp_unschedule_event($timestamp, 'cron-welco');
}*/
if(!wp_next_scheduled('cron-welco')){
    //TODO a commenter juste là pour les test
  //  wp_schedule_event(time(),'ten_seconds', 'cron-welco');
    //TODO a décommenter pour la prod et a commentrer pour les tests
    wp_schedule_event(time(),'hourly', 'cron-welco');
}
