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
        $this->binPath = XASSET_PATH . 'tools/xasset-cli/xasset-cli';
        //windows
//        $this->binPath = XASSET_PATH . 'tools/xasset-cli/xasset-cli.exe';
        $crypto = new \EcdsaCrypto($this->binPath);
        $config = new \XassetConfig($crypto);

        $appId = 110005;
        $ak = '053cf951b5764eda2093a6ced598e13c';
        $sk = '2b25940aee83fba4c5a87fef7f8f06ab';
        $config->setCredentials($appId, $ak, $sk);

        $config->endPoint = "http://120.48.16.137:8360";
        $this->xHandle = new \XassetClient($config);
    }


    public  function createAccount()
    {
        //生成新的account
        $ac = new \Account($this->binPath);
        return $ac->createAccount();
    }

    public function __call($name,$params)
    {
        return $this->xHandle->$name(...$params);
    }

}