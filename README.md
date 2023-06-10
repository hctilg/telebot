# Telebot

The Library for Build a Telegram Bot.

<br>

## Get Started

<!-- sudo apt-get install php-curl -->

```php
<?php

// Checking the exists "Telebot Library".
if (!file_exists("lib/telebot.php")) {
    copy("https://github.com/hctilg/telebot/blob/v1.0/index.php", "lib/telebot.php");
}

require('telebot.php');

// $TeleBot->Bot(string $method, array $parameters);
$TeleBot = new Telebot('TOKEN');

?>
```

<br>

### [Example](Example.md)

<br>

#### [Telegram Bot API](https://core.telegram.org/bots/api)