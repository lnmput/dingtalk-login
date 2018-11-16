<?php
namespace Yangzie\Dingtalk;

use GuzzleHttp\Client;

class DingtalkLogin
{
    /**
     * @var array|\Illuminate\Http\Request|string
     */
    public $request;

    /**
     * @var Client
     */
    public $httpClient;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    public $appId;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    public $appSecret;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    public $corpId;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    public $corpSecret;


    public function __construct()
    {
        $this->httpClient = new Client();

        $this->request = request();

        $this->appId = config('dingtalk.app_id');

        $this->appSecret = config('dingtalk.app_secret');

        $this->corpId = config('dingtalk.corp_id');

        $this->corpSecret = config('dingtalk.corp_secret');

    }

    /**
     * 登录
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        $domain = config('app.url');

        $redirectUrl = $domain.'/login/dingding/callback';

        $url = 'https://oapi.dingtalk.com/connect/qrconnect?appid='.$this->appId.'&response_type=code&scope=snsapi_login&state=STATE&redirect_uri='.$redirectUrl;

        return \Redirect::to($url);
    }


    /**
     * 回调
     *
     * @return mixed|string
     */
    public function callback()
    {
        $tmpAuthCode = $this->request->code;

        // 1. 获取 access token
        $url = 'https://oapi.dingtalk.com/sns/gettoken?appid='.$this->appId.'&appsecret='.$this->appSecret;

        $body = $this->httpClient->get($url)->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        $accessToekn = $bodyArr['access_token'];


        // 2. 获取用户授权的持久授权码

        $url = 'https://oapi.dingtalk.com/sns/get_persistent_code?access_token='.$accessToekn;

        $body = $this->httpClient->post($url,  [
            'json' => ['tmp_auth_code' => $tmpAuthCode]
        ])->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        $persistentCode = $bodyArr['persistent_code'];

        $openid = $bodyArr['openid'];


        // 3.获取用户授权的SNS_TOKEN

        $url = 'https://oapi.dingtalk.com/sns/get_sns_token?access_token='.$accessToekn;

        $body = $this->httpClient->post($url,  [
            'json' => [
                'openid' => $openid,
                'persistent_code' => $persistentCode,
            ]
        ])->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        $snsToken = $bodyArr['sns_token'];

        // 4. 获取用户授权的个人信息

        $url = 'https://oapi.dingtalk.com/sns/getuserinfo?sns_token='.$snsToken;

        $body = $this->httpClient->get($url)->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        $unionid = $bodyArr['user_info']['unionid'];

        // 5. 获取企业的 access_token
        $url = 'https://oapi.dingtalk.com/gettoken?corpid='.$this->corpId.'&corpsecret='.$this->corpSecret;
        $body = $this->httpClient->get($url)->getBody()->getContents();
        $bodyArr = json_decode($body, true);
        $accessToken = $bodyArr['access_token'];

        // 6. 根据unionid获取userid

        $url = 'https://oapi.dingtalk.com/user/getUseridByUnionid?access_token='.$accessToken.'&unionid='.$unionid;


        $body = $this->httpClient->get($url)->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        try {

            $userId = $bodyArr['userid'];

        } catch (\Exception $exception) {

            return '你不是企业用户';

        }

        // 6. 获取用户详情

        $url = 'https://oapi.dingtalk.com/user/get?access_token='.$accessToken.'&userid='.$userId;

        $body = $this->httpClient->get($url)->getBody()->getContents();

        $bodyArr = json_decode($body, true);

        return $bodyArr;
    }
}