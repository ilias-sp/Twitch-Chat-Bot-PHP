<?php

namespace IZYBOT\lib;

class AppDataHandler {

    private $bot_config;
    
    private $logger;

    public function __construct($config, $logger) {
        
        $this->logger = $logger;

        $this->bot_config = $config;

    }
    //----------------------------------------------------------------------------------
    //
    /*
    return codes:

    FALSE, 1, NULL : file does not exist.
    FALSE, 2, NULL : file_get_contents error.
    TRUE, 0, <stuff>: file was found, returning contents.

    */
    //----------------------------------------------------------------------------------
    public function ReadAppDatafile($filename, $mode) {

        $appdata_file = APPPATH . '/appdata/' . $filename;

        if (file_exists($appdata_file)) {

            $file_contents = file_get_contents($appdata_file);

            if ($file_contents === FALSE) {
                
                // problem reading contents:
                $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'File: ' . $filename . ' could NOT be read.');
                return array(FALSE, 2, NULL);

            }
            else {
                
                // all OK:
                $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'File: ' . $filename . ' was successfully read.');
                return array(TRUE, 0, $file_contents);

            }
        }
        else {

            // file was not found:
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'File: ' . $filename . ' was not found.');
            return array(FALSE, 1, NULL);

        }

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
    public function WriteAppDatafile($filename, $target_dir, $filecontents, $mode) {

        $appdata_file = APPPATH . '/' . $target_dir . '/' . $filename;

        if (file_put_contents($appdata_file, $filecontents, LOCK_EX) === FALSE)
        {
            $this->logger->log_it('ERROR', __CLASS__, __FUNCTION__, 'Error occured while flushing appdata contents to file: ' . $appdata_file);
            return array(FALSE, 1, NULL);
        }
        else
        {
            $this->logger->log_it('DEBUG', __CLASS__, __FUNCTION__, 'Flushing appdata contents to file ' . $appdata_file . ' was successful.');
            return array(TRUE, 0, NULL);
        }

    }
    //----------------------------------------------------------------------------------
    //
    //----------------------------------------------------------------------------------
}

