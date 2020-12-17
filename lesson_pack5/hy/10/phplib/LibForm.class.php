<?php
//////////////////////////////////////////////////////////////////////
//
//  LibForm.class.php
//
//  フォームクラス Ver1.2.0 ※独自smtp対応済
//
//////////////////////////////////////////////////////////////////////

class LibForm
{
	public $formDatas;
	public $formGroups;
	public $sendTime;
	private $mail;
	
	function __construct($form_datas, $form_groups){
		$this->formDatas = $this->arrayToObject($form_datas);
		$this->formGroups = $this->arrayToObject($form_groups);
		$this->sendTime = "";
		$this->mail = null;

		$is_post = false;
		foreach($this->formDatas as $key => $val) {
			$val->value = "";

			if(isset($_POST[$key])) {
				$val->value = $_POST[$key];
				$is_post = true;
			}
			else if(isset($_SESSION['lform_name']) && $_SESSION['lform_name'] == $_SERVER['SCRIPT_NAME'] && isset($_SESSION[$key])) {
				$val->value = $_SESSION[$key];
			}
			
			if(!is_array($val->value)) {
				$val->value = htmlsc(trim(mb_convert_kana($val->value, "KV")));
			}
			else {
				foreach($val->value as $key2 => $val2) {
					$val->value[$key2] = htmlsc(trim(mb_convert_kana($val->value[$key2], "KV")));
				}
			}

			if(isset($val->radio_vals)) {
				$tmp = array();
				foreach($val->radio_vals as $val2) {
					$tmp[] = $val2;
				}
				$val->radio_vals = $tmp;

				$val->radio_checked = array();
				foreach($val->radio_vals as $val2) {
					$val->radio_checked[] = $val->value == $val2 ? "checked" : "";
				}
			}

			if(isset($val->check_vals)) {
				if(!is_array($val->value)) {
					$val->value = array();
				}

				$tmp = array();
				foreach($val->check_vals as $val2) {
					$tmp[] = $val2;
				}
				$val->check_vals = $tmp;

				$val->value = array_values(array_diff($val->value, array("")));

				$val->check_checked = array();
				foreach($val->check_vals as $val2) {
					$val->check_checked[] = in_array($val2, $val->value) ? "checked" : "";
				}
				
				$val->valuestr = implode(",", $val->value);
			}

			if(isset($val->select_vals)) {
				$tmp = array();
				foreach($val->select_vals as $val2) {
					$tmp[] = $val2;
				}
				$val->select_vals = $tmp;

				$val->select_selected = array();
				foreach($val->select_vals as $val2) {
					$val->select_selected[] = $val->value == $val2 ? "selected" : "";
				}
			}
		}
		
		if($is_post) {
			$_SESSION['lform_name'] = $_SERVER['SCRIPT_NAME'];
			foreach($this->formDatas as $key => $val) {
				$_SESSION[$key] = $val->value;
				if(isset($val->valuestr)) {
					$_SESSION[$key . "_valuestr"] = $val->valuestr;
				}
			}
		}
	}
	
	function getValidateRules() {
		$ret = "";
		foreach($this->formDatas as $key => $val) {
			if(!isset($val->validate)) continue;
			if(!isset($val->check_vals)) {
				$ret .= "{$key}: {";
			}
			else {
				$ret .= "'{$key}[]': {";
			}
			foreach($val->validate as $key2 => $val2) {
				$ret .= "{$key2}: {$val2->rule}, ";
			}
			$ret .= "}, ";
		}
		return $ret;
	}
	
	function getValidateMessages() {
		$ret = "";
		foreach($this->formDatas as $key => $val) {
			if(!isset($val->validate)) continue;
			if(!isset($val->check_vals)) {
				$ret .= "{$key}: {";
			}
			else {
				$ret .= "'{$key}[]': {";
			}
			foreach($val->validate as $key2 => $val2) {
				$ret .= "{$key2}: '{$val2->mes}', ";
			}
			$ret .= "}, ";
		}
		return $ret;
	}
	
	function getValidateGroups() {
		$ret = "";
		foreach($this->formGroups as $key => $val) {
			$ret .= "{$key}: \"" . implode(" ", (array)$val->members) . "\",";
		}
		return $ret;
	}
	
	function getValidateErrorPlacement() {
		$ret = "";
		foreach($this->formGroups as $key => $val) {
			$tmp = array();
			foreach($val->members as $val2) {
				$tmp[] = "element.attr(\"name\") == \"{$val2}\"";
			}
			$tmp2 = implode(" || ", $tmp);
			$ret .= "if({$tmp2}) {error.appendTo(\"#{$val->messege_id}\");return;}";
		}
		foreach($this->formDatas as $key => $val) {
			if(!isset($val->messege_id)) continue;
			$ret .= "if(element.attr(\"name\") == \"{$key}\") {error.appendTo(\"#{$val->messege_id}\");return;}";
		}
		return $ret;
	}
	
