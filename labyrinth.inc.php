<?php

/**
 * labyrinth.inc.php
 *
 * Functions for creating a web page with bogus links in order to entrap
 * web scanners.
 *
 * All code Copyright (c) 2010-2011, Ben Jackson and Mayhemic Labs - 
 * bbj@mayhemiclabs.com. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code mustu retain the above copyright
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
 */
 
class Labyrinth {
	
	var $dbhandle;
	var $crawler_info;
	var $crawler_ip;
	var $crawler_useragent;

	public function Labyrinth($ip,$useragent){
		global $config;
		mt_srand(Labyrinth::MakeSeed());

		$this->crawler_ip = $ip;
		$this->crawler_useragent = $useragent;

		$this->dbhandle = new SQLiteDatabase($config['tracking_db']);
		$this->crawler_info = $this->dbhandle->query("SELECT crawler_ip FROM crawlers WHERE crawler_ip='$ip' AND crawler_useragent='$useragent'");
	}

	function CheckForSearchEngines(){
		switch(true){
			case preg_match("/Google/",$this->crawler_useragent):
			case preg_match("/Yandex/",$this->crawler_useragent):
			case preg_match("/Openfind/",$this->crawler_useragent):
			case preg_match("/msnbot/",$this->crawler_useragent):
			case preg_match("/bingbot/",$this->crawler_useragent):
			case preg_match("/Slurp/",$this->crawler_useragent):
			case preg_match("/Yahoo/",$this->crawler_useragent):
			case preg_match("/Architext/",$this->crawler_useragent):
				return true;
				break;
		}
	}

	function MakeSeed(){
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 123456);
	}

	function ProcessText($text, $directory){

		global $config;

		$link = mt_rand(0,100);

		if ($link < 10){
			$text = trim($text);
			$link = base64_encode(mt_rand(0,100000000));
			$link = str_replace('=','',$link);

			if ($config['email']){
				$email_link = mt_rand(0,100);
				if ($email_link <= $config['email_probability']){
					return '<a href="mailto:' . $link . '@' . $config['email_domain'] . '">' . $text . '</a> ';
				}
			}

			return '<a href="' . $directory . '/' . $link . '">' . $text . '</a> ';
		}else{
			return "$text";
		}
	}

	function SpinTheWheelOfErrors(){
		$error_chance = rand(0,100);

		$error_string = false;

		if ($error_chance == 16){
			$error_string = "HTTP/1.1 404 Not Found";
		}elseif ($error_chance == 23){
			$error_string = "HTTP/1.1 403 Forbidden";
		}elseif ($error_chance == 42){
			#Included just for the WTF Factor
			$error_string = "HTTP/1.1 402 Payment Required";
		}

		if ($error_string){
			header($error_string);
			exit;
		}
	}

	function GenerateAlert(){
		global $config;

		//Have we seen this crawler recently?		
		$last_seen_query = $this->dbhandle->query("SELECT strftime('%s',datetime('now','localtime')) - strftime('%s',last_alert) FROM crawlers");
		
		$time = $last_seen_query->fetchSingle();

		if (($time == 0) || ($time > 3600)){
			if ($config['alert_snort']){
				print $config['alert_snort_text'] . ' ';
			}

			if ($config['alert_email']){
				mail($config['alert_email_address'], "WebLabyrinth Alert - " . $this->crawler_ip, "We've got a live one!\n\nIP: "  . $this->crawler_ip . "User Agent: " . $this->crawler_useragent);
			}
		}
	}

	function LogCrawler(){

		if($this->crawler_info->numRows() > 0){
			$this->dbhandle->query("UPDATE crawlers SET last_seen = datetime('now','localtime'), num_hits=num_hits+1 WHERE crawler_ip='" . $this->crawler_ip . "' AND crawler_useragent='" . $this->crawler_useragent . "'");
		}else{
			$crawler_rdns = gethostbyaddr($crawler_ip);
			$this->dbhandle->query("INSERT INTO crawlers(crawler_ip, crawler_rdns, crawler_useragent, first_seen, last_seen, num_hits) VALUES('" . $this->crawler_ip . "', '$crawler_rdns', '" . $this->crawler_useragent . "', datetime('now','localtime'), datetime('now','localtime'), 1)");
		}
	}
}
?>


