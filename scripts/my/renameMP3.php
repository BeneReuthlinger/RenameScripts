<?php

    include ".." . DIRECTORY_SEPARATOR . "\RenameDirsAndFiles.php";

	$rn = new RenameDirsAndFiles(['mydir' => 'V:\MP3\MP3-3-untagged']);
	$rn->startRename();