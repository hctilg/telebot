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
    // Check php version
    if (version_compare(phpversion(), '5.4', '<')) {
      die("It requires PHP 5.4 or higher. Your PHP version is " . phpversion() . PHP_EOL);
    }

    // Check bot token
    if (empty($token)) {
      die("Bot token should not be empty!\n");
    }
    
    $this->api = $api;
    $this->token = $token;
  }

  private function send(string $method, array $args=[]) {
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

  /**
   * $bot = new Telebot("TOKEN");
   * $bot($method, $args);
   * for example: $bot('sendMessage', ['chat_id'=>$chat_id, 'text'=>$text]);
   */
  public function __invoke(string $method, array $args=[]) {
    return $this->send($method, $args);
  }

  /**
   * $bot = new Telebot("TOKEN");
   * $bot->$method($args);
   * for example: $bot->sendMessage(['chat_id'=>$chat_id, 'text'=>$text]);
   */
  public function __call(string $method, array $args=[]) {
    return $this->send($method, $args);
  }
}

?>