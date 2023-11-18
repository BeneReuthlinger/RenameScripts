<?php
  
  $mydir = 'T:\MP3\_unsortiert';
  $dryrun = true;
  
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
        
        $nlastdir = substr($lastdir, 0, 40);
        
        $nlastdir = preg_replace('/[^äöüÄÖÜa-zA-Z0-9-_., ]/', '_', $nlastdir);
        $nlastdir = preg_replace('/ {1,}/', ' ', $nlastdir);
        $nlastdir = trim($nlastdir);

        $new = $predir . DIRECTORY_SEPARATOR . $nlastdir;
              
        if ($file !== $new)
        {
          echo $file . "\n";
          echo '--> ' . $new . "\n";   
          
          if ($dryrun === false)
          {
            $r = rename($file, $new);
            var_dump($r);
            
            if ($r === true)
            {
              $breakit = true;
              
              usleep(200000);
              
              // DirSchleife neustarten, weil RecursiveIteratorIterator sonst Exception wirft
              break;
            }
          }
          
          echo "\n"; 
        }
      }
      
    }
    
    if ($breakit === false)
      break;  
  }
  
  