<?php

namespace addons\shopro\job;

use addons\shopro\model\User;
use app\common\library\Sms;
use think\Log;
use think\queue\Job;

/**
 * 队列消息通知
 */
class GoodsDing extends BaseJob
{
    /**
     * 发送通知
     */
    public function ding(Job $job, $data){
        try {
            Log::info('订阅提醒:::::::queue::::::'.json_encode($data) );
            $res = \addons\shopro\model\GoodsDing::where('id',$data['id'])->find();
            if ($res && $res['status'] == 1 && $res['ding_time']>time()){
                $user = User::get($res['user_id']);
                if ($user && $user['mobile']){
                    $config = get_addon_config('alisms');
                    if (!isset($config['template']['ding'])) {
                        return false;
                    }
                    Sms::notice($user['mobile'],'',$config['template']['ding']);
                }
            }
            // 删除 job
            $job->delete();
        } catch (\Exception $e) {
            // 队列执行失败
            \think\Log::error('queue-' . get_class() . '-GoodsDing' . '：执行失败，错误信息：' . $e->getMessage());
        }
    }
}