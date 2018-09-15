<?php

namespace IZYBOT\lib;

class Twitchapi {

    // logger
    private $logger;

    // config
    private $bot_config;

    // 
    private $channel_id;

    // 
    private $response_module_not_enabled;

    // 
    private $app_client_id;
    private $app_client_secret;

    private $twitchapi_channel_data_file;
    private $twitch_api_version = 'v5';
    private $module_enabled;

    // classes:
    private $appdatahandler;

    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function __construct($config, $logger)
    {
        
        // classes:
        $this->logger = $logger;
        $this->appdatahandler = new AppDataHandler($this->bot_config, $this->logger);

        $this->bot_config = $config;
        $this->app_client_id = $this->bot_config['app_client_id'];
        $this->app_client_secret = $this->bot_config['app_client_secret'];

        $this->twitchapi_channel_data_file = 'twitch_channel_data.cfg';

        if ( strlen($this->bot_config['app_client_id']) > 0 && strlen($this->bot_config['app_client_secret']) > 0) {

            // $this->_read_twitch_api_oath_token();
            $this->module_enabled = TRUE;

        }
        else {

            $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'TwitchAPI module is disabled, Twitch App Client ID or Client Secret is empty.');
            // $this->oath_token = NULL;
            $this->module_enabled = FALSE;

        }

        $this->response_module_not_enabled = 'Bot is currently not authenticated to query Twitch API. Check Twitch App is defined properly.';

