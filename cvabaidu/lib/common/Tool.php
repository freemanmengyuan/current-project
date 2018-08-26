<?php

#namespace common\models;

class Tool 
{
	public static function getHttp($url,$timeout,$cookie,$transFlag)
	{
		//初始化
		$ch = curl_init();
		//设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);   //只需要设置一个秒的数量就可以  

		$useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 

		$ip = self::get_rand_ip();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));
		if($cookie !="" && file_exists($cookie))
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		}
		//执行并获取HTML文档内容
		$output = curl_exec($ch); 
		//释放curl句柄
		curl_close($ch); 
		//打印获得的数据
		if($transFlag)
		{
			$output = iconv('GBK', 'UTF-8', $output); //将字符串的编码从GB2312转到UTF-8
		}
		return $output;
	}

	public static function getHttpForGzip($url,$timeout,$cookie,$transFlag,$zipFlag)
	{
		//初始化
		$ch = curl_init();
		//设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);   //只需要设置一个秒的数量就可以
		if($zipFlag)
		{
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");	
		}

		$useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36";
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 

		$ip = self::get_rand_ip();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));
		if($cookie !="" && file_exists($cookie))
		{
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		}
		//执行并获取HTML文档内容
		$output = curl_exec($ch); 
		//释放curl句柄
		curl_close($ch); 
		//打印获得的数据
		if($transFlag)
		{
			$output = iconv('GBK', 'UTF-8', $output); //将字符串的编码从GB2312转到UTF-8
		}
		return $output;
	}

	public static function get_rand_ip() {
	    $arr_1 = array("218", "218", "66", "66", "218", "218", "60", "60", "202", "204", 
						"66", "66", "66", "59", "61", "60", "222", "221", "66", "59", "60", "60", "66", "218", "218", "62", "63", "64", "66", "66", "122", "211");
		$randarr = mt_rand(0, count($arr_1)-1);
		$ip1id = $arr_1[$randarr];
		$ip2id = round(rand(600000, 2550000) / 10000);
		$ip3id = round(rand(600000, 2550000) / 10000);
		$ip4id = round(rand(600000, 2550000) / 10000);
		return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
	}

	public static function getCookie($url, $username,$password,$cookie_file)
	{
		$useragent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36";
		#$url = 'http://xueqiu.com/user/login' ;
		$fields = array(
				   'username'=>$username ,
				   'areacode'=>'86' ,
				   'telephone'=>'',
				   'remember_me'=>1 ,
				   'password'=>$password,
				  );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 

		$ip = self::get_rand_ip();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$ip, 'CLIENT-IP:'.$ip));
		$referer = "https://xueqiu.com/account/reg";
		curl_setopt($ch,CURLOPT_REFERER,$referer);

		curl_setopt($ch, CURLOPT_POST,count($fields)) ; // 启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
		curl_setopt($ch, CURLOPT_POSTFIELDS,$fields); // 在HTTP中的“POST”操作。如果要传送一个文件，需要一个@开头的文件名
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

		ob_start();
		curl_exec($ch);
		ob_get_contents() ;
		ob_end_clean();

		//close connection
		curl_close($ch) ;	
    }

	public static function isFileHas($file,$str)
	{
		if(!file_exists($file))
			return false;
		$fp = fopen($file, "r"); 
		while(!feof($fp)) 
		{ 
			$line = fgets($fp);
			if(strpos($line,$str) !== false)
			{
				return true;
			}
		} 
		fclose($fp); 

		return false;
	}

	public static function encode($content)
	{
		$content = iconv('utf-8', 'gbk', $content);

		return urlencode($content);
	}

	public static function sendMessage($message)
	{
		$url = "http://sms.mobset.com/SDK/Sms_Send.asp?";
		$CorpID = "112741";
		$LoginName = "客服";
		$Passwd = "123123";
		$phone = "18600044215";

		$finalUrl = $url .
			'CorpID=' . $CorpID .
			'&LoginName=' . self::encode($LoginName) .
			'&Passwd=' . $Passwd .
			'&send_no=' . $phone .
			'&LongSms=1' .
			'&msg=' . self::encode($message);

		$ret = self::getHttp($finalUrl,120,"",true);

		if (strpos($ret, '1,') === 0)
			return true;

		return false;
	}

	public static function sendMessageNew($message,$phone)
	{
		$url = "http://sms.mobset.com/SDK/Sms_Send.asp?";
		$CorpID = "112741";
		$LoginName = "客服";
		$Passwd = "123123";

		$finalUrl = $url .
			'CorpID=' . $CorpID .
			'&LoginName=' . self::encode($LoginName) .
			'&Passwd=' . $Passwd .
			'&send_no=' . $phone .
			'&LongSms=1' .
			'&msg=' . self::encode($message);

		$ret = self::getHttp($finalUrl,120,"",true);

		if (strpos($ret, '1,') === 0)
			return true;

		return false;
	}
	
	//Error,Notice,Warn,Trace,Fatal
	public static function log_print($errorType, $msg)
	{
		$basePath = dirname(__FILE__)."/../..";
		$logPath = $basePath . "/log";
		$accessLog = $logPath . "/access.log." . date("Ymd");
		$errorLog = $logPath . "/error.log." . date("Ymd");

		$str = "[" . $errorType . "] " . date("Y-m-d H:i:s") . " " . $msg . "\n";
		$logFile = $accessLog;
		if((strcasecmp($errorType,"error")==0) || (strcasecmp($errorType,"warn")==0) || 
			(strcasecmp($errorType,"fatal")==0))
		{
			$logFile = $errorLog;	
		}
		error_log($str, 3, $logFile);
	}

}
