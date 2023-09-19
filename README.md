# Telebot

The Library for Build a Telegram Bot.

### Requirements
+ php-curl  <!-- sudo apt-get install php-curl -->

## Get Started

```php
// Checking the exists "Telebot Library".
if (!file_exists("telebot.php")) {
  copy("https://raw.githubusercontent.com/hctilg/telebot/v1.8/index.php", "telebot.php");
}

require('telebot.php');

$bot = new Telebot('TOKEN');

// codes

$bot->run();
```

## Use

```php
/**
 * for example: 
 *  $bot($method, $args);
 */
$bot('sendMessage', ['chat_id'=> $chat_id, 'text'=> $text]);
```

 Or

```php
/**
 * for example: 
 *  $bot->$method($args);
 */
$bot->sendMessage(['chat_id'=> $chat_id, 'text'=> $text]);
```

## Event Handler
```php
/**
 * all events
 * for example: 
 *  $bot->on('all' or '*', function($type, $data) use ($bot, $args..) { ... });
 */
$bot->on('*', function($type, $data) use ($bot) {
  $chat_id = $data['chat']['id'];
  $bot->sendMessage(['chat_id'=> $chat_id, 'text'=> "$type\n\n" . json_encode($data)]);
});

/**
 * one event
 * for example: 
 *  $bot->on($ev_type, function($data) use ($bot, $args..) { ... });
 */
$bot->on('text', function($data) use ($bot) {
  $chat_id = $data['chat']['id'];
  $text = $data['text'];
  $bot->sendMessage(['chat_id'=> $chat_id, 'text'=> "Parrot: $text"]);
});
```

## Example

a few examples..

## SendMessage
```php
$chat_id = $data['chat']['id'];
$name = $data['chat']['first_name'];
$bot->sendMessage([
  'chat_id'=> $chat_id,
  'text'=> "Hello $name",
  'parse_mode'=> 'Markdown'
]);
```

## DownloadFile
```php
$bot->download($file_id, $file_path);
```

## SendDocument
```php
$chat_id = $data['chat']['id'];
$bot->sendDocument([
  'chat_id'=> $chat_id,
  'document'=> 'file.zip',
  'caption'=> 'this is a caption.'
]);
```

## SendPhoto
```php
$chat_id = $data['chat']['id'];
$bot->sendPhoto([
  'chat_id'=> $chat_id,
  'photo'=> 'image.jpg',
  'caption'=> 'this is a caption.'
]);
```

## Keyboard Button
```php
$bot->on('text', function($data) use ($bot) {
  $chat_id = $data['chat']['id'];
  $text = $data['text'];

  $keyboard_btn = Telebot::keyboard("
    [Help]
    [Share Phone Number|request_contact] [Share Location|request_location]
  ");

  $bot->sendMessage([
    'chat_id'=> $chat_id,
    'text'=> "Keyboard Buttons :",
    'reply_markup'=> $keyboard_btn
  ]);

  if ($text == 'Help') {
    $bot->sendMessage([
      'chat_id'=>$chat_id,
      'text'=> "Help Text Here",
      'disable_web_page_preview'=> true
    ]);
  }
});

$bot->on('contact', function($data) use ($bot) {
  $chat_id = $data['chat']['id'];
  $contact = $data['contact'];
  $phone_number = $contact['phone_number'];

  $bot->sendMessage([
    'chat_id'=>$chat_id,
    'text'=> "Phone Number: $phone_number"
  ]);
});
```

## Inline Keyboard Button
```php
$bot->on('text', function($data) use ($bot) {
  $chat_id = $data['chat']['id'];
  $text = $data['text'];

  $inline_btn = Telebot::inline_keyboard("
    [Dialog|show_dialog] [Toast|show_toast]
    [SIQ|switch_inline_query:query] [SIQC|switch_inline_query_current_chat:query]
    [GitHub|url:https://github.com/hctilg/telebot]
  ");

  $bot->sendMessage([
    'chat_id'=> $chat_id,
    'text'=> "Inline Keyboard Buttons :",
    'reply_markup'=> $inline_btn
  ]);
});

$bot->on('callback_query', function($callback_query) use ($bot) {
  $callback_query_id = $callback_query['id'];
  $callback_query_data = $callback_query['data'];
  $callback_query_chat_id = $callback_query['message']['chat']['id'];

  if ($callback_query_data == 'show_dialog') {
    $bot->answerCallbackQuery([
      'callback_query_id'=> $callback_query_id,
      'text'=> 'Dialog Text Here.',
      'show_alert'=> true
    ]);
  } elseif ($callback_query_data == 'show_toast') {
    $bot->answerCallbackQuery([
      'callback_query_id'=> $callback_query_id,
      'text'=> 'Toast Text Here.',
      'show_alert'=> false
    ]);
  }
});
```

#### [Telegram Bot API](https://core.telegram.org/bots/api)
