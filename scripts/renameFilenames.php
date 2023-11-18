<?php
	
	if (!isset($mydir))
		$mydir = 'T:\MP3';
	if (!isset($dryrun))
		$dryrun = true;
	if (!isset($maxlen))
		$maxlen = 64;
	if (!isset($errlog))
		$errlog = __DIR__ . DIRECTORY_SEPARATOR . 'error.log';
	
	// -------
	
	if (DIRECTORY_SEPARATOR === '\\')
		$mydir = str_replace('/', '\\', $mydir);
	elseif (DIRECTORY_SEPARATOR === '/')
		$mydir = str_replace('\\', '/', $mydir);
	
	if ($maxlen < 20)
		exit('$maxlen must be at least 20 chars');
	
	file_put_contents($errlog, "");
	
	$dir_iterator = new RecursiveDirectoryIterator($mydir);
	$iterator     = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	
	foreach ($iterator as $file)
	{
		$file = trim($file);
		
		if (is_file($file))
		{
			$pi        = pathinfo($file);
			$dirname   = rtrim($pi['dirname'], DIRECTORY_SEPARATOR);
			$basename  = $pi['basename'];
			$extension = $pi['extension'];
			
			if (empty($extension) || strlen($extension) > 10)
			{
				$nfilename = substr($basename, 0, $maxlen);
			}
			else
			{
				$nfilename = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $basename);
				$nfilename = substr($nfilename, 0, $maxlen - strlen($extension) - 1);
				$nfilename = $nfilename . '.' . $extension;
			}
			
			$nfilename = preg_replace('/[^äöüÄÖÜa-zA-Z0-9-_., ]/ui', '_', $nfilename);
			$nfilename = preg_replace('/ {1,}/ui', ' ', $nfilename);
			
			$new = $dirname . DIRECTORY_SEPARATOR . $nfilename;
			
			if ($file !== $new)
			{
				echo $file . "\n";
				echo '--> ' . $new . "\n";
				
				if ($dryrun === false)
				{
					$r = rename($file, $new);
					var_dump($r);
					
					if ($r === false)
						file_put_contents($errlog, $file . "\n", FILE_APPEND);
					
					//usleep(20000);
				}
				
				echo "\n";
			}
		}
	}
  
  