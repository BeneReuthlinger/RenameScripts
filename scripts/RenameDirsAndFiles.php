<?php
	
	/**
	 * renameDirsAndFiles
	 */
	class RenameDirsAndFiles
	{
		/**
		 * @var string
		 */
		protected string $mydir = 'T:\MP3';
		
		/**
		 * @var bool
		 */
		protected bool $dryrun = true;
		
		/**
		 * @var int
		 */
		protected int $dirs_maxlen = 40;
		
		/**
		 * @var int
		 */
		protected int $files_maxlen = 64;
		
		/**
		 * @var string
		 */
		protected string $errlog = __DIR__ . DIRECTORY_SEPARATOR . 'error.log';
		
		/**
		 * @param array $options
		 */
		public function __construct($options = [])
		{
			if (isset($options['mydir']))
				$this->mydir = $options['mydir'];
			if (isset($options['dryrun']))
				$this->dryrun = $options['dryrun'];
			if (isset($options['dirs_maxlen']))
				$this->dirs_maxlen = $options['dirs_maxlen'];
			if (isset($options['files_maxlen']))
				$this->files_maxlen = $options['files_maxlen'];
			if (isset($options['errlog']))
				$this->errlog = $options['errlog'];
			
			if (DIRECTORY_SEPARATOR === '\\')
				$this->mydir = str_replace('/', '\\', $this->mydir);
			elseif (DIRECTORY_SEPARATOR === '/')
				$this->mydir = str_replace('\\', '/', $this->mydir);
			
			if ($this->files_maxlen < 20)
				throw new Exception('files_maxlen must be at least 20 chars');
			if ($this->dirs_maxlen < 20)
				throw new Exception('dirs_maxlen must be at least 20 chars');
			
			file_put_contents($this->errlog, "");
		}
		
		/**
		 * @return void
		 */
		public function startRename(): void
		{
			$this->startRenameDirs();
			$this->startRenameFiles();
		}
		
		/**
		 * @return void
		 */
		public function startRenameDirs(): void
		{
			$mydir  = $this->mydir;
			$maxlen = $this->files_maxlen;
			$dryrun = $this->dryrun;
			$errlog = $this->errlog;
			
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
						$nlastdir = preg_replace('/[^äöüÄÖÜa-zA-Z0-9&\-_., ]/ui', '_', $nlastdir);
						
						if (preg_match('/ {2}/ui', $nlastdir))
							$nlastdir = preg_replace('/ +/ui', ' ', $nlastdir);
						
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
		}
		
		/**
		 * @return void
		 */
		public function startRenameFiles(): void
		{
			$mydir  = $this->mydir;
			$maxlen = $this->files_maxlen;
			$dryrun = $this->dryrun;
			$errlog = $this->errlog;
			
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
						$nfilename = mb_substr($basename, 0, $maxlen);
					}
					else
					{
						$nfilename = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $basename);
						$nfilename = mb_substr($nfilename, 0, $maxlen - strlen($extension) - 1);
						$nfilename = $nfilename . '.' . $extension;
					}
					
					$nfilename = trim($nfilename);
					$nfilename = preg_replace('/[^äöüÄÖÜa-zA-Z0-9&\-_., ]/ui', '_', $nfilename);
					$nfilename = preg_replace('/ +/ui', ' ', $nfilename);
					
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
		}
		
	}
	
	