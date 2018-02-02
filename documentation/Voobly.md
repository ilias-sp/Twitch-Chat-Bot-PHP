# Voobly integration

Voobly is a plugin developed for the Voobly platform, that hosts the Age of Empires 2 RTS game and more.

# INSTRUCTIONS

- You will need a Voobly API Key. request one according to instructions at: http://www.voobly.com/pages/view/146/Developer-Membership-Types
- Upon receiving the API Key - it probably looks like a string of 32 alphanumeric string - rename the config_Voobly.php.TEMPLATE to config_Voobly.php at the config folder, and using a text editor (notepad.exe) fill in the API Key to the $config['Voobly_API_KEY'] entry.
- Enable the plugin. Rename the Voobly.php.PLUGIN to Voobly.php at the plugins folder.
- You will need to define a bot command that triggers the request to the API. For example, you want to name it "!voobly". You will need to add the below text to the the appdata/admin_commands_nonsafe.cfg file:

if file was not existing before:

{"!voobly":"PHPFUNCIZYBOT\\plugins\\Voobly_getUserRating($1, $this->bot_config)PHPFUNC."}

if file was already in place with other commands defined, you will have to append the appropriate text and make it look like:

{"!gmt":"It's PHPFUNCgmdate('H:i, d/m/Y')PHPFUNC now GMT.","!voobly":"PHPFUNCIZYBOT\\plugins\\Voobly_getUserRating($1, $this->bot_config, $this->logger)PHPFUNC."}

- Start the Bot.

