<?php

namespace LinkV\IM;

use LinkV\IM\Util\Util;
use LinkV\IM\Socket\SocketInterface;
use LinkV\IM\Exception\ResponseException;
use LinkV\IM\Model\BindResponse;


/**
 * Class IM
 *
 * @package LinkV\IM
 */
class IM
{
    /**
     * @var string The app_key.
     */
    protected $app_key;
    /**
     * @var string The app_secret.
     */
    protected $app_secret;
    /**
     * @var string The im_app_id.
     */
    protected $im_app_id;
    /**
     * @var string The im_app_key.
     */
    protected $im_app_key;
    /**
     * @var string The im_app_secret.
     */
    protected $im_app_secret;
    /**
     * @var string The im_host.
     */
    protected $im_host;

    /**
     * @var SocketInterface The httpclient.
     */
    protected $http;

    /**
     * @var string The uri.
     */
    protected $uri = 'https://thr.linkv.sg';

    /**
     * Instantiates a new Shop super-class object.
     *
     * @param string $secret
     * @param SocketInterface $http
     *
     */
    public function __construct($secret, $http)
    {
        $this->http = $http;
        $config = json_decode(base64_decode($secret), true);

        $this->app_key = $config['app_key'];
        $this->app_secret = $config['app_secret'];
        $this->im_app_id = $config['im_app_id'];
        $this->im_app_key = $config['im_app_key'];
        $this->im_app_secret = $config['im_app_secret'];
        $this->im_host = $config['im_host'];

    }

    /**
     * GetTokenByThirdUID
     *
     * @param string $user_id
     * @param string $aid
     * @param string $name
     * @param string $portrait_uri
     * @param string $email
     * @param string $country_code
     * @param string $birthday
     * @param string $sex
     *
     * @return BindResponse
     *
     * @throws ResponseException
     *
     */
    public function GetTokenByThirdUID($user_id, $aid, $name = '', $sex = '', $portrait_uri = '', $email = '', $country_code = '', $birthday = '')
    {
        $nonce = Util::genNonce();

        $params = array();
        $params['app_id'] = $this->app_key;
        $params['nonce_str'] = $nonce;
        $params['userId'] = $user_id;
        $params['aid'] = $aid;
        if (!empty($name)) {
            $params['name'] = $name;
        }
        if (!empty($sex)) {
            $params['sex'] = $sex;
        }
        if (!empty($portrait_uri)) {
            $params['portraitUri'] = $portrait_uri;
        }
        if (!empty($email)) {
            $params['email'] = $email;
        }
        if (!empty($country_code)) {
            $params['country_code'] = $country_code;
        }
        if (!empty($birthday)) {
            $params['birthday'] = $birthday;
        }

        $params['sign'] = Util::genSign($params, $this->app_secret);

        $header = array();
        $header[] = 'User-Agent: ' . 'PHP Composer SDK v0.0.1';

        $uri = $this->uri . '/open/v0/thGetToken';
        $resp = $this->http->post($uri, $params, $header);
        $status_code = isset($resp['status_code']) ? $resp['status_code'] : -1;
        $body = isset($resp['body']) ? $resp['body'] : '';

        if ($status_code != 200) {
            throw new ResponseException('http status code not 200');
        }
        $jsonData = json_decode($body, true);
        if ($jsonData == null) {
            throw new ResponseException("response decode error body:{$body}");
        }
        $status = isset($jsonData['status']) ? $jsonData['status'] : -1;
        $msg = isset($jsonData['msg']) ? $jsonData['msg'] : '';
        $data = isset($jsonData['data']) ? $jsonData['data'] : [];

        if ($status != 200) {
            throw new ResponseException("api result status not 200 message:{$msg}");
        }
        return new BindResponse($data);
    }

