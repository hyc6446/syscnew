<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:78:"D:\code\nft\public/../application/admin\view\shopro\dispatch\autosend\add.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <link rel="stylesheet" href="/assets/addons/shopro/libs/element/element.css">
<link rel="stylesheet" href="/assets/addons/shopro/libs/common.css">
<style>
    #dispatchDetail {
        background: #fff;
        border-radius: 10px 10px 0px 0px;
        color: #444;
        font-weight: 500;
    }

    .demo-dispatchForm {
        height: calc(100vh - 110px);
        overflow-y: auto;
        padding-right: 25px;
    }

    .demo-dispatchForm::-webkit-scrollbar {
        width: 6px;
    }

    .demo-dispatchForm::-webkit-scrollbar-thumb {
        width: 6px;
        background: #e6e6e6;
        height: 20px;
        border-radius: 3px;
    }
    .pagination,
    .el-pagination {
        margin: 0;
    }

    .el-pagination__sizes {
        display: none !important;
    }


    .params-detail-table {
        border: 1px solid #E6E6E6;
        border-bottom: none;
        margin-bottom: 20px;
    }

    .params-header-item {
        height: 40px;
    }

    .params-header-item,
    .params-detail-item {
        border-bottom: 1px solid #E6E6E6;
    }

    .params-detail-item>div,
    .params-header-item>div {
        padding: 5px 10px;
    }

    .params-detail-name {
        width: 120px;
    }

    .params-detail-msg {
        width: 378px;
    }

    .params-detail-del,
    .params-detail-move {
        width: 50px;
        display: flex;
        justify-content: center;
    }

    .params-detail-del-icon {
        color: #ff5959;
    }

    .add-params {
        width: 98px;
        height: 32px;
        background: #7536D0;
        border-radius: 4px;
        justify-content: center;
        color: #fff;
        cursor: pointer;
    }

    .add-params i {
        margin-right: 6px;
        font-size: 14px;
    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/Sortable.min.js"></script>
<script src="/assets/addons/shopro/libs/vuedraggable.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="dispatchDetail" v-cloak>
    <el-form :model="dispatchForm" ref="dispatchForm" :rules="rules" label-width="100px" class="demo-dispatchForm">
        <el-form-item label="模板名称：" prop="name">
            <el-input v-model="dispatchForm.name" placeholder="请输入模板名称"></el-input>
        </el-form-item>
        <el-form-item label="发货类型：" prop="type">
            <el-radio-group v-model="dispatchForm.type" @change="radioChange">
                <el-radio label="text">固定内容</el-radio>
                <!-- <el-radio label="card">卡密</el-radio> -->
                <el-radio label="params">自定义内容</el-radio>
            </el-radio-group>
        </el-form-item>
        <el-form-item label="发货内容：" prop="timecontent" v-if="dispatchForm.type=='text'">
            <el-input v-model="dispatchForm.timecontent" placeholder="请输入自动发货内容"></el-input>
        </el-form-item>
        <el-form-item label="发货内容：" prop="timecontent" v-if="dispatchForm.type=='params'">
            <div>
                <div class="params-detail-table">
                    <div class="display-flex params-header-item">
                        <div class="params-detail-name">
                            参数名称
                        </div>
                        <div class="params-detail-msg">
                            内容
                        </div>
                        <div class="params-detail-del">
                            删除
                        </div>
                        <div class="params-detail-move">
                            移动
                        </div>
                    </div>
                    <draggable :list="dispatchForm.timecontent" v-bind="$attrs" :options="{animation:500}">
                        <div class="display-flex params-detail-item" v-for="(it,index) in dispatchForm.timecontent">
                            <div class="params-detail-name">
                                <el-input type="input" v-model="it.title" style="width:90px">
                                </el-input>
                            </div>
                            <div class="params-detail-msg">
                                <el-input type="input" v-model="it.content" style="width:348px">
                                </el-input>
                            </div>
                            <div class="params-detail-del">
                                <div class="params-detail-del-icon" @click="delParams(index)">
                                    删除
                                </div>
                            </div>
                            <div class="params-detail-move">
                                <img src="/assets/addons/shopro/img/goods/move.png">
                            </div>
                        </div>
                    </draggable>
                </div>
                <div class="add-params display-flex" @click="addParams">
                    <i class="el-icon-plus"></i>
                    <span>添加参数</span>
                </div>
            </div>
        </el-form-item>
    </el-form>
    <div class="dialog-footer display-flex">
        <div @click="dispatchSub" class="dialog-cancel-btn display-flex-c cursor-pointer">取消</div>
        <div @click="dispatchSub('yes','dispatchForm')" class="dialog-define-btn display-flex-c cursor-pointer">确定</div>
    </div>
</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
