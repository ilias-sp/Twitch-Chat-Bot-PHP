
<script>
$(document).ready(function() {

help_text = `
# Twitch-Chat-Bot-PHP
This is Izybot! A simple, yet powerful, Twitch Chat bot in PHP.


# INTRODUCTION
Izybot is a basic Twitch Chat bot, to help make streamers life easier. It will assist you by replying any command you will configure it to do with a relevant message.


# FEATURES
- Runs locally on user's PC. Needs only PHP installed.
- bot's admins management via Twitch Chat. Bot admins can configure the bot commands.
- bot's commands management via Twitch Chat. Bot commands are special keywords users type in chat, and bot replies with admin's configured response.
- response suppression for confirable interval if same command was replied earlier.
- periodic messages initiated by the bot. Can be used to promote streamer's social media etc. (Please note that although the periodic messages are initiated by the bot, they still have to be triggered by some activity in the IRC traffic of the channel. This can cause some delay when the channel has not much chat activity, up to 1 minute.)
- Polls. create poll for the viewers and bot will count their votes.
- Giveaways. Create giveaways for the viewers, let them participate, and in the end have the bot pick winners randomly from the list.

On the other hand, Bot was not written to be performing Moderator tasks, at least not yet :)


# FAQ


# PREREQUISITES
PHP will be needed on your environment.
Additionally, you will need a valid Twitch User that the bot will use to authenticate to Twitch. It can be the username of your real account, or another account that you will create to use dedicated for your bot. This account will be the username the rest Twitch users will see in your bot's responses.
To login to IRC, you will need to obtain an Oath token from Twitch API first. You can obtain it at: https://twitchapps.com/tmi/


# INSTALLATION
download the zip from this repository, and extract its contents to a folder.
to install PHP on Windows, see APPENDIX.


# CONFIGURATION
go to conf directory and copy the config.php.TEMPLATE file to a new file called: config.php
edit that file and fill in the information required. edit the fields according to the instructions.
Repeat with the channel_credentials.php.TEMPLATE file: copy it to channel_credentials.php and fill in your channel/username and oath token (obtained from the https://twitchapps.com/tmi/ site)

conf/admin_commands_nonsafe.cfg.TEMPLATE file:

This is for advanced users with PHP knowledge. If you want to use this feature copy this file to appdata/admin_commands_nonsafe.cfg and manually add commands that include PHP code (i.e. nonsafe), for producing dynamic output. As a security measure, PHP enabled commands can't be added via Twitch Chat like other regular admin commands.


# STARTING THE BOT
to start the bot, open "cmd" from the folder of the bot and run:

php startIzyBot.php

OR, for Windows users, you can use the supplied batch file.

# STARTING THE WEB INTERFACE
This uses the php built-in web server. To start it, run the supplied batch file called: startHTTP_GUI.bat

To access the Web interface, open this link from your browser: http://127.0.0.1:33333/


# ADMIN COMMANDS
Unless, you have defined otherwise in the startIzyBot.php, the bot supports the below admin reserved commands:

!addcmd: used to add a new command. the command will be available to all.

!editcmd: used to update the response of an existing command.

!removecmd: used to remove an existing command.

!addadmin: used to add a new username to the admin group. This admin group is not necessarily the same with the Mods. if you want to make a person a Mod to your channel, you will have to explicitly add him to the Bots admin group.

!removeadmin: used to remove a user from the bot's admin group.

!addperiodicmsg: used to add some message to periodically sent out in the chat.

!removeperiodicmsg: used to remove a message from the list of messages periodically sent out in the chat. You need to type the whole sentence that you want to remove, exactly as  is when you see it in chat.

!makepoll: used to create a new poll. Command syntax is expected to be: !makepoll <poll duration in seconds> <free text describing the poll and the available options to vote (options need to be numeric and less than 5 digits, meaning up to 99999)>

!cancelpoll: used to cancel the active poll.

!giveaway-start "title": will start the giveaway title. You can optionally add any string as description to the giveaway.

!giveaway-end: will stop the giveaway, throughout it viewers can join the giveaway.

!giveaway-status: check the current status of the giveaway function, and how many viewers have joined.

!giveaway-reset: reset the giveaway. run this before starting a new giveaway. This command will flush the current giveaway's details (title) to file.

!giveaway-winner: have Izybot pick up a winner from the eligible viewers list. You can run this multiple times to select more than one winners.

!addquote: to add a quote.

!removequote: to remove a quote, followed by its numerical ID.


# LOYALTY POINTS
Loyalty points is a virtual currency that is awarded to viewers, according to their presence in the channel. They can use this virtual currency on bets. To enable and configure it, see config.php.

# QUOTES
Quotes can be added to the bot configuration, and displayed on demand.


# USER COMMANDS
special reserved commands that are already configured:

!help: will reply the list of available commands for the users (not including the admin commands).,

!uptime: will reply the uptime of the bot. This should not be assumed it is the same as the uptime of the stream (unless you start the bot simultaneously with the stream session).

!quote "ID": to display a specific quote. pass no ID, to display a random quote.

!botinfo: a command to print information about this bot and where people can find it.


# APPENDIX
Installing PHP on Windows:
- download the PHP x86 or x64 version according to your system architecture from: http://php.net/downloads.php.
- extract its contents in a folder under C:, lets say under folder C:\PHP.
- cp php.ini-production to php.ini.
- Using a text editor, edit the php.ini file and:
  1. search for line:
  ; On windows:
  ; extension_dir = "ext"

  uncomment the 2nd line, making it look:

  ; On windows:
  extension_dir = "ext"
  
  2. search for line:
  ; extension=php_mbstring.dll

  uncomment it, making it look:

  extension=php_mbstring.dll

  same for line:

  ;extension=php_sockets.dll

  uncomment it, making it look:

  extension=php_sockets.dll


  save the file.
- add the php binary to the %PATH% env variable:

  right click on "My computer" > Properties > Change Settings > Advanced > Environment Variables.
  on bottom panel its the System Variables for your Windows account. Locate the "Path" Variable, click Edit and Append in its value: ;C:\PHP; You just made the PHP binary avaiable to be located and executed from any folder of your system.
  Press OK to save your change, press OK to close the Dialog. You may need to restart your PC for changes to take effect.

- Installation of PHP is done!


# PLUGINS

# VOOBLY PLUGIN

Voobly is a plugin developed for the Voobly platform, that hosts the Age of Empires 2 RTS game and more. [Check here](documentation/Voobly.md) for instructions on how to enable and configure the plugin.



# 3RD PARTY SOFTWARE USED

IzyBot uses below 3rd open source software:

- [KLogger](https://github.com/katzgrau/KLogger)
- [AdminBSB - Material Design Dashboard](https://github.com/gurayyarar/AdminBSBMaterialDesign)
- [Plankton, a PHP pico framework](https://github.com/Gregwar/Plankton)
`;



help_text = help_text.replace(/(?:\r\n|\r|\n)/g, '<br />');

$('#README_MD').html(help_text);

});

</script>
