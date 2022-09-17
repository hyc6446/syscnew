<?php

namespace addons\shopro\controller;


use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use think\Exception;
use think\Log;

class UserBank extends Base
{

    protected $noNeedLogin = ['AuthCallback'];
    protected $noNeedRight = ['*'];


    // 获取提现账户信息
    public function info()
    {
        $type = $this->request->post('type');
        $code = $this->request->post('code');
        try {
            $bankInfo = \addons\shopro\model\UserBank::info($type,$code);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('提现账户', $bankInfo);
    }


    public function edit()
    {
        $params = $this->request->post();

        $platform = request()->header('platform');

        $code = isset($params['auth_code'])?$params['auth_code']:'';
        //  Log::write('auth-code:'. $code);die;


        if ($params['type'] === 'alipay') {
            $platformConfig = json_decode(\addons\shopro\model\Config::get(['name' => $params['type']])->value, true);
            $app_id = $platformConfig['app_id'];
            // 支付宝授权
            if(isset($params['state']) && $params['state']=='init'){
                if(empty($code)){
                    $this->error('参数不正确，auth_code不能为空');
                }

                // 获取授权信息
                try{
                    $userinfo = self::getAliPayInfo($code,$platformConfig);
                }catch (Exception $e){
                    $this->error($e->getMessage(),$e->getCode());
                }

                $userInfo['bank_name'] = '支付宝用户';

            }else{
                $user = $this->auth->id;
                $return_url =$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] ."/addons/shopro/user_bank/AuthCallback";

                $url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id='.$app_id.'&scope=auth_user&redirect_uri='.$return_url.'&state=userId'.$user;

//                $url = "alipays://platformapi/startapp?appId='{$app_id}'&url=".urlencode($url);

                $this->success('获取授权地址成功',['data'=>$url]);
            }

        }else{
            $platformConfig = json_decode(\addons\shopro\model\Config::get(['name' => $platform])->value, true);
            if(empty($code)){
                $this->error('参数不正确，auth_code不能为空');
            }
            try{
                $userInfo = self::getUserInfo($code,$platformConfig);
            }catch (Exception $e){
                $this->error($e->getMessage(),$e->getCode());
            }

            $userInfo['bank_name'] = '微信用户';
        }

        $this->success('编辑成功', \addons\shopro\model\UserBank::edit($userInfo,$params['type']));
    }


    public function AuthCallback()
    {

        $data = $this->request->param();

        Log::write('auth-result:'. json_encode($data));

        $platformConfig = json_decode(\addons\shopro\model\Config::get(['name' => 'alipay'])->value, true);
        $code = $data['auth_code'];

        $user_id = substr($data['state'],6);

        try{
            $userinfo = self::getAliPayInfo($code,$platformConfig);
            $userinfo['bank_name'] = '支付宝用户';

            \addons\shopro\model\UserBank::alipayEdit($user_id,$userinfo,'alipay');

            $return_url =$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] ."/h5/share/auth.html"; //回调地址

            header("Location: {$return_url}");
        }catch (Exception $e){
            $this->error($e->getMessage(),$e->getCode());
        }
    }


    public function checkBankAuth()
    {
        $type = input('type/row','');
        $user_id = $this->auth->id;
        $res = \addons\shopro\model\UserBank::checkBank($user_id,$type);
        if($res){
            $this->success('已授权');
        }

        $this->error('未授权');
    }



    /**
     * 获取支付宝的用户信息
     */
    private static function getAliPayInfo($code,$platformConfig){

        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $options->appId = $platformConfig['app_id'];

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $platformConfig['private_key'];

        $options->alipayCertPath =  ROOT_PATH . 'public' . $platformConfig['ali_public_key'];
        $options->alipayRootCertPath =  ROOT_PATH . 'public' . $platformConfig['alipay_root_cert'];
        $options->merchantCertPath =  ROOT_PATH . 'public' . $platformConfig['app_cert_public_key'];

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        // $options->alipayPublicKey = '<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->';
        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = "";

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey = "";

        Factory::setOptions($options);
        $access_token =  Factory::base()->oauth()->getToken($code)->accessToken;

        $method = 'alipay.user.info.share';

        //设置系统参数
        $textParams = array("auth_token" => $access_token);
        //设置业务参数（OpenAPI中biz_content里的参数）
        $bizParams = array();

        $data  = Factory::util()->generic()->execute($method,$textParams,$bizParams)->toMap();
        if($data['code'] != 10000){
            throw \Exception($data['sub_msg'],$data['code']);
            return  false;
        }else{
            $body = json_decode($data['http_body'],true);

            $userInfo = $body['alipay_user_info_share_response'];
            $user = [
                'nickname'=>$userInfo['nick_name'],
                'user_id'=>$userInfo['user_id'],
                'avatar'=>$userInfo['avatar'],
            ];
            return $user;
        }
    }





    /**
     * 微信 授权获取用户信息
     * @param $code
     * @param $platform
     * @return bool|mixed
     * @throws \think\exception\DbException
     */
    private static function getUserInfo($code,$platformConfig)
    {
        $appid = $platformConfig['app_id'];
        $appsecret = $platformConfig['secret'];
        
        //  $appid = "wx721aa551336a11d7";
        // $appsecret = "e08bb378435e78dafc386afc49330ba4";

        // 通过code获取access_token
        $get_token_url ="https://api.weixin.qq.com/sns/oauth2/access_token?appid=". $appid ."&secret=". $appsecret ."&code={$code}&grant_type=authorization_code";
        $token_info = \fast\Http::get($get_token_url);
    //   var_dump($token_info);die;
        $token_info = json_decode($token_info,true);
        if(isset($token_info['errcode'])){
            throw \Exception($token_info['errmsg'],$token_info['errcode']);
            return false;
        }
        // 通过access_token和openid获取用户信息
        $get_userinfo_url ='https://api.weixin.qq.com/sns/userinfo?access_token='. $token_info['access_token'].'&openid='. $token_info['openid'].'&lang=zh_CN';
        $userinfo = \fast\Http::get($get_userinfo_url);
        $userinfo = json_decode($userinfo,true);
        if(isset($userinfo['errcode'])){
            throw \Exception($userinfo['errmsg'],$userinfo['errcode']);
            return false;
        }
        return $userinfo;
    }


    /**
     * 测试H5回调
     */

    public function getH5Auth()
    {
        $app_id = '20102121012****';// app_id
        $user = $this->auth->id; //用户ID
        // 回调地址(注意:这个需要在支付宝应用里面去配置,授权回调地址,不然没法返回数据)
        $return_url =$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] ."/addons/shopro/user_bank/AuthCallback"; //回调地址

        // 拼接授权url
        $url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id='.$app_id.'&scope=auth_user&redirect_uri='.$return_url.'&state=userId'.$user;

        $this->success('编辑成功', ['data'=>$url]);
    }




}
