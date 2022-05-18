<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:80:"D:\code\nft\public/../application/admin\view\shopro\wechat\auto_reply\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
                                <meta name="referrer" content="never">
<link rel="stylesheet" href="/assets/addons/shopro/libs/element/element.css">
<link rel="stylesheet" href="/assets/addons/shopro/libs/common.css">
<style>
    #replyIndex {
        background: #fff;
        border-radius: 10px 10px 0px 0px;
        color: #444;
        font-weight: 500;
        padding-bottom: 30px;
    }

    .el-tabs__nav {
        margin-left: 20px;
    }

    .el-tabs__header {
        margin-bottom: 0;
    }

    .el-tabs__item {
        height: 50px;
        line-height: 50px;
        width: 120px;
    }

    .el-tabs__active-bar {
        width: 80px !important;
        left: 10px;
        height: 3px;
        border-radius: 1px;
    }

    .el-tabs--top .el-tabs__item.is-top:last-child {
        padding-right: 20px;
    }

    .custom-tabs {
        padding-bottom: 20px;
    }

    .custom-body {
        padding: 0 20px 30px;
    }

    .custom-button-container {
        padding-left: 20px;
    }

    .demo-detailForm {
        height: calc(100vh - 246px);
    }
    .select-item{
        background: #fff;
        box-shadow: 0px 1px 8px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        display: block;
        width: 230px;
        height: 136px;
        color: #fff;
        position: relative;
    }
    .select-item:focus,.select-item:hover{
        color: #fff;
    }
    .select-item .select-item-img{
        height: 136px;
    } 
    .select-item .el-image{
        width: 100%;
        height: 100%;
    }
    .news-title,.image-title,.video-title{
        position: absolute;
        bottom: 0px;
        left: 0px;
        width: 100%;
        line-height: 16px;
        color: #fff;
        z-index: 100;
        background: linear-gradient(360deg, rgba(0, 0, 0, 0.35) 0%, rgba(0, 0, 0, 0) 100%);
        height: 42px;
        padding: 5px 10px;
    }
    .voice-item,.text-item,.link-item{
        width: 230px;
height: 40px;
background: #fff;
        box-shadow: 0px 1px 8px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        display: flex;
        color: #444;
        align-items: center;
        justify-content: space-between;
        padding: 0 10px;
    }
    .voice-item:hover,.voice-item:focus{
        color: #444; 
    }
    .voice-title{
        line-height: 28px;flex: 1;margin-right: 10px;
    }


    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<div id="replyIndex" v-cloak>
    <div class="custom-tabs">
        <el-tabs v-model="activeName" @tab-click="tabClick">
            <el-tab-pane label="关键字回复" name="auto_reply"></el-tab-pane>
            <el-tab-pane label="关注回复" name="subscribe"></el-tab-pane>

            <el-tab-pane label="默认回复" name="default_reply"></el-tab-pane>
        </el-tabs>
    </div>
    <div class="custom-button-container" v-if="activeName=='auto_reply'">
        <div class="display-flex">
            <div class="custom-refresh" @click="getList">
                <i class="el-icon-refresh"></i>
            </div>
            <?php if($auth->check('shopro/wechat/auto_reply/add')): ?>
            <el-button type="primary" size="small" icon="el-icon-plus" @click="operation('create',null)">新建
            </el-button>
            <?php endif; ?>
        </div>
    </div>
    <div class="custom-body demo-detailForm" v-if="activeName=='subscribe' || activeName=='default_reply'">
        <el-form :model="detailForm" ref="detailForm" :rules="rules" label-width="100px" class="">
            <el-form-item label="类型：" prop="type">
                <el-radio-group v-model="detailForm.type" @change="typeChange">
                    <el-radio label="news">图文消息</el-radio>
                    <el-radio label="image">图片</el-radio>
                    <el-radio label="video">视频</el-radio>
                    <el-radio label="voice">音频</el-radio>
                    <el-radio label="text">文本</el-radio>
                    <el-radio label="link">链接</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="回复内容：" prop="content_title" v-if="detailForm.type">
                <div class="display-flex">
                    <div class="flex-1">
                        <el-select style="position: relative;" v-model="detailForm.content_title"
                            @change="selectChange">
                            <el-option v-for="item in options" :key="item.media_id" :label="item.title" :value="item.media_id">
                                <div class="display-flex">
                                    <img style="width: 30px;
                                    height: 30px;margin-right:20px" :src="Fast.api.cdnurl(item.thumb_url)"
                                        v-if="detailForm.type!='voice' && detailForm.type!='text'  && detailForm.type!='link'">
                                        <img style="width: 30px;
                                    height: 30px;margin-right:20px" :src="Fast.api.cdnurl(item.image)"
                                        v-if="detailForm.type=='link'">
                                    <div class="ellipsis-item" style="width: 60px;" v-if="detailForm.type=='text' || detailForm.type=='link'">
                                        {{ item.media_id }}</div>
                                    <div class="ellipsis-1" style="width: 100px;">{{ item.title }}</div>
                                    <div class="ellipsis-item" style="flex: 1;"  v-if="detailForm.type!='text' && detailForm.type!='link'">
                                        {{ item.media_id }}</div>
                                    <div class="ellipsis-item" style="flex: 1;" v-if="detailForm.type=='text'">
                                        {{ item.thumb_url }}</div>
                                </div>
                            </el-option>
                            <div class="text-center display-flex"
                                style="position: sticky;background: #fff;height:32px;top:0;z-index:1;justify-content: center;">
                                <el-pagination class="pagination" :page-sizes="[6]" :current-page="selectCurrentPage"
                                    :total="selectTotalPage" layout="total, prev, pager,next, jumper" pager-count="5"
                                    @size-change.stop="selectSizeChange" @current-change="selectCurrentChange" />
                                </el-pagination>
                                <div class="theme-color cursor-pointer" style="margin-left: 8px;" @click="getoptions">
                                    跳转
                                </div>
                            </div>
                        </el-select>
                    </div>
                    <div class="create-store theme-color cursor-pointer" @click="createTemplate"
                        v-if="detailForm.type=='text' || detailForm.type=='link'">创建</div>
                </div>
            </el-form-item>
            <!-- <el-form-item label="" v-if="detailForm.content_title">
                <a class="select-item" v-if="detailForm.type=='news'" :href="detailForm.content_id.url" target="_blank" rel="noopener noreferrer">
                    <el-image v-if="detailForm.content_id.thumb_url" :src="Fast.api.cdnurl(detailForm.content_id.thumb_url)">
                    </el-image>
                    <div class="news-title ellipsis-item-2">{{detailForm.content_id.title}}</div>
                </a>
                <div class="select-item" v-if="detailForm.type=='image'">
                    <div class="select-item-img">
                        <el-image v-if="detailForm.content_id.thumb_url" :src="Fast.api.cdnurl(detailForm.content_id.thumb_url)">
                        </el-image>
                    </div>
                    <div class="image-title ellipsis-item">{{detailForm.content_id.title}}</div>
                </div>
                <div class="select-item" v-if="detailForm.type=='video'">
                    <div class="select-item-img">
                        <el-image v-if="detailForm.content_id.thumb_url" :src="Fast.api.cdnurl(detailForm.content_id.thumb_url)">
                        </el-image>
                    </div>
                    <div class="video-title ellipsis-item">{{detailForm.content_id.title}}</div>
                </div>
                <a class="voice-item" v-if="detailForm.type=='voice'" :href="'detail?media_id='+detailForm.content_id.media_id">
                        <div class="voice-title ellipsis-item">
                            {{detailForm.content_id.title}}</div>
                        <div class="theme-color" style="line-height: 28px;font-size: 20px;"><i
                                class="el-icon-video-play"></i></div>
                </a>
                <div class="text-item" v-if="detailForm.type=='text'">
                    <div class="voice-title ellipsis-item">
                        {{detailForm.content_id.title}}</div>
                </div>
                <div class="link-item" v-if="detailForm.type=='link'">
                    <div class="voice-title ellipsis-item">
                        {{detailForm.content_id.title}}</div>
                </div>
            </el-form-item> -->
        </el-form>
    <div class="dialog-footer display-flex">
        <div @click="dispatchSub" class="dialog-cancel-btn display-flex-c cursor-pointer">取消</div>
        <div @click="dispatchSub('yes','detailForm')" class="dialog-define-btn display-flex-c cursor-pointer">确定
        </div>
    </div>
