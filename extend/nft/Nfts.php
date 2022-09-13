<?php


namespace nft;


use addons\shopro\exception\Exception;
use fast\Random;
use think\Log;

class Nfts extends ChainAccount
{

    public function getClassId($id)
    {
        $url = request()->domain();
        $arr_url = parse_url($url);
        $class_id = str_replace('.', '', $arr_url['host']) . sprintf('%05s', $id) . time();
        return $class_id;
    }

    //创建 NFT 类别
    public function nftClasses($params)
    {

        /**
         * https://apis.avata.bianjie.ai/#tag/NFT/paths/~1v1beta1~1nft~1classes/post
        name
        required
        string [ 1 .. 64 ] characters
        名称

        class_id
        string [ 3 .. 64 ] characters
        NFT 类别 ID，仅支持小写字母及数字，以字母开头

        symbol
        string [ 3 .. 64 ] characters
        标识

        description
        string <= 2048 characters
        描述

        uri
        string <uri> <= 256 characters
        链外数据链接

        uri_hash
        string <= 512 characters
        链外数据 Hash

        data
        string <= 4096 characters
        自定义链上元数据

        owner
        required
        string <= 128 characters
        NFT 类别权属者地址，支持任一文昌链合法链账户地址

        tag
        object <= 3 characters
        交易标签, 自定义 key：支持大小写英文字母和汉字和数字，长度6-12位，自定义 value：长度限制在64位字符，支持大小写字母和数字

        operation_id
        required
        string <= 64 characters ^[a-zA-Z0-9_-]+$
        操作 ID，保证幂等性，避免重复请求，保证对于同一操作发起的一次请求或者多次请求的结果是一致的；由接入方生成的、针对每个 Project ID 唯一的、不超过 64 个大小写字母、数字、-、下划线的字符串
         */
        extract($params);
        // return $owner;
        $body = [
            "name" => $name,
            "class_id" => $class_id,
            "uri" => $image,
            "uri_hash" => $hash,
            "owner" => $owner,
            "operation_id" => Random::uuid(),
        ];
        $res = $this->request("/v1beta1/nft/classes", [], $body, "POST");
        return $res;
    }

    //查询NFT类别详情
    public function getClasses($class_id)
    {
        $res = $this->request("/v1beta1/nft/classes/" . $class_id, [], [], "GET");
        return $this->result($res);
    }

    //铸造NFT
    public function publishNft($goods, $addr)
    {
        /**
         *    https://apis.avata.bianjie.ai/#tag/NFT/paths/~1v1beta1~1nft~1nfts~1{class_id}/post
        name
        required
        string [ 1 .. 64 ] characters
        NFT 名称

        uri
        string <= 256 characters
        链外数据链接

        uri_hash
        string <= 512 characters
        链外数据 Hash

        data
        string <= 4096 characters
        自定义链上元数据

        recipient
        string <= 128 characters
        NFT 接收者地址，支持任一文昌链合法链账户地址，默认为 NFT 类别的权属者地址

        tag
        object <= 3 characters
        交易标签, 自定义 key：支持大小写英文字母和汉字和数字，长度6-12位，自定义 value：长度限制在64位字符，支持大小写字母和数字

        operation_id
        required
        string <= 64 characters ^[a-zA-Z0-9_-]+$
        操作 ID，保证幂等性，避免重复请求，保证对于同一操作发起的一次请求或者多次请求的结果是一致的；由接入方生成的、针对每个 Project ID 唯一的、不超过 64 个大小写字母、数字、-、下划线的字符串
         */
        if (!$goods['asset_id']) {
            return false;
        }
        $data = 'id' . $goods['id'];
        if (!empty($goods['title'])) $data .= 'title' . $goods['title'];
        if (!empty($goods['category_ids'])) $data .= 'category_ids' . $goods['category_ids'];
        if (!empty($goods['image'])) $data .= 'image' . $goods['image'];
        if (!empty($goods['price'])) $data .= 'price' . $goods['price'];
        if (!empty($goods['brand_ids'])) $data .= 'brand_ids' . $goods['brand_ids'];
        if (!empty($goods['sales_time'])) $data .= 'sales_time' . $goods['sales_time'];
        if (!empty($goods['is_syn'])) $data .= 'is_syn' . $goods['is_syn'];
        if (!empty($goods['can_sales'])) $data .= 'can_sales' . $goods['can_sales'];
        if (!empty($goods['syn_end_time'])) $data .= 'syn_end_time' . $goods['syn_end_time'];
        if (!empty($goods['children'])) $data .= 'children' . $goods['children'];
        if (!empty($goods['issue'])) $data .= 'issue' . $goods['issue'];
        if (!empty($goods['issue_num'])) $data .= 'issue_num' . $goods['issue_num'];
        if (!empty($goods['asset_id'])) $data .= 'asset_id' . $goods['asset_id'];


        $body = [
            "name" => $goods['title'],
            "uri" => cdnurl($goods['image'], true),
            "uri_hash" => hash('sha256', $goods['image']),
            "data" => $data,
            "recipient" => $addr,
            "operation_id" => Random::uuid(),
        ];
        // return $addr;
        $res = $this->request("/v1beta1/nft/nfts/{$goods['asset_id']}", [], $body, "POST");
        return $res;
        return $this->result($res);
    }

