<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Config.php file</h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $poll_text = '';
            $poll_tables_text = '';
            $total_count = 0;

            if (($configfile_details) === FALSE)
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-pink info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">build</i>
                                </div>
                                <div class="content">
                                    <div class="text">ROWS</div>
                                    <div class="number">' . $total_count . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
                $configfile_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Config.php</span> - file contents
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> The file you specified was not found. Make sure the file exists in the relevant directory.<br/><br/>
                            <a href="/" class="btn btn-default btn-lg waves-effect">BACK TO HOME</a>
                        </div>
                        </div>
                    </div>
                </div>
';
            }
            else
            {

                // prepare widget:

                $total_count = count(explode("\n", $configfile_details));

                //

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">build</i>
                        </div>
                        <div class="content">
                            <div class="text">ROWS</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                

                $configfile_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Config.php</span> - file contents
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">

                            <div style="background-color: #222; color: #c9c9c9; padding: 10px;">
                                ' . nl2br(htmlspecialchars($configfile_details)) . '
                            </div>

                        </div>
                    </div>

                    <div class="body">
                        <div class="alert alert-success" style="line-height: 2em;">
                            <ul>
                            <li>To edit parameters defined in this file, open the <b>conf/config.php</b> with a textfile editor (notepad, notepad++).</li>
                            </ul>
                        </div>
                    </div>

                </div>
';

            }

            // print response:

            echo $widget_text;
                            
            echo $configfile_text;

            ?>

        </div>
    </section>
