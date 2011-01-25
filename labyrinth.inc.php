<?php

/**
 * labyrinth.inc.php
 *
 * Functions for creating a web page with bogus links in order to entrap
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
 */
 
 class Labyrinth {

	function CheckForSearchEngines($user_agent){
		switch(true){
			case preg_match("/Google/",$user_agent):
			case preg_match("/Yandex/",$user_agent):
			case preg_match("/Openfind/",$user_agent):
			case preg_match("/msnbot/",$user_agent):
			case preg_match("/Slurp/",$user_agent):
			case preg_match("/Yahoo/",$user_agent):
			case preg_match("/Architext/",$user_agent):
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

		mt_srand(Labyrinth::MakeSeed());

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
}
?>


