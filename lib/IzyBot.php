<?php

class IzyBot {

    private $bot_config;
    private $hostname;
    private $port;

    private $oath_pass;
    private $nickname;
    private $channel;
    private $bot_name;

    private $socket;

    private $log_file;
    private $log_file_irc;
    private $log_level;

    private $admin_commands;
    private $admin_commands_reserved_names;
    private $admin_commands_file;
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

        // logging info:
        $this->log_file = APPPATH . '/log/' . $config['log_file'] . '__' . date('Ymd_H_i') . '.txt';
        $this->log_file_irc = (mb_strlen($config['log_file_irc']) > 0) ? APPPATH . '/log/' . $config['log_file_irc'] . '__' . date('Ymd_H_i') . '.txt' : '';
        $this->log_level = $config['log_level'];

        // channel info:
        $this->oath_pass = $config['oath_pass'];
        $this->nickname = $config['nickname'];
        $this->channel = '#' . $config['channel'];
        $this->bot_name = $config['bot_name'];
        
        // admin commands:
        $this->admin_commands_file = APPPATH . '/conf/admin_commands.cfg';
        $this->admin_commands = array();
        $this->admin_commands_reserved_names = array($config['admin_addcommand_keyword'],
                                                     $config['admin_removecommand_keyword'], 
                                                     $config['admin_addadmin_keyword'],
                                                     $config['admin_removeadmin_keyword'],
                                                     $config['admin_addperiodicmsg_keyword'],
                                                     $config['admin_removeperiodicmsg_keyword'],
                                                     $config['helpcommand_keyword'],
                                                     $config['uptimecommand_keyword'],
                                                     $this->bot_config['botinfocommand_keyword']
        );

        // admins:
        $this->admin_usernames_file = APPPATH . '/conf/admin_usernames.cfg';
        $this->admin_usernames = array();
        $this->admin_usernames[] = $config['nickname'];

