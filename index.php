<?php

class Telebot
{

  /**
   * Telegram Bot API URL / endpoint
   */
  public static $api;

  /**
   * Bot token from @BotFather
   */
  public static $token;

  public function __construct(string $token, string $api="https://api.telegram.org/bot"){
    // Check bot token
    if (empty($token)) die("Bot token should not be empty!\n");
    
    self::$api = $api;
    self::$token = $token;
  }

  /** Bot **/
  public function Bot(string $method, array $parameters=array()){
    $url = self::$api . self::$token . '/';
    if (!$parameters) $parameters = array();
    $parameters['method'] = $method;

    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($request, CURLOPT_CONNETTIMEOUT, 7);
    curl_setopt($request, CURLOPT_TIMEOUT, 60);
    curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($request);
    curl_close($request);
    return($result);
  }
}

?>
