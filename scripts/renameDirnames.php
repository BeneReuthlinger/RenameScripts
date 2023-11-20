<?php
	
	if (!isset($mydir))
		$mydir = 'T:\MP3';
	if (!isset($dryrun))
		$dryrun = true;
	if (!isset($maxlen))
		$maxlen = 40;
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
	
	while (true)
	{
		$dir_iterator = new RecursiveDirectoryIterator($mydir);
		$iterator     = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		
		$breakit = false;
		
		foreach ($iterator as $file)
		{
			$file = trim($file);
			
			if (is_dir($file))
			{
				$dirsplit = explode(DIRECTORY_SEPARATOR, $file);
				$lastdir  = $dirsplit[ count($dirsplit) - 1 ];
				
				if ($lastdir === '.' || $lastdir === '..')
					continue;
				
				unset($dirsplit[ count($dirsplit) - 1 ]);
				$predir = implode(DIRECTORY_SEPARATOR, $dirsplit);
				$predir = rtrim($predir, DIRECTORY_SEPARATOR);
				
				$nlastdir = mb_substr($lastdir, 0, $maxlen);
				$nlastdir = trim($nlastdir);
				$nlastdir = preg_replace('/[^äöüÄÖÜa-zA-Z0-9&-_., ]/ui', '_', $nlastdir);
				
				if (preg_match('/  /ui', $nlastdir))
					$nlastdir = preg_replace('/ {1,}/ui', ' ', $nlastdir);
				
				$nlastdir = trim($nlastdir, ' .-_,');
				$nlastdir = trim($nlastdir);
				
				if (empty($nlastdir))
					$nlastdir = substr(md5(microtime()), 0, 10);
				
				$new = $predir . DIRECTORY_SEPARATOR . $nlastdir;
				
				if ($file !== $new)
				{
					// Wenn dir schon vorhanden, dann Nummer anhängen
					if (is_dir($new))
					{
						for ($i = 1; $i < 10; $i++)
						{
							$nlastdir = substr($nlastdir, 0, strlen($nlastdir) - 1) . $i;
							$new      = $predir . DIRECTORY_SEPARATOR . $nlastdir;
							if (!is_dir($new))
								break;
						}
					}
					
					echo $file . "\n";
					echo '--> ' . $new . "\n";
					
					if ($dryrun === false)
					{
						$r = rename($file, $new);
						var_dump($r);
						
						if ($r === true)
						{
							$breakit = true;
							
							//usleep(20000);
							
							// DirSchleife neustarten, weil RecursiveIteratorIterator sonst Exception wirft
							break;
						}
						else
						{
							if (!str_contains(file_get_contents($errlog), $file))
								file_put_contents($errlog, $file . "\n", FILE_APPEND);
						}
					}
					
					echo "\n";
				}
			}
			
		}
		
		if ($breakit === false)
			break;
	}
  
  