        // periodic messages:
        $this->periodic_messages_last_date_sent = date('U');
        $this->periodic_messages = array();
        $this->periodic_messages_file = APPPATH . '/conf/periodic_messages.cfg';
        $this->periodic_messages_last_message_sent_index = -1;
        $this->periodic_messages_interval_seconds = $config['periodic_messages_interval_seconds'];
        //
        $this->duplicate_message_cuttoff_seconds = $config['duplicate_message_cuttoff_seconds'];
        $this->bot_responses_last_date = array();
        $this->_log_it('INFO', __FUNCTION__, $this->bot_name . "\'s initialization is complete!" . "\n");
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _open_socket()
    {
        $this->socket = fsockopen($this->hostname, $this->port, $errno, $errstr);
        
        if (! $this->socket)
        {
            $this->_log_it('ERROR', __FUNCTION__, 'Could not open socket: ' . $this->hostname . ', port: ' . $this->port);
            $this->_log_it('ERROR', __FUNCTION__, 'Errno: ' . $errno . ', Errstr: ' . $errstr);
            throw new \Exception('Could not open socket: ' . $this->hostname . ', port: ' . $this->port);
        }
        else
        {
            $this->_log_it('INFO', __FUNCTION__, 'Opened socket successfully to server: ' . $this->hostname . ', port: ' . $this->port . '.');
            return TRUE;
        }
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _close_socket()
    {
        fclose($this->socket);
        $this->_log_it('INFO', __FUNCTION__, 'Socket closed.');
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
            $this->_log_it('DEBUG', __FUNCTION__, '--> | ' . $text);
            //
            return (fwrite($this->socket, $text . "\r\n") === FALSE) ? FALSE : TRUE;
        }
        else
        {
            $this->_log_it('DEBUG', __FUNCTION__, 'Bot on listen_only mode or command not needed for service. Supressing it.');
            return TRUE;
        }        
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function main()
    {
        while (true)
        {
            $this->_open_socket();
            $this->_login_to_twitch();
            //
            while (true)
            {
                $text = fgets($this->socket);
                if (feof($this->socket)) {
                    $this->_log_it('INFO', __FUNCTION__, 'Detected socket was closed.');
                    goto ENDCONNECTION;
                }
                //---
                $this->_log_irc_traffic('<-- | ' . mb_substr($text, 0, mb_strlen($text) - 1)); // delete last char, its NewLine.
                $this->_log_it('DEBUG', __FUNCTION__, '<-- | ' . mb_substr($text, 0, mb_strlen($text) - 1)); // delete last char, its Newline.
                $this->_process_irc_incoming_message($text);
                $this->_check_and_send_periodic_message();
            }
            //
            ENDCONNECTION:
            $this->_close_socket();
            $this->_log_it('INFO', __FUNCTION__, 'Attempting to reconnect in 5 seconds..');
            sleep(5);
            //  
        }
        //
        return $this;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _log_it($level, $caller_function, $message)
    {
        //ERROR - 1
        //INFO  - 2
        //DEBUG - 3

        $log_message = date('d/m/Y H:i:s') . ' - ' . $level . ' - ' . $caller_function . ' - ' . $message . "\r\n";

        if (constant($level) <= constant($this->log_level))
        {
            echo $log_message;
        }        
        //
        if (strlen($this->log_file) > 0)
        {
            if (file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX) === FALSE)
            {
                throw new \Exception("Error occured while writing to log file: " . $this->log_file);
            }
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _log_irc_traffic($message)
    {
        $log_message = date('d/m/Y H:i:s') . ' - ' . $message . "\r\n";
        if (mb_strlen($this->log_file_irc) > 0)
        {
            if (file_put_contents($this->log_file_irc, $log_message, FILE_APPEND | LOCK_EX) === FALSE)
            {
                throw new \Exception("Error occured while writing IRC traffic to log file: " . $this->log_file_irc);
            }
        }
        //
        return TRUE;        
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _process_irc_incoming_message($text)
    {
        if (preg_match("/PING :(.*)/i", $text, $match)) // PING:
        {
            $this->_log_it('DEBUG', __FUNCTION__, "Requested to PING $match[1]");
            $this->send_text_to_server('service', "PONG :$match[1]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ JOIN (#\S+)/i", $text, $match)) // USER JOINED CHANNEL:
        {
            $this->_log_it('DEBUG', __FUNCTION__, "User $match[1] joined channel $match[2]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ PART (#\S+)/i", $text, $match)) // USER LEFT CHANNEL:
        {
            $this->_log_it('DEBUG', __FUNCTION__, "User $match[1] left channel $match[2]");
        }
        elseif (preg_match("/:(\S+)!\S+@\S+ PRIVMSG (#\S+) :(.*)/i", $text, $match)) // USER MESSAGE:
        {
            $this->_log_it('DEBUG', __FUNCTION__, "$match[1]@$match[2]: $match[3]");
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
        
        // $this->_log_it('DEBUG', __FUNCTION__, 'words_in_message_text:' . "\n\n" . print_r($words_in_message_text, true) . "\n\n");

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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted admin command addition is malformed, command: |' . $message_text . '| was ignored.');
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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted admin command removal is malformed, command: |' . $message_text . '| was ignored.');
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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted admin username addition is malformed, command: |' . $message_text . '| was ignored.');
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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted admin username removal is malformed, command: |' . $message_text . '| was ignored.');
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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted periodic message addition is malformed, command: |' . $message_text . '| was ignored.');
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
                    $this->_log_it('DEBUG', __FUNCTION__, 'Attempted periodic message removal is malformed, command: |' . $message_text . '| was ignored.');
                    return FALSE;
                }
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
        if (count($this->admin_commands) > 0)
        {
            foreach ($this->admin_commands as $command => $response)
            {
                if ($words_in_message_text[0] == $command)
                {
                    if ($this->_check_response_should_be_silenced($command) === FALSE)
                    {
                        $this->_log_it('DEBUG', __FUNCTION__, 'Received command: ' . $command . ', replying it with: ' . $response);
                        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : @' . $username . ' ' . $response);
                        $this->_add_command_to_bot_responses_last_date($command);
                    }                    
                    return TRUE;
                }
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
        usleep(500000);
        $this->send_text_to_server('service', 'JOIN ' . $this->channel);
        usleep(1000000);
        $this->_log_it('INFO', __FUNCTION__, 'Login commands were sent. Bot is ready.');
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_admin_commands()
    {
        if (file_exists($this->admin_commands_file))
        {
            $admin_commands_text = file_get_contents($this->admin_commands_file);
            if ($admin_commands_text !== FALSE)
            {
                $this->admin_commands = json_decode($admin_commands_text, true);
            }
        }
        //
        $this->_log_it('INFO', __FUNCTION__, 'Bot admin commands loaded:' . "\n\n" . print_r($this->admin_commands, true) . "\n\n");
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_admin_usernames()
    {
        if (file_exists($this->admin_usernames_file))
        {
            $admin_usernames_text = file_get_contents($this->admin_usernames_file);
            if ($admin_usernames_text !== FALSE)
            {
                $this->admin_usernames = array_merge($this->admin_usernames, json_decode($admin_usernames_text));
            }
        }
        //
        $this->admin_usernames = array_unique($this->admin_usernames);
        $this->_log_it('INFO', __FUNCTION__, 'Bot admin usernames loaded:' . "\n\n" . print_r($this->admin_usernames, true) . "\n\n");
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_periodic_messages()
    {
        if (file_exists($this->periodic_messages_file))
        {
            $periodic_messages_text = file_get_contents($this->periodic_messages_file);
            if ($periodic_messages_text !== FALSE)
            {
                $this->periodic_messages = json_decode($periodic_messages_text);
            }
        }
        //
        $this->periodic_messages = array_unique($this->periodic_messages);
        $this->_log_it('INFO', __FUNCTION__, 'Bot periodic_messages loaded:' . "\n\n" . print_r($this->periodic_messages, true) . "\n\n");
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_admin_commands()
    {
        if (file_put_contents($this->admin_commands_file, json_encode($this->admin_commands)) === FALSE)
        {
            throw new \Exception("Error occured while flushing admin commands to file: " . $this->admin_commands_file);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_admin_usernames()
    {
        if (file_put_contents($this->admin_usernames_file, json_encode($this->admin_usernames)) === FALSE)
        {
            throw new \Exception("Error occured while flushing admin usernames to file: " . $this->admin_usernames_file);
        }
        //
        return TRUE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _write_periodic_messages()
    {
        if (file_put_contents($this->periodic_messages_file, json_encode($this->periodic_messages)) === FALSE)
        {
            throw new \Exception("Error occured while flushing periodic messages to file: " . $this->periodic_messages_file);
        }
        //
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
        foreach ($this->admin_usernames as $admin_username)
        {
            if ($admin_username === $username)
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
            $this->_log_it('DEBUG', __FUNCTION__, 'attempted admin command addition with keyword: ' . $words_in_message_text[1] . ' failed due to reserved keyword.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' could not be added (reserved keyword).');
            return FALSE;
        }
        //
        foreach ($this->admin_commands as $command => $response)
        {
            if ($words_in_message_text[1] == $command)
            {
                $this->_log_it('DEBUG', __FUNCTION__, 'attempted admin command addition with keyword: ' . $words_in_message_text[1] . ' failed, command already exists.');
                $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Command ' . $words_in_message_text[1] . ' already exists.');
                return FALSE;
            }
        }
        //
        $this->admin_commands[$words_in_message_text[1]] = implode(' ', array_slice($words_in_message_text, 2));
        $this->_log_it('DEBUG', __FUNCTION__, 'admin command set: ' . $words_in_message_text[1] . ', to respond: ' . implode(' ', array_slice($words_in_message_text, 2)));
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
            $this->_log_it('DEBUG', __FUNCTION__, 'attempted admin addition with username: ' . $words_in_message_text[1] . ' failed, user is already admin.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : User ' . $words_in_message_text[1] . ' is already member of the admins.');
            RETURN FALSE;
        }
        //
        $this->admin_usernames[] = $words_in_message_text[1];
        $this->_log_it('DEBUG', __FUNCTION__, 'Username: ' . $words_in_message_text[1] . ' was added to the admins.');
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
            $this->_log_it('DEBUG', __FUNCTION__, 'attempted periodic message addition with text: ' . $periodic_message . ' failed, message already exists.');
            $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : This periodic message already exists.');
            RETURN FALSE;
        }
        //
        $this->periodic_messages[] = $periodic_message;
        $this->_log_it('DEBUG', __FUNCTION__, 'Periodic message: ' . $periodic_message . ' was added to the periodic_messages.');
        $this->_write_periodic_messages();
        $this->send_text_to_server('bot', 'PRIVMSG ' . $channel . ' : Periodic message was added to the list.');
        //
        return TRUE;
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
                $this->_log_it('DEBUG', __FUNCTION__, 'admin command removed: ' . $words_in_message_text[1]);

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
            $this->_log_it('DEBUG', __FUNCTION__, 'Periodic message: ' . $periodic_message . ' was removed.');

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
            $this->_log_it('DEBUG', __FUNCTION__, 'Username: ' . $words_in_message_text[1] . ' was removed from the admins.');

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
                    $this->_log_it('DEBUG', __FUNCTION__, 'returning TRUE (response should be suppressed) for command: ' . $attempted_command);
                    return TRUE;
                }
            }
        }
        //
        $this->_log_it('DEBUG', __FUNCTION__, 'returning FALSE (response should be sent) for command: ' . $attempted_command);
        //
        return FALSE;
    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _add_command_to_bot_responses_last_date($command)
    {
        $this->bot_responses_last_date[$command] = date('U');
        $this->_log_it('DEBUG', __FUNCTION__, 'this->bot_responses_last_date:' . "\n\n" . print_r($this->bot_responses_last_date, true) . "\n\n");
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
            $this->_log_it('DEBUG', __FUNCTION__, 'Time to send a periodic message.');
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

    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------

    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------

    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    
}
