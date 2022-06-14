<?php

namespace addons\xasset\library;
require_once(dirname(__FILE__) . '/../index.php');

class Service
{

    private $xHandle;
    private $binPath;

    public function __construct()
    {
        //linux mac
        //$binPath = XASSET_PATH . 'tools/xasset-cli/xasset-cli';
        //windows
        $this->binPath = XASSET_PATH . 'tools/xasset-cli/xasset-cli.exe';
        $crypto = new \EcdsaCrypto($this->binPath);
        $config = new \XassetConfig($crypto);

        $appId = 110005;
        $ak = '053cf951b5764eda2093a6ced598e13c';
        $sk = '2b25940aee83fba4c5a87fef7f8f06ab';
//        $appId = 110005;
//        $ak = 'f6efedb758a7a59be06e07d40691011c';
//        $sk = 'e931285b11ec4944547bb90e024e9a89';
        $config->setCredentials($appId, $ak, $sk);

        $config->endPoint = "http://120.48.16.137:8360";
        $this->xHandle = new \XassetClient($config);
//
//        //生成新的account
//        $ac = new \Account(self::$binPath);
//        $account = $ac->createAccount();
//
//        //使用现有account
//        /*$addr = '';
//        $pubKey = '';
//        $privtKey = '';
//        $account = array(
//            'address' => $addr,
//            'public_key' => $pubKey,
//            'private_key' => $privtKey,
//        );*/
//        //文件相关接口
//        $stoken =$this->xHandle->getStoken($account);
//        var_dump($stoken);

    }


    public  function createAccount()
    {
        //生成新的account
        $ac = new \Account($this->binPath);
        return $ac->createAccount();
    }

}