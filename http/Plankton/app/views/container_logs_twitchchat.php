<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>History - Twitch chat logs</h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($log_files))
            {
                $log_files_IRC = false;
            }
            else
            {
                foreach ($log_files as $file)
                {
                    if (preg_match('/^(IzyBot_IRC_)(.*)(.txt)$/', $file['name'], $matches) === 1)
                    {
                        $log_files_IRC[] = $file;
                    }
                }
            }

            if (@!is_array($log_files_IRC))
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
                                    <div class="text">TWITCH CHAT LOGS</div>
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

                $total_count = count($log_files_IRC);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">insert_chart</i>
                        </div>
                        <div class="content">
                            <div class="text">TWITCH CHAT LOGS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                arsort($log_files);

                $current_counter = 1;
                foreach ($log_files_IRC as $log_file_info)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($log_file_info['name'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . date('l, d F Y (T), H:i', $log_file_info['date']) . '</td>
                        <td><a href="/history/twitchchat_log_details?file=' . $log_file_info['name'] . '">Details</a></td>
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
                                HELP - About Twitch Chat logs
                            </h2>
                        </div>
                        <div class="body">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
