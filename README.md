# Twitch-Chat-Bot-PHP

This is Izybot! A simple, yet powerful, Twitch Chat bot written in PHP, to help make streamers' life easier. It will assist you by replying any command you will configure it to do with a relevant message.


# FEATURES

|  |
| ----------------------- |
| **Runs locally on your PC**, making you completely independent of 3rd party websites/software etc. Requires only PHP installed on your PC. |
| **Bot commands** management via Twitch Chat. Bot commands are special keywords users type in chat, that Izybot has to reply with the respective admin's configured response. |
| **Periodic messages** initiated by the bot. Can be used to promote streamer's social media etc. |
| **Quotes**. |
| **Polls**. Create polls for the viewers and the bot will collect their votes. |
| **Giveaways**. Create giveaways for the viewers, let them participate, and in the end have the bot pick winners randomly from the generated list. |
| **Loyalty Points**. Streamer can enable this feature to award his viewers with virtual currency points. |
| **Bets**. Viewers can use their virtual currency points to place bets on a event the streamer decides to (e.g. the outcome of an upcoming game). |
| **Commands usage statistics**. Find out which commands are the most popular among your viewers, and which are not. |

On the other hand, Bot was not written to be performing Moderator tasks, at least not yet :)


# PREREQUISITES

1. PHP will be needed on your environment.
2. You will need a valid Twitch User that the bot will use to authenticate to Twitch. It can be the username of your real account, or another account that you will create to use exclusively from your bot. This account will be the username the rest Twitch users will see in your bot's responses. To login to IRC, you will need to obtain an Oath token from Twitch API first. You can obtain it at: [https://twitchapps.com/tmi/](https://twitchapps.com/tmi/)


# INSTALLATION

