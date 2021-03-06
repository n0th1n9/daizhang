<?php
namespace App\Library;

use Config;
use Exception;
/**
 * 短信发送类
 * @version 1.0
 * @author AndyLee <root@lostman.org>
 */

class Sms
{
    public $api_key;
    public $url;

//    Usage
//    $text="【云片网】您的验证码是1234";
//    $mobile = "18626616501";
//    $sms = new Sms();
//    $result = $sms->sendMessage($text, $mobile);

    /**
     * 初始化方法
     * @throws Exception
     */
    public function __construct(){
        $this->api_key = Config::get('app.api_key');
        $this->url = Config::get('app.server_url');

        if(empty($this->api_key) || empty($this->url)){
            \App::abort('500','短信发送初始化失败');
        }

    }

    /**
     * 发送短信
     * @param string $text 手机号码
     * @param int $mobile 手机号码
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function sendMessage($text, $mobile)
    {
        $encoded_text = urlencode("$text");
        $post_string="apikey=$this->api_key&text=$encoded_text&mobile=$mobile";
        return $this->sock_post($this->url, $post_string);
    }

    /**
     * cURL POST请求
     * @param $url
     * @param $query
     * @return string
     * @author AndyLee <root@lostman.org>
     */
    public function sock_post($url,$query){
        //TODO 计数器
        $data = "";
        $info=parse_url($url);
        $fp=fsockopen($info["host"],80,$errno,$errstr,30);
        if(!$fp){
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp,$head);
        $header = "";
        while ($str = trim(fgets($fp,4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp,4096);
        }
        return json_decode($data);
    }
}