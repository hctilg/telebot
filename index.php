<?php

class Telebot {

  /**
   * Telegram Bot API URL / endpoint
   */
  public $api;

  /**
   * Bot token from @BotFather
   */
  public $token;

  public function __construct(string $token, string $api="https://api.telegram.org/bot") {
    // Check bot token
    if (empty($token)) die("Bot token should not be empty!\n");
    
    $this->api = $api;
    $this->token = $token;
  }

  public function __call(string $method, array $args=array()) {
    $url = $this->api . $this->token . '/';
    $params = !empty($args[0]) ? $args[0] : Null;
    if (!$params) $params = array();
    $params['method'] = $method;
    
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($request, CURLOPT_CONNETTIMEOUT, 7);
    curl_setopt($request, CURLOPT_TIMEOUT, 60);
    curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = curl_exec($request);
    curl_close($request);

    return($result ? json_decode($result, true) : false);
  }
}

?>