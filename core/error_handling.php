<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.

error_reporting(E_ALL);
ini_set('display_errors', true);

if( !defined("E_STRICT") ) define("E_STRICT", 2048);
if( !defined("E_RECOVERABLE_ERROR") ) define("E_RECOVERABLE_ERROR", 4096);
if( !defined("E_DEPRECATED") ) define("E_DEPRECATED", 8192);
if( !defined("E_USER_DEPRECATED") ) define("E_USER_DEPRECATED", 16384);
if( !defined("DEBUG_BACKTRACE_IGNORE_ARGS") ) define("DEBUG_BACKTRACE_IGNORE_ARGS", 0); // I JUST COULD NOT FIND WHAT VALUE IT IS!

/**
* Function used to spread log records using WildFire facilities.
* @param mixed $type, $msg, $file, $line.
*/
function PC_firebug_log($type, $msg, $file, $line) {
	global $Wildfire_header_sent, $Wildfire_msg_idx;
	return;
	if( headers_sent() ) return;
	if (is_array($msg) || is_object($msg)) $msg = print_r($msg, true);
	$types = Array('LOG', 'INFO', 'WARN', 'ERROR');
	$type = in_array(strtoupper($type), $types) ? strtoupper($type) : $types[0];
	$escape = "\'\"\0\n\r\t\\";
	//$msg = str_replace("\n", "<html:br />", $msg);
	//$msg = json_encode(explode("\n", $msg));
	$message = '[{"Type":"'.$type.'","File":"'.addcslashes($file, $escape).'",'.
		'"Line":'.$line.'},"'.addcslashes($msg, $escape).'"]';
	if ( !isset($Wildfire_header_sent) ) {
		$Wildfire_header_sent = true;
		header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
		header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
		header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
	}
	$maxlen = 4500;
	$count = ceil(mb_strlen($message, '8bit') / $maxlen);
	if( !isset($Wildfire_msg_idx) ) $Wildfire_msg_idx = 0;
	for ($i = 0; $i < $count; $i++) {
		$Wildfire_msg_idx++;
		$part = mb_substr($message, ($i * $maxlen), $maxlen, '8bit');
		header('X-Wf-1-1-1-'.$Wildfire_msg_idx.': '.(($i == 0) ? mb_strlen($message, '8bit') : '').
			'|'.$part.'|'.(($i < ($count - 1)) ? '\\' : ''));
	}
}

/**
* Function which writes log record to current working directory. Format "[Date] [time] [-] [string to log]".
* @param mixed $e - used to submit log type to this function.
* @param string $str - the log record text.
*/
function PC_log_error($e, $str) {
	global $cfg;
	if( (error_reporting() & $e["type"]) == 0 ) return;
	$type = "INFO";
	if( $e["type"] & (E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED) ) $type = "WARN";
	if( $e["type"] & (E_ERROR | E_USER_ERROR) ) $type = "ERROR";
	if( $e["type"] & (E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) ) $type = "EXCEPTION";
	// PC_firebug_log($type, $str, $e["file"], $e["line"]);
	if (isset($cfg['debug_mode']) && $cfg['debug_mode']) echo $str;
	if (defined('PC_TEST_MODE') and PC_TEST_MODE) {
		return;
	}
	@file_put_contents(rtrim(dirname(dirname(__FILE__)), "/\\").'/PC_errors.txt', @date('Y-m-d H:i:s').' - '.$str."\n", FILE_APPEND);
}

/**
* Function to get current error type by error number.
* @param int $errno - error number.
* @return string - error name.
*/
function PC_error_typename($errno) {
	if( $errno & E_ERROR ) return "ERROR";
	if( $errno & E_WARNING  ) return "WARNING";
	if( $errno & E_PARSE  ) return "PARSE ERROR";
	if( $errno & E_NOTICE  ) return "NOTICE";
	if( $errno & E_CORE_ERROR ) return "CORE ERROR";
	if( $errno & E_CORE_WARNING ) return "CORE WARNING";
	if( $errno & E_COMPILE_ERROR ) return "COMPILE ERROR";
	if( $errno & E_COMPILE_WARNING ) return "COMPILE WARNING";
	if( $errno & E_USER_ERROR ) return "USER ERROR";
	if( $errno & E_USER_WARNING ) return "USER WARNING";
	if( $errno & E_USER_NOTICE ) return "USER NOTICE";
	if( $errno & E_STRICT  ) return "STRICT";
	if( $errno & E_DEPRECATED ) return "DEPRECATED";
	if( $errno & E_USER_DEPRECATED ) return "USER DEPRECATED";
	// if( $errno & E_RECOVERABLE_ERROR ) return "RECOVERABLE ERROR";
	return "UNKNOWN ERROR";
}

/**
* Function used to format error message.
* @param mixed $errinfo - error information array.
* @param mixed $backtrace - more detailed information about error.
* @return string. Format "[type]: [message text] in script [file name] on line [line number] (optional{inner error text})".
*/
function PC_format_error_string($errinfo, $backtrace = NULL) {
	$rv = "";
	$rv .= PC_error_typename($errinfo["type"]).": ".$errinfo["message"]." in script ".$errinfo["file"]." on line ".$errinfo["line"]."\r\n";
	if( $backtrace ) {
		$brv = "";
		foreach($backtrace as $idx => $trace) {
			if( !isset($trace["function"]) )
				continue;
			if( $trace["function"] == "PC_error_handler" )
				continue;
			if( in_array($trace["function"], Array("require", "include", "require_once", "include_once")) )
				$brv .= "included";
			else {
				$brv .= "in ";
				if( isset($trace["class"]) ) $brv .= $trace["class"];
				if( isset($trace["type"]) ) $brv .= $trace["type"];
				$brv .= $trace["function"] . "() called";
			}
			$brv .= " from script " . (isset($trace["file"])?$trace["file"]:"[unknown]");
			if( isset($trace["line"]) )
				$brv .= " on line " . $trace["line"];
			$brv .= "\r\n";
		}
		$rv .= $brv;
	}
	return $rv;
}

/**
* Function used to register runtime errors; registered to "set_error_handler()".
* @param int $errno,$errline stands for the error number and line on which error occured respectively.
* @param string $errstr, $errfile stands for the error text and file name where error occured respectively.
* @return boolean TRUE.
*/
function PC_error_handler($errno, $errstr, $errfile, $errline) {
	$e = array('type'=> $errno,'message'=> $errstr,'file'=> $errfile,'line'=> $errline);
	PC_log_error($e, PC_format_error_string($e, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
	if (defined('PC_THROW_EXCEPTIONS_ON_EVERYTHING')) {
		echo '<!DOCTYPE html><html><head><meta charset="utf-8" /><title>Error</title></head><body>';
		echo '<pre style="font-size: 11px; line-height: 13px;">';
		throw new ErrorException($errstr, $errno, 1, $errfile, $errline);
	}
	return TRUE;
}

/**
* Function used to register computer shutdowns; registered to  "register_shutdown_function()".
* @return bool true.
*/
function PC_shutdown_handler() {
	if (is_null($e = error_get_last()) === false)
		if (($e['type'] & (E_WARNING | E_NOTICE)) != 0)
			PC_log_error($e, PC_format_error_string($e));
	return TRUE;
}
set_error_handler('PC_error_handler');
register_shutdown_function('PC_shutdown_handler');