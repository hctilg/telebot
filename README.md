# Telebot

The Library for Build a Telegram Bot.

## Get Started

<!-- sudo apt-get install php-curl -->

```php
<?php

// Checking the exists "Telebot Library".
if (!file_exists("lib/telebot.php")) {
    copy("https://raw.githubusercontent.com/hctilg/telebot/v1.1/index.php", "lib/telebot.php");
}

require('lib/telebot.php');

$bot = new Telebot('TOKEN');

?>
```

### [A Few Example](Example.md)

#### [Telegram Bot API](https://core.telegram.org/bots/api)
