<?php

namespace addons\shopro\controller;

use addons\shopro\model\User;
use think\Db;
use think\Log;
class Identity extends Base
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    public function identityAuth()
    {
        $apiurl="http://v.juhe.cn/telecom/query";//请求地址
        $key = "74d0d0ee1cc84fca34ae3c560bdd206f";//32位的KEY
        $realname = $this->request->post('realname');;//真实姓名
        $idcard=$this->request->post('idcard');;//身份证号码
        $mobile=$this->request->post('mobile');;//手机号码
        $showid=1;//传入返回单号
        $params=compact('key','realname','idcard','mobile','showid');//组合请求参数

        $ipSendTotal = Db::name('identity')->where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal > 3) {
            $this->error(__('验证频繁'));
        }
        Db::name('identity')->insert(['mobile' =>$mobile,'ip' => $this->request->ip(),'createtime'=>time()]);
        $content=$this->juhecurl($apiurl,$params);//获取接口返回内容json字符串
        $result = json_decode($content,true);//解析成数组
        
        if($result){
            if($result['error_code']=='0'){
                $res = $result['result'];
                if($res['res']==1){
                    //实名认证成功，更新数据库
                    $user =$this->auth->getUser();
                    $user->identity_card = $result['result']['idcard'];
                    $user->realname = $result['result']['realname'];
                    $user->is_auth = 1;
                    $user->save();
                    $this->success('实名认证成功',$user);
                }else{
                    $this->error($res['resmsg'],[],$res['res']);
                }
            }else{
                Log::info("实名认证结果",json_encode($result));
                $this->error($result['reason'],[],$result['error_code']);
            }
        }else{
            $this->error('认证失败');
        }
    }

    private function juhecurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 3);
        curl_setopt( $ch, CURLOPT_TIMEOUT , 8);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if ($params) {
            if (is_array($params)) {
                $paramsString = http_build_query($params);
            } else {
                $paramsString = $params;
            }
        } else {
            $paramsString = "";
        }
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $paramsString);
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($paramsString ){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$paramsString);
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }









}