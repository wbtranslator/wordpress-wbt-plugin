<?php

class WbtAdmin extends WbtAbstract
{
    public function init_menu ()
    {
        add_options_page(
            'Translation Settings',
            'WBT Settings',
            'manage_options',
            'wbt-settings',
            array($this, 'route')
        );
    }

    public function route()
    {
        $route = (empty($_GET['action'])) ? 'dashboard' : $_GET['action'];

        switch ($route){
            case 'dashboard':
                $this->render('dashboard.view.php', 'dashboard');
                break;

            case 'add_key':
                $this->render('dashboard.view.php', 'add_api_key');
                break;

            case 'export':
                $cnt = $this->container()->get('api')->export();
                echo 'Export ' . $cnt . ' abstract names.';
                break;

            case 'import':
                $cnt = $this->container()->get('api')->import();
                echo 'Import ' . $cnt . ' translation values.';
                break;

            default:
                $this->render('dashboard');
                break;
        }
    }

    public function render($page, $controller = null)
    {
        ob_start();
        $file = dirname(__FILE__) . '/views/' . strtolower($page);

        if( (file_exists($file)) && (method_exists($this, $controller)) ){
            extract($this->$controller());

            include $file;
            $ret = ob_get_contents();
            ob_end_clean();
            echo $ret;
        }else{
            exit(' Template not found!');
        }

        if (is_wp_error($this->getErrors())) {
            var_dump($this->getErrors());
        }
    }

    public function dashboard()
    {   //$this->log($this->getTerm(2));
        return array(
            'wbt_languages' => $this->container()->get('config')->getLanguages(),
            'api_key' => $this->container()->get('config')->getOption('api_key')
        );
    }

    public function add_api_key()
    {
        $languages = $this->container()->get('config')->getLanguages();

        if ( ( !empty($_POST['api_key'])  ) ) {
            $api_key = $_POST['api_key'];

            $this->container()->get('config')->setOption('api_key', $api_key);
            $this->container()->get('api')->init();

            return array(
                'wbt_languages' =>$languages,
                'api_key' => $api_key,
                'success' => array('Key was added')
            );
        } else {
            return array(
                'wbt_languages' => $languages,
                'api_key' => $this->container()->get('config')->getOption('api_key'),
                'errors' => array('Please set API key')
            );
        }        
    }
}