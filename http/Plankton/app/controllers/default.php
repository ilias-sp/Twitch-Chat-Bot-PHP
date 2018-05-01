<?php return array(
    '/' => function() {
        // $parameters = $app['request']['parameters'];
        header('Location: /twitchchat');
        return array();
    },
    '/home' => function() {
        // $parameters = $app['request']['parameters'];
        header('Location: /twitchchat');
        return array();
    },
    '/config/administrators' => function($app) {
        return array('botadministrators',
            'title' => 'Bot Administrators - Configuration',
            'route' => 'config_botadministrators',
            'administrators' => $app['model']->get_botadministrators()
        );
    },
    '/config/commands' => function($app) {
        return array('botcommands',
            'title' => 'Bot Commands - Configuration',
            'route' => 'config_botcommands',
            'commands' => $app['model']->get_botcommands()
        );
    },
    '/commands_usage' => function($app) {
        return array('botcommands_usage',
            'title' => 'Bot Commands Usage Statistics',
            'route' => 'botcommands_usage',
            'commands_usage' => $app['model']->get_botcommands_usage()
        );
    },
    '/config/periodicmessages' => function($app) {
        return array('botperiodicmessages',
            'title' => 'Periodic Messages - Configuration',
            'route' => 'config_botperiodicmessages',
            'periodic_messages' => $app['model']->get_botperiodic_msgs()
        );
    },
    '/config/quotes' => function($app) {
        return array('botquotes',
            'title' => 'Quotes - Configuration',
            'route' => 'config_botquotes',
            'quotes' => $app['model']->get_quotes()
        );
    },
    '/config/configfile' => function($app) {
        return array('configfile',
            'title' => 'Config.php - Configuration',
            'route' => 'config_configfile',
            'configfile' => $app['model']->get_configfile()
        );
    },
    '/twitchchat' => function($app) {
        return array('twitchchat',
            'title' => 'Twitch Chat',
            'route' => 'twitchchat',
            'twitch_channel_name' => $app['model']->get_channel_name()
        );
    },
    '/twitchstream' => function($app) {
        return array('twitchstream',
            'title' => 'Twitch Stream',
            'route' => 'twitchstream',
            'twitch_channel_name' => $app['model']->get_channel_name()
        );
    },
    '/polls_home' => function($app) {
        return array('polls_home',
            'title' => 'Polls',
            'route' => 'polls_home',
            'poll_files' => $app['model']->get_poll_files()
        );
    },
    '/poll_details' => function($app) {

        $parameters = $app['request']['parameters'];
        $poll_filename = isset($parameters['file']) ? $parameters['file'] : NULL;

        return array('poll_details',
            'title' => 'Poll Details',
            'route' => 'poll_details',
            'poll_filename' => $poll_filename,
            'poll_details' => $app['model']->get_poll_file_details($poll_filename)
        );
    },
    '/bets_home' => function($app) {
        return array('bets_home',
            'title' => 'Bets',
            'route' => 'bets_home',
            'bet_files' => $app['model']->get_bet_files()
        );
    },
    '/bet_details' => function($app) {

        $parameters = $app['request']['parameters'];
        $bet_filename = isset($parameters['file']) ? $parameters['file'] : NULL;

        return array('bet_details',
            'title' => 'Bet Details',
            'route' => 'bet_details',
            'bet_filename' => $bet_filename,
            'bet_details' => $app['model']->get_bet_file_details($bet_filename)
        );
    },
    '/giveaways_home' => function($app) {
        return array('giveaways_home',
            'title' => 'Giveaways',
            'route' => 'giveaways_home',
            'giveaway_files' => $app['model']->get_giveaway_files()
        );
    },
    '/giveaway_details' => function($app) {

        $parameters = $app['request']['parameters'];
        $giveaway_filename = isset($parameters['file']) ? $parameters['file'] : NULL;

        return array('giveaway_details',
            'title' => 'Giveaway Details',
            'route' => 'giveaway_details',
            'giveaway_filename' => $giveaway_filename,
            'giveaway_details' => $app['model']->get_giveaway_file_details($giveaway_filename)
        );
    },
    '/loyaltypoints' => function($app) {
        return array('Loyalty Points',
            'title' => 'Loyalty Points',
            'route' => 'loyalty_points',
            'loyalty_details' => $app['model']->get_viewers_loyalty_XP_details()
        );
    },
    '/history/twitchchat' => function($app) {
        return array('History - Twitch chat logs',
            'title' => 'History - Twitch chat logs',
            'route' => 'history_twitchchat',
            'log_files' => $app['model']->get_log_files()
        );
    },
    '/history/twitchchat_log_details' => function($app) {

        $parameters = $app['request']['parameters'];
        $log_filename = isset($parameters['file']) ? $parameters['file'] : NULL;

        return array('History - Twitch chat log details',
            'title' => 'History - Twitch chat details',
            'route' => 'history_twitchchat_log_details',
            'log_filename' => $log_filename,
            'log_details' => $app['model']->get_log_file_details($log_filename)
        );
    },
    '/help' => function($app) {
        return array('help',
            'title' => 'Help',
            'route' => 'help'
        );
    },
    '/error' => function() {
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
        return array('error');
    },
);
