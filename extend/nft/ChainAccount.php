<?php

namespace nft;


use fast\Random;
use think\Log;

class ChainAccount extends ApiClient
{

    // 创建链账户
    public function CreateChainAccount($name)
    {
        $body = [
            "name" => $name,
            "operation_id" =>Random::uuid(),
        ];

        $res = $this->request("/v1beta1/account", [], $body, "POST");
        return $this->result($res);
    }

    // 查询链账户
    public function QueryChainAccount($operation_id){
        $query = [
            "operation_id" => $operation_id,
        ];
        $res = $this->request("/v1beta1/accounts", $query, [], "GET");
        Log::info('查询链账户：：：：：：'.json_encode($res));
        return $res;
    }
}