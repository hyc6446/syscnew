<?php

namespace nft;


use addons\shopro\exception\Exception;

class ApiClient
{
//    private $apiKey = "g2d2K0r7O0I6Z1s0z3z5P5J8K3w8u7e";
//    private $apiSecret = "o242x0e7Z096O1B0E3t5A5j823U8c7S";
    private $apiKey;
    private $apiSecret;
//    private $domain = "https://stage.apis.avata.bianjie.ai";//test
    private $domain;
    const EPOCH = 1641862815726;    //开始时间,固定一个小于当前时间的毫秒数
    const max12bit = 4095;
    const max41bit = 1099511627775;

    public function __construct()
    {
        $this->apiKey = config('nft.apiKey');
        $this->apiSecret = config('nft.apiSecret');
        $this->domain = config('nft.domain');
    }

    public function request($path, $query = [], $body = [], $method = 'GET')
    {
        $method = strtoupper($method);
        $apiGateway = rtrim($this->domain, '/') . '/' . ltrim($path,
                '/') . ($query ? '?' . http_build_query($query) : '');
        $timestamp = $this->getMillisecond();
        $params = ["path_url" => $path];
        if ($query) {
            // 组装 query
            foreach ($query as $k => $v) {
                $params["query_{$k}"] = $v;
            }
        }
        if ($body) {
            // 组装 post body
            foreach ($body as $k => $v) {
                $params["body_{$k}"] = $v;
            }
        }
        // 数组递归排序
        $this->SortAll($params);
        $hexHash = hash("sha256", "{$timestamp}" . $this->apiSecret);
        if (count($params) > 0) {
            // 序列化且不编码
            $s = json_encode($params,JSON_UNESCAPED_UNICODE);
            $hexHash = hash("sha256", stripcslashes($s . "{$timestamp}" . $this->apiSecret));
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiGateway);
        $header = [
            "Content-Type:application/json",
            "X-Api-Key:{$this->apiKey}",
            "X-Signature:{$hexHash}",
            "X-Timestamp:{$timestamp}",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $jsonStr = $body ? json_encode($body) : ''; //转换为json格式
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);

        return $response;

    }


    public function SortAll(&$params){
        if (is_array($params)) {
            ksort($params);
        }
        foreach ($params as &$v){
            if (is_array($v)) {
                $this->SortAll($v);
            }
        }
    }

    /** get timestamp
     *
     * @return float
     */
    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)));
    }


    /**
     * 生成唯一数字串
     * @param int|null $machineId
     * @return float|int
     */
    protected function CreateOnlyId(int $machineId=null){
        // 时间戳 42字节
        $time = floor(microtime(true) * 1000);
        // 当前时间 与 开始时间 差值
        $time -= self::EPOCH;
        // 二进制的 毫秒级时间戳
        $base = decbin(self::max41bit + $time);
        // 机器id  10 字节
        if($machineId){
            $machineId = str_pad(decbin($machineId), 10, "0", STR_PAD_LEFT);
        }
        // 序列数 12字节
        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);
        // 拼接
        $base = $base.$machineId.$random;
        // 转化为 十进制 返回
        return bindec($base);
    }



    protected function result($result)
    {
        if (isset($result['error'])){
            new Exception('code:'.$result['error']['code'].'msg:'.$result['error']['message']);
        }
        if (isset($result['data'])){
            return ['code'=>1,'data'=>$result['data'],'msg'=>''];
        }
        new Exception('请求失败');
    }
}
