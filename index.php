<?php

/**
 * WebLabyrinth
 *
 * index.php
 *
 * Main web page that creates bogus links in order to entrap
 * web scanners.
 *
 * All code Copyright (c) 2010, Ben Jackson and Mayhemic Labs - 
 * bbj@mayhemiclabs.com. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the author nor the names of contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */


include_once('config.inc.php');
include_once('labyrinth.inc.php');
include_once('dissociated-press.inc.php');

if(preg_match("/Google/",$_SERVER['HTTP_USER_AGENT'])){
	header("HTTP/1.0 404 Not Found");
	print "o/~ Whatever you're looking for / Hey! Don't come around here no more... o/~";
}

// Randomly generate an error just to "Keep it real" this was mainly done to fool w3af
$error_chance = rand(0,100);

if ($error_chance == 16){
	header("HTTP/1.1 404 Not Found");
}elseif ($error_chance == 23){
	header("HTTP/1.1 403 Forbidden");
}elseif ($error_chance == 42){
	#Included just for the WTF Factor
	header("HTTP/1.1 402 Payment Required");
}

// If index.php is in the request URI, lob it off. Otherwise, lob off the trailing slash.

if(preg_match("/index.php/",$_SERVER['REQUEST_URI'])){	
	$directory = dirname($_SERVER['REQUEST_URI']);
}else{
	$directory = rtrim($_SERVER['REQUEST_URI'], "/");
}


// Read the text into a variable for processing by the dissociated press class.
$fh = fopen($config['corpus'], 'r');
$corpus = fread($fh, filesize($config['corpus']));
fclose($fh); 

?>

<html>
<link rel="stylesheet" type="text/css" href="<?php print $config['web_path']; ?>/labyrinth.css">
<title><?php print basename($_SERVER['REQUEST_URI']); ?></title>
<body>
	<?php 
		// Print the text with links
		print dissociatedpress::dissociate($corpus, $directory); 
	?>
</body>
</html>
