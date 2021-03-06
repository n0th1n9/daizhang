<?php
namespace App\Library;

use Config;

/**
 * PHP Requests By cURL
 * @package App\Library
 * @version 1.0
 * @author  AndyLee <root@lostman.org>
 */
class Requests
{
    /**
     * 获取请求会话
     * @param $url string 请求的url
     * @return string
     * @author AndyLee <
     *
     *
     *
     * root@lostman.org>
     */
    public function getRequestCookie($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $headers = array();

        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $headers[] = 'Accept-Encoding: gzip, deflate, sdch';
        $headers[] = 'Accept-Language: zh-CN,zh;q=0.8,gl;q=0.6,zh-TW;q=0.4';
        $headers[] = 'Cache-Control: max-age=0';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'DNT:1';
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::get('constants.CURL_TIME_OUT'));
        $result = curl_exec($ch);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }


        $c = '';
        foreach($cookies as $k=>$v){
            $c .=  $k.'='.$v.'; ';
        }

        return $c;
    }

    /**
     * 下载图片 支持传入会话信息
     * @param $url string 图片地址
     * @param $path string 图片保存位置  相对路径
     * @param $cookie string 会话信息
     * @return void
     * @author AndyLee <root@lostman.org>
     */
    public function saveImage($url, $path, $cookie=null)
    {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::get('constants.CURL_TIME_OUT'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟随301 302

        if(!empty($cookie)){
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        $raw=curl_exec($ch);
        curl_close ($ch);
        if(file_exists($path)){
            unlink($path);
        }
        $fp = fopen($path,'x');
        fwrite($fp, $raw);
        fclose($fp);
    }

    /**
     * POST by cURL
     * @param  string  $url
     * @param   array $params
     * @param null $cookie
     * @return mixed
     * @author AndyLee <root@lostman.org>
     */
    public function postRequest($url, $params, $cookie=null)
    {
        $postData = '';
        /**
         * 序列化数组
         */
        foreach($params as $k => $v)
        {
            $postData .= $k . '='.$v.'&';
        }
        rtrim($postData, '&');

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::get('constants.CURL_TIME_OUT'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        if(!empty($cookie)){
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }


        $output=curl_exec($ch);
//
//        if(curl_exec($ch) === false)
//        {
//            echo 'Curl error: ' . curl_error($ch);
//        }

        curl_close($ch);
        return $output;
    }

    public function getRequest($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
//        curl_setopt($ch, CURLOPT_ENCODING ,'gzip');
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟随301 302
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
        $headers = array();
        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $headers[] = 'Accept-Encoding: gzip, deflate, sdch';
        $headers[] = 'Accept-Language: zh-CN,zh;q=0.8,gl;q=0.6,zh-TW;q=0.4';
        $headers[] = 'Cache-Control: max-age=0';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'DNT:1';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        curl_close($ch);
        return utf8_decode($data);

    }
}