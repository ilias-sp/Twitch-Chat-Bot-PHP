<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?php echo $title; ?> | IzyBot, Twitch-Chat-Bot-PHP | A simple, yet powerful, Twitch Chat bot in PHP!</title>
    <!-- Favicon-->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="/http_res/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="/http_res/plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="/http_res/plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="/http_res/css/style.css" rel="stylesheet">

    <!-- JQuery DataTable Css -->
    <link href="/http_res/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">

    <!-- material-design-iconic-font Css -->
    <link href="/http_res/plugins/material-design-iconic-font/css/material-design-iconic-font.min.css" rel="stylesheet">

    <!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
    <link href="/http_res/css/themes/all-themes.css" rel="stylesheet" />
</head>

<body class="theme-red">
    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Please wait...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <!-- Overlay For Sidebars -->
    <div class="overlay"></div>
    <!-- #END# Overlay For Sidebars -->
    <!-- Search Bar -->
    <div class="search-bar">
        <div class="search-icon">
            <i class="material-icons">search</i>
        </div>
        <input type="text" placeholder="START TYPING...">
        <div class="close-search">
            <i class="material-icons">close</i>
        </div>
    </div>
    <!-- #END# Search Bar -->
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="/home"><i class="material-icons">gavel</i>&nbsp;&nbsp;&nbsp;&nbsp;IzyBot</a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse">
                
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <div class="image">
                    <img src="/http_res/images/user.png" width="48" height="48" alt="User" />
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Administrator</div>
                    <div class="email"></div>
                </div>
            </div>
            <!-- #User Info -->
            <!-- Menu -->
            <div class="menu">
                <ul class="list">
                    <li class="header">MAIN NAVIGATION</li>
                    <li class="active">
                        <a href="/twitchchat">
                            <i class="material-icons">forum</i>
                            <span>Twitch Chat</span>
                        </a>
                    </li>
                    <li>
                        <a href="/twitchstream">
                            <i class="zmdi zmdi-twitch zmdi-hc-2x"></i>
                            <span>Twitch Stream</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">settings</i>
                            <span>Configuration</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="/config/administrators">Administrators</a>
                            </li>
                            <li>
                                <a href="/config/commands">Commands</a>
                            </li>
                            <li>
                                <a href="/config/periodicmessages">Periodic Messages</a>
                            </li>
                            <li>
                                <a href="/config/quotes">Quotes</a>
                            </li>
                            <li>
                                <a href="/config/configfile">config.php file</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="/loyaltypoints">
                            <i class="material-icons">stars</i>
                            <span>Loyalty Points</span>
                        </a>
                    </li>
                    <li>
                        <a href="/bets_home">
                            <i class="material-icons">monetization_on</i>
                            <span>Bets</span>
                        </a>
                    </li>
                    <li>
                        <a href="/polls_home">
                            <i class="material-icons">insert_chart</i>
                            <span>Polls</span>
                        </a>
                    </li>
                    <li>
                        <a href="/giveaways_home">
                            <i class="material-icons">pie_chart</i>
                            <span>Giveaways</span>
                        </a>
                    </li>
                    <li>
                        <a href="/commands_usage">
                            <i class="material-icons">assessment</i>
                            <span>Commands Usage statistics</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">history</i>
                            <span>History</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="/history/twitchchat">Twitch Chat logs</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="/help">
                            <i class="material-icons">help</i>
                            <span>Help</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- #Menu -->
            <!-- Footer -->
            <div class="legal">
                <div class="copyright">
                	<a href="https://github.com/ilias-sp/Twitch-Chat-Bot-PHP" target="_blank">Izybot</a> is using:<br/>
                	&copy; 2016 - 2017 <a href="https://github.com/gurayyarar/AdminBSBMaterialDesign">AdminBSB - Material Design</a><br/>and<br/> 
                	<a href="https://github.com/Gregwar/Plankton">Plankton, a PHP pico framework.</a><br/>
                </div>
            </div>
            <!-- #Footer -->
        </aside>
        <!-- #END# Left Sidebar -->
    </section>