    /**
     * PushConverseData
     *
     * @param string $from_uid
     * @param string $to_uid
     * @param string $object_name
     * @param string $content
     * @param string $push_content
     * @param string $push_data
     * @param string $device_id
     * @param string $to_app_id
     * @param string $to_user_ext_sys_user_id
     * @param string $is_check_sensitive_words
     *
     * @return bool
     *
     * @throws ResponseException
     *
     */
    public function PushConverseData($from_uid, $to_uid, $object_name, $content, $push_content = '', $push_data = '', $device_id = '', $to_app_id = '', $to_user_ext_sys_user_id = '', $is_check_sensitive_words = '')
    {
        $nonce = Util::genNonce();
        $timestamp = strval(Util::GetTimestamp());
        $arr = [$nonce, $timestamp, $this->im_app_secret];
        asort($arr);
        $cm_im_token = strtolower(md5(join("", $arr)));
        $sign = strtoupper(sha1($this->im_app_id . '|' . $this->im_app_key . '|' . $timestamp . '|' . $nonce));

        $header = array();
        $header[] = 'User-Agent: ' . 'PHP Composer SDK v0.0.1';

        $header[] = 'nonce: ' . $nonce;
        $header[] = 'timestamp: ' . $timestamp;
        $header[] = 'cmimToken: ' . $cm_im_token;
        $header[] = 'sign: ' . $sign;
        $header[] = 'appkey: ' . $this->im_app_key;
        $header[] = 'appId: ' . $this->im_app_id;
        $header[] = 'appUid: ' . $from_uid;

        $params = array();
        $params['fromUserId'] = $from_uid;
        $params['toUserId'] = $to_uid;
        $params['objectName'] = $object_name;
        $params['content'] = $content;
        $params['appId'] = $this->im_app_id;

        if (!empty($push_content)) {
            $params['pushContent'] = $push_content;
        }

        if (!empty($push_data)) {
            $params['pushData'] = $push_data;
        }

        if (!empty($device_id)) {
            $params['deviceId'] = $device_id;
        }

        if (!empty($to_app_id)) {
            $params['toUserAppid'] = $to_app_id;
        }

        if (!empty($to_user_ext_sys_user_id)) {
            $params['toUserExtSysUserId'] = $to_user_ext_sys_user_id;
        }

        if (!empty($is_check_sensitive_words)) {
            $params['isCheckSensitiveWords'] = $is_check_sensitive_words;
        }

        $uri = $this->im_host . '/api/rest/message/converse/pushConverseData';

        $resp = $this->http->post($uri, $params, $header);

        $status_code = isset($resp['status_code']) ? $resp['status_code'] : -1;
        $body = isset($resp['body']) ? $resp['body'] : '';

        if ($status_code != 200) {
            throw new ResponseException('http status code not 200');
        }
        $jsonData = json_decode($body, true);
        if ($jsonData == null) {
            throw new ResponseException("response decode error body:{$body}");
        }
        $code = isset($jsonData['code']) ? $jsonData['code'] : -1;

        if ($code != 200) {
            throw new ResponseException("api result status not 200 {$body}");
        }
        return true;
    }

    /**
     * PushEventData
     *
     * @param string $from_uid
     * @param string $to_uid
     * @param string $object_name
     * @param string $content
     * @param string $push_data
     * @param string $to_app_id
     * @param string $to_user_ext_sys_user_id
     * @param string $is_check_sensitive_words
     *
     * @return bool
     *
     * @throws ResponseException
     *
     */
    public function PushEventData($from_uid, $to_uid, $object_name, $content, $push_data = '', $to_app_id = '', $to_user_ext_sys_user_id = '', $is_check_sensitive_words = '')
    {
        $nonce = Util::genNonce();
        $timestamp = strval(Util::GetTimestamp());
        $arr = [$nonce, $timestamp, $this->im_app_secret];
        asort($arr);
        $cm_im_token = strtolower(md5(join("", $arr)));
        $sign = strtoupper(sha1($this->im_app_id . '|' . $this->im_app_key . '|' . $timestamp . '|' . $nonce));

        $header = array();
        $header[] = 'User-Agent: PHP Composer SDK v0.0.1';

        $header[] = 'nonce: ' . $nonce;
        $header[] = 'timestamp: ' . $timestamp;
        $header[] = 'cmimToken: ' . $cm_im_token;
        $header[] = 'sign: ' . $sign;
        $header[] = 'appkey: ' . $this->im_app_key;
        $header[] = 'appId: ' . $this->im_app_id;
        $header[] = 'appUid: ' . $from_uid;

        $params = array();
        $params['fromUserId'] = $from_uid;
        $params['toUserId'] = $to_uid;
        $params['objectName'] = $object_name;
        $params['content'] = $content;
        $params['appId'] = $this->im_app_id;

        if (!empty($push_data)) {
            $params['pushData'] = $push_data;
        }

        if (!empty($to_app_id)) {
            $params['toUserAppid'] = $to_app_id;
        }

        if (!empty($to_user_ext_sys_user_id)) {
            $params['toUserExtSysUserId'] = $to_user_ext_sys_user_id;
        }

        if (!empty($is_check_sensitive_words)) {
            $params['isCheckSensitiveWords'] = $is_check_sensitive_words;
        }

        $uri = $this->im_host . '/api/rest/sendEventMsg';

        $resp = $this->http->post($uri, $params, $header);

        $status_code = isset($resp['status_code']) ? $resp['status_code'] : -1;
        $body = isset($resp['body']) ? $resp['body'] : '';

        if ($status_code != 200) {
            throw new ResponseException('http status code not 200');
        }
        $jsonData = json_decode($body, true);
        if ($jsonData == null) {
            throw new ResponseException("response decode error body:{$body}");
        }
        $code = isset($jsonData['code']) ? $jsonData['code'] : -1;

        if ($code != 200) {
            throw new ResponseException("api result status not 200 {$body}");
        }
        return true;
    }
}
