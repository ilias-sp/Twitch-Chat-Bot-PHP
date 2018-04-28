<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Giveaway details<?php if (isset($giveaway_filename) && mb_strlen($giveaway_filename) > 0) {echo ' - <span class="font-bold col-orange">' . $giveaway_filename . '</span>'; }?></h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $giveaway_tables_text = '';
            $total_count = 0;

            // var_dump($giveaway_details);

            if (($giveaway_details) === FALSE)
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
                                    <div class="text">Joined the Giveaway</div>
                                    <div class="number">' . $total_count . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
                $giveaway_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Giveaway
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> The file you specified was not found. Make sure you used a valid link.<br/><br/>
                            <a href="/giveaways_home" class="btn btn-default btn-lg waves-effect">BACK TO GIVEAWAYS HOME</a>
                        </div>
                        </div>
                    </div>
                </div>
';
            }
            else
            {

                // prepare widget:

                $giveaway_details_array = json_decode($giveaway_details, TRUE);

                // $votes_array = array();
                foreach ($giveaway_details_array as $k => $v)
                {
                    if ($k === 'Giveaway enrolled users')
                    {
                        $giveaway_enrolled_users_list = $v;
                    }
                    elseif ($k === 'Giveaway start date')
                    {
                        $giveaway_date = $v;
                    }
                    elseif ($k === 'Giveaway winners')
                    {
                        $giveaway_winners_list = $v;
                    }
                    elseif ($k === 'Giveaway description')
                    {
                        $giveaway_description = $v;
                    }
                }

                $total_count = count($giveaway_enrolled_users_list);

                //

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">recent_actors</i>
                        </div>
                        <div class="content">
                            <div class="text">Joined the Giveaway</div>
                            <div class="number">' . $total_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';
                
                // ------------ prepare giveaways winners table START

                $table_winners_text = '';
                $current_counter = 1;

                asort($giveaway_winners_list);

                foreach ($giveaway_winners_list as $username)
                {
                    $table_winners_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . $username . '</td>
                        <td><a href="https://www.twitch.tv/' . $username . '" target="_blank">profile on Twitch</a></td>
                    </tr>
';
                }

                // ------------ prepare giveaways winner table END


                // ------------ prepare enrolled users table START

                $table_giveaway_enrolled_viewers_details_text = '';
                $current_counter = 1;

                asort($giveaway_enrolled_users_list);

                foreach ($giveaway_enrolled_users_list as $username)
                {
                    $table_giveaway_enrolled_viewers_details_text .= '
                    <tr>
                        <th scope="row">' . $current_counter . '</th>
                        <td>' . $username . '</td>
                        <td><a href="https://www.twitch.tv/' . $username . '" target="_blank">profile on Twitch</a></td>
                    </tr>
';
                    $current_counter++;
                }



                // ------------ prepare enrolled users table END


                $giveaway_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="body table-responsive">
                        <div class="alert alert-info">
                        ' . $giveaway_description . '
                        </div>

                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Giveaway winners
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-4">#</th>
                                        <th class="col-xs-4">USERNAME</th>
                                        <th class="col-xs-4">PROFILE ON TWITCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_winners_text . '
                                </tbody>
                            </table>

                        
                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Giveaway enrolled viewers
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-4">#</th>
                                        <th class="col-xs-4">USERNAME</th>
                                        <th class="col-xs-4">PROFILE ON TWITCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_giveaway_enrolled_viewers_details_text . '
                                </tbody>
                            </table>


                        </div>
                    </div>

                    <div class="alert alert-info">
                        <a href="/giveaways_home" class="btn btn-default btn-lg waves-effect">BACK TO GIVEAWAYS HOME</a>
                    </div>
                    
                </div>
';

            }

            // print response:

            echo $widget_text;
                            
            echo $giveaway_text;

            ?>

        </div>
    </section>