    //批量铸造NFT
    public function publishBatchNft($goods, $addr, $amount)
    {
        /**
         *    https://apis.avata.bianjie.ai/v1beta1/nft/batch/nfts/{class_id}
         */
        if (!$goods['asset_id']) {
            return false;
        }
        $data = 'id' . $goods['id'];
        if (!empty($goods['title'])) $data .= 'title' . $goods['title'];
        if (!empty($goods['category_ids'])) $data .= 'category_ids' . $goods['category_ids'];
        if (!empty($goods['image'])) $data .= 'image' . $goods['image'];
        if (!empty($goods['price'])) $data .= 'price' . $goods['price'];
        if (!empty($goods['brand_ids'])) $data .= 'brand_ids' . $goods['brand_ids'];
        if (!empty($goods['sales_time'])) $data .= 'sales_time' . $goods['sales_time'];
        if (!empty($goods['is_syn'])) $data .= 'is_syn' . $goods['is_syn'];
        if (!empty($goods['can_sales'])) $data .= 'can_sales' . $goods['can_sales'];
        if (!empty($goods['syn_end_time'])) $data .= 'syn_end_time' . $goods['syn_end_time'];
        if (!empty($goods['children'])) $data .= 'children' . $goods['children'];
        if (!empty($goods['issue'])) $data .= 'issue' . $goods['issue'];
        if (!empty($goods['issue_num'])) $data .= 'issue_num' . $goods['issue_num'];
        if (!empty($goods['asset_id'])) $data .= 'asset_id' . $goods['asset_id'];
        $recipients = [
            ['amount'=>$amount, 'recipient'=>$addr]
        ];
        $body = [
            "name" => $goods['title'],
            "uri" => cdnurl($goods['image'], true),
            "uri_hash" => hash('sha256', $goods['image']),
            "data" => $data,
            "recipients" => $recipients,
            "operation_id" => Random::uuid(),
        ];
        $res = $this->request("/v1beta1/nft/batch/nfts/{$goods['asset_id']}", [], $body, "POST");
        return $res;
        return $this->result($res);
    }


    public function transferShard($class_id, $owner, $nft_id, $addr)
    {
        /**
         *
        class_id
        required
        string
        NFT 类别 ID

        owner
        required
        string
        NFT 持有者地址

        nft_id
        required
        string
        NFT ID
        recipient
        required
        string <= 128 characters
        NFT 接收者地址

        operation_id
        required
        string <= 64 characters ^[a-zA-Z0-9_-]+$
        操作 ID，保证幂等性，避免重复请求，保证对于同一操作发起的一次请求或者多次请求的结果是一致的；由接入方生成的、针对每个 Project ID 唯一的、不超过 64 个大小写字母、数字、-、下划线的字符串

        tag
        object <= 3 characters
        交易标签, 自定义 key：支持大小写英文字母和汉字和数字，长度6-12位，自定义 value：长度限制在64位字符，支持大小写字母和数字
         */


        $body = [
            "recipient" => $addr,
            "operation_id" => Random::uuid(),
        ];
        $res = $this->request("/v1beta1/nft/nft-transfers/{$class_id}/{$owner}/{$nft_id}", [], $body, "POST");
        return $this->result($res);
    }


    public function txOperationRes($operation_id)
    {

        /**
         * https://apis.avata.bianjie.ai/#tag/%E4%BA%A4%E6%98%93%E7%BB%93%E6%9E%9C%E6%9F%A5%E8%AF%A2%E6%8E%A5%E5%8F%A3/paths/~1v1beta1~1tx~1{operation_id}/get
         * 返回
        type
        required
        string <= 16 characters
        Enum: "issue_class" "mint_nft" "edit_nft" "burn_nft" "transfer_class" "transfer_nft" "mint_nft_batch" "edit_nft_batch" "burn_nft_batch" "transfer_nft_batch"
        用户操作类型

        tx_hash
        required
        string <= 128 characters
        交易哈希

        status
        required
        integer
        Enum: 0 1 2 3
        交易状态, 0 处理中; 1 成功; 2 失败; 3 未处理

        class_id
        string [ 3 .. 128 ] characters
        类别 ID

        nft_id
        string
        NFT ID

        message
        string <= 512 characters
        错误描述

        block_height
        number
        交易上链的区块高度

        timestamp
        string
        交易上链时间（UTC 时间）

        tag
        object <= 3 characters
        交易标签, 自定义 key：支持大小写英文字母和汉字和数字，长度6-12位，自定义 value：长度限制在64位字符，支持大小写字母和数字
         */
        $res = $this->request("/v1beta1/tx/{$operation_id}", [], [], "GET");
        Log::info('查询链交易状态：：：：：：'.json_encode($res));
        return $res;
    }
}
