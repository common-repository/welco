<?php


class ApiWelco
{
    private static $instance;

    private $url;

    private $headers = [
        'Content-Type: application/json',
    ];

    public $opts = [
        'http' => [
            'timeout' => '20',
            'ignore_errors' => true
        ],
    ];

    private $endpoints = [
        'createShipment' => '/shipment/create',
        'getShipment' => '/shipment/get/',
        'getShipmentTrace' => '/shipment/getTrace/',
        'getWelker' => '/widget/welker/',
        'linkTo' => '/widget/linkTo/',
        'captureErrorEvent' => '/widget/sentry/capture-event',
    ];

    public function __construct()
    {
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
        $my_fields = get_option('woocommerce_welco_'.$idShipping.'_settings');
        $welcoKey = get_option('welcokey_welco');
        $welcoApiUrl = get_option('welcoapiurl_welco');

        $this->welco_setHeader('Authorization: '.$welcoKey);
        $headers = $this->welco_formatHeaders();
        $this->opts['http']['header'] = $headers;
      //  $this->url = 'https://api.preprod.welco.io';
        $this->url = $welcoApiUrl;

    }

    /**
     * @param string $value
     */
    public function welco_setHeader($value)
    {
        $this->headers[] = $value;
    }

    /**
     * @return string
     */
    private function welco_formatHeaders()
    {
        return join("\r\n", $this->headers);
    }

    /**
     * @return ApiWelco
     */
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $deliveryId
     */
    public function createShipment($postdata)
    {
        $this->opts['http']['method'] = 'POST';
        $this->opts['http']['content'] = json_encode($postdata);
        $url = $this->url.$this->endpoints['createShipment'];

        return $this->request($url);
    }

    /**
     * @param string $deliveryId
     */
    public function getShipment($deliveryId)
    {
        $this->opts['http']['method'] = 'GET';
        $url = $this->url.$this->endpoints['getShipment'].$deliveryId;

        return $this->request($url);
    }

    /**
     * @param string $deliveryId
     */
    public function getShipmentTrace($deliveryId)
    {
        $this->opts['http']['method'] = 'GET';
        $url = $this->url.$this->endpoints['getShipmentTrace'].$deliveryId;
        return $this->request($url);
    }

    /**
     * @param $welkerId
     *
     * @return mixed
     */
    public function getWelker($welkerId)
    {
        $this->opts['http']['method'] = 'GET';
        $url = $this->url.$this->endpoints['getWelker'].$welkerId;
        return $this->request($url);
    }

    /**
     * @param $welkerId
     * @param $postdata
     *
     * @return mixed
     */
    public function linkTo($welkerId, $postdata)
    {
        $this->opts['http']['method'] = 'POST';
        $this->opts['http']['content'] = json_encode($postdata);
        $url = $this->url.$this->endpoints['linkTo'].$welkerId;
        return $this->request($url);
    }

    /**
     * @param $postdata
     *
     * @return mixed
     */
    public function captureErrorEvent($postdata)
    {
        $this->opts['http']['method'] = 'POST';
        $this->opts['http']['content'] = json_encode($postdata);

        $url = $this->url.$this->endpoints['captureErrorEvent'];

        return $this->request($url);
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function request($url)
    {
        $context = stream_context_create($this->opts);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, true);
        return $response;
    }
}
