<?php

namespace AntService;

class NetworkRequest
{
    private static $curl = null;
    private static $outTime = 60000;
    private static $headers = array();
    private static $cookie = array();
    private static $referer = array();

    public static function setOutTime($msec = 60000)
    {
        self::$outTime = $msec;
        return new self;
    }

    public static function setHeaders($headers = array())
    {
        self::$headers = $headers;
        return new self;
    }

    public static function setCookie($cookie = array())
    {
        self::$cookie = $cookie;
        return new self;
    }

    public static function setReferer($referer = array())
    {
        self::$referer = $referer;
        return new self;
    }

    public static function getRequest($url, $param = array(), $prot = 80, &$resultSet = array())
    {
        self::init();
        count($param) and $url .= '?' . http_build_query($param);
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_PORT, $prot);
        count(self::$headers) and curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers);
        return self::pushResult();
    }

    public static function postRequest($url, $data = array(), $prot = 80, &$resultSet = array())
    {
        self::init();
        curl_setopt(self::$curl, CURLOPT_POST, true);
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_PORT, $prot);
        count(self::$headers) ? curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers) : ($data = http_build_query($data) and curl_setopt(self::$curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Content-Length:' . strlen($data)
        ]));
        if ($data != null) curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $data);
        return self::pushResult();
    }

    public static function putRequest($url, $data, $prot = 80, &$resultSet = array())
    {
        self::init();
        curl_setopt(self::$curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_PORT, $prot);
        count(self::$headers) ? curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers) :
            $data = http_build_query($data) and curl_setopt(self::$curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length:' . strlen($data)
            ]);
        if ($data != null) curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $data);
        return self::pushResult();
    }

    public static function patchRequest($url, $data, $prot = 80, &$resultSet = array())
    {
        self::init();
        curl_setopt(self::$curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_PORT, $prot);
        count(self::$headers) ? curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers) :
            $data = http_build_query($data) and curl_setopt(self::$curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length:' . strlen($data)
            ]);
        if ($data != null) curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $data);
        return self::pushResult();
    }

    public static function deleteRequest($url, $data, $prot = 80, &$resultSet = array())
    {
        self::init();
        curl_setopt(self::$curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_PORT, $prot);
        count(self::$headers) ? curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers) :
            $data = http_build_query($data) and curl_setopt(self::$curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Content-Length:' . strlen($data)
            ]);
        if ($data != null) curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $data);
        return self::pushResult();
    }

    private static function init()
    {
        self::$curl = curl_init();  //初始化
        curl_setopt(self::$curl, CURLOPT_FOLLOWLOCATION, true); //自动跟随源地址重定向
        curl_setopt(self::$curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   //模拟用户操作
        curl_setopt(self::$curl, CURLOPT_AUTOREFERER, true);    //自动设置header中Referer信息
        curl_setopt(self::$curl, CURLOPT_TIMEOUT, self::$outTime);  //设置超时时间
        curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true); //以文件流形式返回
        curl_setopt(self::$curl, CURLOPT_HEADER, true); //显示header头区域
        curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, false);  //对认证证书来源的检查
        curl_setopt(self::$curl, CURLOPT_SSL_VERIFYHOST, false);  //从证书中检查SSL加密算法是否存在
        self::$cookie and curl_setopt(self::$curl, CURLOPT_COOKIE, self::$cookie);    //设置cookie
        self::$referer and curl_setopt(self::$curl, CURLOPT_REFERER, self::$referer);  //设置referer
    }

    private static function pushResult()
    {
        $content = curl_exec(self::$curl);
        $status = curl_getinfo(self::$curl, CURLINFO_HTTP_CODE);
        $error = curl_error(self::$curl);
        curl_close(self::$curl);
        $resultArr = explode(PHP_EOL . PHP_EOL, $content);
        list($headerInfo, $content) = array($resultArr[count($resultArr) - 2], $resultArr[count($resultArr) - 1]);
        foreach (explode(PHP_EOL, $headerInfo) as $header) {
            $headerArr = explode(':', $header);
            count($headerArr) == 2 and $headers[] = array($headerArr[0] => $headerArr[1]);
        }
        return array(
            'status' => $status,
            'headers' => isset($headers) ? $headers : array(),
            'content' => $content,
            'error' => $error
        );
    }
}