        $this->_read_channel_data();
        
    }   
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    private function _read_channel_data()
    {

        $channel_data_text = $this->appdatahandler->ReadAppDatafile($this->twitchapi_channel_data_file, 'READ');

        if ($channel_data_text[0] === TRUE)
        {
            $channel_data_text_array = json_decode($channel_data_text[2], true);            
            if (!is_array($channel_data_text_array))
            {
                
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch channel data file: ' . $this->twitchapi_channel_data_file . ' is malformed.');
                $this->_get_channel_data();

            }
            else {

                $json_text = json_decode($channel_data_text_array[0]);
                $this->channel_id = $json_text->users[0]->_id;

            }
        }
        else {

            $this->_get_channel_data();

        }

        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Twitch channel ID = ' . $this->channel_id);

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    public function _run_twitch_api_call($web_url, $access_method, $headers, $payload, $add_gzip)
    {
        
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_URL, $web_url);
        
        if ($access_method === 'POST') {
            
            curl_setopt($ch, CURLOPT_POST, 1);

        }
        elseif ($access_method === 'PUT') {
            
            // curl_setopt($ch, CURLOPT_PUT, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        }

        if ($headers !== NULL) {

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        }

        if ($payload !== NULL) {

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        }

        if ($add_gzip === TRUE) {

            curl_setopt($ch, CURLOPT_ENCODING , 'gzip');

        }

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'New request. Headers: ' . print_r($headers, true));

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 0 gia na perimenei gia panta
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        //curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/1');
    
        $html_response = curl_exec($ch);
    
        $curl_transfer_result = curl_getinfo($ch);

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'API Response: ' . print_r(array($html_response, $curl_transfer_result), true));
    
        return array($html_response, $curl_transfer_result);

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _write_channel_data($channel_data)
    {

        $this->appdatahandler->WriteAppDatafile($this->twitchapi_channel_data_file, 'appdata', json_encode(array($channel_data)), 'WRITE');

        return TRUE;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    public function _get_users_id_by_username($username)
    {
        
        if ($this->module_enabled === FAlSE) {
            
            return array(FALSE, NULL, $this->response_module_not_enabled);

        }
        // 
        $url = 'https://api.twitch.tv/kraken/users?login=' . $username;

        $headers = array('Accept: application/vnd.twitchtv.' . $this->twitch_api_version . '+json', 'Client-ID: ' . $this->app_client_id);

        $api_call = $this->_run_twitch_api_call($url, 'GET', $headers, NULL, FALSE);

        return $api_call;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    public function set_channel_title($title)
    {
        
        
        
        if ($this->module_enabled === FALSE) {
            
            return array(FALSE, NULL, $this->response_module_not_enabled);
        }

        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Setting Channels stream title via Twitch API..');
        // 
        $url = 'https://api.twitch.tv/kraken/channels/' . $this->channel_id;

        $headers = array('Client-ID: ' . $this->app_client_id, 'Accept: application/vnd.twitchtv.' . $this->twitch_api_version . '+json', 'Authorization: OAuth ' . mb_substr($this->bot_config['oath_pass'], 6) );

        $payload = array( 'channel[status]' => $title
        );

        $api_call = $this->_run_twitch_api_call($url, 'PUT', $headers, $payload, FALSE);

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch API call result: ' . "\n\n" . print_r($api_call, true) . "\n\n");

        return $api_call;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    public function set_channel_game($game)
    {
        
        if ($this->module_enabled === FALSE) {
            
            return array(FALSE, NULL, $this->response_module_not_enabled);
            
        }

        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Setting Channels game via Twitch API..');
        // 
        $url = 'https://api.twitch.tv/kraken/channels/' . $this->channel_id;

        $headers = array('Client-ID: ' . $this->app_client_id, 'Accept: application/vnd.twitchtv.' . $this->twitch_api_version . '+json', 'Authorization: OAuth ' . mb_substr($this->bot_config['oath_pass'], 6) );

        $payload = array( 'channel[game]' => $game
        );

        $api_call = $this->_run_twitch_api_call($url, 'PUT', $headers, $payload, FALSE);

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch API call result: ' . "\n\n" . print_r($api_call, true) . "\n\n");

        return $api_call;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _check_user_id_is_sub($user_id)
    {
        
        if ($this->module_enabled === FALSE) {
            
            return array(FALSE, NULL, $this->response_module_not_enabled);

        }
        // 
        $this->logger->log_it('INFO', __CLASS__, __FUNCTION__, 'Checking if a user is subscriber to the channel, via Twitch API..');

        $url = 'https://api.twitch.tv/kraken/channels/' . $this->channel_id . '/subscriptions/' . $user_id;

        $headers = array('Client-ID: ' . $this->app_client_id, 'Accept: application/vnd.twitchtv.' . $this->twitch_api_version . '+json', 'Authorization: OAuth ' . mb_substr($this->bot_config['oath_pass'], 6) );

        $payload = NULL;

        $api_call = $this->_run_twitch_api_call($url, 'GET', $headers, $payload, TRUE);

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch API call result: ' . "\n\n" . print_r($api_call, true) . "\n\n");

        return $api_call;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    public function check_username_is_sub($username)
    {
        
        if ($this->module_enabled === FALSE) {
            
            return array(FALSE, NULL, $this->response_module_not_enabled);

        }
        //

        $api_call_1 = $this->_get_users_id_by_username($username);

        if ($api_call_1[1]['http_code'] != 200 ) {

            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch API call http response is not 200.');

            return array(FALSE, NULL);

        }
        // 
        $json_response_1 = json_decode($api_call_1[0]);

        if (! isset($json_response_1->_total) ) {

            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch API response is missing vital field.');
            
            return array(FALSE, NULL);

        }
        // 
        if ($json_response_1->_total != 1)  {

            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'User: ' . $username . ' not found.');
            
            return array(FALSE, NULL, 'User: ' . $username . ' not found.');

        }
        // 
        $user_id = $json_response_1->users[0]->_id;
        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch Username: ' . $username . ', has ID: ' . $user_id . '.');

        $api_call_2 = $this->_check_user_id_is_sub($user_id);

        // var_dump($api_call_2);
        $json_response_2 = json_decode($api_call_2[0]);

        if ($json_response_2 === FALSE) {

            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Twitch API response could not be json decoded.');
            
            return array(FALSE, NULL);

        }
        // 
        $is_sub_result = 'not_sub';
        
        if (isset($json_response_2->_id) && isset($json_response_2->user) ) {

            $is_sub_result = 'is_sub';

        }
        
        return array(TRUE, $is_sub_result);   

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
    private function _get_channel_data()
    {

        $api_call = $this->_get_users_id_by_username($this->bot_config['channel']);

        $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Twitch API call result: ' . "\n\n" . print_r($api_call, true) . "\n\n");
        // 
        if ($api_call[1]['http_code'] != 200) {

            echo __CLASS__ . ': API call to get returned with error. Exiting.. ' . "\n";
            throw new \Exception('API call to get returned with error. Exiting..');

        }

        $this->_write_channel_data($api_call[0]);

        $json_text = json_decode($api_call[0]);

        $this->channel_id = $json_text->users[0]->_id;

        return $api_call;

    }
    //----------------------------------------------------------------------------------
    // 
    //----------------------------------------------------------------------------------
}