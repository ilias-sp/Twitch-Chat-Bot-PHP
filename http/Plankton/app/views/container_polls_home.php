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
                        <td><a href="/poll_details?file=' . $poll_file_info['name'] . '" target="_blank">Details</a></td>
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
                            <table class="table table-hover">
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

        </div>
    </section>
