<?php

namespace app\admin\model\shopro;

use addons\shopro\exception\Exception;
use app\admin\model\Admin;
use nft\ChainAccount;
use nft\Nfts;
use think\Model;

/**
 * 分类模型
 */
class Category extends Model
{

    // 表名,不含前缀
    protected $name = 'shopro_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'wcl_status_text'
    ];
    public function getWclStatusList()
    {
        return ['处理中', '成功', '失败', '未处理'];
    }
    public function getWclStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['wcl_status']) ? $data['wcl_status'] : '');
        $list = $this->getWclStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function children()
    {
        return $this->hasMany(\app\admin\model\shopro\Category::class, 'pid', 'id')->order('weigh desc, id asc');
    }

    public static function setData($cate, $txRes)
    {
        if (!empty($txRes['data'])) {
            $data = $txRes['data'];
            extract($data);
            $time = date('Y-m-s H:i:s', strtotime($timestamp));
            $cate->nft_id = $nft_id ?? '';
            $cate->trans_hash = $tx_hash ?? '';
            $cate->class_id = $class_id ?? '';
            $cate->message = $message ?? '';
            $cate->block_height = $block_height ?? '';
            $cate->timestamp = $time ?? '';
            $cate->tag = $tag ?? '';
            $cate->wcl_type = $type ?? '';
            $cate->wcl_status = $status ?? '';
        }
        return $cate;
    }

    public  function setWclCate($id)
    {
        $cate = self::get($id);
        //查询
        $txRes = (new Nfts())->txOperationRes($cate->operation_id);
        $cate = self::setData($cate, $txRes);
        $cate->save();
        $cate = self::get($id);
        return $cate;
    }
}
