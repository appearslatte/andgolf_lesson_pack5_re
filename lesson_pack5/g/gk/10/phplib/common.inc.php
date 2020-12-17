<?php
//////////////////////////////////////////////////////////////////////
//
//  common.inc.php
//
//  共通機能 Ver4.7.0
//
//////////////////////////////////////////////////////////////////////

$_conf['debug_mode'] 				= false;
$_conf['error_log_file']			= $_root_path . "error_log.txt";

if(!defined("SESSION_NAME")){
	define("SESSION_NAME", "SESS");
}
session_name(SESSION_NAME);

if(version_compare(PHP_VERSION, "5.6", ">=")){
	ini_set("default_charset", "UTF-8");
}
else{
	ini_set("mbstring.internal_encoding", "UTF-8");
	ini_set("mbstring.http_input", "pass");
	ini_set("mbstring.http_output", "UTF-8");
}
ini_set("mbstring.detect_order", "auto");
ini_set("mbstring.substitute_character", "none");

if(is_dir($_root_path . "includes/"))
	ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . realpath($_root_path . "includes/"));

if(!defined("NO_SESSION")){
	ini_set("session.use_cookies", 1);
	ini_set("session.use_only_cookies", 1);
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
		ini_set("session.cookie_secure", 1);
	}
	session_start();
}

if(!defined("E_STRICT"))
	define("E_STRICT", 2048);
if(!defined("E_RECOVERABLE_ERROR"))
	define("E_RECOVERABLE_ERROR", 4096);
if(!defined("E_DEPRECATED"))
	define("E_DEPRECATED", 8192);
if(!defined("E_USER_DEPRECATED"))
	define("E_USER_DEPRECATED", 16384);

error_reporting(E_ALL|E_STRICT);
set_error_handler("errorHandler");

if(ini_get("output_handler") != "mb_output_handler")
	ob_start("mb_output_handler");
else
	ob_start();

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
	foreach($_POST as $key => $val){
		$_POST[$key] = mb_convert_encoding($val, mb_internal_encoding(), "UTF-8,EUC-JP,eucJP-win,SJIS,SJIS-win");
	}
}
elseif(!empty($_GET)){
	foreach($_GET as $key => $val){
		$_GET[$key] = mb_convert_encoding($val, mb_internal_encoding(), "UTF-8,EUC-JP,eucJP-win,SJIS,SJIS-win");
	}
}

if(version_compare(PHP_VERSION, "5.4", "<=") && get_magic_quotes_gpc()){
    function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}

if(version_compare(PHP_VERSION, "5.1", ">="))
	date_default_timezone_set("Asia/Tokyo");

function errorHandler($err_no, $err_str, $err_file, $err_line)
{
	global $_conf, $_root_path;
	
	if(isset($_conf['error_ignore_file'])){
		foreach($_conf['error_ignore_file'] as $ignore){
			if(preg_match($ignore, $err_file))
				return;
		}
	}
	if(isset($_conf['error_ignore_str'])){
		foreach($_conf['error_ignore_str'] as $ignore){
			if(preg_match($ignore, $err_str))
				return;
		}
	}
	
	ob_end_clean();
	if(ini_get("output_handler") != "mb_output_handler")
		ob_start("mb_output_handler");
	
	$err_type = array (
					E_ERROR             => "Error",
					E_WARNING           => "Warning",
					E_PARSE             => "Parsing Error",
					E_NOTICE            => "Notice",
					E_CORE_ERROR        => "Core Error",
					E_CORE_WARNING      => "Core Warning",
					E_COMPILE_ERROR     => "Compile Error",
					E_COMPILE_WARNING   => "Compile Warning",
					E_USER_ERROR        => "User Error",
					E_USER_WARNING      => "User Warning",
					E_USER_NOTICE       => "User Notice",
					E_STRICT            => "Runtime Notice",
					E_RECOVERABLE_ERROR => "Recoverable Error",
					E_DEPRECATED        => "Deprecated",
					E_USER_DEPRECATED   => "User Deprecated",
				);
	
    $err_date = date("Y/m/d H:i:s");
	
	error_log($err_date . "," . $err_type[$err_no] . "," . $err_str . "," . $err_file . "," . $err_line . "\n", 3, $_conf['error_log_file']);
	
	$debug_info = "";
	if($_conf['debug_mode']){
		$debug_info = <<<EOP
<p style="margin-top: 10px;">エラー情報　({$err_str}, {$err_file}, {$err_line})</p>

EOP;
	}
	
	if(file_exists($_root_path . "error.php")){
		require_once($_root_path . "error.php");
	}
	else{
		echo <<<EOP
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<title>エラー</title>
</head>

<body>

<div style="text-align:center;margin:30px;">
<p>エラーが発生しました。<br>
しばらくしてから再度実行して下さい。</p>
{$debug_info}
</div>

</body>
</html>
	
EOP;
	
	}
	exit();
}

