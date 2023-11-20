<?php
	
	$mydir  = 'U:\xxx';
	$dryrun = false;
	
	$maxlen = 40;
	$errlog = __DIR__ . DIRECTORY_SEPARATOR . 'errorDirs.log';
	
	include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'renameDirnames.php';
	
	$maxlen = 64;
	$errlog = __DIR__ . DIRECTORY_SEPARATOR . 'errorFiles.log';
	
	include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'renameFilenames.php';