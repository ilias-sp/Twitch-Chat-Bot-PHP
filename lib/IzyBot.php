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

    private $periodic_messages_interval_seconds;
    private $periodic_messages;
    private $periodic_messages_file;
    private $periodic_messages_last_message_sent_index;
    private $periodic_messages_last_date_sent;


    // poll stuff:
    private $poll_question;
    private $active_poll_exists;
    private $votes_array;
    private $poll_deadline_timestamp;
    private $poll_duration;

    // giveaway:
    private $giveaway_currently_enabled;
    private $giveaway_viewers_list;
    private $giveaway_start_time;

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
                                                     $config['giveaway_join_keyword'],
                                                     $this->bot_config['botinfocommand_keyword']
        );

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
        
        //
        $this->duplicate_message_cuttoff_seconds = $config['duplicate_message_cuttoff_seconds'];
        $this->bot_responses_last_date = array();

        // poll stuff:
        $this->active_poll_exists = FALSE;
        $this->votes_array = array();
        $this->poll_help_message = $config['poll_help_message'];
        
        // giveaway:
        $this->giveaway_currently_enabled = FALSE;
        $this->giveaway_viewers_list = array();
        $this->giveaway_start_time = NULL;

        // classes:
        $this->appdatahandler = new AppDataHandler($this->bot_config, $this->logger);
        
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
            $this->log_it('ERROR', __CLASS__, __FUNCTION__, 'Could not open socket: ' . $this->hostname . ', port: ' . $this->port);
            // $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Errno: ' . $errno . ', Errstr: ' . $errstr);
            throw new \Exception('Could not open socket: ' . $this->hostname . ', port: ' . $this->port . ', error: ' . socket_last_error($this->socket));
        }
        else
        {
            socket_set_nonblock($this->socket);
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Opened socket successfully to server: ' . $this->hostname . ', port: ' . $this->port . '.');
            return TRUE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _close_socket()
    {
        socket_close($this->socket);
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Socket closed.');
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
                    $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Detected socket was closed, error=|' . socket_strerror(socket_last_error($this->socket)));
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
                if ($this->active_poll_exists === TRUE)
                {
                    $this->_monitor_ongoing_poll();
                }
                sleep(1);
            }
            //
            ENDCONNECTION:
            $this->_close_socket();
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
                $this->_giveway_status_modify('enable');
                return TRUE;
            }
            elseif ($words_in_message_text[0] === $this->bot_config['admin_giveaway_stop_keyword'])
            {
                $this->_giveway_status_modify('disable');
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
        }
        //
        // commands for admins END 
        // -----------------------------------------
        // commands for all users START
        if ($message_text === $this->bot_config['helpcommand_keyword'])
        {
            $this->_display_help_command($username, $channel, $words_in_message_text, $message_text);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['uptimecommand_keyword'])
        {
            $this->_display_uptime_command($username, $channel, $words_in_message_text, $message_text);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['botinfocommand_keyword'])
        {
            $this->_display_botinfo_command($username, $channel, $words_in_message_text, $message_text);
            return TRUE;
        }
        elseif (mb_strtolower($words_in_message_text[0]) === mb_strtolower($this->bot_config['votecommand_keyword']) &&
                $this->active_poll_exists === TRUE)
        {
            $this->_register_users_vote($username, $channel, $words_in_message_text, $message_text);
            return TRUE;
        }
        elseif ($message_text === $this->bot_config['giveaway_join_keyword'])
        {
            $this->_giveaway_join_viewer($username);
            return TRUE;
        }
        //
        foreach ($this->admin_commands_nonsafe as $command => $html_code)
        {
            if ($words_in_message_text[0] == $command)
            {
                if ($this->_check_response_should_be_silenced($command) === FALSE)
                {
                    $response = $this->_run_eval_text($html_code);
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
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Login commands were sent. Bot is ready.');
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
    public function start_bot()
    {
        $this->_read_admin_commands();
        $this->_read_admin_usernames();
        $this->_read_periodic_messages();
        //
        return $this;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function stop_bot()
    {
        $this->_write_admin_commands();
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
        $message .=  ' ' . $this->bot_config['helpcommand_keyword'] . ' ' . $this->bot_config['uptimecommand_keyword'] . ' ' . $this->bot_config['botinfocommand_keyword'] . ' .';
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
    private function _run_eval_text($command_text)
    {
        if (preg_match('/^(.*)?(PHPFUNC(.*)PHPFUNC)(.*)?$/i', $command_text, $matches) === 1)
        {
            eval('$ret_text = ' . $matches[3] . ';');
            return $matches[1] . $ret_text . $matches[4];
        }
        else
        {
            return $command_text;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _giveway_status_modify($status)
    {
        if ($status === 'enable')
        {
            if ($this->giveaway_currently_enabled === FALSE)
            {
                $this->giveaway_currently_enabled = TRUE;
                $this->giveaway_start_time = date('U');
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
                $this->giveaway_start_time = NULL;

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
    private function _giveaway_join_viewer($username)
    {
        if ($this->giveaway_currently_enabled === TRUE)
        {
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'adding to the giveaway the viewer: ' . $username );
        
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
            
            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'picked up give away winner: ' . $this->giveaway_viewers_list[$rand_elements] );

            $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : Giveaway winner randomly picked: ' . $this->giveaway_viewers_list[$rand_elements] . ', congrats!');
            //
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
        $this->giveaway_viewers_list = array();

        $this->send_text_to_server('bot', 'PRIVMSG ' . $this->channel . ' : The giveaway was reset.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
}