1. Download the zip from this repository, and extract its contents to a folder.
2. To install PHP on Windows, see [Appendix](#appendix).


# CONFIGURATION

1. Go to the `conf` directory and copy the `config.php.TEMPLATE` file to a new file called: `config.php`.
2. Edit that file and fill in the information required. edit the fields according to the instructions.
3. Repeat with the `channel_credentials.php.TEMPLATE` file: copy it to `channel_credentials.php` and fill in your channel,username and oath token (obtained from the `https://twitchapps.com/tmi/` site)

4. Streamers with PHP knowledge who want to implement commands that include PHP code for maximum flexibility, can use the `conf/admin_commands_nonsafe.cfg.TEMPLATE` file. Copy this file to `appdata/admin_commands_nonsafe.cfg` and manually add commands that include PHP code (i.e. nonsafe), for producing dynamic output. As a security measure, PHP enabled commands can't be added via Twitch Chat like other regular admin commands.


# STARTING THE BOT

to start the bot, open "cmd" from the folder of the bot and run:

```bash
php startIzyBot.php
```

OR, for Windows users, you can use the supplied batch file.


# STARTING THE WEB INTERFACE

To access the Web interface, we will be using the PHP built-in web server. To start it, run the supplied batch file called: `startHTTP_GUI.bat`.

To access the Web interface of the Bot, open this link with your browser: [http://127.0.0.1:33333/](http://127.0.0.1:33333/)

To start **both** the IzyBot and the Web interface, use the batch file: `startIzyBot_and_HTTP_GUI.bat`.


# SAMPLE PREVIEW PICS FROM THE WEB INTERFACE

Twitch Chat:

![Preview image](preview/twitch.png?raw=true "Preview image")

Polls:

![Preview image](preview/polls.png?raw=true "Preview image")


# ADMIN COMMANDS

Unless, you have defined otherwise in the `conf/config.php`, the bot supports the below administrator commands:

- messages the bot will reply when triggered by viewers' commands:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !addcmd | used to add a new command. the command will be available to all. |
| !editcmd | used to update the response of an existing command. |
| !removecmd | used to remove an existing command. |

- Bot's administrators management:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !addadmin | used to add a new username to the admin group. This admin group is not necessarily the same with the Mods. if you want to make a person a Mod to your channel, you will have to explicitly add him to the Bots admin group. |
| !removeadmin | used to remove a user from the bot's admin group. |

- Periodic messages management:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !addperiodicmsg | used to add some message to periodically sent out in the chat. |
| !removeperiodicmsg | used to remove a message from the list of messages periodically sent out in the chat. You need to type the whole sentence that you want to remove, exactly as-is when you see it in chat. |

- Polls:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !makepoll &lt;poll duration&gt; &lt;poll description&gt; | Used to create a new poll. Command syntax is expected to be: !makepoll &lt;poll duration in seconds&gt; &lt;free text describing the poll and the available options to vote (options need to be numeric and less than 5 digits, meaning up to 99999)&gt; |
| !cancelpoll | Used to cancel the active poll. |

- Quotes:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !addquote | To add a quote. |
| !removequote | To remove a quote, followed by its numerical ID. |

- Giveaways:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !giveaway-start &lt;title&gt; | Will start the giveaway title. You can optionally add any string as description to the giveaway. |
| !giveaway-end | Will stop the giveaway, throughout it viewers can join the giveaway. |
| !giveaway-status | Check the current status of the giveaway function, and how many viewers have joined. |
| !giveaway-reset | Reset the giveaway. run this before starting a new giveaway. This command will flush the current giveaway's details (title) to file. |
| !giveaway-winner | Have Izybot pick up a winner from the eligible viewers list. You can run this multiple times to select more than one winners. |

- Bets:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !startbet &lt;bet duration&gt; &lt;bet description&gt; | Start a new bet. Define the duration in seconds that the bot will accept bets, and a clear description of the options your viewers can choose from. |
| !endbet &lt;winning option&gt; | End the active bet. Provide also the winning option for the bot to award the viewers who bet on that option, and deduct the amount from the viewers who lost. |
| !cancelbet | Cancel the ongoing bet, refund the viewers who had already placed a bet. |


# USER COMMANDS

- Special reserved commands that are already configured:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !help | will reply the list of available commands for the users (not including the admin commands). |
| !uptime | will reply the uptime of the bot. This should not be assumed it is the same as the uptime of the stream (unless you start the bot simultaneously with the stream session). |
| !quote "ID" | to display a specific quote. pass no ID, to display a random quote. |
| !botinfo | a command to print information about this bot and where people can find it. |

- Quotes:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !quote &lt;quote id&gt; | Request the bot to reply with the relevant quote from its quote database. |

- Polls:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !vote &lt;option&gt; | Participate in the active poll, providing the desired &lt;option&gt;. Options accepted have to be in numerical format. |

- Giveaways:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !giveaway | Command the viewer sends to join the ongoing giveaway. |

- Loyalty Points:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !izyeuros | Request the bot to reply with the Loyalty Points (virtual currenty) the viewer has accumulated over time in streamer's chat. |

- Bets:

| Command | Purpose |
| ----------------------- | ----------------------- |
| !bet &lt;option&gt; &lt;LP amount&gt; | Place a bet, to option &lt;option&gt; for &lt;LP amount&gt; LP points. |


# COMMANDS STATISTICS

Izybot keeps track of all the registered commands being triggered, for you to have some insights on the popularity of your configured commands.

You can view these statistics from the web interface.

![Preview image](preview/commands_usage.png?raw=true "Preview image")


# LOYALTY POINTS

Loyalty points is a virtual currency that is awarded to viewers, according to their presence in the channel. They can use this virtual currency on bets. To enable and configure it, see config.php.

![Preview image](preview/loyalty.png?raw=true "Preview image")


# PLUGINS

Plugins are developed on demand.

| Plugin | Description |
| ----------------------- | ----------------------- |
| Voobly | a plugin developed for the Voobly platform, that hosts the Age of Empires 2 RTS game and more. [Check here](documentation/Voobly.md) for instructions on how to enable and configure the plugin. |


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


# 3RD PARTY SOFTWARE USED

IzyBot uses below 3rd party open source software:

| Library |
| ----------------------- |
| [KLogger](https://github.com/katzgrau/KLogger) |
| [AdminBSB - Material Design Dashboard](https://github.com/gurayyarar/AdminBSBMaterialDesign) |
| [Plankton, a PHP pico framework](https://github.com/Gregwar/Plankton) |
