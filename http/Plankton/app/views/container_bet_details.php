<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Bet details<?php if (isset($bet_filename) && mb_strlen($bet_filename) > 0) {echo ' - <span class="font-bold col-orange">' . $bet_filename . '</span>'; }?></h2>
            </div>

            <?php

            $table_text = '';
            $widget_text = '';
            $bet_tables_text = '';
            $total_count = 0;

            // var_dump($bet_details);

            if (($bet_details) === FALSE)
            {
                // file not found or malformed:
                $widget_text = '
                    <div class="row clearfix">
                        <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                            <div class="info-box bg-light-green info-box hover-zoom-effect">
                                <div class="icon">
                                    <i class="material-icons">monetization_on</i>
                                </div>
                                <div class="content">
                                    <div class="text">JOINED THE BET</div>
                                    <div class="number">' . $total_count . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
        
        ';
                
                $bet_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Bet
                                <small>&nbsp;</small>
                            </h2>
                        </div>
                        <div class="body table-responsive">
                        <div class="alert alert-danger">
                            <strong>Oh snap!</strong> The file you specified was not found. Make sure you used a valid link.<br/><br/>
                            <a href="/bets_home" class="btn btn-default btn-lg waves-effect">BACK TO BETS HOME</a>
                        </div>
                        </div>
                    </div>
                </div>
';
            }
            else
            {

                // prepare widget:

                $bet_details_array = json_decode($bet_details, TRUE);

                foreach ($bet_details_array as $k => $v)
                {
                    if ($k === 'Bet description')
                    {
                        $bet_description = $v;
                    }
                    elseif ($k === 'Bet closure status')
                    {
                        $bet_close_status = $v;
                    }
                    elseif ($k === 'Total Bets count')
                    {
                        $total_bets_count = $v;
                    }
                    elseif ($k === 'Winners count')
                    {
                        $total_winners = $v;
                    }
                    elseif ($k === 'Losers count')
                    {
                        $total_losers = $v;
                    }
                    elseif ($k === 'Total bet amount')
                    {
                        $total_bet_amount = $v;
                    }
                    elseif ($k === 'Total bets won amount')
                    {
                        $total_bet_amount_won = $v;
                    }
                    elseif ($k === 'Total bets lost amount')
                    {
                        $total_bet_amount_lost = $v;
                    }
                    elseif ($k === 'Bets')
                    {
                        $bets_array = $v;
                    }
                    elseif ($k === 'Bet start date')
                    {
                        $bet_start_date = $v;
                    }
                    elseif ($k === 'Bet end date')
                    {
                        $bet_end_date = $v;
                    }
                    elseif ($k === 'Bet winning option')
                    {
                        $bet_winning_option = $v;
                    }
                }

                //

                $widget_text = '
            <div class="row clearfix">
                <div class="col-lg-offset-9 col-lg-3 col-md-offset-9 col-md-3 col-sm-offset-6 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">monetization_on</i>
                        </div>
                        <div class="content">
                            <div class="text">JOINED THE BET</div>
                            <div class="number">' . $total_bets_count . '</div>
                        </div>
                    </div>
                </div>
            </div>

';

                // ------------ prepare bet winners table START

                $table_winners_text = '';
                $table_losers_text = '';
                $current_winners_counter = 1;
                $current_losers_counter = 1;

                asort($bets_array);

                foreach ($bets_array as $bet)
                {
                    if ($bet['option'] === $bet_winning_option)
                    {
                        $table_winners_text .= '
                        <tr>
                            <th scope="row">' . $current_winners_counter . '</th>
                            <td>' . $bet['username'] . '</td>
                            <td>' . $bet['amount'] . '</td>
                            <td>' . $bet['option'] . '</td>
                            <td>' . date('l, d F Y (T), H:i', $bet['bet_date']) . '</td>
                            <td><a href="https://www.twitch.tv/' . $bet['username'] . '" target="_blank">profile on Twitch</a></td>
                        </tr>
    ';
                        $current_winners_counter++;
                    }
                    else
                    {
                        $table_losers_text .= '
                        <tr>
                            <th scope="row">' . $current_losers_counter . '</th>
                            <td>' . $bet['username'] . '</td>
                            <td>' . $bet['amount'] . '</td>
                            <td>' . $bet['option'] . '</td>
                            <td>' . date('l, d F Y (T), H:i', $bet['bet_date']) . '</td>
                            <td><a href="https://www.twitch.tv/' . $bet['username'] . '" target="_blank">profile on Twitch</a></td>
                        </tr>
    ';
                        $current_losers_counter++;
                    }
                }

                // ------------ prepare bet winner table END


                $bet_text = '
                <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="body table-responsive">

                        <div class="header">
                            <h2>
                                <span class="font-bold col-orange">Bet information
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="col-xs-4">&nbsp;</th>
                                    <th class="col-xs-8">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Bet command</td>
                                    <td>' . $bet_description . '</td>
                                </tr>
                                <tr>
                                    <td>Bet winning option</td>
                                    <td>' . $bet_winning_option . '</td>
                                </tr>
                                <tr>
                                    <td>Bet closure status</td>
                                    <td>' . $bet_close_status . '</td>
                                </tr>
                                <tr>
                                    <td>Total Bets count</td>
                                    <td>' . $total_bets_count . '</td>
                                </tr>
                                <tr>
                                    <td>Winners count</td>
                                    <td>' . $total_winners . '</td>
                                </tr>
                                <tr>
                                    <td>Losers count</td>
                                    <td>' . $total_losers . '</td>
                                </tr>
                                <tr>
                                    <td>Total bet amount</td>
                                    <td>' . $total_bet_amount . '</td>
                                </tr>
                                <tr>
                                    <td>Total amount won</td>
                                    <td>' . $total_bet_amount_won . '</td>
                                </tr>
                                <tr>
                                    <td>Total amount lost</td>
                                    <td>' . $total_bet_amount_lost . '</td>
                                </tr>
                                <tr>
                                    <td>Bet start date</td>
                                    <td>' . date('l, d F Y (T), H:i', $bet_start_date) . '</td>
                                </tr>
                                <tr>
                                    <td>Bet end date</td>
                                    <td>' . date('l, d F Y (T), H:i', $bet_end_date) . '</td>
                                </tr>
                            </tbody>
                        </table>
                        

                        <div class="header">
                            <h2>
                                <span class="font-bold col-green">Bet winners
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-1">#</th>
                                        <th class="col-xs-3">USERNAME</th>
                                        <th class="col-xs-2">AMOUNT</th>
                                        <th class="col-xs-1">OPTION</th>
                                        <th class="col-xs-3">BET PLACE DATE</th>
                                        <th class="col-xs-2">PROFILE ON TWITCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_winners_text . '
                                </tbody>
                            </table>

                        
                        <div class="header">
                            <h2>
                                <span class="font-bold col-red">Bet losers
                                <small>&nbsp;</small>
                            </h2>
                        </div>

                            <table class="table table-hover js-basic-example dataTable">
                                <thead>
                                    <tr>
                                        <th class="col-xs-1">#</th>
                                        <th class="col-xs-3">USERNAME</th>
                                        <th class="col-xs-2">AMOUNT</th>
                                        <th class="col-xs-1">OPTION</th>
                                        <th class="col-xs-3">BET PLACE DATE</th>
                                        <th class="col-xs-2">PROFILE ON TWITCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ' . $table_losers_text . '
                                </tbody>
                            </table>


                        </div>
                    </div>

                    <div class="alert alert-info">
                        <a href="/bets_home" class="btn btn-default btn-lg waves-effect">BACK TO BETS HOME</a>
                    </div>
                    
                </div>
';

            }

            // print response:

            echo $widget_text;
                            
            echo $bet_text;

            ?>

        </div>
    </section>
