<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Commands Usage statistics</h2>
            </div>

            <?php

            $bot_commands_usage = json_decode($commands_usage, true);

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($bot_commands_usage))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-cyan info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">build</i>
                                </div>
                                <div class="content">
                                    <div class="text">COMMANDS</div>
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

                $total_count = count($bot_commands_usage);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">build</i>
                        </div>
                        <div class="content">
                            <div class="text">COMMANDS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                ksort($bot_commands_usage);

                $current_counter = 1;
                foreach ($bot_commands_usage as $bot_command => $bot_command_usage)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($bot_command, ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars($bot_command_usage, ENT_QUOTES, 'UTF-8') . '</td>
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
                                Commands Usage
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>COMMAND</th>
                                        <th>USAGE (COUNT)</th>
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
                                HELP - About Commands Usage
                            </h2>
                        </div>
                        <div class="body">
                            <div class="alert alert-info">
                                <ul>
                                 <li>In this tab, you can see the statistics of your bot's commands, how many times each command was requested in your chat.</li>
                                 </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
