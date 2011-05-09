<?php

/**
 * dissociated-press.inc.php
 *
 * Functions for creating random text for use within WebLabyrinth.
 *
 * Code based off of David Pascoe-Deslauriers' <dpascoed@csiuo.com> 
 * dissociatedpress class. 
 *
 * @link       http://www.csiuo.com/drupal/node/13
 *
 * Copyright 2000-2009 David Pascoe-Deslauriers. All rights reserved.
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions 
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright 
 * notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above 
 * copyright notice, this list of conditions and the following 
 * disclaimer in the documentation and/or other materials provided 
 * with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT ``AS IS'' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE FREEBSD PROJECT OR 
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF 
 * USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN 
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Modifications Copyright (c) 2010, Ben Jackson and Mayhemic Labs - 
 * bbj@mayhemiclabs.com. All rights reserved.
 *
 */
 
 
class dissociatedpress {
 
function dissociate ($str, $url_dir, $randomstart = true, $groupsize = 4, $max = 128) {
	if ($groupsize < 2) {
		$groupsize = 2;
	}
		// Capitalize the first word
	$capital = true;
 
		//Remove from corpus, they just make the result confusing
	$str = str_replace(array("(",")","[","]","{","}"), array(),$str);
 
		//Break up tokens
	$tokens = preg_split("/[ \r\n\t]/",$str);
 
		//Clean up token array
	for ($i = 0; $i < sizeof($tokens); $i++){
		if ($tokens[$i] == ""){
			unset($tokens[$i]);
		}
	}
 
	$tokens = array_values($tokens);
 
		//Init variables
	$return = "";
	$lastmatch = array();
 
		// if we start at the beginning, start there
	if (!$randomstart) {
		for ($n = 0; $n < $groupsize; $n++){
			array_push($lastmatch,$tokens[$n]);
			$res = $this->cleanToken($tokens[$n],$capital);
			$return .= $res[0];
			$capital = $res[1];
		}
	}
 
		//Loop until we have enough output
	$i = 0;
	while ($i < $max + 32){
		// Try and end on a full sentence
		if ($i > $max - 8 and $capital){
			break;
		}
 
		//If the lastmatch group isn't good enough, start randomly
		if (sizeof($lastmatch) < $groupsize){
			$loc = rand(0,sizeof($tokens)-$groupsize);
			$lastmatch = array();
			for ($n = 0; $n < $groupsize; $n++){
				array_push($lastmatch,$tokens[$loc+$n]);
				$res = $this->cleanToken($tokens[$loc+$n],$capital);
				$return .= labyrinth::processtext($res[0],$url_dir);
				$capital = $res[1];
			}
		} else {
			$chains = $this->findChains($tokens, $lastmatch);
			$lastmatch = array();
 
			// If there aren't enough chains, start randomly next time (avoid getting caught in loops)
			if (sizeof($chains) > 2) {
				$loc = $chains[rand(0, sizeof($chains)-1)];
				for ($n = 0; $n < $groupsize; $n++){
					array_push($lastmatch,$tokens[$loc+$n]);
					$res = $this->cleanToken($tokens[$loc+$n],$capital);
					$return .= labyrinth::processtext($res[0],$url_dir);
					$capital = $res[1];
				}
			}
		}
		$i++;
	}
 
	return $return;
}
 
/**
 * Join the tokens with proper typography
 */
 
function cleanToken($token,$capital) {
	if ($capital){
		$token = ucfirst($token);
		$capital = false;
	}
 
	if (substr($token,-1,1) == '.'){
		$capital = true;
		return array($token . "  ",$capital);
	} else {
		return array($token . " ",$capital);
	}
}
 
/**
 * Naively find possible Markov Chains
 */
 
function findChains($haystack, $needle) {
	$return = array();
	for ($i = 0; $i < sizeof($haystack) - sizeof($needle); $i++){
		if ($haystack[$i] == $needle[0]){
			$matches = true;
			for ($j = 1; $j < sizeof($needle); $j++){
				if ($haystack[$i+$j] != $needle[$j]){
					$matches = false;
					break;
				}
			}
			if ($matches == true){
				array_push($return,$i+sizeof($needle));
			}
		}
	}
	return $return;
}
 
}

?>
