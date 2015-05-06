<?php
namespace Home\Event;
class EmailEvent
{
	public function sendMail($to, $subject, $content) {
		Vendor('PHPMailer.PHPMailerAutoload');
		$config=C('EMAIL_CONFIG');
		$mail = new \PHPMailer(); //实例化
		$mail->IsSMTP(); // 启用SMTP
		$mail->SMTPAuth = true; //启用smtp认证
		$mail->SMTPSecure = 'ssl';                 // 使用安全协议
		$mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
		$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
		$mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
		$mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
		$mail->From =$config['FROM_EMAIL']; //发件人地址（也就是你的邮箱地址）
		$mail->FromName = $config['FROM_NAME']; //发件人姓名
		$mail->AddAddress($to);
		$mail->WordWrap = 50; //设置每行字符长度
		$mail->IsHTML(false); // 是否HTML格式邮件
		$mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
		$mail->Subject =$subject; //邮件主题
		$mail->Body = $content; //邮件内容
		$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
		//$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示
		if(!$mail->Send()) {
			//echo "Mailer Error: " . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}
}
