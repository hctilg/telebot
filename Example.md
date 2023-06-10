# Example

first, get content from input :
```php
$content = file_get_contents('php://input');
$data = json_decode($content, true);
```

<br>

## SendMessage
```php
$chat_id = $data['message']['chat']['id'];
$name = $data['message']['chat']['first_name'];
$TeleBot->Bot('sendMessage',
  array(
    'chat_id'=> $chat_id,
    'text'=> "Hello [$name](tg://user?id=$chat_id)",
    'parse_mode'=> 'Markdown'
  )
);
```

## SendDocument
```php
$chat_id = $data['message']['chat']['id'];
$link = "your file link";
$TeleBot->Bot('sendDocument', array(
  'chat_id'=> $chat_id,
  'document'=> "$link"
));
```

## SendPhoto
```php
$chat_id = $data['message']['chat']['id'];
$link = "your photo link";
$TeleBot->Bot('sendPhoto', array(
  'chat_id'=> $chat_id,
  'photo'=> "$link"
));
```

## Keyboard Button
```php
$chat_id = $data['message']['chat']['id'];
$contact = $data['message']['contact'];
$text = $data['message']['text'];

$keyboard_btn = array(
  'resize_keyboard'=> true ,
  'keyboard'=> array(
    array('Help', 'Contact'),
    array(
      array(
        'text'=> ' Share Phone Number ' ,
        'request_contact'=>true
      )
    )
  )
);

$TeleBot->Bot('sendMessage',
  array(
    'chat_id'=> $chat_id,
    'text'=> "Keyboard Buttons :",
    'reply_markup'=> $keyboard_btn
  )
);

if ($text == 'Contact') {
  $TeleBot->Bot('sendMessage',
    array(
      'chat_id'=>$chat_id,
      'text'=> "@telegram"
    )
  );
}

if ($text == 'Help') {
  $TeleBot->Bot('sendMessage',
    array(
      'chat_id'=>$chat_id,
      'text'=> "Help Text Here",
      'disable_web_page_preview'=> 'true'
    )
  );
}

if ($contact != null) {
  $phone_number = $contact['phone_number'];
  $TeleBot->Bot('sendMessage',
    array(
      'chat_id'=>$chat_id,
      'text'=> "Phone Number: $phone_number"
    )
  );
}
```


## Inline Keyboard Button
```php
$chat_id = $data['message']['chat']['id'];
$text = $data['message']['text'];
$callback_query = $data['callback_query'];

$inline_btn = array(
  'inline_keyboard'=> array(
    array(
      array(
        'text'=> 'Dialog',
        'callback_data'=> 'show_dialog'
      ),
      array(
        'text'=> 'Toast',
        'callback_data'=> 'show_toast'
      )
    ),
    array(
      array(
        'text'=> 'GitHub',
        'url'=> 'https://github.com/hctilg/telebot'
      )
    )
  )
);

$TeleBot->Bot('sendMessage',
  array(
    'chat_id'=> $chat_id,
    'text'=> "Inline Keyboard Buttons :",
    'reply_markup'=> $inline_btn
  )
);

if ($callback_query != null) {
  $callback_query_id = $callback_query['id'];
  $callback_query_data = $callback_query['data'];
  $callback_query_chat_id = $callback_query['message']['chat']['id'];

  if ($callback_query_data == 'show_dialog') {
    $TeleBot->Bot('answerCallbackQuery',
      array(
        'callback_query_id'=> $callback_query_id,
        'text'=> 'Dialog Text Here.',
        'show_alert'=> true
      )
    );
  }
  elseif($callback_query_data == 'show_toast') {
    $TeleBot->Bot('answerCallbackQuery',
      array(
        'callback_query_id'=> $callback_query_id,
        'text'=> 'Toast Text Here.',
        'show_alert'=> false
      )
    );
  }
}
```

<br>

### [Back](README.md)