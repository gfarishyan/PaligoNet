<?php

namespace Gfarishyan\PaligoNet;

class Configuration {
  /**
    * @var string username
    * Paligo.net username
    */
  protected string $username;

    /**
     * @var string $api_key
     * The paligo.net api password.
     */
  protected $api_key;

    /**
     * @var string $instance
     * Paligo.net instance name.
     */
  protected $instance;

  public function __construct(string $username, string $api_key, string $instance) {
    $this->username = $username;
    $this->api_key = $api_key;
    $this->instance = $instance;
  }

  public function getUrl() {
      //paligoapp.com/api/v2

     //return sprintf('https://%s.paligo.com/api/v2/', $this->instance);
     return sprintf('https://%s.paligoapp.com/api/v2/', $this->instance);
  }

  public function getUsername() :string {
      return $this->username;
  }

  public function getApiKey() :string {
      return $this->api_key;
  }

  public function getAuthenticationHeader() {
     return base64_encode($this->username.':'.$this->api_key);
  }
}