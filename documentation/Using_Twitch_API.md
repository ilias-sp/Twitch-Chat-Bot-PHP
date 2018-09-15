# setting up the Bot to use Twitch API

## step 1 - create a Twitch APP

To use the Twitch API, you need to set up a Twitch App first. 

1. visit `https://dev.twitch.tv/dashboard`.
2. switch to `Apps`.
3. Create a new App. You can set the redirect URL to `http://localhost`
4. Get the `Client ID` and `Client Secret` and save them in the `channel_credentials.php` file.


## step 2 - get a token with advanced permissions (scopes)

to change the channel's title or game, the Oath token your bot will use, has to have extra permissions than the default tokens do.

1. visit the [https://twitchapps.com/tmi/](https://twitchapps.com/tmi/) website. If you are using another Twitch account to run the bot, make sure you visit this URL with a browser that is logged in to Twitch using the Bots account, not the account of your Channel!
2. Right click the `Connect with Twitch` button, copy the URL it points to to your clipboard.
3. Paste the URL to a text editor. 
4. Notice the `scope` parameter in the URL. it probably includes only these entries: `chat:read+chat:edit+channel:moderate`.
5. Append more scopes, separating each scope from others with the `+` symbol. List of scopes can be found at [official Twitch website](https://dev.twitch.tv/docs/authentication/#scopes).
6. For your convenience, you can use this scopes collection: `chat:read+chat:edit+channel:moderate+channel_editor+channel_check_subscription`.
7. Paste the updated URL to your browser, and hit enter to visit the link. You will be prompted to confirm your action by Twitch server, then you will receive the Oath token on your screen. Paste this Oath key to `channel_credentials.php` file, at `$config['oath_pass']` variable.

Configuration is complete. The bots account should be able to access the Twitch API and run the desired elevated actions.