</div>
<div class="custom-body" v-if="activeName=='auto_reply'">
    <div class="custom-table-body">
        <el-table :data="listData" border style="width: 100%" :row-class-name="tableRowClassName"
            :cell-class-name="tableCellClassName" :header-cell-class-name="tableCellClassName"
            @row-dblclick="operation">
            <el-table-column prop="id" label="ID" width="60">
            </el-table-column>
            <el-table-column label="规则名称" min-width="300">
                <template slot-scope="scope">
                    <div class="ellipsis-item">
                        {{scope.row.name}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="关键词" min-width="200">
                <template slot-scope="scope">
                    <div class="ellipsis-item">
                        {{scope.row.rules}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="回复类型" width="100">
                <template slot-scope="scope">
                    <div class="ellipsis-item">
                        {{operation('filter',JSON.parse(scope.row.content).type)}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="操作" min-width="120">
                <template slot-scope="scope">
                    <div class="opt-container display-flex">
                        <?php if($auth->check('shopro/wechat/auto_reply/edit')): ?>
                        <div class="table-edit-text" @click="operation('edit',scope.row.id)">
                            编辑
                        </div>
                        <?php endif; if($auth->check('shopro/wechat/auto_reply/del')): ?>
                        <div class="table-delete-text" @click="operation('delete',scope.row.id)">
                            删除
                        </div>
                        <?php endif; ?>
                    </div>
                </template>
            </el-table-column>
        </el-table>
    </div>

</div>
<div class="pagination-container" v-if="activeName=='auto_reply'">
    <el-pagination @size-change="pageSizeChange" @current-change="pageCurrentChange" :current-page="currentPage"
        :page-sizes="[10, 20, 30, 40]" :page-size="limit" layout="total, sizes, prev, pager, next, jumper"
        :total="totalPage">
    </el-pagination>
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
