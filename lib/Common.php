<?php


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