<?php

namespace IZYBOT;

set_time_limit(0);
define('APPPATH', dirname(__FILE__));
define('ERROR', 1);
define('INFO', 2);
define('DEBUG', 3);

require_once 'vendor/autoload.php';

require_once(APPPATH . '/conf/config.php');
require_once(APPPATH . '/conf/channel_credentials.php');
require_once(APPPATH . '/lib/Common.php');
require_once(APPPATH . '/lib/Logger.php');
require_once(APPPATH . '/lib/IzyBot.php');
require_once(APPPATH . '/lib/AppDataHandler.php');



date_default_timezone_set($config['timezone']);
//-----------------------------------------------
//-----------------------------------------------
//-----------------------------------------------
use IZYBOT\lib\IzyBot as IZYBOT;

//-----------------------------------------------

$twitch_bot = new IZYBOT($config);

$twitch_bot->start_bot()
           ->main()
           ->stop_bot();
