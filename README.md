# Telebot

The Library for Build a Telegram Bot.

## Get Started

<!-- sudo apt-get install php-curl -->

```php
// Checking the exists "Telebot Library".
if (!file_exists("lib/telebot.php")) {
    copy("https://raw.githubusercontent.com/hctilg/telebot/v1.2/index.php", "lib/telebot.php");
}

require('lib/telebot.php');

$bot = new Telebot('TOKEN');
```

## Use

```php
$bot($method, $args);

// for example: 
$bot('sendMessage', ['chat_id'=> $chat_id, 'text'=> $text]);
```

 Or

```php
$bot->$method($args);

// for example: 
$bot->sendMessage(['chat_id'=> $chat_id, 'text'=> $text]);
```

## Example

a few examples...

first, get content from input(post requests) :
```php
$content = file_get_contents('php://input');
$update = json_decode($content, true);
```

<br>

## SendMessage
```php
$chat_id = $update['message']['chat']['id'];
$name = $update['message']['chat']['first_name'];
$bot->sendMessage([
  'chat_id'=> $chat_id,
  'text'=> "Hello $name",
  'parse_mode'=> 'Markdown'
]);
```

## SendDocument
```php
$chat_id = $update['message']['chat']['id'];
$link = "your file link";
$bot->sendDocument([
  'chat_id'=> $chat_id,
  'document'=> $link,
  'caption'=> "this is a caption."
]);
```

## SendPhoto
```php
$chat_id = $update['message']['chat']['id'];
$link = "your photo link";
$bot->sendPhoto([
  'chat_id'=> $chat_id,
  'photo'=> $link,
  'caption'=> "this is a caption."
]);
```

## Keyboard Button
```php
$chat_id = $update['message']['chat']['id'];
$contact = $update['message']['contact'];
$text = $update['message']['text'];

$keyboard_btn = [
  'resize_keyboard'=> true,
  'keyboard'=> [
    ['Help', 'Contact'],
    [
      ['text'=> 'Share Phone Number', 'request_contact'=> true]
    ]
  ]
];

$bot->sendMessage([
  'chat_id'=> $chat_id,
  'text'=> "Keyboard Buttons :",
  'reply_markup'=> $keyboard_btn
]);

if ($text == 'Contact') {
  $bot->sendMessage([
    'chat_id'=>$chat_id,
    'text'=> "@telegram"
  ]);
}

if ($text == 'Help') {
  $bot->sendMessage([
    'chat_id'=>$chat_id,
    'text'=> "Help Text Here",
    'disable_web_page_preview'=> true
  ]);
}

if (!empty($contact)) {
  $phone_number = $contact['phone_number'];
  $bot->sendMessage([
    'chat_id'=>$chat_id,
    'text'=> "Phone Number: $phone_number"
  ]);
}
```

## Inline Keyboard Button
```php
$chat_id = $update['message']['chat']['id'];
$text = $update['message']['text'];
$callback_query = $update['callback_query'];

$inline_btn = [
  'inline_keyboard'=> [
    [
      ['text'=> 'Dialog', 'callback_data'=> 'show_dialog'],
      ['text'=> 'Toast', 'callback_data'=> 'show_toast']
    ],
    [
      ['text'=> 'GitHub', 'url'=> 'https://github.com/hctilg/telebot']
    ]
  ]
];

$bot->sendMessage([
  'chat_id'=> $chat_id,
  'text'=> "Inline Keyboard Buttons :",
  'reply_markup'=> $inline_btn
]);

if (!empty($callback_query)) {
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
}
```

#### [Telegram Bot API](https://core.telegram.org/bots/api)