	function initServerValidate() {
		require_once(dirname(__FILE__) . "/Validator.php");
		
		Valitron\Validator::lang("ja");
		Valitron\Validator::langDir(dirname(__FILE__));
		$v = new Valitron\Validator($_SESSION);

		foreach($this->formDatas as $key => $val) {
			if(!isset($val->validate)) continue;
			if(isset($val->check_vals)) {
				$key = "{$key}_valuestr";
			}
			foreach($val->validate as $key2 => $val2) {
				switch($key2) {
					case 'required':
					case 'email':
					case 'url':
					case 'date':
						$v->rule($key2, $key);
						break;
					case 'number':
						$v->rule('numeric', $key);
						break;
					case 'equalTo':
						$v->rule('equals', $key, str_replace(array("'", "#"), "", $val2->rule));
						break;
					case 'max':
					case 'min':
						$v->rule($key2, $key, (int)$val2->rule);
						break;
					case 'maxlength':
						$v->rule('lengthMax', $key, (int)$val2->rule);
						break;
					case 'minlength':
						$v->rule('lengthMin', $key, (int)$val2->rule);
						break;
					case 'katakana':
						$v->rule('regex', $key, "/^([ァ-ヶー 　]+)$/u");
						break;
					case 'hiragana':
						$v->rule('regex', $key, "/^([ぁ-んー 　]+)$/u");
						break;
					case 'katahira':
						$v->rule('regex', $key, "/^([ぁ-んァ-ヶー 　]+)$/u");
						break;
					case 'tel':
						$v->rule('regex', $key, "/^([0-9\-]+)$/");
						break;
				}
			}
			$v->labels(array($key => $val->label));
		}

		return $v;
	}

	function changeValueBr() {
		foreach($this->formDatas as $val) {
			if(!isset($val->valuestr)) {
				$val->value = nl2br($val->value, false);
			}
			else {
				$val->valuestr = nl2br($val->valuestr, false);
			}
		}
	}
	
	function valitronErrorsToArray($errors) {
		$ret = array();
		foreach($errors as $key => $val) {
			foreach($val as $key2 => $val2) {
				$ret[] = $val2;
			}
		}
		return $ret;
	}
	
	function initMail() {
		global $_conf;

		if(version_compare(PHP_VERSION, "5.5", ">=")) {
			require_once(dirname(__FILE__) . "/PHPMailer.php");
			require_once(dirname(__FILE__) . "/Exception.php");
			require_once(dirname(__FILE__) . "/SMTP.php");
			$this->mail = new PHPMailer\PHPMailer\PHPMailer();
			
			if(isset($_conf['smtp_host'])) {
				$this->mail->isSMTP();  // SMTPを使用
				$this->mail->Host = $_conf['smtp_host'];  // SMTPサーバーを指定
				$this->mail->SMTPAuth = true;   // SMTP authentication を有効に
				$this->mail->Username = $_conf['smtp_user'];  // SMTP ユーザ名
				$this->mail->Password = $_conf['smtp_pass'];  // SMTP パスワード
				$this->mail->SMTPSecure = "tls";  // 暗号化を有効に
				$this->mail->Port = 587;  // TCP ポートを指定
			}
		}
		else {
			require_once(dirname(__FILE__) . "/class.phpmailer.php");
			$this->mail = new PHPMailer();
		}

		$this->mail->CharSet = "UTF-8";
		$this->mail->Encoding = "8bit";

		$this->sendTime = date("Y/m/d H:i:s");
	}
	
	function sendMail($mailTo, $mailFrom, $mailFromName, $subject, $body) {

		$org_encoding = mb_internal_encoding();
		mb_internal_encoding("UTF-8"); 

		$this->mail->AddAddress($mailTo);
		$this->mail->Sender = $mailFrom;
		$this->mail->From = $mailFrom;
		$this->mail->FromName = $mailFromName;
		$this->mail->Subject = $subject;
		$this->mail->Body  = $body;

		if(!$this->mail->Send()){
			trigger_error("メールの送信に失敗しました。({$this->mail->ErrorInfo})", E_USER_ERROR);
		}
		$this->mail->ClearAddresses();

		mb_internal_encoding($org_encoding); 

	}
	
	function getHostName() {
		if(isset($_SERVER['REMOTE_HOST']))
			return $_SERVER['REMOTE_HOST'];
		else
			return gethostbyaddr($_SERVER['REMOTE_ADDR']);
	}
	
	function isDataSuccess() {
		if(!isset($_SESSION['lform_name']) || $_SESSION['lform_name'] != $_SERVER['SCRIPT_NAME']) {
			return false;
		}
		foreach($this->formDatas as $key => $val) {
			if(!isset($_SESSION[$key])) {
				return false;
			}
		}

		foreach($this->formDatas as $key => $val) {
			foreach($val->validate as $key2 => $val2) {
				if(strtolower($key2) == "required" && strtolower($val2->rule) == "true") {
					if(!is_array($_SESSION[$key])) {
						if(strlen($_SESSION[$key]) == 0) {
							return false;
						}
					}
					else {
						if(count($_SESSION[$key]) == 0) {
							return false;
						}
					}
				}
			}
		}

		return true;
	}
	
	function clearSession() {
		$_SESSION = array();
		if(ini_get("register_globals"))
			session_unset();
	}
	
	function arrayToObject($array) {
		$obj = new stdClass;
		foreach($array as $key => $val) {
			if(strlen($key)) {
				if(is_array($val)) {
					$obj->{$key} = $this->arrayToObject($val);
				} else {
					$obj->{$key} = $val;
				}
			}
		}
		return $obj;
	} 

}
