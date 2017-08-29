<?php

class WbtHttpClient extends WbtAbstract
{
    protected $baseUrl = '';
    
    protected $params = array(
        'method' => "GET",
        'user-agent' => 'WBTranslator'
    );
    
    public function __construct($container = null)
    {
        parent::__construct($container);
        
        if (defined('WBT_API_URL')) {
            $this->setBaseUrl(WBT_API_URL);
        }
    }
    
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    public function getBaseUrl()
    {
        return rtrim($this->baseUrl, '/') . '/';
    }
    
    public function getApiKey()
    {
        return $this->container()->get('config')->getApiKey();
    }

    public function getUrl($endpoint)
    {
        return $this->getBaseUrl() . $this->getApiKey() . '/' . ltrim($endpoint, '/');
    }
    
    public function remote($endpoint, $params = [])
    {
        $url = $this->getUrl($endpoint);

        $request = wp_remote_request($url, array_merge($this->params, $params));

        $code = wp_remote_retrieve_response_code( $request );
        $mesg = wp_remote_retrieve_response_message( $request );
        $body = json_decode(wp_remote_retrieve_body( $request ), true );

        if (200 != $code) {
            if (!empty($body['message'])) {
                if (is_array($body['message'])) {
                    $mesg = reset($body['message']);
                } else {
                    $mesg = $body['message'];
                }
            }
            throw new \Exception((!empty($mesg) ? $mesg : 'Unknown error!'), $code);
        }
        
        return $body;
    }

    public static function normalize_multipart_params($data)
    {
        $output = array();

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $output[] = ['name' => $key, 'contents' => $value];
                continue;
            }

            foreach($value as $multiKey => $multiValue) {
                $multiName = $key . '[' .$multiKey . ']' . (is_array($multiValue) ? '[' . key($multiValue) . ']' : '' ) . '';
                $output[] = ['name' => $multiName, 'contents' => (is_array($multiValue) ? reset($multiValue) : $multiValue)];
            }
        }

        return $output;
    }
}