<?php
//////////////////////////////////////////////////////////////////////
//
//  LibTemplete.class.php
//
//  テンプレートクラス Ver2.2.0
//
//////////////////////////////////////////////////////////////////////

class LibTemplete
{
	private $template_dir;
	private $cache_dir;
	private $encoding;
	
	function __construct($templete_dir = NULL, $cache_dir = NULL, $encoding = "UTF-8"){
		$this->template_dir = $templete_dir;
		$this->cache_dir = $cache_dir;
		$this->encoding = $encoding;
	}
	
	function include_template($_filename, $_context){
		$_cachename = $this->convert_template($_filename);
		extract($_context);
		include($_cachename);
	}
	
	function convert_template($filename){
		if(!file_exists($filename) && $this->template_dir)
			$filename = "{$this->template_dir}{$filename}";
		$cachename = $this->cache_dir . md5(realpath($filename)) . '.cache';
		if(!file_exists($cachename) || filemtime($cachename) < filemtime($filename) || filesize($cachename) == 0){
			$s = file_get_contents($filename);
			if(strtoupper($this->encoding) == "UTF-8"){
				$s = $this->delete_bom($s);
			}
			$s = mb_convert_encoding($s, mb_internal_encoding(), $this->encoding);
			$s = $this->convert_string($s);
			
			if(!file_exists($cachename)){
				if(!touch($cachename))
					trigger_error("ファイルの作成に失敗しました。(" . $cachename . ")", E_USER_ERROR);
			}
			if(!$fp = fopen($cachename, "r+"))
				trigger_error("ファイルのオープンに失敗しました。(" . $cachename . ")", E_USER_ERROR);
			if(!flock($fp, LOCK_EX))
				trigger_error("ファイルのロックに失敗しました。(" . $cachename . ")", E_USER_ERROR);
			if(!ftruncate($fp, 0))
				trigger_error("ファイルのトランケートに失敗しました。(" . $cachename . ")", E_USER_ERROR);
			if(fseek($fp, 0, SEEK_SET) != 0)
				trigger_error("ファイルのシークに失敗しました。(" . $cachename . ")", E_USER_ERROR);
		    if(!fwrite($fp, $s)){
				trigger_error("ファイルの書き込みに失敗しました。(" . $this->data_file . ")", E_USER_ERROR);
			}
			flock($fp, LOCK_UN);
			fclose($fp);
		}
		return $cachename;
	}
	
	function convert_string($s) {
		$s = preg_replace('/^<\?xml/', '<<?php ?>?xml', $s);
		$s = preg_replace('/#\{(.*?)\}/', '<?php echo $1; ?>', $s);
		$s = preg_replace('/%\{(.*?)\}/', '<?php echo htmlspecialchars($1, ENT_COMPAT, ""); ?>', $s);
		return $s;
	}
	
	function delete_bom($str){
		if (($str == NULL) || (mb_strlen($str) == 0)){
			return $str;
		}
		if (ord($str[0]) == 0xef && ord($str[1]) == 0xbb && ord($str[2]) == 0xbf){
			$str = substr($str, 3);
		}
		return $str;
	}
}
?>
