<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Polls</h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($poll_files))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-pink info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">insert_chart</i>
                                </div>
                                <div class="content">
                                    <div class="text">POLLS</div>
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

                $total_count = count($poll_files);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">insert_chart</i>
                        </div>
                        <div class="content">
                            <div class="text">POLLS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                arsort($poll_files);

                $current_counter = 1;
                foreach ($poll_files as $poll_file_info)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($poll_file_info['name'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . date('l, d F Y (T), H:i', $poll_file_info['date']) . '</td>
                        <td><a href="/poll_details?file=' . $poll_file_info['name'] . '">Details</a></td>
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
                                Polls
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
                                HELP - About Polls
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-success" style="line-height: 2em;">
                                <ul>
                                 <li>To configure the poll commands, open with a text editor the conf/config.php file and check the "Polls config" section.</li>
                                 <li>Assuming the poll commands are set to their default values, to start the poll, any of the bot administrators can run: <br/>
                                 !makepoll <poll duration in seconds> <poll title> <br/>
                                 for example, to make a poll about what game to play next, that viewers can vote for the next 2 minutes:<br/><br/>
                                 !makepoll 120 What should i play next? Press 1 for League of Legends, 2 for GTA, 3 for CS:GO.<br/><br/>
                                 </li>
                                 <li>!cancelpoll to stop and cancel the ongoin poll.</li>
                                 <li>the Bot accepts all numeric responses the viewers may send. its up to Admin to discard the invalid ones, once he gets the summary at the poll's closure or checking the poll's summary via this GUI.</li>
                                 <li>When poll timer expires, the poll will be automatically stopped, an automated response will be displayed in Twitch Chat and a summary file will be saved, and will be displayed in the list above for the administrator to view all the poll's details!</li>
                                 <li>The poll files can be found under polls/ folder.</li>
                                 </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
