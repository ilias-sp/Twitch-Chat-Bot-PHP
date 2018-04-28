<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Bets</h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($bet_files))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-pink info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">monetization_on</i>
                                </div>
                                <div class="content">
                                    <div class="text">BETS</div>
                                    <div class="number">' . $total_count . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
            }
            else
            {

                // prepare widget:

                $total_count = count($bet_files);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">monetization_on</i>
                        </div>
                        <div class="content">
                            <div class="text">BETS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                arsort($bet_files);

                $current_counter = 1;
                foreach ($bet_files as $bet_file_info)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($bet_file_info['name'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . date('l, d F Y (T), H:i', $bet_file_info['date']) . '</td>
                        <td><a href="/bet_details?file=' . $bet_file_info['name'] . '">Details</a></td>
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
                                Bets
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>FILE NAME</th>
                                        <th>DATE</th>
                                        <th>&nbsp;</th>
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
                                HELP - About Bets
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-success" style="line-height: 2em;">
                                <ul>
                                    <li>For users to be able to place bets, they need Loyalty Points. To turn on the LP, check <a href="/loyaltypoints">Loyalty Points help</a>.</li>
                                    <li>To start a bet, you should provide a clear description to your viewers, for them to choose their desired option and the amount of LP points to bet.</li>
                                    <li>The bot accepts only numerical options to bet, you will need to guide your viewers in choosing a numerical value as the option to bet</li>
                                    <li>The &lt;start bet&gt; command, should be structured as follows: &lt;start bet keyword you have defined&gt; &lt;period in seconds when users are allowed to bet&gt; &lt;bet description&gt;.</li>
                                    <li>The &lt;end bet&gt; command, should be structured as follows: &lt;end bet keyword you have defined&gt; &lt;winning option&gt;.</li>
                                    <li>You can use the &lt;cancel bet&gt; command to cancel an active bet. The users who had already placed a bet, will be refunded their LPs.</li>
                                 </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
