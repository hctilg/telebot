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

  /**
   * to be switched ON / OFF on CLI mode
   */
  public $debug;

  public function __construct(
    string $token,
    bool $debug=true,
    string $api="https://api.telegram.org/bot"
  ) {
    // Check php version
    if (version_compare(phpversion(), '5.4', '<'))
      die("It requires PHP 5.4 or higher. Your PHP version is " . phpversion() . "\n");

    // Check bot token
    if (empty($token))
      die("Bot token should not be empty!\n");
    
    $this->api = $api;
    $this->token = $token;
    $this->debug = !!$debug;
  }

  private static function curlFile($path) {
    // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
    // See: https://wiki.php.net/rfc/curl-file-upload
    if (function_exists('curl_file_create')) {
      return curl_file_create($path);
    } else {
      // Use the old style if using an older version of PHP
      return "@$path";
    }
  }

  private function send(string $method, array $args=[]) {
    $url = $this->api . $this->token;
    $params = !empty($args[0]) ? $args[0] : Null;
    if (!$params) $params = array();

    $upload = false;
    $actionUpload = ['sendPhoto', 'sendAudio', 'sendDocument', 'sendSticker', 'sendVideo', 'sendVoice'];

    if (in_array($method, $actionUpload)) {
      $field = str_replace('send', '', strtolower($method));
      
      if (is_file($params[$field])) {
        $upload = true;
        $params[$field] = self::curlFile($params[$field]);
      }
    }

    if (function_exists('curl_version')) {
      $ch = curl_init();
      $options = [
        CURLOPT_URL => "$url/$method",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false
      ];

      if (is_array($params)) $options[CURLOPT_POSTFIELDS] = $params;
      if ($upload) $options[CURLOPT_HTTPHEADER] = ['Content-Type: multipart/form-data'];
      curl_setopt_array($ch, $options);
      $result = curl_exec($ch);

      if (curl_errno($ch)) echo curl_error($ch) . PHP_EOL;
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
    } else {
      if ($upload)
        throw new Exception("Sorry, this service is not available because the current PHP version does not support the curl function. Please install first");

      $opts = [
        'http' => [
          'method' => "POST",
          'header' => 'Content-Type: application/x-www-form-urlencoded',
          'content' => http_build_query($params)
        ]
      ];

      $result = file_get_contents("$url/$method", false, stream_context_create($opts));
      if (!$result) return false;

      // need another review
      $httpcode = null;
    }

    if ($this->debug && $method != 'getUpdates') {
      echo "Method: $method" . PHP_EOL;
      echo "Data: " . print_r($params, true) . PHP_EOL;
      echo "Response: $result" .PHP_EOL;
    }

    if ($httpcode == 401) 
      throw new Exception('Incorect bot token');     
    else return json_decode($result, true);
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

  /**
   * to build keyboard from string
   * for example: 
   * $btn = Telebot::keyboard('[text] [contact|request_contact] [location|request_location]');
   * $bot->sendMessage(['chat_id'=> $chat_id, 'text'=> $text, 'reply_markup' => $btn]);
   */
  public static function keyboard(
    string $pattern,
    $input_field_placeholder = 'type here..',
    $resize_keyboard = true,
    $one_time_keyboard = true
  ) {
    if (preg_match_all('/\[[^\|\]]+\|?[^\|\]]+\]([^\n]+)?([\n]+|$)/', $pattern, $match)) {
      $arr = $match[0]; # array
      $keyboard = [];
      foreach ($arr as $list) {
        preg_match_all('/\[[^\|\]]+\|?[^\|\]]+\]/', $list, $new);
        $array = $new[0];
        $arrange = [];
        foreach ($array as $a) {
          $b = explode('|', $a);
          $x = [];
          foreach ($b as $c) $x[] = $c;
          $f  = trim(str_replace(['[',']'], '', $a));
          $b0 = trim(str_replace(['[',']'], '', $x[0]));
          $b1 = isset($x[1]) ? trim(str_replace(']', '', $x[1])) : '';
          $is_req = $b1 === "request_contact" || $b1 === "request_location";
          $btn = ["text" => $is_req ? $b0 : $f];
          if ($is_req) $btn[$b1] = true;
          $arrange[] = $btn;
        }
        $keyboard[] = $arrange;
      }
      return json_encode([
        "keyboard" => $keyboard,
        'resize_keyboard' => $resize_keyboard,
        'one_time_keyboard' => $one_time_keyboard,
        'input_field_placeholder' => $input_field_placeholder
      ]);
    }
  }
}

?>