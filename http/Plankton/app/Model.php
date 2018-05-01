<?php

class Model
{
	protected $pdo = null;
	
	private $appdatadir;

    public function __construct()
    {
		
		$this->appdatadir = dirname(dirname(dirname(__DIR__)));

    }


	// 
	// function taken from Codeigniter: http://www.codeigniter.com:
	// 

    public function get_directory_files($source_dir, $include_path = FALSE, $_recursion = FALSE)
	{
		static $_filedata = array();

		if ($fp = opendir($source_dir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === FALSE)
			{
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			while (FALSE !== ($file = readdir($fp)))
			{
				if (is_dir($source_dir.$file) && $file[0] !== '.')
				{
					$this->get_directory_files($source_dir.$file.DIRECTORY_SEPARATOR, $include_path, TRUE);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[] = ($include_path === TRUE) ? $source_dir.$file : $file;
				}
			}

			closedir($fp);
			return $_filedata;
        }
        
		return FALSE;
	}

	// 
	// function taken from Codeigniter: http://www.codeigniter.com:
	// 

	function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE)
	{
		static $_filedata = array();
		$relative_path = $source_dir;

		if ($fp = @opendir($source_dir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === FALSE)
			{
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (FALSE !== ($file = readdir($fp)))
			{
				if (is_dir($source_dir.$file) && $file[0] !== '.' && $top_level_only === FALSE)
				{
					get_dir_file_info($source_dir.$file.DIRECTORY_SEPARATOR, $top_level_only, TRUE);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[$file] = $this->get_file_info($source_dir.$file);
					$_filedata[$file]['relative_path'] = $relative_path;
				}
			}

			closedir($fp);
			return $_filedata;
		}

		return FALSE;
	}

	// 
	// function taken from Codeigniter: http://www.codeigniter.com:
	// 

	function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date'))
	{
		if ( ! file_exists($file))
		{
			return FALSE;
		}

		if (is_string($returned_values))
		{
			$returned_values = explode(',', $returned_values);
		}

		foreach ($returned_values as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileinfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileinfo['server_path'] = $file;
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileinfo['writable'] = is_really_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileinfo;
	}

	public function get_botadministrators()
	{
		
		$appdata_file = $this->appdatadir . '/appdata/admin_usernames.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}

	}

	public function get_botcommands()
	{
		
		$appdata_file = $this->appdatadir . '/appdata/admin_commands.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}

	}

	public function get_botcommands_usage()
	{
		
		$appdata_file = $this->appdatadir . '/appdata/bot_commands_usage_stats.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}

	}

	public function get_botperiodic_msgs()
	{
		
		$appdata_file = $this->appdatadir . '/appdata/periodic_messages.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}

	}

	public function get_channel_name()
	{
		$appdata_file = $this->appdatadir . '/conf/channel_credentials.php';

		include($appdata_file);

		return $config['channel'];
	}

	public function get_log_files()
	{
		$logfiles_dir = $this->appdatadir . '/log';

		$logfiles = $this->get_dir_file_info($logfiles_dir);

		return $logfiles;
	}

	public function get_poll_files()
	{
		$pollfiles_dir = $this->appdatadir . '/polls';

		$pollfiles = $this->get_dir_file_info($pollfiles_dir);

		return $pollfiles;
	}

	public function get_bet_files()
	{
		$betfiles_dir = $this->appdatadir . '/bets';

		$betfiles = $this->get_dir_file_info($betfiles_dir);

		return $betfiles;
	}

	public function get_bet_file_details($file_name)
	{

		$appdata_file = $this->appdatadir . '/bets/' . $file_name;

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
		
	}

	public function get_poll_file_details($file_name)
	{

		$appdata_file = $this->appdatadir . '/polls/' . $file_name;

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
		
	}
	
	public function get_giveaway_files()
	{
		$giveawayfiles_dir = $this->appdatadir . '/giveaways';

		$giveawayfiles = $this->get_dir_file_info($giveawayfiles_dir);

		return $giveawayfiles;
	}

	public function get_giveaway_file_details($file_name)
	{

		$appdata_file = $this->appdatadir . '/giveaways/' . $file_name;

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
		
	}

	public function get_log_file_details($file_name)
	{

		$appdata_file = $this->appdatadir . '/log/' . $file_name;

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
		
	}

	public function get_configfile()
	{

		$appdata_file = $this->appdatadir . '/conf/config.php';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
		
	}

	public function get_viewers_loyalty_XP_details()
	{
		$appdata_file = $this->appdatadir . '/appdata/loyalty_viewers_XP_array.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
	}

	public function get_quotes()
	{
		$appdata_file = $this->appdatadir . '/appdata/quotes.cfg';

		if (file_exists($appdata_file)) 
		{
			$file_contents = file_get_contents($appdata_file);

			return $file_contents;

		}
		else
		{
			return FALSE;
		}
	}
}
