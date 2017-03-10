<?php

set_time_limit(0);
define('APPPATH', dirname(__FILE__));
define('ERROR', 1);
define('INFO', 2);
define('DEBUG', 3);

require_once(APPPATH . '/conf/config.php');
require_once(APPPATH . '/lib/Common.php');
require_once(APPPATH . '/lib/IzyBot.php');


$twitch_bot = new IzyBot($config);

$twitch_bot->start_bot()
           ->main()
           ->stop_bot();
