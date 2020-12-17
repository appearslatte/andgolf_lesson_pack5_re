<?php
//////////////////////////////////////////////////////////////////////
//
//  reservation.php
//
//   概要  :      体験レッスン予約フォーム
//   作成日:      2020/09/16
//   改定日:      
//   備考  :      ※独自smtp対応済
//
//////////////////////////////////////////////////////////////////////

$_root_path = "./";

require_once($_root_path . "phplib/common.inc.php");
require_once($_root_path . "phplib/LibForm.class.php");
require_once($_root_path . "phplib/LibTemplete.class.php");

//##### 設定項目
$_conf['mail_email_col'] = "email";							// 管理者メールfromと確認メールtoに利用するinput項目
$_conf['mail_admin'] = ["info@andgolf.jp", "andgolf-order@realabo.com"];	// 管理者メールto
$_conf['mail_sub'] = "And Golf「スイング改造プログラム」無料体験レッスン 予約フォーム";		// 管理者メールsub
$_conf['mail_kakunin_from'] = "info@andgolf.jp";			// 確認メールfrom
$_conf['mail_kakunin_fromname'] = "アンドゴルフ海浜幕張店";		// 確認メールfromname
$_conf['mail_kakunin_sub'] = "「スイング改造プログラム」無料体験レッスンのご予約を受け付けました";	// 確認メールsub

//##### SMTP設定
$_conf['smtp_host'] = "andgolf.sakura.ne.jp";		// SMTPサーバ
$_conf['smtp_user'] = "info@andgolf.jp";			// SMTP User
$_conf['smtp_pass'] = "h1r0o3To3";					// SMTP Password

