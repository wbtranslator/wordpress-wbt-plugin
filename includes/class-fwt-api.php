<?php

/**
 * The api functionality of the plugin.
 */

class Fwt_Api
{
    const API_URL = 'http://192.168.88.149:8080/api/v2/';

    protected $api_key;

    protected $errors;

    protected $params = array(
        'method' => "GET",
        'user-agent' => 'WEB Translator'
    );

    private $config;

    private $translate;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $config, $translate )
    {
        $this->config = $config;
        $this->translate = $translate;
    }

    public function sync()
    {
        $project = $this->remote_request('project/' . $this->get_api_key());

        if (isset($project['data']['language'])) {
            $this->config->set_option('default_language', $project['data']['language']);
        }

        if (isset($project['data']['languages'])) {
            $this->config->set_option('languages', $project['data']['languages']);
        }

        $this->config->set_option('updated_at', time());


    }

    public function refresh()
    {
        $posts = $this->translate->get_posts();

        if (!empty($posts)) {
            foreach ($posts as $post) {
                foreach ($post as $k => $v) {
                    $post[$k] = $this->translate->join($v);
                }

                wp_update_post($post);
            }
        }

        $this->create_tasks();
        $this->get_translations();
    }

    public function get_translations()
    {
        $url = 'project/' . $this->get_api_key() . '/translations/';

        $languages = $this->config->get_languages();

        $tasks = $this->config->get_option('tasks');

        foreach ($languages as $language) {
            $data = $this->remote_request($url . $language['id']);

            if( is_wp_error( $data ) ){
                echo $data->get_error_code() . $data->get_error_message();
                break;
            }

            if (!empty($data['data']['data'])) {
                foreach ($data['data']['data'] as $val) {
                    //$this->dump($val);

                    if (!empty($tasks[$val['name']]) && !empty($val['translation'])) {
                        $t = $tasks[$val['name']];

                        $row = array('ID' => $t['id']);

                        switch ($t['type']) {
                            case 'post_title': $row['post_title'] = $val['translation']['value'];
                                break;
                            case 'post_content': $row['post_content'] = $val['translation']['value'];
                                break;
                        }
$this->dump($row);
                        wp_update_post( $row );
                    }
                }
            }
        }
    }

    public function create_tasks()
    {
        $url = 'project/' . $this->get_api_key() . '/tasks/create';

        $default_language = $this->config->get_option('default_language');
        $default_language = $default_language['code'];

        $tasks = $this->config->get_option('tasks');

        if (empty($tasks)) {
            $tasks = array();
            $this->config->set_option('tasks', $tasks);
        }

        $posts = $this->translate->get_posts();

        if (!empty($posts)) {
            foreach ($posts as $post) {
                if (!empty($post['post_content'][$default_language])) {
                    $task = array(
                        'id' => $post['ID'],
                        'type' => 'post_content',
                    );

                    $key = md5(serialize($task));

                    $this->remote_request($url, array(
                        'method' => 'POST',
                        'body' => array(
                            'name' => $key,
                            'value' => $post['post_content'][$default_language],
                        )
                    ));

                    $tasks[$key] = $task;
                }

                if (!empty($post['post_title'][$default_language])) {
                    $task = array(
                        'id' => $post['ID'],
                        'type' => 'post_title',
                    );

                    $key = md5(serialize($task));

                    $this->remote_request($url, array(
                        'method' => 'POST',
                        'data' => array(
                            'name' => $key,
                            'value' => $post['post_title'][$default_language],
                        )
                    ));

                    $tasks[$key] = $task;
                }
            }
        }
        $this->dump($tasks);
        $this->config->set_option('tasks', $tasks);
    }

    public function remote_request($type, $params = [])
    {
        $url = rtrim(self::API_URL, '/') . '/' . $type;

        $request = wp_remote_request($url, array_merge($this->params, $params));

        $code = wp_remote_retrieve_response_code( $request );
        $mesg = wp_remote_retrieve_response_message( $request );
        $body = json_decode(wp_remote_retrieve_body( $request ), true );

        if ( 200 != $code && !empty( $mesg ) ) {
            return new WP_Error($code, $mesg);
        } elseif ( 200 != $code ) {
            return new WP_Error($code, 'Unknown error!');
        } elseif( !$body ) {
            return new WP_Error('nodata', 'Data not found.');
        } else {
            return $body;
        }
    }

    public function get_api_key()
    {
        if (null === $this->api_key) {
            $this->api_key = $this->config->get_option('api_key');
        }
        return $this->api_key;
    }

    public function dump($data)
    {
        print "<pre>" . print_r($data, true) . "</pre>";
    }
}