# VOOBLY PLUGIN

Voobly is a plugin developed for the Voobly platform that hosts the Age of Empires 2 RTS game and more.

# INSTALLATION INSTRUCTIONS

1. You will need a Voobly API Key. request one according to instructions at: [http://www.voobly.com/pages/view/146/Developer-Membership-Types](http://www.voobly.com/pages/view/146/Developer-Membership-Types).
2. Upon receiving the API Key - it probably looks like a string of 32 alphanumeric characters - rename the `config_Voobly.php.TEMPLATE` to `config_Voobly.php` at the `conf` folder, and using a text editor (notepad.exe) fill in the API Key to the `$config['Voobly_API_KEY']` entry.
3. Enable the plugin. Rename the `Voobly.php.PLUGIN` to `Voobly.php` at the `plugins` folder.
4. You will need to define a bot command that triggers the request to the API. For example, you want to name it "!voobly". You will need to add the below text to the the `appdata/admin_commands_nonsafe.cfg` file:

- if file was not existing before:

```php
{"!voobly":"PHPFUNCIZYBOT\\plugins\\Voobly_getUserRating($1, $this->bot_config)PHPFUNC."}
```

- if file was already in place with other commands defined, you will have to append the appropriate text and make it look like:

```php
{"!gmt":"It's PHPFUNCgmdate('H:i, d/m/Y')PHPFUNC now GMT.","!voobly":"PHPFUNCIZYBOT\\plugins\\Voobly_getUserRating($1, $this->bot_config, $this->logger)PHPFUNC."}
```

 5. Start the Bot.

