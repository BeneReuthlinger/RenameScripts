<?php
  
  $mydir = 'U:\xxx\unsortiert';
  $dryrun = true;
  
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
      
      $nfilename = substr($basename, 0, 64);
      
      if (!preg_match('/\.' . preg_quote($extension, '/') . '$/', $nfilename))
        $nfilename = $nfilename . '.' . $extension;
      
      $nfilename = preg_replace('/[^äöüÄÖÜa-zA-Z0-9-_., ]/', '_', $nfilename);
      $new = $dirname . DIRECTORY_SEPARATOR . $nfilename;
      
      if ($file !== $new)
      {
        echo $file . "\n";
        echo '--> ' . $new . "\n";      
        
        if ($dryrun === false)
        {
          $r = rename($file, $new);
          var_dump($r);
          usleep(200000);
        }
        
        echo "\n";
      }
    }
  }
  
  