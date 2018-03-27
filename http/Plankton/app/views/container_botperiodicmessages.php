    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Periodic Messages</h2>
            </div>

            <?php

            $bot_periodic_messages = json_decode($periodic_messages, true);

            $table_text = '';
            $widget_text = '';
            $total_count = 0;

            if (!is_array($bot_periodic_messages))
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-orange info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">update</i>
                                </div>
                                <div class="content">
                                    <div class="text">PERIODIC MESSAGES</div>
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

                $total_count = count($bot_periodic_messages);

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-orange info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">update</i>
                        </div>
                        <div class="content">
                            <div class="text">PERIODIC MESSAGES</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                // prepare table:
                asort($bot_periodic_messages);

                $current_counter = 1;
                foreach ($bot_periodic_messages as $bot_periodic_message)
                {
                    $table_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . htmlspecialchars($bot_periodic_message, ENT_QUOTES, 'UTF-8') . '</td>
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
                                Periodic Messages
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>PERIODIC MESSAGE TEXT</th>
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

        </div>
    </section>
