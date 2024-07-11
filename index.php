<?php

/**
 * function to check string starting (with given substring)
 */
function startsWith(string $startString, string $string) {
  $len = strlen($startString);
  return (substr($string, 0, $len) === $startString);
}

/**
 * function to download file
 * @return bool
 */
function download_file($url, $path, $chunk_size) {
  // disabled time limit
  set_time_limit(0);
  try {
    // open file for read
    $file_handle = fopen($url, 'rb');
    if (!!$file_handle) {
      // open file for write
      $output_handle = fopen($path, 'wb');
      while (!feof($file_handle)) {
        // read a chunk from file
        $chunk = fread($file_handle, $chunk_size);
        // write a readed chunk in outlet file
        fwrite($output_handle, $chunk);
      }

      // close files
      fclose($output_handle);
      fclose($file_handle);
      return true;
    }
  } catch (Exception $e) { }
  return false;
}

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

  /**
   * version of this code
   */
  protected static $version = '2.0';

  /**
   * array of events (types) and the responds
   */
  protected $events = [];

  /**
   * list of events (types)
   * see: https://core.telegram.org/bots/api#message
   */
  public static $types = [
    'text',
    'animation',
    'audio',
    'document',
    'photo',
    'sticker',
    'video',
    'video_note',
    'voice',
    'contact',
    'dice',
    'game',
    'poll',
    'venue',
    'location',
    'chat_join_request',
    'my_chat_member',
    'new_chat_members',
    'left_chat_members',
    'new_chat_title',
    'new_chat_photo',
    'delete_chat_photo',
    'group_chat_created',
    'supergroup_chat_created',
    'channel_chat_created',
    'message_auto_delete_timer_changed',
    'migrate_to_chat_id',
    'migrate_from_chat_id',
    'pinned_message',
    'invoice',
    'successful_payment',
    'connected_website',
    'passport_data',
    'proximity_alert_triggered',
    'voice_chat_scheduled',
    'voice_chat_started',
    'voice_chat_ended',
    'voice_chat_participants_invited',
    'inline_query',
    'callback_query',
    'edited_message',
    'channel_post',
    'edited_channel_post',
  ];

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

  /**
   * Download file with file_id
   * @param string $file_id
   * @param string $path       default: auto
   * @param        $chunk_size default: (1MB)
   * @return bool
   */
  public function download(string $file_id, string $path='auto', $chunk_size=(1024**2)) {
    if (empty($file_id) || empty($path) || $chunk_size < 1) return false;
    $file_data = $this->getFile(['file_id' => "$file_id"]);
    if (!$file_data['ok']) return false;
    $file_path = $file_data['result']['file_path'];
    if ($path == 'auto') {
      $lp = explode('/', $file_path);
      $path = $lp[count($lp)-1];
    }
    $file_url = "https://api.telegram.org/file/bot" . $this->token . "/$file_path";
    return !!download_file($file_url, $path, $chunk_size);
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
      echo "\nMethod: $method \n";
      echo "Data: " . json_encode($params) . "\n";
      echo "Response: $result \n";
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
   * delete pending updates
   */
  private function clear_pending_updates() {
    // create curl handle
    $ch = curl_init();

    // set url to request
    curl_setopt($ch, CURLOPT_URL, "{$this->api}{$this->token}/setWebhook?url=etc&drop_pending_updates=true");

    // set option to return the response as a string instead of outputting it
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // execute curl request
    curl_exec($ch);

    // close curl handle
    curl_close($ch);
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

  /**
   * to build inline_keyboard from string
   * for example: 
   * $btn = Telebot::inline_keyboard('[text] [text|callback_data] [text|url:link] [text|switch_inline_query:query] [text|switch_inline_query_current_chat:query]');
   * $bot->sendMessage(['chat_id'=> $chat_id, 'text'=> $text, 'reply_markup' => $btn]);
   */
  public static function inline_keyboard(string $pattern) {
    if (preg_match_all('/\[[^\|\]]+\|?[^\|\]]+\]([^\n]+)?([\n]+|$)/', $pattern, $match)) {
      $arr = $match[0]; #array
      $inline_keyboard = [];
      foreach ($arr as $list) {
        preg_match_all('/\[[^\|\]]+\|?[^\|\]]+\]/', $list, $new);
        $array = $new[0];
        $arrange = [];
        foreach ($array as $a) {
          $b = explode('|', $a);
          $x = [];
          foreach ($b as $c) $x[] = $c;
          $b0 = trim(str_replace(['[',']'], '', $x[0]));
          $b1 = isset($x[1]) ? trim(str_replace(']', '', $x[1])) : '';

          if (startsWith('url:', $b1)) {
            $arrange[] = ["text"=> $b0, "url"=> substr($b1, strlen('url:'), strlen($b1))];
          } else if (startsWith('switch_inline_query:', $b1)) {
            $arrange[] = ["text"=> $b0, "switch_inline_query"=> substr($b1, strlen('switch_inline_query:'), strlen($b1))];
          } else if (startsWith('switch_inline_query_current_chat:', $b1)) {
            $arrange[] = ["text"=> $b0, "switch_inline_query_current_chat"=> substr($b1, strlen('switch_inline_query_current_chat:'), strlen($b1))];
          } else {
            if ($b1 == '*' || empty($b1)) $b1 = $b0;
            $arrange[] = ["text"=> $b0, "callback_data"=> $b1];
          }
        }
        $inline_keyboard[] = $arrange;
      }
      return json_encode(["inline_keyboard" => $inline_keyboard]);
    }
  }

  private function message($update) {
    if (isset($update['message'])) {
      return $update['message'];
    } elseif (isset($update['callback_query'])) {
      return $update['callback_query'];
    } elseif (isset($update['inline_query'])) {
      return $update['inline_query'];
    } elseif (isset($update['edited_message'])) {
      return $update['edited_message'];
    } elseif (isset($update['channel_post'])) {
      return $update['channel_post'];
    } elseif (isset($update['edited_channel_post'])) {
      return $update['edited_channel_post'];
    } elseif (isset($update['chat_join_request'])) {
      return $update['chat_join_request'];
    } elseif (isset($update['my_chat_member'])) {
      return $update['my_chat_member'];
    } else {
      return [];
    }
  }

  private function type($update) {
    if (isset($update['message']['text'])) {
      return 'text';
    } elseif (isset($update['message']['animation'])) {
      return 'animation';
    } elseif (isset($update['message']['photo'])) {
      return 'photo';
    } elseif (isset($update['message']['video'])) {
      return 'video';
    } elseif (isset($update['message']['video_note'])) {
      return 'video_note';
    } elseif (isset($update['message']['audio'])) {
      return 'audio';
    } elseif (isset($update['message']['contact'])) {
      return 'contact';
    } elseif (isset($update['message']['dice'])) {
      return 'dice';
    } elseif (isset($update['message']['poll'])) {
      return 'poll';
    } elseif (isset($update['message']['voice'])) {
      return 'voice';
    } elseif (isset($update['message']['document'])) {
      return 'document';
    } elseif (isset($update['message']['sticker'])) {
      return 'sticker';
    } elseif (isset($update['message']['venue'])) {
      return 'venue';
    } elseif (isset($update['message']['location'])) {
      return 'location';
    } elseif (isset($update['inline_query'])) {
      return 'inline_query';
    } elseif (isset($update['callback_query'])) {
      return 'callback_query';
    } elseif (isset($update['chat_join_request'])) {
      return 'chat_join_request';
    } elseif (isset($update['my_chat_member'])) {
      return 'my_chat_member';
    } elseif (isset($update['message']['new_chat_members'])) {
      return 'new_chat_members';
    } elseif (isset($update['message']['left_chat_members'])) {
      return 'left_chat_members';
    } elseif (isset($update['message']['new_chat_title'])) {
      return 'new_chat_title';
    } elseif (isset($update['message']['new_chat_photo'])) {
      return 'new_chat_photo';
    } elseif (isset($update['message']['delete_chat_photo'])) {
      return 'delete_chat_photo';
    } elseif (isset($update['message']['group_chat_created'])) {
      return 'group_chat_created';
    } elseif (isset($update['message']['channel_chat_created'])) {
      return 'channel_chat_created';
    } elseif (isset($update['message']['supergroup_chat_created'])) {
      return 'supergroup_chat_created';
    } elseif (isset($update['message']['migrate_to_chat_id'])) {
      return 'migrate_to_chat_id';
    } elseif (isset($update['message']['migrate_from_chat_id'])) {
      return 'migrate_from_chat_id';
    } elseif (isset($update['message']['pinned_message'])) {
      return 'pinned_message';
    } elseif (isset($update['message']['invoice'])) {
      return 'invoice';
    } elseif (isset($update['message']['successful_payment'])) {
      return 'successful_payment';
    } elseif (isset($update['message']['connected_website'])) {
      return 'connected_website';
    } elseif (isset($update['edited_message'])) {
      return 'edited_message';
    } elseif (isset($update['message']['game'])) {
      return 'game';
    } elseif (isset($update['channel_post'])) {
      return 'channel_post';
    } elseif (isset($update['edited_channel_post'])) {
      return 'edited_channel_post';
    }  else {
      return 'unknown';
    }
  }

  /**
   * Events.
   * @param string          $types
   * @param callable|string $answer
   * @return bool
   */
  public function on($type, $answer) {
    $type = trim($type);
    $type = $type == '*' ? 'all' : strtolower($type);
    if (isset($answer) && is_callable($answer) && ($type == 'all' || in_array($type, self::$types))) {
      $this->events[$type] = $answer;
      return true;
    }
    return false;
  }

  /**
   * Run telegram bot.
   * @return bool
   */
  public function run() {
    try {
      if (php_sapi_name() == 'cli') {
        echo 'PHP Telebot version ' . self::$version;
        echo "\nMode\t: Long Polling\n";
        $options = getopt('q', ['quiet']);
        if (isset($options['q']) || isset($options['quiet'])) {
          $this->debug = false;
        }
        echo "Debug\t: " . ($this->debug ? 'ON' : 'OFF') . "\n";
        $this->longPoll();
      } else {
        $this->webhook();
      }

      return true;
    } catch (Exception $e) {
      echo $e->getMessage() . "\n";
      return false;
    }
  }

  /**
   * Process the message.
   * @return bool
   */
  private function process($update, $check_time=true) {
    $run = false;

    if ($check_time && isset($update['message']['date']) && (time() - $update['message']['date'] > 20)) {
      return false;
    }

    $events_type = array_keys($this->events);
    $data = $this->message($update);
    $type = $this->type($update);

    if (in_array('all', $events_type)) {
      $call = $this->events['all'];
      call_user_func_array($call, [$type, $data]);
    }

    if (in_array($type, $events_type)) {
      $call = $this->events[$type];
      call_user_func_array($call, [$data]);
    }

    if ($type == 'unknown') {
      call_user_func_array($call, ['update', $update]);
    }

    return true;
  }

  /**
   * Webhook Mode.
   */
  private function webhook() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['CONTENT_TYPE'] == 'application/json') {
      $update = json_decode(file_get_contents('php://input'), true);
      $this->process($update, false);
    } else {
      http_response_code(400);
      throw new Exception('Access not allowed!');
    }
  }

  /**
   * Long Poll Mode.
   * @throws Exception
   */
  private function longPoll() {
    $this->clear_pending_updates();

    $offset = 0;
    $rsv = [];
    while (true) {
      // delay 1 second
      sleep(1);

      $req = $this->send('getUpdates', ['offset' => $offset + 1, 'timeout' => 30]);

      // Check error.
      if (isset($req['error_code'])) {
        if ($req['error_code'] == 404) {
          $req['description'] = 'Incorrect bot token';
        }
        throw new Exception($req['description']);
      }

      if (!empty($req['result'])) {
        foreach ($req['result'] as $update) {
          if (!in_array($update['update_id'], $rsv)) {
            $this->process($update, true);
          }
          $rsv[] = $update['update_id'];
          $offset = $update['update_id'];
        }
      }
    }
  }
}

?>
