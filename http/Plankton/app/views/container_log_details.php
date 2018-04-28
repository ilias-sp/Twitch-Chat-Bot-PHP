<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Twitch chat log details<?php if (isset($poll_filename) && mb_strlen($poll_filename) > 0) {echo ' - <span class="font-bold col-orange">' . $poll_filename . '</span>'; }?></h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $poll_text = '';
            $poll_tables_text = '';
            $total_count = 0;

            // var_dump($poll_details);

            if (($log_details) === FALSE)
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-pink info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">recent_actors</i>
                                </div>
                                <div class="content">
                                    <div class="text">VOTES</div>
                                    <div class="number">' . $total_count . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
                $poll_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Votes Summary
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> The file you specified was not found. Make sure you used a valid link.<br/><br/>
                            <a href="/polls_home" class="btn btn-default btn-lg waves-effect">BACK TO POLLS HOME</a>
                        </div>
                        </div>
                    </div>
                </div>
';
            }
            else
            {

                // prepare widget:

                $total_count = count(explode("\n", $log_details));

                //

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">description</i>
                        </div>
                        <div class="content">
                            <div class="text">ROWS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                
                // prepare poll_tables_text:

                // $votes_unique_options = array_unique(array_values($votes_array));



                $log_file_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">' . $log_filename  . '</span> - Log details
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">

                            <div style="background-color: #222; color: #c9c9c9; padding: 10px;">
                                ' . nl2br(htmlspecialchars($log_details)) . '
                            </div>

                        </div>
                    </div>

                    <div class="alert alert-info">
                        <a href="/history/twitchchat" class="btn btn-default btn-lg waves-effect">BACK TO LOGS HOME</a>
                    </div>

                </div>
';

            }

            // print response:

            echo $widget_text;
                            
            echo $log_file_text;

            ?>

        </div>
    </section>
