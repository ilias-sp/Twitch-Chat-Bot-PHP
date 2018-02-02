<?php

namespace IZYBOT\lib;

function timespan($seconds = 1, $time = '', $units = 7)
{

	is_numeric($seconds) OR $seconds = 1;
	is_numeric($time) OR $time = time();
	is_numeric($units) OR $units = 7;

	$seconds = ($time <= $seconds) ? 1 : $time - $seconds;

	$str = array();
	$years = floor($seconds / 31557600);

	if ($years > 0)
	{
		$str[] = $years.' '.($years > 1 ? 'years' : 'year');
	}

	$seconds -= $years * 31557600;
	$months = floor($seconds / 2629743);

	if (count($str) < $units && ($years > 0 OR $months > 0))
	{
		if ($months > 0)
		{
			$str[] = $months.' '.($months > 1 ? 'months' : 'month');
		}

		$seconds -= $months * 2629743;
	}

	$weeks = floor($seconds / 604800);

	if (count($str) < $units && ($years > 0 OR $months > 0 OR $weeks > 0))
	{
		if ($weeks > 0)
		{
			$str[] = $weeks.' '.($weeks > 1 ? 'weeks' : 'week');
		}

		$seconds -= $weeks * 604800;
	}

	$days = floor($seconds / 86400);

	if (count($str) < $units && ($months > 0 OR $weeks > 0 OR $days > 0))
	{
		if ($days > 0)
		{
			$str[] = $days.' '.($days > 1 ? 'days' : 'day');
		}

		$seconds -= $days * 86400;
	}

	$hours = floor($seconds / 3600);

	if (count($str) < $units && ($days > 0 OR $hours > 0))
	{
		if ($hours > 0)
		{
			$str[] = $hours.' '.($hours > 1 ? 'hours' : 'hour');
		}

		$seconds -= $hours * 3600;
	}

	$minutes = floor($seconds / 60);

	if (count($str) < $units && ($days > 0 OR $hours > 0 OR $minutes > 0))
	{
		if ($minutes > 0)
		{
			$str[] = $minutes.' '.($minutes > 1 ? 'minutes' : 'minute');
		}

		$seconds -= $minutes * 60;
	}

	if (count($str) === 0)
	{
		$str[] = $seconds.' '.($seconds > 1 ? 'seconds' : 'second');
	}

	return implode(', ', $str);
}


function retrieve_web_page($web_url)
{
	$html_response = file_get_contents($web_url);

	if ($html_response === FALSE)
	{
		return array($html_response, FALSE);
	}
	else
	{
		return array($html_response, TRUE);
	}	
}

function retrieve_web_page_CURL($web_url, $use_random_user_agent)
{
	$ch = curl_init();
	if ($use_random_user_agent === TRUE)
	{
		curl_setopt($ch, CURLOPT_USERAGENT, user_agent_select_random());
	}

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_URL, $web_url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 0 gia na perimenei gia panta
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	//curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/1');

	$html_response = curl_exec($ch);

	$curl_transfer_result = curl_getinfo($ch);

	return array($html_response, $curl_transfer_result);
}

function user_agent_select_random()
{
 	//
 	// apo: https://udger.com/resources/ua-list
 	//
 	
 	$user_agents_array = array(
		'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:48.0) Gecko/20100101 Firefox/48.0', 
		'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.53 Safari/525.19',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.38 Safari/537.36',
		'Mozilla/5.0 (IE 11.0; Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0) like Gecko',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/600.3.10 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.10',
		'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36 OPR/32.0.1948.25',
		'Mozilla/5.0 (IE 11.0; Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko',
		'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0)',
		'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36',
		'Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.792.0 Safari/535.1',
		'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0',
		'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0',
		'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0'
 	);
 	$rand_keys = array_rand($user_agents_array);
 	return $user_agents_array[$rand_keys];
}
