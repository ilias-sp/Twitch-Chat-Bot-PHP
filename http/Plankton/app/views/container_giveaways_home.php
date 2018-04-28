<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Giveaways</h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($giveaway_files))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-green info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">pie_chart</i>
                                </div>
                                <div class="content">
                                    <div class="text">GIVEAWAYS</div>
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

                $total_count = count($giveaway_files);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-green info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">pie_chart</i>
                        </div>
                        <div class="content">
                            <div class="text">GIVEAWAYS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                arsort($giveaway_files);

                $current_counter = 1;
                foreach ($giveaway_files as $giveaway_file_info)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($giveaway_file_info['name'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . date('l, d F Y (T), H:i', $giveaway_file_info['date']) . '</td>
                        <td><a href="/giveaway_details?file=' . $giveaway_file_info['name'] . '">Details</a></td>
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
                                Giveaways
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
                                HELP - About Giveaways
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-success" style="line-height: 2em;">
                                <ul>
                                 <li>To configure the giveaway commands, open with a text editor the conf/config.php file and check the "giveaways config" section.</li>
                                 <li>Assuming the giveaway commands are set to their default values, to start the giveaway, any of the bot administrators can run: <br/>
                                 !giveaway-start <giveaway title> <br/>
                                 for example, to make a giveaway for a gaming mouse:<br/><br/>
                                 !giveaway for a gaming mouse<br/><br/>
                                 </li>
                                 <li>Viewers who want to enter the giveaway, should send:<br/><br/>!giveaway<br/><br/></li>
                                 <li>!giveaway-end to stop the bot from accepting new viewers.</li>
                                 <li>!giveaway-winner to have the bot select a random viewer from the list of viewers who joined the giveaway. If more than 1 winners are needed, repeat the command accordingly.</li>
                                 <li>!giveaway-reset to clear up the current giveaway's data from the IzyBot's memory. Use this if you are planning to start a new giveaway. It will also trigger the generation of a summary file to be saved to disk, and will be displayed in the list above for the administrator to view all the giveaway's details!</li>
                                 <li>The giveaway files can be found under giveaways/ folder.</li>
                                 </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
