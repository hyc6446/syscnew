<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"D:\code\nft\public/../application/admin\view\shopro\wechat\menu\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
    #listsIndex {
        padding: 0 20px 30px;
        background: #fff;
        border-radius: 10px 10px 0px 0px;
        color: #444;
        font-weight: 500;
    }

    .current-menu {
        /* border: 1px solid #e6e6e6; */
        padding: 10px;
        border-radius: 8px;
        /* margin-bottom: 10px; */
        margin-left: 70px;
        flex: 1;
    }

    .current-menu-title {
        margin-right: 20px;
    }

    .el-tag {
        margin-right: 10px;
    }

    .table-edit-publish,
    .table-edit-copy {
        margin-right: 20px;
        cursor: pointer;
    }

    .table-edit-publish {
        color: #18d3a9
    }

    .span-item {
        padding: 5px 10px;
        background: rgba(157, 96, 255, 0.1);
        border: 1px solid rgba(157, 96, 255, 0.5);
        box-sizing: border-box;
        border-radius: 2px;
        font-size: 12px;
        margin-right: 6px;
        line-height: 12px;
        color: #A268FF;
    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="listsIndex" v-cloak>
    <div class="custom-header">
        <div class="custom-header-title">
            菜单管理
        </div>
    </div>
    <div class="custom-button-container">
        <div class="display-flex">
            <div class="custom-refresh" @click="getList">
                <i class="el-icon-refresh"></i>
            </div>
            <?php if($auth->check('shopro/wechat/menu/add')): ?>
            <div class="create-btn" @click="operation('create',null)"><i class="el-icon-plus"></i>新建菜单</div>
            <?php endif; ?>
        </div>
        <div class="current-menu display-flex-b" v-if="currentMenu.length>0">
            <div class="current-menu-title">当前菜单</div>
            <div style="border: 1px solid #e6e6e6;height: 32px;flex: 1;border-radius: 4px;" class="display-flex">
                <div class="display-flex" style="flex: 1;padding: 5px;">
                    <div class="span-item" v-for="item in currentMenu">{{item.name}}</div>
                </div>
                <?php if($auth->check('shopro/wechat/menu/copy')): ?>
                <el-button type="primary" size="small" @click="operation('copy',0)">复制</el-button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="custom-table-body">
        <el-table :data="listData" border style="width: 100%" :row-class-name="tableRowClassName"
            :cell-class-name="tableCellClassName" :header-cell-class-name="tableCellClassName"
            @row-dblclick="operation">
            <el-table-column prop="id" label="ID" width="60">
            </el-table-column>
            <el-table-column label="菜单名称" min-width="500">
                <template slot-scope="scope">
                    <div class="display-flex">
                        <div class="ellipsis-item" style="margin-right: 40px;width: 200px;">{{scope.row.name}}</div>
                        <div class="display-flex">
                            <div class="span-item" v-for="item in JSON.parse(scope.row.content)">{{item.name}}</div>
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="操作" min-width="200">
                <template slot-scope="scope">
                    <div class="opt-container display-flex">
                        <?php if($auth->check('shopro/wechat/menu/publish')): ?>
                        <div class="table-edit-publish" @click="operation('status',scope.row.id)">
                            发布
                        </div>
                        <?php endif; if($auth->check('shopro/wechat/menu/edit')): ?>
                        <div class="table-edit-text" @click="operation('edit',scope.row.id)">
                            编辑
                        </div>
                        <?php endif; if($auth->check('shopro/wechat/menu/copy')): ?>
                        <div class="table-edit-copy" @click="operation('copy',scope.row.id)">复制</div>
                        <?php endif; if($auth->check('shopro/wechat/menu/del')): ?>
                        <div class="table-delete-text" @click="operation('delete',scope.row.id)">
                            删除
                        </div>
                        <?php endif; ?>
                    </div>
                </template>
            </el-table-column>
        </el-table>
    </div>
    <div class="pagination-container">
        <el-pagination @size-change="pageSizeChange" @current-change="pageCurrentChange" :current-page="currentPage"
            :page-sizes="[10, 20, 30, 40]" :page-size="10" layout="total, sizes, prev, pager, next, jumper"
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
