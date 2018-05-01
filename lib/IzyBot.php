<?php

namespace IZYBOT\lib;

use \DateTime;
// use IZYBOT\lib\AppDataHandler as AppDataHandler;




class IzyBot {

    private $bot_config;
    private $hostname;
    private $port;

    private $oath_pass;
    private $nickname;
    private $channel;
    private $bot_name;

    private $socket;

    private $log_level;

    private $logger;
    private $IRC_logger;

    private $admin_commands;
    private $admin_commands_nonsafe;
    private $admin_commands_reserved_names;
    private $admin_commands_file;
    private $admin_commands_nonsafe_file;
    private $admin_usernames;
    private $admin_usernames_file;

    private $start_timestamp;

    private $duplicate_message_cuttoff_seconds;
    private $bot_responses_last_date;

    // bot commands usage stats:
    private $bot_commands_usage;
    private $bot_commands_usage_file;
    private $bot_commands_usage_flush_to_file_interval_seconds = 60;
    private $bot_commands_usage_last_date_flushed;

    // periodic messages:
    private $periodic_messages_interval_seconds;
    private $periodic_messages;
    private $periodic_messages_file;
    private $periodic_messages_last_message_sent_index;
    private $periodic_messages_last_date_sent;

    // quotes:
    private $quotes;
    private $quotes_file;

    // poll stuff:
    private $poll_question;
    private $active_poll_exists;
    private $votes_array;
    private $poll_deadline_timestamp;
    private $poll_duration;

    // bets:
    private $bet_description = '';
    private $bet_currently_running = FALSE;
    private $bet_currently_accepting = FALSE;
    private $bets_array = array();
    private $bet_start_time;
    private $bet_end_time;
    private $bet_accept_end_time;
    private $bet_winning_option;

    // giveaway:
    private $giveaway_currently_enabled;
    private $giveaway_viewers_list;
    private $giveaway_start_time;
    private $giveaway_description;
    private $giveaway_winners_list;

    // loyalty points:
    private $loyalty_currency;
    private $loyalty_points_per_interval;
    private $loyalty_check_interval;
    private $loyalty_check_last_date_done;
    private $loyalty_viewers_XP_array;
    private $loyalty_viewers_XP_file;
    private $loyalty_commands;

    // classes:
    private $appdatahandler;

    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function __construct($config)
    {
        $this->start_timestamp = date('U');
        
        $this->bot_config = $config;
        $this->bot_config['botinfocommand_keyword'] = '!botinfo';
        
        // twitch info:
        $this->hostname = 'irc.twitch.tv';
        $this->port = 6667;

        // loggers:
        $logger_config = array ('log_file_prefix' => $this->bot_config['log_file_prefix']
        );
        $this->logger = new Logger($logger_config);

        $logger_config = array ('log_file_prefix' => $this->bot_config['log_file_prefix_IRC']
        );
        $this->IRC_logger = new Logger($logger_config);

        // classes:
        $this->appdatahandler = new AppDataHandler($this->bot_config, $this->logger);

        // logging info:
        // $this->log_file = APPPATH . '/log/' . $config['log_file'] . '__' . date('Ymd_H_i') . '.txt';
        // $this->log_file_irc = (mb_strlen($config['log_file_irc']) > 0) ? APPPATH . '/log/' . $config['log_file_irc'] . '__' . date('Ymd_H_i') . '.txt' : '';
        // $this->log_level = $config['log_level'];

        // channel info:
        $this->oath_pass = $config['oath_pass'];
        $this->nickname = $config['nickname'];
        $this->channel = '#' . $config['channel'];
        $this->bot_name = $config['bot_name'];
        
        // admin commands:
        $this->admin_commands_file = 'admin_commands.cfg';
        $this->admin_commands_nonsafe_file = 'admin_commands_nonsafe.cfg';
        $this->admin_commands = array();
        $this->admin_commands_nonsafe = array();
        $this->admin_commands_reserved_names = array($config['admin_addcommand_keyword'],
                                                     $config['admin_editcommand_keyword'], 
                                                     $config['admin_removecommand_keyword'], 
                                                     $config['admin_addadmin_keyword'],
                                                     $config['admin_removeadmin_keyword'],
                                                     $config['admin_addperiodicmsg_keyword'],
                                                     $config['admin_removeperiodicmsg_keyword'],
                                                     $config['helpcommand_keyword'],
                                                     $config['uptimecommand_keyword'],
                                                     $config['admin_makepoll_keyword'],
                                                     $config['admin_cancelpoll_keyword'],
                                                     $config['admin_giveaway_start_keyword'],
                                                     $config['admin_giveaway_stop_keyword'],
                                                     $config['admin_giveaway_find_winner_keyword'],
                                                     $config['admin_giveaway_status_keyword'],
                                                     $config['admin_giveaway_reset_keyword'],
                                                     $config['admin_addquote_keyword'],
                                                     $config['admin_removequote_keyword'],
                                                     $config['quote_keyword'],
                                                     $config['giveaway_join_keyword'],
                                                     $config['admin_startbet_keyword'],
                                                     $config['admin_endbet_keyword'],
                                                     $config['admin_cancelbet_keyword'],
                                                     $config['bet_place_keyword'],
                                                     $this->bot_config['botinfocommand_keyword']
        );
        
        // bot commands usage stats:
        $this->bot_commands_usage_file = 'bot_commands_usage_stats.cfg';
        $this->bot_commands_usage = array();
        $this->bot_commands_usage_last_date_flushed = date('U');

        // admins:
        $this->admin_usernames_file = 'admin_usernames.cfg';
        $this->admin_usernames = array();
        $this->admin_usernames[] = $config['nickname'];

        // periodic messages:
        $this->periodic_messages_last_date_sent = date('U');
        $this->periodic_messages = array();
        $this->periodic_messages_file = 'periodic_messages.cfg';
        $this->periodic_messages_last_message_sent_index = -1;
        $this->periodic_messages_interval_seconds = $config['periodic_messages_interval_seconds'];
        
        // quotes:
        $this->quotes_file = 'quotes.cfg';
        //
        $this->duplicate_message_cuttoff_seconds = $config['duplicate_message_cuttoff_seconds'];
        $this->bot_responses_last_date = array();

        // poll stuff:
        $this->active_poll_exists = FALSE;
        $this->votes_array = array();
        $this->poll_help_message = $config['poll_help_message'];

        // bets:

        // giveaway:
        $this->giveaway_currently_enabled = FALSE;
        $this->giveaway_viewers_list = array();
        $this->giveaway_start_time = NULL;
        $this->giveaway_description = '';
        $this->giveaway_winners_list = array();

        // loyalty points:
        $this->loyalty_currency = $config['loyalty_currency'];
        $this->loyalty_points_per_interval = $config['loyalty_points_per_interval'];
        $this->loyalty_check_interval = $config['loyalty_check_interval'];
        $this->loyalty_check_last_date_done = date('U');
        $this->loyalty_viewers_XP_file = 'loyalty_viewers_XP_array.cfg';

        if ($this->loyalty_points_per_interval > 0)
        {
            $this->admin_commands_reserved_names[] = $config['loyaltypoints_keyword'];
            $this->loyalty_commands = array($config['loyaltypoints_keyword']
            );
        }
        else
        {
            $this->loyalty_commands = array();
        }
        
        //
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, $this->bot_name . "'s initialization is complete!" . "\n");
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _open_socket()
    {
        // $this->socket = fsockopen($this->hostname, $this->port, $errno, $errstr);
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);

        // dont use below, twitch IRC has multiple servers, we dont want to connect to the one we had before:
        // if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1))
        // {
        //     $this->log_it('ERROR', __CLASS__, __FUNCTION__, 'Unable to set option on socket: ' . socket_strerror(socket_last_error($this->socket)));
        // }
        socket_clear_error($this->socket);
        