function redirect_url($url)
{
	ob_end_clean();
	
	if(!isset($_SERVER['PHP_SELF']))	exit();
	if(!isset($_SERVER['HTTP_HOST']))	exit();
	if(!isset($_SERVER['SERVER_PORT']))	exit();
	
	if($_SERVER["SERVER_PORT"] == 443)
		$protocol = "https";
	else
		$protocol = "http";
	
	$soutai_url = TRUE;
	if(preg_match("/^https?:\/\//i", $url)){
		$soutai_url = FALSE;
	}
	else if(preg_match("/^\//", $url)){
		$url = "{$protocol}://{$_SERVER['HTTP_HOST']}{$url}";
	}
	else{
		if(dirname($_SERVER['PHP_SELF']) != "/"){
			$url = "{$protocol}://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['PHP_SELF'])."/{$url}";
		}
		else{
			$url = "{$protocol}://{$_SERVER['HTTP_HOST']}/{$url}";
		}
		
	}
	
	if($soutai_url){
		if(isset($_SESSION) && SID != ""){
			if(preg_match("/\?(.*)/", $url, $match_str)){
				if(!preg_match("/".session_name()."=/", $match_str[1])){
					if(strlen($match_str[1]) > 0){
						$url .= "&".htmlspecialchars(SID);
					}
					else{
						$url .= htmlspecialchars(SID);
					}
				}
			}
			else{
				$url .= "?".htmlspecialchars(SID);
			}
		}
	}
	
	header("Location: {$url}");
	exit();
}

function kaigyo_replace($text, $replace = "")
{
	$ret = preg_replace("/\r\n/", $replace, $text);
	$ret = preg_replace("/\r/", $replace, $ret);
	$ret = preg_replace("/\n/", $replace, $ret);
	return $ret;
}

function mbstr_round($text, $size, $addstr = "", $byte_count = true, $delete_tag = true, $allowable_tags = "<br>")
{
	if($delete_tag){
		$text = strip_tags($text, $allowable_tags);
	}
	
	if($allowable_tags == "<br>"){
		$text = preg_replace(array("/<br>/", "/<br \/>/"), "\t", $text);
	}
	
	if($byte_count)
		$s_text = mb_strimwidth($text, 0, $size);
	else
		$s_text = mb_substr($text, 0, $size);
	
	if($allowable_tags == "<br>"){
		$s_text = preg_replace("/\t/", "<br />", $s_text);
	}
	
	if($s_text != $text)
		$s_text .= $addstr;
	
	return $s_text;
}

function get_query_string()
{
	if($_SERVER['QUERY_STRING'] != "")
		return "?{$_SERVER['QUERY_STRING']}";
	else
		return "";
}

function init_query_string()
{
	if(isset($_SESSION) && SID != "")
		return "?" . SID;
	else
		return "";
}

function remove_query_string($query_string, $remove_key)
{
	if($query_string == "")		return "";
	if(substr($query_string, 0, 1) == "?"){
		$query_string = substr($query_string, 1);
		$head = "?";
	}
	else
		$head = "";
	$ret = "";
	$strings = preg_split("/&/", $query_string);
	foreach($strings as $one){
		if($one == "")	continue;
		list($key, $val) = preg_split("/=/", $one, 2);
		if($key != $remove_key){
			$ret .= "{$one}&";
		}
	}
	if(strlen($ret) > 0){
		$ret = $head . substr($ret, 0, strlen($ret)-1);
	}
	return $ret;
}

