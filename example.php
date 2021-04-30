<?php

require_once 'vendor/autoload.php';

use LinkV\IM\IM;
use LinkV\IM\Socket\SocketInterface;
use LinkV\IM\Exception\ResponseException;

class HttpClient implements SocketInterface
{
    public function get($url, $params, $headers = array())
    {
        $url = $url . "?" . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $body = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status_code' => $status_code, 'body' => $body];
    }

    public function post($url, $params, $headers = array())
    {

        $ch = curl_init();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $body = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status_code' => $status_code, 'body' => $body];
    }
}

function test()
{
    $secret = 'eyJhcHBfa2V5IjoiTE02MDAwMTU3Mzk2MjE0NzAyMzcxMjc5IiwiYXBwX3NlY3JldCI6IjY0MjRhMTk4MDk3YjVmNDg2NWYyYmY0MzRkZTQ5ZWIwIiwiaW1fYXBwX2lkIjoid29vcGx1c19mbHV0dGVyIiwiaW1fYXBwX2tleSI6IjgxNjJmY2QwIiwiaW1fYXBwX3NlY3JldCI6IjNmYzQwMDljMWMxYmU4NzMwMTE3MzI2YmM3YjVhNTM3IiwiaW1faG9zdCI6Imh0dHA6Ly9pbS1hcGkuZnVzaW9udi5jb20ifQ==';
    $http_client = new HttpClient();
    $a = new IM($secret, $http_client);

    $thirdUID = "test-php-tob";
	$aID = "test";

    try {
        $resp = $a->GetTokenByThirdUID($thirdUID, $aID,"test-php",-1,'http://xxxxx/app/rank-list/static/img/defaultavatar.cd935fdb.png');
        printf("im_token: %s\n", $resp->getIMToken());
        printf("open_id: %s\n", $resp->getOpenID());
    } catch (ResponseException $e) {
        echo $e;
    }
    $toUID = "1100";
	$objectName = "RC:textMsg";
	$content = "测试单聊";
    try {
        printf("code: %d \n",$a->PushConverseData($thirdUID,$toUID,$objectName,$content));
    } catch (ResponseException $e) {
        echo $e;
    }
    $content = "测试 事件";
    try {
        printf("code: %d \n",$a->PushEventData($thirdUID,$toUID,$objectName,$content));
    } catch (ResponseException $e) {
        echo $e;
    }
}

test();