        if (socket_connect($this->socket, $this->hostname, $this->port) === FALSE)
        {
            $this->log_it('ERROR', __CLASS__, __FUNCTION__, 'Could not open connection to server: ' . $this->hostname . ', port: ' . $this->port);
            // $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Errno: ' . $errno . ', Errstr: ' . $errstr);
            echo __CLASS__ . ': Could not open connection to server: ' . $this->hostname . ', port: ' . $this->port . ', error: ' . socket_last_error($this->socket) . "\n";
            throw new \Exception('Could not open connection to server: ' . $this->hostname . ', port: ' . $this->port . ', error: ' . socket_last_error($this->socket));
        }
        else
        {
            socket_set_nonblock($this->socket);
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Opened connection successfully to server: ' . $this->hostname . ', port: ' . $this->port . '.');
            echo __CLASS__ . ': Opened connection successfully to server: ' . $this->hostname . ', port: ' . $this->port . '.' . "\n";
            return TRUE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _close_socket()
    {
        socket_close($this->socket);
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Connection to server ' . $this->hostname . ' closed.');
        echo __CLASS__ . ': Connection to server ' . $this->hostname . ' closed.' . "\n";
        //
        // unset($this->socket);
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function send_text_to_server($command_type, $text)
    {
        if ($command_type == 'service' ||
            $this->bot_config['listen_only_mode'] === FALSE)
        {
            $this->_log_irc_traffic('--> | ' . $text);
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, '--> | ' . $text);
            //
            return (socket_write($this->socket, $text . "\r\n") === FALSE) ? FALSE : TRUE;
        }
        else
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Bot on listen_only mode or command not needed for service. Supressing it.');
            return TRUE;
        }        
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_from_socket($raw = '')
    {
            $response = socket_read($this->socket, 1024);
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'After socket_read, error=|' . print_r(socket_last_error($this->socket), true) . '|' . print_r(socket_strerror(socket_last_error($this->socket)), true) . '| mb_strlen=|' . mb_strlen($response) . '|, strlen=|' . strlen($response) . '|');

            // it needs strlen and not mb_strlen below:
            //
            if ($response === FALSE)
            {
                return FALSE;
            }
            else if (strlen($response) === 0)
            {
                return $raw;
            }
            //
            if (strlen($response) === 1024)
            {
                return $this->_read_from_socket($raw . $response);
            }
            else
            {
                return $raw . $response;
            }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function main()
    {
        while (TRUE)
        {
            $this->_open_socket();
            sleep(2);
            $this->_login_to_twitch();
            //
            while (TRUE)
            {
                $text = $this->_read_from_socket();

                if (socket_last_error($this->socket) === 104)
                {
                    echo __CLASS__ . ': Detected connection was closed, error=|' . socket_strerror(socket_last_error($this->socket)) . "\n";
                    $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Detected connection was closed, error=|' . socket_strerror(socket_last_error($this->socket)));
                    goto ENDCONNECTION;
                }
                //---
                if ($text === FALSE)
                {
                    GOTO NOMESSAGE;
                }
                //-------------------
                // process line by line:
                $text_lines = explode("\r\n", $text);

                foreach ($text_lines as $line)
                {
                    if (mb_strlen($line) > 0)
                    {
                        $this->_log_irc_traffic('<-- | ' . $line); // delete last char, its NewLine.
                        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, '<-- | ' . $line); // delete last char, its Newline.
                        $this->_process_irc_incoming_message($line);
                    }                    
                }
                //-------------------
                //
                NOMESSAGE:
                $this->_check_and_send_periodic_message();
                $this->_check_and_query_loyalty_URL();
                $this->_check_and_flush_bot_commands_usage();
                if ($this->active_poll_exists === TRUE)
                {
                    $this->_monitor_ongoing_poll();
                }
                if ($this->bet_currently_accepting === TRUE)
                {
                    $this->_monitor_ongoing_bet();
                }
                sleep(1);
            }
            //
            ENDCONNECTION:
            $this->_close_socket();
            echo __CLASS__ . ': Attempting to reconnect in 15 seconds..' . "\n";
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Attempting to reconnect in 15 seconds..');
            sleep(15);
            //  
        }
        //
        return $this;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _getTimestamp()
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.'.$micro, $originalTime));

        return $date->format('Y-m-d G:i:s.u');
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _log_irc_traffic($message)
    {
        $this->IRC_logger->log_it('DEBUG', __CLASS__, __FUNCTION__, $message);

        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _process_irc_incoming_message($text)
    {
        if (preg_match("/PING :(.*)/i", $text, $match)) // PING:
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, "Requested to PING $match[1]");
            $this->send_text_to_server('service', "PONG :$match[1]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ JOIN (#\S+)/i", $text, $match)) // USER JOINED CHANNEL:
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, "User $match[1] joined channel $match[2]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ PART (#\S+)/i", $text, $match)) // USER LEFT CHANNEL:
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, "User $match[1] left channel $match[2]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ PRIVMSG (#\S+) :(.*)/i", $text, $match)) // USER MESSAGE:
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, "$match[1]@$match[2]: $match[3]");
            $this->_process_user_message($match[1], $match[2], $match[3]);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _process_user_message($username, $channel, $message_text)
    {
        $message_text = preg_replace('/\s+/', ' ', trim($message_text));
        $words_in_message_text = explode(' ', $message_text);
        
        // $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'words_in_message_text:' . "\n\n" . print_r($words_in_message_text, true) . "\n\n");

        // -----------------------------------------
        // commands for admins START
        if ($this->_check_user_is_admin($username) === TRUE) 
        {
            if ($words_in_message_text[0] === $this->bot_config['admin_addcommand_keyword']) // Add admin command check
            {
                if (count($words_in_message_text) >= 3)
                {
                    $this->_add_admin_command($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted admin command addition is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_editcommand_keyword']) // edit admin command check
            {
                if (count($words_in_message_text) >= 3)
                {
                    $this->_edit_admin_command($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted admin command edit is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_removecommand_keyword']) // remove admin command check
            {
                if (count($words_in_message_text) == 2)
                {
                    $this->_remove_admin_command($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted admin command removal is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_addadmin_keyword']) // add admin username check
            {
                if (count($words_in_message_text) == 2)
                {
                    $this->_add_admin_username($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted admin username addition is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_removeadmin_keyword']) // remove admin username check
            {
                if (count($words_in_message_text) == 2)
                {
                    $this->_remove_admin_username($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted admin username removal is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            //
            elseif ($words_in_message_text[0] === $this->bot_config['admin_addperiodicmsg_keyword']) // add periodic message check
            {
                if (count($words_in_message_text) > 2)
                {
                    $this->_add_periodic_message($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted periodic message addition is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_removeperiodicmsg_keyword']) // remove periodic message check
            {
                if (count($words_in_message_text) > 2)
                {
                    $this->_remove_periodic_message($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted periodic message removal is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_makepoll_keyword']) // make poll check
            {
                if (count($words_in_message_text) > 2)
                {
                    $this->_create_poll($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted poll creation command is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_cancelpoll_keyword']) // cancel poll check
            {
                if (count($words_in_message_text) === 1)
                {
                    $this->_cancel_poll($username, $channel, $words_in_message_text, $message_text);
                    return TRUE;
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Attempted poll cancellation command is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_status_keyword'])
            {
                $this->_giveaway_show_status();
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_start_keyword'])
            {
                $this->_giveway_status_modify('enable', $words_in_message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_stop_keyword'])
            {
                $this->_giveway_status_modify('disable', $words_in_message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_find_winner_keyword'])
            {
                $this->_giveaway_winner_get();
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_reset_keyword'])
            {
                $this->_giveaway_reset();
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_addquote_keyword'])
            {
                $this->_add_quote($username, $channel, $words_in_message_text, $message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_removequote_keyword'])
            {
                $this->_remove_quote($username, $channel, $words_in_message_text, $message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_startbet_keyword'])
            {
                $this->_start_bet($username, $channel, $words_in_message_text, $message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_endbet_keyword'])
            {
                $this->_end_bet($username, $channel, $words_in_message_text, $message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_cancelbet_keyword'])
            {
                $this->_cancel_bet($username, $channel, $words_in_message_text, $message_text);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['bet_place_keyword'])
            {
                $this->_register_bet($username, $channel, $words_in_message_text, $message_text);
                // add the bot command to usage:
                $this->_bot_command_add_usage($this->bot_config['bet_place_keyword']);
                return TRUE;
            }
            elseif ($message_text === $this->bot_config['loyaltypoints_keyword'])
            {
                $this->_display_loyalty_XP_of_viewer($username);
                // add the bot command to usage:
                $this->_bot_command_add_usage($this->bot_config['loyaltypoints_keyword']);
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['quote_keyword'])
            {
                $this->_display_quote($username, $channel, $words_in_message_text, $message_text);
                // add the bot command to usage:
                $this->_bot_command_add_usage($this->bot_config['quote_keyword']);
                return TRUE;
            }
        }
        //
        // commands for admins END 
        // -----------------------------------------
        // commands for all users START
        if ($message_text === $this->bot_config['helpcommand_keyword'])
        {
            $this->_display_help_command($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['helpcommand_keyword']);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['uptimecommand_keyword'])
        {
            $this->_display_uptime_command($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['uptimecommand_keyword']);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['botinfocommand_keyword'])
        {
            $this->_display_botinfo_command($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['botinfocommand_keyword']);
            return TRUE;
        }
        elseif (mb_strtolower($words_in_message_text[0]) === mb_strtolower($this->bot_config['votecommand_keyword']) &&
                $this->active_poll_exists === TRUE)
        {
            $this->_register_users_vote($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['votecommand_keyword']);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['loyaltypoints_keyword'])
        {
            $this->_display_loyalty_XP_of_viewer($username);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['loyaltypoints_keyword']);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['bet_place_keyword'])
        {
            $this->_register_bet($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['bet_place_keyword']);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['giveaway_join_keyword'])
        {
            $this->_giveaway_add_viewer($username);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['giveaway_join_keyword']);
            return TRUE;
        }
        elseif ($words_in_message_text[0] === $this->bot_config['quote_keyword'])
        {
            $this->_display_quote($username, $channel, $words_in_message_text, $message_text);
            // add the bot command to usage:
            $this->_bot_command_add_usage($this->bot_config['quote_keyword']);
            return TRUE;
        }
        //
        foreach ($this->admin_commands_nonsafe as $command => $html_code)
        {
            if ($words_in_message_text[0] == $command)
            {
                if ($this->_check_response_should_be_silenced($command) === FALSE)
                {
                    $response = $this->_run_eval_text($html_code, $words_in_message_text);
                    // var_dump($response);
                    
                    //
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Received command (nonsafe): ' . $command . ', replying it with: ' . $response);
                    //
                    if ($this->bot_config['reply_format'] === 1)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : @' . $username . ' ' . $response);
                    }
                    elseif ($this->bot_config['reply_format'] === 2)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $username . ' ' . $response);
                    }
                    elseif ($this->bot_config['reply_format'] === 3)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $response);
                    }
                    //                       
                    $this->_add_command_to_bot_responses_last_date($command);
                }
                // add the bot command to usage:
                $this->_bot_command_add_usage($command);
                return TRUE;
            }
        }
        //
        foreach ($this->admin_commands as $command => $response)
        {
            if ($words_in_message_text[0] == $command)
            {
                if ($this->_check_response_should_be_silenced($command) === FALSE)
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Received command: ' . $command . ', replying it with: ' . $response);
                    //
                    if ($this->bot_config['reply_format'] === 1)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : @' . $username . ' ' . $response);
                    }
                    elseif ($this->bot_config['reply_format'] === 2)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $username . ' ' . $response);
                    }
                    elseif ($this->bot_config['reply_format'] === 3)
                    {
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $response);
                    }
                    //                       
                    $this->_add_command_to_bot_responses_last_date($command);
                }
                // add the bot command to usage:
                $this->_bot_command_add_usage($command);
                return TRUE;
            }
        }
        //
        // commands for all users END 
        // -----------------------------------------
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    private function _login_to_twitch()
    {
        $this->send_text_to_server('service', 'PASS ' . $this->oath_pass);
        usleep(1000000);
        $this->send_text_to_server('service', 'NICK ' . $this->nickname);
        usleep(1000000);
        $this->send_text_to_server('service', 'CAP REQ :twitch.tv/membership');
        usleep(1000000);
        $this->send_text_to_server('service', 'CAP REQ :twitch.tv/commands');
        usleep(1000000);
        $this->send_text_to_server('service', 'JOIN ' . $this->channel);
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Login commands were sent. Bot is ready to serve commands.');
        echo __CLASS__ . ': Login commands were sent. Bot is ready to serve commands.' . "\n";
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_admin_commands()
    {
        // admin commands:

        $admin_commands_text = $this->appdatahandler->ReadAppDatafile($this->admin_commands_file, 'READ');

        if ($admin_commands_text[0] === TRUE)
        {
            $this->admin_commands = json_decode($admin_commands_text[2], true);
            if (!is_array($this->admin_commands))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot admin commands file: ' . $this->admin_commands_file . ' is malformed.');
                $this->admin_commands = array();
            }
        }
        else
        {
            $this->admin_commands = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot admin commands loaded:' . "\n\n" . print_r($this->admin_commands, true) . "\n\n");
        
        // admin commands nonsafe:

        $admin_commands_nonsafe_text = $this->appdatahandler->ReadAppDatafile($this->admin_commands_nonsafe_file, 'READ');

        if ($admin_commands_nonsafe_text[0] === TRUE)
        {
            $this->admin_commands_nonsafe = json_decode($admin_commands_nonsafe_text[2], true);
            if (!is_array($this->admin_commands_nonsafe))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot admin commands file: ' . $this->admin_commands_nonsafe_file . ' is malformed.');
                $this->admin_commands_nonsafe = array();
            }
        }
        else
        {
            $this->admin_commands_nonsafe = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot admin commands (nonsafe) loaded:' . "\n\n" . print_r($this->admin_commands_nonsafe, true) . "\n\n");
        
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_admin_usernames()
    {

        $admin_usernames_text = $this->appdatahandler->ReadAppDatafile($this->admin_usernames_file, 'READ');

        if ($admin_usernames_text[0] === TRUE)
        {
            $this->admin_usernames = json_decode($admin_usernames_text[2], true);
            if (!is_array($this->admin_usernames))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot admin commands file: ' . $this->admin_usernames_file . ' is malformed.');
                $this->admin_usernames = array();
            }
        }
        else
        {
            $this->admin_usernames = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot admin usernames loaded:' . "\n\n" . print_r($this->admin_usernames, true) . "\n\n");

        return TRUE;
        
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_periodic_messages()
    {

        $periodic_messages_text = $this->appdatahandler->ReadAppDatafile($this->periodic_messages_file, 'READ');

        if ($periodic_messages_text[0] === TRUE)
        {
            $this->periodic_messages = json_decode($periodic_messages_text[2], true);
            if (!is_array($this->periodic_messages))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot periodic_messages file: ' . $this->periodic_messages_file . ' is malformed.');
                $this->periodic_messages = array();
            }
        }
        else
        {
            $this->periodic_messages = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot periodic_messages loaded:' . "\n\n" . print_r($this->periodic_messages, true) . "\n\n");

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_bot_commands_usage()
    {

        $bot_commands_usage_text = $this->appdatahandler->ReadAppDatafile($this->bot_commands_usage_file, 'READ');

        if ($bot_commands_usage_text[0] === TRUE)
        {
            $this->bot_commands_usage = json_decode($bot_commands_usage_text[2], true);
            if (!is_array($this->bot_commands_usage))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot commands usage file: ' . $this->bot_commands_usage_file . ' is malformed.');
                $this->bot_commands_usage = array();
            }
        }
        else
        {
            $this->bot_commands_usage = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot commands usage loaded:' . "\n\n" . print_r($this->bot_commands_usage, true) . "\n\n");

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_quotes()
    {

        $quotes_text = $this->appdatahandler->ReadAppDatafile($this->quotes_file, 'READ');

        if ($quotes_text[0] === TRUE)
        {
            $this->quotes = json_decode($quotes_text[2], true);
            if (!is_array($this->quotes))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Bot quotes file: ' . $this->quotes_file . ' is malformed.');
                $this->quotes = array();
            }
        }
        else
        {
            $this->quotes = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bot quotes loaded:' . "\n\n" . print_r($this->quotes, true) . "\n\n");

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_admin_commands()
    {

        $this->appdatahandler->WriteAppDatafile($this->admin_commands_file, 'appdata', json_encode($this->admin_commands), 'WRITE');

        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_admin_usernames()
    {

        $this->appdatahandler->WriteAppDatafile($this->admin_usernames_file, 'appdata', json_encode($this->admin_usernames), 'WRITE');

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_periodic_messages()
    {
        
        $this->appdatahandler->WriteAppDatafile($this->periodic_messages_file, 'appdata', json_encode($this->periodic_messages), 'WRITE');

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_quotes()
    {
        
        $this->appdatahandler->WriteAppDatafile($this->quotes_file, 'appdata', json_encode($this->quotes), 'WRITE');

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_loyalty_viewers_XP()
    {
        
        $this->appdatahandler->WriteAppDatafile($this->loyalty_viewers_XP_file, 'appdata', json_encode($this->loyalty_viewers_XP_array), 'WRITE');

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_bot_commands_usage()
    {

        $this->appdatahandler->WriteAppDatafile($this->bot_commands_usage_file, 'appdata', json_encode($this->bot_commands_usage), 'WRITE');

        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function start_bot()
    {
        $this->_read_admin_commands();
        $this->_read_admin_usernames();
        $this->_read_periodic_messages();
        $this->_read_bot_commands_usage();
        $this->_read_quotes();
        $this->_read_loyalty_viewers_XP_array();
        //
        return $this;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function stop_bot()
    {
        $this->_write_admin_commands();
        $this->_write_bot_commands_usage();
        //
        return $this;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _check_user_is_admin($username)
    {
        if (mb_strtolower($username) === mb_strtolower($this->bot_config['channel']))
        {
            return TRUE;
        }
        //
        foreach ($this->admin_usernames as $admin_username)
        {
            if (mb_strtolower($admin_username) === mb_strtolower($username))
            {
                return TRUE;
            }
        }
        //
        return FALSE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_admin_command($username, $channel, $words_in_message_text, $message_text)
    {
        if (array_search($words_in_message_text[1], $this->admin_commands_reserved_names) !== FALSE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted admin command addition with keyword: ' . $words_in_message_text[1] . ' failed due to reserved keyword.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' could not be added (reserved keyword).');
            return FALSE;
        }
        //
        foreach ($this->admin_commands_nonsafe as $command => $response)
        {
            if ($words_in_message_text[1] == $command)
            {
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted admin command addition with keyword: ' . $words_in_message_text[1] . ' failed, command (nonsafe) already exists.');
                $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' already exists.');
                return FALSE;
            }
        }
        //
        foreach ($this->admin_commands as $command => $response)
        {
            if ($words_in_message_text[1] == $command)
            {
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted admin command addition with keyword: ' . $words_in_message_text[1] . ' failed, command already exists.');
                $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' already exists.');
                return FALSE;
            }
        }
        //
        $this->admin_commands[$words_in_message_text[1]] = implode(' ', array_slice($words_in_message_text, 2));
        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'admin command set: ' . $words_in_message_text[1] . ', to respond: ' . implode(' ', array_slice($words_in_message_text, 2)));
        $this->_write_admin_commands();
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' was added.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_admin_username($username, $channel, $words_in_message_text, $message_text)
    {
        if (array_search($words_in_message_text[1], $this->admin_usernames) !== FALSE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted admin addition with username: ' . $words_in_message_text[1] . ' failed, user is already admin.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : User ' . $words_in_message_text[1] . ' is already member of the admins.');
            RETURN FALSE;
        }
        //
        $this->admin_usernames[] = $words_in_message_text[1];
        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Username: ' . $words_in_message_text[1] . ' was added to the admins.');
        $this->_write_admin_usernames();
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : User ' . $words_in_message_text[1] . ' was added to the admins.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_periodic_message($username, $channel, $words_in_message_text, $message_text)
    {
        $periodic_message = implode(' ', array_slice($words_in_message_text, 1));
        //
        if (array_search($periodic_message, $this->periodic_messages) !== FALSE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted periodic message addition with text: ' . $periodic_message . ' failed, message already exists.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : This periodic message already exists.');
            RETURN FALSE;
        }
        //
        $this->periodic_messages[] = $periodic_message;
        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Periodic message: ' . $periodic_message . ' was added to the periodic_messages.');
        $this->_write_periodic_messages();
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Periodic message was added to the list.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_quote($username, $channel, $words_in_message_text, $message_text)
    {
        $quote_text = implode(' ', array_slice($words_in_message_text, 1));
        //
        if (array_search($quote_text, $this->quotes) !== FALSE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'attempted quote addition with text: ' . $quote_text . ' failed, quote already exists.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : This quote already exists.');
            RETURN FALSE;
        }
        //
        $next_id = max(array_column($this->quotes, "id")) + 1;
        $this->quotes[] = array( 'id' => $next_id,
            'text' => $quote_text
        );

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Quote: #' . $next_id . ' was added.');
        $this->_write_quotes();
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Quote: #' . $next_id . ' was added.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _edit_admin_command($username, $channel, $words_in_message_text, $message_text)
    {
        foreach ($this->admin_commands as $command => $response)
        {
            if ($words_in_message_text[1] == $command)
            {
                $this->admin_commands[$words_in_message_text[1]] = implode(' ', array_slice($words_in_message_text, 2));
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'admin command updated: ' . $words_in_message_text[1]);

                $this->_write_admin_commands();
                $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' was updated.');
                return TRUE;
            }
        }
        //
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : No such command: ' . $words_in_message_text[1]);
        //
        return FALSE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _remove_admin_command($username, $channel, $words_in_message_text, $message_text)
    {
        foreach ($this->admin_commands as $command => $response)
        {
            if ($words_in_message_text[1] == $command)
            {
                unset($this->admin_commands[$words_in_message_text[1]]);
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'admin command removed: ' . $words_in_message_text[1]);

                $this->_write_admin_commands();
                $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' was removed.');
                return TRUE;
            }
        }
        //
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : No such command: ' . $words_in_message_text[1]);
        //
        return FALSE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _remove_periodic_message($username, $channel, $words_in_message_text, $message_text)
    {
        $periodic_message = implode(' ', array_slice($words_in_message_text, 1));
        //        
        if (array_search($periodic_message, $this->periodic_messages) !== FALSE)
        {
            unset($this->periodic_messages[array_search($periodic_message, $this->periodic_messages)]);
            // reset the last sent index:
            $this->periodic_messages_last_message_sent_index = -1;
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Periodic message: ' . $periodic_message . ' was removed.');

            $this->_write_periodic_messages();
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Periodic message: ' . $periodic_message . ' was removed from the list.');
            return TRUE;
        }
        else
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Periodic message: ' . $periodic_message . ' was not found in the list.');
            return FALSE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _remove_quote($username, $channel, $words_in_message_text, $message_text)
    {
        $message_response = NULL;
            
        if (count($words_in_message_text) > 1)
        {
            if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) === 1)
            {
                $requested_quote_id = $words_in_message_text[1];
            }
            else
            {
                $message_response = 'The numeric id of the quote is expected to complete this command.';
                GOTO ENDOFREMOVEQUOTE;
            }
        }
        else
        {
            $message_response = 'The numeric id of the quote is expected to complete this command.';
            GOTO ENDOFREMOVEQUOTE;
        }
        
        //
        $key_id = NULL;
        
        foreach ($this->quotes as $array_key => $quote)
        {
            if ($quote['id'] == $requested_quote_id)
            {
                $key_id = $array_key;
            }
        }
        
        if ($key_id !== NULL)
        {
            unset($this->quotes[$key_id]);
            $this->_write_quotes();
            $message_response = 'Quote #' . $requested_quote_id . ' was deleted.';
            GOTO ENDOFREMOVEQUOTE;
        }
        else
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Quote #' . $requested_quote_id . ' was not found.');
            return FALSE;
        }
        // 
        ENDOFREMOVEQUOTE:
        if ($message_response !== NULL)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $message_response);
        }
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _remove_admin_username($username, $channel, $words_in_message_text, $message_text)
    {
        if (array_search($words_in_message_text[1], $this->admin_usernames) !== FALSE)
        {
            unset($this->admin_usernames[array_search($words_in_message_text[1], $this->admin_usernames)]);
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Username: ' . $words_in_message_text[1] . ' was removed from the admins.');

            $this->_write_admin_usernames();
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : User ' . $words_in_message_text[1] . ' was removed from the admins.');
            return TRUE;
        }
        else
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : User ' . $words_in_message_text[1] . ' is not member of the admins.');
            return FALSE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _display_help_command($username, $channel, $words_in_message_text, $message_text)
    {
        $message = 'Available commands: ';
        foreach ($this->admin_commands as $command => $response)
        {
            $message .= ' ' . $command;
        }
        //
        foreach ($this->admin_commands_nonsafe as $command => $response)
        {
            $message .= ' ' . $command;
        }
        //
        foreach ($this->loyalty_commands as $command)
        {
            $message .= ' ' . $command;
        }
        //
        $message .=  ' ' . $this->bot_config['helpcommand_keyword'] . ' ' . $this->bot_config['uptimecommand_keyword'] . ' ' . $this->bot_config['quote_keyword'] . ' ' . $this->bot_config['botinfocommand_keyword'] . ' .';
        //
        if ($this->_check_response_should_be_silenced($this->bot_config['helpcommand_keyword']) === FALSE)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' :' . $message);
            $this->_add_command_to_bot_responses_last_date($this->bot_config['helpcommand_keyword']);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _display_uptime_command($username, $channel, $words_in_message_text, $message_text)
    {
        $stream_uptime = timespan($this->start_timestamp);
        $message = $this->bot_name . ' is up for: ' . $stream_uptime . '.';
        //
        if ($this->_check_response_should_be_silenced($this->bot_config['uptimecommand_keyword']) === FALSE)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' :' . $message);
            $this->_add_command_to_bot_responses_last_date($this->bot_config['uptimecommand_keyword']);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _display_quote($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->_check_response_should_be_silenced($this->bot_config['quote_keyword']) === FALSE)
        {
            // check if quote # was included:

            $requested_quote_id = NULL;
            
            if (count($words_in_message_text) > 1)
            {
                if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) === 1)
                {
                    $requested_quote_id = $words_in_message_text[1];
                }
                else
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'quote parameter passed is not numeric: ' . $words_in_message_text[1]);
                    GOTO ENDDISPLAYQUOTEQUOTE;
                }
            }
            
            //
            if ($requested_quote_id === NULL)
            {
                if (count($this->quotes) === 0)
                {
                    $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'No quotes provisioned to respond with.');
                    GOTO ENDDISPLAYQUOTEQUOTE;
                }

                // select a random:
                $key_id = array_rand($this->quotes, 1);
                $quote_text = '#' . $this->quotes[$key_id]['id'] . ' - ' . $this->quotes[$key_id]['text'];
            }
            else
            {
                $key_id = NULL;

                foreach ($this->quotes as $array_key => $quote)
                {
                    if ($quote['id'] == $requested_quote_id)
                    {
                        $key_id = $array_key;
                    }
                }

                if ($key_id === NULL)
                {
                    // quote not found:
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Quote not found for id: #' . $requested_quote_id);
                    GOTO ENDDISPLAYQUOTEQUOTE;
                }
                else
                {
                    $quote_text = '#' . $this->quotes[$key_id]['id'] . ' - ' . $this->quotes[$key_id]['text'];
                }
            }
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : ' . $quote_text);
            $this->_add_command_to_bot_responses_last_date($this->bot_config['quote_keyword']);
        }
        //
        ENDDISPLAYQUOTEQUOTE:
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _display_botinfo_command($username, $channel, $words_in_message_text, $message_text)
    {
        $message = $this->bot_name . ' is free and can be found at: https://github.com/ilias-sp/Twitch-Chat-Bot-PHP';
        //
        if ($this->_check_response_should_be_silenced($this->bot_config['botinfocommand_keyword']) === FALSE)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' :' . $message);
            $this->_add_command_to_bot_responses_last_date($this->bot_config['botinfocommand_keyword']);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _check_response_should_be_silenced($attempted_command)
    {
        foreach ($this->bot_responses_last_date as $command => $last_used_date)
        {
            if ($command == $attempted_command)
            {
                if (date('U') - $last_used_date <= $this->duplicate_message_cuttoff_seconds)
                {
                    $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'returning TRUE (response should be suppressed) for command: ' . $attempted_command);
                    return TRUE;
                }
            }
        }
        //
        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'returning FALSE (response should be sent) for command: ' . $attempted_command);
        //
        return FALSE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_command_to_bot_responses_last_date($command)
    {
        $this->bot_responses_last_date[$command] = date('U');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _check_and_send_periodic_message()
    {
        if ($this->periodic_messages_interval_seconds > 0 && 
            date('U') - $this->periodic_messages_last_date_sent > $this->periodic_messages_interval_seconds &&
            count($this->periodic_messages) > 0
            )
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Time to send a periodic message.');
            // select next periodic message from the array:
            $this->periodic_messages_last_message_sent_index++;
            if ($this->periodic_messages_last_message_sent_index > (count($this->periodic_messages) - 1))
            {
                $this->periodic_messages_last_message_sent_index = 0;
            }
            $periodic_message = $this->periodic_messages[$this->periodic_messages_last_message_sent_index];
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $periodic_message);
            $this->periodic_messages_last_date_sent = date('U');
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _check_and_flush_bot_commands_usage()
    {
        if (date('U') - $this->bot_commands_usage_last_date_flushed > $this->bot_commands_usage_flush_to_file_interval_seconds &&
            count($this->bot_commands_usage) > 0
            )
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Time to flush the bot commands usage to file.');
            $this->_write_bot_commands_usage();
            $this->bot_commands_usage_last_date_flushed = date('U');
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _create_poll($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->active_poll_exists === TRUE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'There is an active poll already, ending in ' . ($this->poll_deadline_timestamp - date('U')) . ' seconds.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : There is an active poll already, ending in ' . ($this->poll_deadline_timestamp - date('U')) . ' seconds.');
            return FALSE;
        }
        else
        {
            if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) === 1)
            {
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Poll command was valid and no poll already exists. Creating New poll..');
                $this->active_poll_exists = TRUE;
                $this->poll_question = implode(' ', array_slice($words_in_message_text, 2));
                $this->votes_array = array();
                $this->poll_deadline_timestamp = date('U') + $words_in_message_text[1];

                $this->poll_duration = $words_in_message_text[1];
                //
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $this->bot_config['new_poll_announcement_message'] . ' for the next ' . $this->poll_duration . ' seconds: ' . $this->poll_question);
                return TRUE;
            }
            else
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Invalid poll duration: ' . $words_in_message_text[1]);
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Invalid poll duration: ' . $words_in_message_text[1]);
                return FALSE;
            }
            

        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _cancel_poll($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->active_poll_exists === FALSE)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'There is no active poll at the moment.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : There is no active poll at the moment.');
            return FALSE;
        }
        else
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Poll cancellation command was valid. Cancelling current poll..');
            $this->active_poll_exists = FALSE;
            
            // no need to write poll results to file..

            $this->poll_question = NULL;
            $this->votes_array = array();
            $this->poll_deadline_timestamp = NULL;

            $this->poll_duration = NULL;
            //
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Poll was cancelled.');
            return TRUE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _register_users_vote($username, $channel, $words_in_message_text, $message_text)
    {
        if (count($words_in_message_text) !== 2)
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Vote command is malformed. Command: ' . $message_text);
            return FALSE;
        }
        else
        {
            if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) === 1 &&
                strlen($words_in_message_text[1]) <= 5
                )
            {
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Vote command is accepted. Command: ' . $message_text);
                $this->votes_array[$username] = $words_in_message_text[1];
                return TRUE;
            }
            else
            {
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Vote command is malformed and rejected. Command: ' . $message_text);
                return FALSE;
            }
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _monitor_ongoing_bet()
    {
        if ($this->bet_accept_end_time <= date('U'))
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Bets acceptance period is over, no more accepting bets.');
            $this->bet_currently_accepting = FALSE;
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $this->bot_config['bet_accept_period_over_announcement_message']);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _monitor_ongoing_poll()
    {
        if ($this->poll_deadline_timestamp <= date('U'))
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Poll deadline date was reached. Closing current poll..');
            $this->active_poll_exists = FALSE;
            
            $poll_results = array();
            $votes_count = count($this->votes_array);
            //
            foreach ($this->votes_array as $user => $vote)
            {
                if (isset($poll_results[$vote]))
                {
                    $poll_results[$vote]++;
                }
                else
                {
                    $poll_results[$vote] = 1;
                }
            }
            //
            $results_text = 'Total votes: ' . $votes_count . '. ';
            //
            if ($votes_count > 0)
            {
                // sort in descending order first
                arsort($poll_results);
                //
                $current_row = 0;
                foreach ($poll_results as $poll_result => $poll_count)
                {
                    $vote_text = ($poll_count === 1) ? 'vote' : 'votes';
                    
                    if ($current_row === 0)
                    {
                        $results_text .= ' option ' . $poll_result . ': ' . $poll_count . ' ' . $vote_text . ' (' . intval((100*$poll_count)/$votes_count) . '%) ';
                    }
                    else
                    {
                        $results_text .= ', option ' . $poll_result . ': ' . $poll_count . ' ' . $vote_text . ' (' . intval((100*$poll_count)/$votes_count) . '%) ';
                    }
                    $current_row++;
                }
            }
            // write poll results to file:
            $this->_write_poll_results($poll_results, $results_text);
            $this->poll_question = NULL;
            $this->votes_array = array();
            $this->poll_deadline_timestamp = NULL;

            $this->poll_duration = NULL;
            //
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $this->bot_config['poll_closure_announcement_message'] . ' The results are: ' . $results_text);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_poll_results($poll_results_array, $results_text)
    {
        $poll_results_file = 'Poll_results__' . date('Ymd_H_i') . '.txt';
        //
        // $text_to_file = "Poll description: " . $this->poll_question . "\n\n" . 
        // "Poll result: " . $results_text . "\n\n" . 
        // "Votes: " . "\n\n" . json_encode($this->votes_array) . "\n\n";        
        //

        $poll_results_array = array( 'Poll description' => $this->poll_question,
            'Poll result' => $results_text,
            'Votes' => $this->votes_array
        );

        $this->appdatahandler->WriteAppDatafile($poll_results_file, 'polls', json_encode($poll_results_array), 'WRITE');

        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function AAAAA_run_eval_text($command_text)
    {
        if (preg_match('/^(.*)?(PHPFUNC(.*)PHPFUNC)(.*)?$/i', $command_text, $matches) === 1)
        {
            // check if $1 exists
            $command_to_execute = $matches[3];
            if (preg_match('/^(.*)?(\(\$1\))(.*)?$/i', $command_to_execute, $matches_2) === 1)
            {
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'nonsafe command includes parameter: ' . $matches_2[2]);
                // execute command:

                eval('$ret_text = ' . $matches[3] . ';');
                return $matches[1] . $ret_text . $matches[4];
                
            }
            else
            {
                // simple dynamic command:
                eval('$ret_text = ' . $matches[3] . ';');
                return $matches[1] . $ret_text . $matches[4];
            }
            
            
            
        }
        else
        {
            return $command_text;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _run_eval_text($command_text, $words_in_message_text)
    {
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Processing nonsafe command with command_text: ' . $command_text );

        if (preg_match('/^(.*)?(PHPFUNC(.*)PHPFUNC)(.*)?$/i', $command_text, $matches) === 1)
        {
            // var_dump($matches);
            
            // ---------------- Find if it includes $1:
            if (preg_match('/^(.*)?(PHPFUNC(.*)(\$1)(.*)PHPFUNC)(.*)?$/i', $command_text, $matches_2) === 1)
            {
                // var_dump($matches_2);
                
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'nonsafe command requires to execute command WITH argument: ' . $command_text );
                $run_with_argument = TRUE;
            }
            else
            {
                $run_with_argument = FALSE;
            }
            //
            //
            // run now:
            if ($run_with_argument === TRUE)
            {
                // replace from 1st matches array:
                // var_dump($matches[3]);

                $command_argument = (empty($words_in_message_text[1])) ? "" : $words_in_message_text[1];

                $final_command_text = preg_replace('/\$1/', '"' . $command_argument . '"', $matches[3]);

                // var_dump($final_command_text);
                
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'nonsafe command to eval: ' . '$ret_text = ' . $final_command_text . ';' );
                eval('$ret_text = ' . $final_command_text . ';');
                return $matches[1] . $ret_text . $matches[4];
            }
            else
            {
                // just run response as PHP:
                eval('$ret_text = ' . $matches[3] . ';');
                return $matches[1] . $ret_text . $matches[4];
            }
        }
        else
        {
            // no PHP code to run:
            //
            return $command_text;
        }

        // check if command expects arguments or not:
        // if (preg_match('/^(.*)?(PHPFUNC\(\$1\)PHPFUNC)(.*)?$/i', $command_text, $matches) === 1)
        // if (preg_match('/^(.*)?(PHPFUNC(.*)\(\$1\)(.*)PHPFUNC)(.*)?$/i', $command_text, $matches) === 1)        
  
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveway_status_modify($status, $words_in_message_text)
    {
        if ($status === 'enable')
        {
            if ($this->giveaway_currently_enabled === FALSE)
            {
                $this->giveaway_currently_enabled = TRUE;
                $this->giveaway_start_time = date('U');
                // description:
                $this->giveaway_description = implode(' ', array_slice($words_in_message_text, 1));
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway has started. Type ' . $this->bot_config['giveaway_join_keyword'] . ' to join!');
            }
            else
            {
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway is already running.');
            }
        }
        elseif ($status === 'disable')
        {
            if ($this->giveaway_currently_enabled === TRUE)
            {
                $this->giveaway_currently_enabled = FALSE;

                $viewers_count = (is_array($this->giveaway_viewers_list)) ? count($this->giveaway_viewers_list) : 0;

                $viewers = ($viewers_count === 1) ? 'viewer' : 'viewers';

                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway has stopped. ' . $viewers_count . ' ' . $viewers . ' have joined it. Type: ' . $this->bot_config['admin_giveaway_find_winner_keyword'] . ' to get a winner.');
            }
            else
            {
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway was not running.');
            }
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveaway_add_viewer($username)
    {
        if ($this->giveaway_currently_enabled === TRUE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'adding the viewer: ' . $username . ' to the giveaway');
        
            $this->giveaway_viewers_list[] = $username;
        
            $this->giveaway_viewers_list = array_unique($this->giveaway_viewers_list);
        }
        // $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'giveaway users list:' . "\n\n" . print_r($this->giveaway_viewers_list, true) . "\n\n");
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveaway_show_status()
    {
        if ($this->giveaway_currently_enabled === TRUE)
        {
           $giveaway_uptime = timespan($this->giveaway_start_time);
           
           $viewers_count = (is_array($this->giveaway_viewers_list)) ? count($this->giveaway_viewers_list) : 0;           

           $viewers = ($viewers_count === 1) ? 'viewer' : 'viewers';
           
           $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway is currently running for ' . $giveaway_uptime . ', ' . $viewers_count . ' ' . $viewers . ' have joined it.');

           $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'giveaway users list:' . "\n\n" . print_r($this->giveaway_viewers_list, true) . "\n\n");
        }
        else 
        {
           $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : No giveaway is currently running.');
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveaway_winner_get()
    {
        if ($this->giveaway_currently_enabled === TRUE)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway is currently running. Please stop it before picking a winner.');
            return TRUE;
        }
        //
        if (! is_array($this->giveaway_viewers_list))
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway is not initialized properly. Please reset and then restart it before you can pick up a winner.');
            return TRUE;
        }
        //
        if (count($this->giveaway_viewers_list) === 0)
        {
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway has no viewers to pick up from. Please restart the giveaway before you can pick up a winner.');
            return TRUE;
        }
        else
        {
            $rand_elements = array_rand($this->giveaway_viewers_list, 1);
            $giveaway_winner_current = $this->giveaway_viewers_list[$rand_elements];
            $this->giveaway_winners_list[] = $giveaway_winner_current;
            
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'picked up give away winner: ' . $giveaway_winner_current );

            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway winner randomly picked: ' . $giveaway_winner_current . ', congrats!');
            //
            // remove the winner, if administrator wants to pick another viewer for giveaway:
            array_splice($this->giveaway_viewers_list, $rand_elements, 1);
            //
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'giveaway users list after picking a winner and removing him from the list:' . "\n\n" . print_r($this->giveaway_viewers_list, true) . "\n\n");
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveaway_reset()
    {
        // write to giveaway file:
        $this->_write_giveaway_results();

        // reset:
        $this->giveaway_viewers_list = array();
        $this->giveaway_description = '';
        $this->giveaway_winners_list = array();

        $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : The giveaway was reset.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_giveaway_results()
    {
        $giveaway_results_file = 'Giveaway_results__' . date('Ymd_H_i') . '.txt';

        $giveaway_results_array = array( 'Giveaway description' => $this->giveaway_description,
            'Giveaway winners' => $this->giveaway_winners_list,
            'Giveaway start date' => date('H:i d/m/Y', $this->giveaway_start_time),
            'Giveaway enrolled users' => array_merge($this->giveaway_winners_list, $this->giveaway_viewers_list)
        );

        $this->appdatahandler->WriteAppDatafile($giveaway_results_file, 'giveaways', json_encode($giveaway_results_array), 'WRITE');

        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _read_loyalty_viewers_XP_array()
    {
        $loyalty_viewers_XP_text = $this->appdatahandler->ReadAppDatafile($this->loyalty_viewers_XP_file, 'READ');

        if ($loyalty_viewers_XP_text[0] === TRUE)
        {
            $this->loyalty_viewers_XP_array = json_decode($loyalty_viewers_XP_text[2], true);
            if (!is_array($this->loyalty_viewers_XP_array))
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Loyalty viewers XP file: ' . $this->loyalty_viewers_XP_file . ' is malformed.');
                $this->loyalty_viewers_XP_array = array();
            }
        }
        else
        {
            $this->loyalty_viewers_XP_array = array();
        }
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Loyalty viewers XP loaded.');
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _check_and_query_loyalty_URL()
    {
        if (date('U') - $this->loyalty_check_last_date_done > $this->loyalty_check_interval &&
            $this->loyalty_points_per_interval > 0
            )
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Time to query twitch URL for chatters currently active.');
            // ----------------------------------
            $check_was_successful = NULL;


            $tmi_twitch_url = 'https://tmi.twitch.tv/group/user/' . $this->bot_config['channel'] . '/chatters';
            
            $chatters_web_response = \IZYBOT\lib\retrieve_web_page($tmi_twitch_url, 'TRUE');

            if ($chatters_web_response[1] === FALSE)
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch response for chatters returned FALSE, URL queried was: ' . $tmi_twitch_url);
                $check_was_successful = FALSE;
                GOTO ENDOFCHATTERSQUERYPROCESSING;
            }

            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch response for chatters was: ' . print_r($chatters_web_response, TRUE));

            $json_response = json_decode($chatters_web_response[0], TRUE);

            if ($json_response === FALSE)
            {
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch response could not be json_decoded, URL queried was: ' . $tmi_twitch_url);
                $check_was_successful = FALSE;
                GOTO ENDOFCHATTERSQUERYPROCESSING;
            }
            
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch response reports there are: ' . $json_response['chatter_count'] . ' people in the chat');
            
            //-----
            $viewers_in_json = array();

            if (isset($json_response['chatters']['moderators']))
            {
                $viewers_in_json = array_merge($viewers_in_json, $json_response['chatters']['moderators']);
            }
            if (isset($json_response['chatters']['staff']))
            {
                $viewers_in_json = array_merge($viewers_in_json, $json_response['chatters']['staff']);
            }
            if (isset($json_response['chatters']['admins']))
            {
                $viewers_in_json = array_merge($viewers_in_json, $json_response['chatters']['admins']);
            }
            if (isset($json_response['chatters']['global_mods']))
            {
                $viewers_in_json = array_merge($viewers_in_json, $json_response['chatters']['global_mods']);
            }
            if (isset($json_response['chatters']['viewers']))
            {
                $viewers_in_json = array_merge($viewers_in_json, $json_response['chatters']['viewers']);
            }

            // $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'viewers_in_json: ' . print_r($viewers_in_json, TRUE));

            foreach ($viewers_in_json as $username)
            {
                $this->_add_loyalty_XP_to_user($username);
            }
            // 
            // uasort($this->loyalty_viewers_XP_array, array($this, '_username_sort_array'));
            // 
            // $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'After Twitch response, loyalty_viewers_XP_array is: ' . "\n\n" . print_r($this->loyalty_viewers_XP_array, TRUE) . "\n\n");
            
            // write to file:
            $this->_write_loyalty_viewers_XP();
            $check_was_successful = TRUE;
            // ----------------------------------
            ENDOFCHATTERSQUERYPROCESSING:
            // increment last date check was done:
            if ($check_was_successful === TRUE)
            {
                $this->loyalty_check_last_date_done = date('U');
            }
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _add_loyalty_XP_to_user($username)
    {
        // check if there is entry for this username.
        $key_for_username = array_search($username, array_column($this->loyalty_viewers_XP_array, 'username'));

        if ($key_for_username === FALSE)
        {
            // new username:
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Username: ' . $username . ' was not found in loyalty_viewers_XP array. Adding it with welcome award');

            $this->loyalty_viewers_XP_array[] = array( 'username' => $username,
                'points' => $this->bot_config['loyalty_points_welcome_award'] + $this->loyalty_points_per_interval,
                'last_date_seen' => date('U')
            );
        }
        else
        {
            // username already exists:
            // $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Username: ' . $username . ' was found.');
            
            $points_before = $this->loyalty_viewers_XP_array[$key_for_username]['points'];

            $this->loyalty_viewers_XP_array[$key_for_username] = array( 'username' => $username,
                'points' => $this->loyalty_points_per_interval + $points_before,
                'last_date_seen' => date('U')
            );
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _display_loyalty_XP_of_viewer($username)
    {
        // check if there is entry for this username.
        $key_for_username = array_search($username, array_column($this->loyalty_viewers_XP_array, 'username'));

        if ($key_for_username === FALSE)
        {
            // new username:
            $loyalty_XP = 0;
        }
        else
        {
            // username exists:
            $loyalty_XP = $this->loyalty_viewers_XP_array[$key_for_username]['points'];
        }
        
        $loyalty_currency_single_plural = ($loyalty_XP > 1 || $loyalty_XP === 0) ? $this->loyalty_currency . 's' : $this->loyalty_currency;

        $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $username . ' has ' . $loyalty_XP . ' ' . $loyalty_currency_single_plural . '.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _username_sort_array($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _bot_command_add_usage($bot_command)
    {
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Adding usage for bot command: ' . $bot_command);
        
        if (array_key_exists($bot_command, $this->bot_commands_usage) !== FALSE)
        {
            $this->bot_commands_usage[$bot_command]++;
        }
        else
        {
            $this->bot_commands_usage[$bot_command] = 1;
        }
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _cancel_bet()
    {
        if ($this->bet_currently_running === TRUE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Cancelling the bet');
            $this->_write_bet_results('Cancelled');

            // reset vars:
            $this->bet_description = '';
            $this->bet_currently_running = FALSE;
            $this->bet_currently_accepting = FALSE;
            $this->bets_array = array();
            $this->bet_start_time = NULL;
            $this->bet_end_time = NULL;
            $this->bet_accept_end_time = NULL;
            $this->bet_winning_option = NULL;
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : The bet was cancelled. Viewers were refunded their bets.');
        }
        else
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'There is no active bet to cancel.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : There is no active bet to cancel.');
        }

        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _start_bet($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->bet_currently_running === TRUE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'There is already an active bet, cant start another one.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Cannot start a bet, there is already an active one.');
        }
        else
        {            
            if (count($words_in_message_text) < 3)
            {
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : To start a bet, use: ' . $this->bot_config['admin_startbet_keyword'] . ' <duration in seconds to accept bets> <bet description>');
                return FALSE;
            }
            else
            {
                if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) != 1)
                {
                    $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : To start a bet, use: ' . $this->bot_config['admin_startbet_keyword'] . ' <duration in seconds to accept bets> <bet description>');
                    return FALSE;
                }
                $this->bet_accept_end_time = date('U') + $words_in_message_text[1];
                //
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Starting a new bet: ' . implode(' ', array_slice($words_in_message_text, 1)) . '.');
                $this->bet_currently_running = TRUE;
                $this->bet_currently_accepting = TRUE;
                $this->bets_array = array();
                $this->bet_description = implode(' ', array_slice($words_in_message_text, 1));
                $this->bet_start_time = date('U');
                //
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $this->bot_config['betstart_announcement_message'] . ': ' . implode(' ', array_slice($words_in_message_text, 2)) . ' for the next ' . $words_in_message_text[1] . ' seconds. To vote type: ' . $this->bot_config['bet_place_keyword'] . ' followed by the option of your choice and the amount you want to bet. For example: ' . $this->bot_config['bet_place_keyword'] . ' 2 400');
            }
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _end_bet($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->bet_currently_running === FALSE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'There is no active bet to stop.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : There is no active bet to stop.');
        }
        else
        {            
            if (count($words_in_message_text) === 1)
            {
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : To end the bet, type: ' . $this->bot_config['admin_endbet_keyword'] . ' <winning option in numerical format>');
            }
            else
            {
                if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) === 1)
                {
                    $this->bet_winning_option = $words_in_message_text[1];

                    // stats initialization:
                    $stats_total_bets = 0;
                    $stats_winners_total = 0;
                    $stats_losers_total = 0;
                    $stats_total_bet_amount = 0;
                    $stats_total_bet_won_amount = 0;
                    $stats_total_bet_lost_amount = 0;
                    
                    $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Stopping the bet, winning option: ' . $this->bet_winning_option);
                    $this->bet_currently_running = FALSE;
                    $this->bet_end_time = date('U');
                    //
                    // parse the bets_array, award viewers
                    foreach ($this->bets_array as $bet)
                    {
                        $stats_total_bet_amount += $bet['amount'];
                        $stats_total_bets++;

                        $key_for_username = array_search($username, array_column($this->loyalty_viewers_XP_array, 'username'));
                        if ($key_for_username === FALSE)
                        {
                            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Could not process the bet for username: ' . $username . ', username not found in loyalty points array (shouldnt have happened!).');
                            GOTO ENDOFBETPROCESSINGLOOP;
                        }
                        //                        
                        if ($bet['option'] === $this->bet_winning_option)
                        {
                            $this->loyalty_viewers_XP_array[$key_for_username]['points'] = $this->loyalty_viewers_XP_array[$key_for_username]['points'] + $bet['amount'];
                            $stats_winners_total++;
                            $stats_total_bet_won_amount += $bet['amount'];
                            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bet processed for username: ' . $username . ', he/she WON: ' . $bet['amount'] . ', new LP amount: ' . $this->loyalty_viewers_XP_array[$key_for_username]['points']);
                        }
                        else
                        {
                            $this->loyalty_viewers_XP_array[$key_for_username]['points'] = $this->loyalty_viewers_XP_array[$key_for_username]['points'] - $bet['amount'];
                            $stats_losers_total++;
                            $stats_total_bet_lost_amount += $bet['amount'];
                            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Bet processed for username: ' . $username . ', he/she LOST: ' . $bet['amount'] . ', new LP amount: ' . $this->loyalty_viewers_XP_array[$key_for_username]['points']);
                        }
                        ENDOFBETPROCESSINGLOOP:
                    }
                    $bet_single_plural = ($stats_total_bets > 1 || $stats_total_bets === 0) ? 'bets' : 'bet';
                    $winners_single_plural = ($stats_winners_total > 1 || $stats_winners_total === 0) ? 'winners' : 'winner';
                    $losers_single_plural = ($stats_losers_total > 1 || $stats_losers_total === 0) ? 'viewers' : 'viewer';
                    //
                    //
                    $this->_write_bet_results('Completed',
                    $stats_total_bets,
                    $stats_winners_total,
                    $stats_losers_total,
                    $stats_total_bet_amount,
                    $stats_total_bet_won_amount,
                    $stats_total_bet_lost_amount
                    );
                    // reset vars:
                    $this->bet_description = '';
                    $this->bet_currently_running = FALSE;
                    $this->bet_currently_accepting = FALSE;
                    $this->bets_array = array();                    
                    $this->bet_start_time = NULL;
                    $this->bet_end_time = NULL;
                    $this->bet_accept_end_time = NULL;
                    $this->bet_winning_option = NULL;
                    //
                    $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : ' . $this->bot_config['betend_announcement_message'] . ' There were ' . $stats_total_bets . ' ' . $bet_single_plural . ' in total placed, for a total bet amount of: ' . $stats_total_bet_amount . ' ' . $this->loyalty_currency . '. We had ' . $stats_winners_total . ' '  . $winners_single_plural . ' who earned a total of ' . $stats_total_bet_won_amount . ' ' . $this->loyalty_currency . ', and ' . $stats_losers_total . ' ' . $losers_single_plural . ' who had to say goodbye to a total of ' . $stats_total_bet_lost_amount . ' ' . $this->loyalty_currency . '.');
                }
                else
                {
                    $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : To end the bet, you need to provide the winning option in numerical format.' );
                }
            }
            
        }

        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _write_bet_results($bet_status, 
    $stats_total_bets = 'N/A',
    $stats_winners_total = 'N/A',
    $stats_losers_total = 'N/A',
    $stats_total_bet_amount = 'N/A',
    $stats_total_bet_won_amount = 'N/A',
    $stats_total_bet_lost_amount = 'N/A'
    )
    {
        $bet_results_file = 'Bet_results__' . date('Ymd_H_i') . '.txt';

        $bet_results_array = array( 'Bet description' => $this->bet_description,
            'Bet closure status' => $bet_status,
            'Total Bets count' => $stats_total_bets,
            'Winners count' => $stats_winners_total,
            'Losers count' => $stats_losers_total,
            'Total bet amount' => $stats_total_bet_amount,
            'Total bets won amount' => $stats_total_bet_won_amount,
            'Total bets lost amount' => $stats_total_bet_lost_amount,
            'Bets' => $this->bets_array,
            'Bet start date' => $this->bet_start_time,
            'Bet end date' => $this->bet_end_time,
            'Bet winning option' => $this->bet_winning_option            
        );

        $this->appdatahandler->WriteAppDatafile($bet_results_file, 'bets', json_encode($bet_results_array), 'WRITE');

        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _register_bet($username, $channel, $words_in_message_text, $message_text)
    {
        if ($this->bet_currently_running === TRUE && $this->bet_currently_accepting === TRUE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Processing vote from user: ' . $username);
            if (count($words_in_message_text) != 3)
            {
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Request is malformed, rejecting it.');
                return FALSE;
            }
            //
            if (preg_match('/^[0-9]+$/', $words_in_message_text[1], $matches) != 1)
            {
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Request is malformed (not numeric option), rejecting it.');
                return FALSE;
            }
            $betting_option = $words_in_message_text[1];
            //
            if (preg_match('/^[0-9]+$/', $words_in_message_text[2], $matches) != 1)
            {
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Request is malformed (not numeric amount to bet), rejecting it.');
                return FALSE;
            }
            $betting_amount = $words_in_message_text[2];
            //
            if (array_search($username, array_column($this->bets_array, 'username')) != FALSE)
            {
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'User has already voted. rejecting new vote.');
                return FALSE;
            }
            //
            if ($this->bot_config['bet_maximum_allowed_amount'] > 0)
            {
                if ($betting_amount > $this->bot_config['bet_maximum_allowed_amount'])
                {
                    $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'betting amount: ' . $betting_amount . ' is greater than max allowed. Rejecting it.');
                    $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : @' . $username . ', you can bet up to ' . $this->bot_config['bet_maximum_allowed_amount'] . ' ' . $this->loyalty_currency . '.' );
                    return FALSE;
                }
            }
            //
            if (array_search($username, array_column($this->loyalty_viewers_XP_array, 'username')) === FALSE)
            {
                // user not found in loyalty points array
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'User not found in loyalty_viewers_XP_array. rejecting new vote.');
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : @' . $username . ', you have no ' . $this->loyalty_currency . ' to bet.' );
                return FALSE;
            }
            //
            $key_for_username = array_search($username, array_column($this->loyalty_viewers_XP_array, 'username'));

            if ($this->loyalty_viewers_XP_array[$key_for_username]['points'] < $betting_amount)
            {
                // user not enoung LP
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'User has not enough LP. Requested: ' . $betting_amount . ', has: ' . $this->loyalty_viewers_XP_array[$key_for_username]['points'] . '. rejecting new vote.');
                $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : @' . $username . ', you have ' . $this->loyalty_viewers_XP_array[$key_for_username]['points'] . ' ' . $this->loyalty_currency . ', which is not enough to place the bet you requested.' );
                return FALSE;
            }
            else
            {
                // placing the bet:
                $this->bets_array[] = array ('username' => $username,
                    'amount' => $betting_amount,
                    'option' => $betting_option,
                    'bet_date' => date('U')
                );
                $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Requested: ' . $betting_amount . ', has: ' . $this->loyalty_viewers_XP_array[$key_for_username]['points'] . '. bet was placed.');
                // no response to user
            }
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------

}
