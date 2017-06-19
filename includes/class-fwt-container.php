<?php

class FwtContainer
{
    protected $api;

    protected $config;

    protected $loader;

    protected $translator;

    protected $httpClient;

    public function getConfig()
    {
        if (null === $this->config) {
            require_once dirname( __FILE__ ) . '/class-fwt-config.php';
            $this->config = new FwtConfig($this);
        }
        return $this->config;
    }

    public function getApi()
    {
        if (null === $this->api) {
            require_once dirname( __FILE__ ) . '/class-fwt-api.php';
            $this->api = new FwtApi($this);
        }
        return $this->api;
    }

    public function getLoader()
    {
        if (null === $this->loader) {
            require_once dirname( __FILE__ ) . '/class-fwt-loader.php';
            $this->loader = new FwtLoader($this);
        }
        return $this->loader;
    }

    public function getTranslator()
    {
        if (null === $this->translator) {
            require_once dirname( __FILE__ ) . '/class-fwt-translator.php';
            $this->translator = new FwtTranslator($this);
        }
        return $this->translator;
    }

    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            require_once dirname( __FILE__ ) . '/class-fwt-http-client.php';
            $this->httpClient = new FwtHttpClient($this);
        }
        return $this->httpClient;
    }
}