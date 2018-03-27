<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Poll details<?php if (isset($poll_filename) && mb_strlen($poll_filename) > 0) {echo ' - <span class="font-bold col-orange">' . $poll_filename . '</span>'; }?></h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $poll_text = '';
            $poll_tables_text = '';
            $total_count = 0;

            // var_dump($poll_details);

            if (($poll_details) === FALSE)
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

                $poll_details_array = json_decode($poll_details, TRUE);

                // vote count:
                $votes_summary = '';
                $votes_array = array();
                foreach ($poll_details_array as $k => $v)
                {
                    if ($k === 'Votes')
                    {
                        $votes_array = $v;
                    }
                    elseif ($k === 'Poll result')
                    {
                        $votes_summary = $v;
                    }
                    elseif ($k === 'Poll description')
                    {
                        $poll_description = $v;
                    }
                }

                $total_count = count($votes_array);

                //

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
                
                // prepare poll_tables_text:

                // $votes_unique_options = array_unique(array_values($votes_array));

                $votes_vote_options_counts = array_count_values($votes_array);
                arsort($votes_vote_options_counts);

                // ------------ prepare vote summary table START

                $table_vote_counts_text = '';
                $current_counter = 1;

                foreach ($votes_vote_options_counts as $vote_option => $vote_count)
                {
                    $table_vote_counts_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . $vote_option . '</td>
                        <td>' . $vote_count . '</td>
                    </tr>
';
                }

                // ------------ prepare vote summary table END


                // ------------ prepare votes in detail table START

                $table_vote_details_text = '';
                $current_counter = 1;

                ksort($votes_array);

                foreach ($votes_array as $username => $voted_option)
                {
                    $table_vote_details_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . $username . '</td>
                        <td>' . $voted_option . '</td>
                    </tr>
';
                    $current_counter++;
                }



                // ------------ prepare votes in detail table END


                $poll_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">' . $poll_description  . '</span> - Votes Summary
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                        <div class="alert alert-info">
                            ' . $votes_summary . '
                        </div>

                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Votes summary
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-4">#</th>
                                        <th class="col-xs-4">VOTE OPTION</th>
                                        <th class="col-xs-4">VOTE COUNTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_vote_counts_text . '
                                </tbody>
                            </table>

                        
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Votes in details
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-4">#</th>
                                        <th class="col-xs-4">USERNAME</th>
                                        <th class="col-xs-4">VOTED OPTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_vote_details_text . '
                                </tbody>
                            </table>


                        </div>
                    </div>
                </div>
';

            }

            // print response:

            echo $widget_text;
                            
            echo $poll_text;

            ?>

        </div>
    </section>
