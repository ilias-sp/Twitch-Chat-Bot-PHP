<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Loyalty Points</h2>
            </div>

            <?php

            $loyalty_details_array = json_decode($loyalty_details, true);

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($loyalty_details_array))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-cyan info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">stars</i>
                                </div>
                                <div class="content">
                                    <div class="text">VIEWERS WITH LOYALTY POINTS</div>
                                    <div class="number">' . $total_count . '</div>
                                    <div class="text"></div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
            }
            else
            {

                // prepare widget:

                $total_count = count($loyalty_details_array);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">stars</i>
                        </div>
                        <div class="content">
                            <div class="text">VIEWERS WITH LOYALTY POINTS</div>
                            <div class="number">' . $total_count . '</div>
                            <div class="text"></div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                asort($loyalty_details_array);

                $current_counter = 1;
                foreach ($loyalty_details_array as $loyalty_viewer)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($loyalty_viewer['username'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . $loyalty_viewer['points'] . '</td>
                        <td>' . date('l, d F Y (T), H:i', $loyalty_viewer['last_date_seen']) . '</td>
                        <td><a href="https://www.twitch.tv/' . htmlspecialchars($loyalty_viewer['username'], ENT_QUOTES, 'UTF-8') . '" target="_blank" title="Visit user\'s Twitch Profile"><i class="zmdi zmdi-twitch zmdi-hc-2x"></i></a></td>
                    </tr>
';
                    $current_counter++;
                }
            }

            // print response:

            echo $widget_text;
                            
            echo '
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Viewer\'s Loyalty Points
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-2">#</th>
                                        <th class="col-xs-3">USERNAME</th>
                                        <th class="col-xs-2">LOYALTY POINTS</th>
                                        <th class="col-xs-4">LAST DATE IN CHAT</th>
                                        <th class="col-xs-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_text . '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

';

            ?>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                HELP - About Loyalty Points
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-success" style="line-height: 2em;">
                                <ul>
                                 <li>Loyalty points are points awarded to viewers for being present in your channel/chat. They can be used when you place a bet for an upcoming event (for example, the result of a game you are about to play).</li>
                                 <li>To enable the Loyalty points, you need to configure the parameters under "loyalty config" section in conf/config.php file. By setting a greater than zero value to the <b>$config['loyalty_points_per_interval']</b>, the feature is enabled.</li>
                                 <li>You can welcome new users to your chat by giving them a welcome bonus amount of points. Check the <b>$config['loyalty_points_welcome_award']</b> in the conf/config.php file.</li>
                                 <li>The database of viewers and their points, is stored in appdata/loyalty_viewers_XP_array.cfg file. It is recommended you backup this file periodically, after each stream.</li>
                                 </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
