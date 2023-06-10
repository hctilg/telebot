# Telebot

The Library for Build a Telegram Bot.

## Get Started

<!-- sudo apt-get install php-curl -->

```php
<?php

// Checking the exists "Telebot Library".
if (!file_exists("lib/telebot.php")) {
    copy("https://raw.githubusercontent.com/hctilg/telebot/v1.0/index.php", "lib/telebot.php");
}

require('telebot.php');

// $TeleBot->Bot(string $method, array $parameters);
$TeleBot = new Telebot('TOKEN');

?>
```

### [Example](Example.md)

#### [Telegram Bot API](https://core.telegram.org/bots/api)