function add_query_string($query_string, $add_val)
{
	if($query_string != "")
		return "{$query_string}&{$add_val}";
	else
		return "?{$add_val}";
}

function clear_post()
{
	foreach($_SESSION as $key => $val){
		if(preg_match("/^_POST_/", $key)){
			unset($_SESSION[$key]);
			if(ini_get("register_globals"))
				session_unregister($key);
		}
	}
}

function mobile_check($mobile_url)
{
	if(!isset($_SERVER['HTTP_USER_AGENT']))	$_SERVER['HTTP_USER_AGENT'] = "";
	if(preg_match("/^DoCoMo\/\d\.\d/", $_SERVER['HTTP_USER_AGENT'])){
		redirect_url($mobile_url);
	}
	elseif(preg_match("/^(?:(?:SoftBank|Vodafone|J-PHONE)\/\d\.\d|MOT-)/", $_SERVER['HTTP_USER_AGENT'])){
		redirect_url($mobile_url);
	}
	elseif(preg_match("/^(?:KDDI-[A-Z]+\d+[A-Z]? )?UP\.Browser\//", $_SERVER['HTTP_USER_AGENT'])){
		redirect_url($mobile_url);
	}
}

function smartphone_check($smartphone_url, $tablet_is_sp = false)
{
	if(!isset($_SERVER['HTTP_USER_AGENT']))	$_SERVER['HTTP_USER_AGENT'] = "";
	if($tablet_is_sp){
		if((strpos($_SERVER['HTTP_USER_AGENT'], "iPhone") !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], "iPad") !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], "iPod") !== false)){
			redirect_url($smartphone_url);
		}			
		else if(strpos($_SERVER['HTTP_USER_AGENT'], "Android") !== false){
			redirect_url($smartphone_url);
		}
	}
	else{
		if((strpos($_SERVER['HTTP_USER_AGENT'], "iPhone") !== false) || (strpos($_SERVER['HTTP_USER_AGENT'], "iPod") !== false)){
			redirect_url($smartphone_url);
		}			
		else if((strpos($_SERVER['HTTP_USER_AGENT'], "Android") !== false) && (strpos($_SERVER['HTTP_USER_AGENT'], "Mobile") !== false)){
			redirect_url($smartphone_url);
		}
	}
}

function htmlsc($string, $flags = ENT_COMPAT, $encoding = "", $double_encode = true)
{
	if(version_compare(PHP_VERSION, "5.4.0", ">=")){
		return htmlspecialchars($string, $flags, $encoding, $double_encode);
	}
	else{
		return htmlspecialchars($string, $flags);
	}
}

function mb_str_replace($needle, $replacement, $haystack)
{
	$needle_len = mb_strlen($needle);
	$replacement_len = mb_strlen($replacement);
	$pos = mb_strpos($haystack, $needle);
	while ($pos !== false)
	{
		$haystack = mb_substr($haystack, 0, $pos) . $replacement . mb_substr($haystack, $pos + $needle_len);
		$pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
	}
	return $haystack;
}


if(isset($_SESSION)){
	if(!defined("NO_POST_TO_GET") && count($_POST) > 0 && count($_FILES) == 0){
		clear_post();
		
		foreach($_POST as $key => $val){
			$key = "_POST_" . $key;
			$_SESSION[$key] = $val;
		}
		
		$query_string = get_query_string();
		
		$sess_name = session_name();
		if(SID != "" && !isset($_GET[$sess_name])){
			$query_string = add_query_string($query_string, htmlspecialchars(SID));
		}
		
		redirect_url($_SERVER['REQUEST_URI']);
	}
	
	foreach($_SESSION as $key => $val){
		if(preg_match("/^_POST_/", $key)){
			$_POST[substr($key, 6)] = $val;
		}
	}
	$_mypost = array();
	foreach($_SESSION as $key => $val){
		if(preg_match("/^_POST_/", $key)){
			$_mypost[substr($key, 6)] = $val;
		}
	}
}

?>
