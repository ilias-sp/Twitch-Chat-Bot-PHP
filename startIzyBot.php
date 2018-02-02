<?php

namespace IZYBOT;

set_time_limit(0);
define('APPPATH', dirname(__FILE__));
define('ERROR', 1);
define('INFO', 2);
define('DEBUG', 3);

require_once(APPPATH . '/vendor/autoload.php');

require_once(APPPATH . '/conf/config.php');
require_once(APPPATH . '/conf/channel_credentials.php');
require_once(APPPATH . '/lib/Common.php');
require_once(APPPATH . '/lib/Logger.php');
require_once(APPPATH . '/lib/IzyBot.php');
require_once(APPPATH . '/lib/AppDataHandler.php');

// require any potential plugins that reside on plugins folder:

foreach (glob(APPPATH . '/plugins/*.php') as $plugin)
{
    require_once $plugin;
}


//-----------------------------------------------
//-----------------------------------------------
//-----------------------------------------------
use IZYBOT\lib\IzyBot as IZYBOT;

//-----------------------------------------------
date_default_timezone_set($config['timezone']);


$twitch_bot = new IZYBOT($config);

$twitch_bot->start_bot()
           ->main()
           ->stop_bot();