//##### フォーム項目設定
$_conf['form_datas'] = array(
	"date1" => array(
		"label" => "第一希望（日程）",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"time1" => array(
		"label" => "第一希望（時間）",
		"select_vals" => array("7:00～10:00","10:00～13:00","13:00～16:00","16:00～19:00","19:00～22:00"),
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"date2" => array(
		"label" => "第二希望（日程）",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"time2" => array(
		"label" => "第二希望（時間）",
		"select_vals" => array("7:00～10:00","10:00～13:00","13:00～16:00","16:00～19:00","19:00～22:00"),
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"date3" => array(
		"label" => "第三希望（日程）",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"time3" => array(
		"label" => "第三希望（時間）",
		"select_vals" => array("7:00～10:00","10:00～13:00","13:00～16:00","16:00～19:00","19:00～22:00"),
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
		),
	),
	"name" => array(
		"label" => "氏名",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
			"maxlength" => array(
				"rule" => "100",
				"mes" => "入力内容が長すぎます",
			),
		),
	),
	"tel" => array(
		"label" => "電話番号",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
			"tel" => array(
				"rule" => "true",
				"mes" => "電話番号が正しくありません",
			),
			"maxlength" => array(
				"rule" => "13",
				"mes" => "入力内容が長すぎます",
			),
		),
	),
	"email" => array(
		"label" => "メールアドレス",
		"validate" => array(
			"required" => array(
				"rule" => "true",
				"mes" => "必須項目です",
			),
			"email" => array(
				"rule" => "true",
				"mes" => "メールアドレスの形式が正しくありません",
			),
		),
	),
	"comment" => array(
		"label" => "現在のお悩み",
		"validate" => array(
			"maxlength" => array(
				"rule" => "4000",
				"mes" => "入力内容が長すぎます",
			),
		),
	),
);
$_conf['form_groups'] = array(
	"kibou1" => array(
		"members" => array("date1", "time1"),
		"messege_id" => "kibou1_error",
	),
	"kibou2" => array(
		"members" => array("date2", "time2"),
		"messege_id" => "kibou2_error",
	),
	"kibou3" => array(
		"members" => array("date3", "time3"),
		"messege_id" => "kibou3_error",
	),
);

// クラス初期化
$libTemplete = new LibTemplete(null, $_root_path . "cache/");
$libForm = New LibForm($_conf['form_datas'], $_conf['form_groups']);

if(!isset($_GET['p'])) $_GET['p'] = "";
if($_GET['p'] == "e"){
	finish_page();
}
else if($_GET['p'] == "v"){
	validate_output();
}
else{
	input_page();
}

// バリデーションJavaScript出力
function validate_output()
{
	global $libForm, $libTemplete;
	
	header('Content-Type: application/javascript; charset=UTF-8');
	
	// js出力
	$context = array(
		"rules" => $libForm->getValidateRules(),
		"messages" => $libForm->getValidateMessages(),
		"groups" => $libForm->getValidateGroups(),
		"group_messageid" => $libForm->getValidateErrorPlacement(),
	);
	$libTemplete->include_template("reservation_validate_tpl.js", $context);
}

// フォーム画面出力
function input_page()
{
	global $libForm, $libTemplete;

	// html出力
	$context = array(
		"datas" => $libForm->formDatas,
		"next_url" => "?p=e",
		"errors" => array(),
		"today" => date("Y-m-d"),
		"twoweekafter" => date("Y-m-d", time() + 60*60*24*14),		// 2週間後
	);
	$libTemplete->include_template("reservation_input_tpl.html", $context);
}

// 完了画面出力
function finish_page()
{
	global $_conf, $libForm, $libTemplete;
	
	// サーバ側バリデーション
	$v = $libForm->initServerValidate();

	// カスタムバリデーション追加（あれば）
	// $v->rule('regex', "xxxxx", "/^([ァ-ヶー 　]+)$/u");
	// 参考：https://github.com/vlucas/valitron

	if(!$v->validate()) {
		// html出力
		$context = array(
			"datas" => $libForm->formDatas,
			"next_url" => "?p=e",
			"errors" => $libForm->valitronErrorsToArray($v->errors()),
			"today" => date("Y-m-d"),
			"twoweekafter" => date("Y-m-d", time() + 60*60*24*14),		// 2週間後
		);
		$libTemplete->include_template("reservation_input_tpl.html", $context);
		return;
	}
	
	// データチェック
	if(!$libForm->isDataSuccess()) {
		redirect_url(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
	}

	// エキスパのフォームに送信する
	$expa_ret = send_expa($libForm->formDatas->email->value);
	$libForm->formDatas->expa_kakka = $expa_ret ? "成功" : "失敗";

	// メール初期化
	$libForm->initMail();

	// メール送信（管理者）
	foreach($_conf['mail_admin'] as $mail_admin) {
		$libForm->sendMail(
			$mail_admin,												// to
			$libForm->formDatas->{$_conf['mail_email_col']}->value,		// from
			"",															// fromname
			$_conf['mail_sub'],											// sub
			body_admin($libForm->formDatas)								// body
		);
	}

	// メール送信（確認メール）
	$libForm->sendMail(
		$libForm->formDatas->{$_conf['mail_email_col']}->value,		// to
		$_conf['mail_kakunin_from'],								// from
		$_conf['mail_kakunin_fromname'],							// fromname
		$_conf['mail_kakunin_sub'],									// sub
		body_kakunin($libForm->formDatas)							// body
	);

	// セッションクリア
	$libForm->clearSession();
	
	// リダイレクト
	redirect_url("https://andgolf.jp/lp/lesson_pack5/thanks/");
	
	// // html出力
	// $context = array();
	// $libTemplete->include_template("reservation_finish_tpl.html", $context);
}

// メール本文作成（管理者）
function body_admin()
{
	global $_conf, $libForm;

	return <<<EOP
{$_conf['mail_sub']}からの送信です。
ご対応をお願いいたします。
----------------------------------------------------------------
[体験レッスン希望日時]
第一希望：{$libForm->formDatas->date1->value} {$libForm->formDatas->time1->value}
第二希望：{$libForm->formDatas->date2->value} {$libForm->formDatas->time2->value}
第三希望：{$libForm->formDatas->date3->value} {$libForm->formDatas->time3->value}

[氏名]
{$libForm->formDatas->name->value}

[電話番号]
{$libForm->formDatas->tel->value}

[メールアドレス]
{$libForm->formDatas->email->value}

[現在のお悩み]
{$libForm->formDatas->comment->value}
----------------------------------------------------------------
送信時刻　　　　：{$libForm->sendTime}
送信者情報　　　：{$libForm->getHostName()}
エキスパ送信　　：{$libForm->formDatas->expa_kakka}

EOP;
}

// メール本文作成（確認メール）
function body_kakunin()
{
	global $_conf, $libForm;

	return <<<EOP

この度は、アンドゴルフ「スイング改造プログラム」体験レッスンのご予約をありがとうございます。

下記の内容にて送信いただきました。

本日から1営業日以内に、体験レッスンの日時、詳細を記載したメール
「体験レッスン日時確定のお知らせ」をお送りさせていただきますので、しばらくお待ちください。

■入力内容
----------------------------------------------------------------
[体験レッスン希望日時]
第一希望：{$libForm->formDatas->date1->value} {$libForm->formDatas->time1->value}
第二希望：{$libForm->formDatas->date2->value} {$libForm->formDatas->time2->value}
第三希望：{$libForm->formDatas->date3->value} {$libForm->formDatas->time3->value}

[氏名]
{$libForm->formDatas->name->value}

[電話番号]
{$libForm->formDatas->tel->value}

[メールアドレス]
{$libForm->formDatas->email->value}

[現在のお悩み]
{$libForm->formDatas->comment->value}
----------------------------------------------------------------

万が一、こちらのメールが「迷惑メールフォルダ」に振り分けられている場合は、
「体験レッスン日時確定のお知らせ」が届かない可能性がございますので、
お手数ですが、必ず迷惑メールフィルターの解除をお願い致します。


その他ご不明点等ございましたら
お気軽に下記アンドゴルフまでお問合せ下さい。


それでは引き続きよろしくお願い申し上げます。
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

■発 行
【＆ゴルフ】運営事務局
（株式会社アンドゴルフ）

■住所（アンドゴルフ事務局）
〒212-0014 神奈川県川崎市幸区大宮町22-2
 ロイヤルシャトー川崎202

■住所（アンドゴルフ海浜幕張スタジオ）
〒261-0025 千葉県千葉市美浜区浜田
 2-7-2 ウェルズ21

■連絡先
【＆ゴルフ】お問い合わせフリーダイヤル
 TEL：0120-088-112（平日10時~19時）

━━━━━━━━━━━━━━━━━━

EOP;
}

// エキスパのフォームに送信
function send_expa($email)
{
	// 送信先URL
	$url = 'https://variation-ex.com/fx/lp5hp';

	// POST内容
	$posts = array(
		'_method' => 'POST',
		'data[MasterSubscriber][Publisher_Id]' => '37826',
		'data[MasterSubscriber][Input_No]' => '49',
		'data[MasterSubscriber][Mail]' => $email,
		'data[MasterSubscriber][C01]' => '既存リスト',
		'data[MasterSubscriber][C02]' => '',
		'data[MasterSubscriber][C03]' => '',
		'data[MasterSubscriber][C04]' => '',
		'data[MasterSubscriber][C05]' => '',
		'data[MasterSubscriber][C50]' => 'maillist',
	);

	$curl_error = null;
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($posts));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$html = curl_exec($ch);

	if(curl_errno($ch)) {
		//curlでエラー発生
		return false;
	}
	curl_close($ch);

	if(strpos($html, "ご登録ありがとうございました") === false) {
		return false;
	}
	
	return true;
}
