<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:73:"D:\code\nft\public/../application/admin\view\shopro\user\group\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
    #groupIndex {
        background: #fff;
        padding: 0 20px 30px;
    }
    .status-normal{
        color: #6ACAA5;
    }
    .status-hidden{
        color: #C4C4C4;
    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="groupIndex" v-cloak>
    <div class="custom-header display-flex">
        <div class="custom-header-title">
            用户分组
        </div>
    </div>
    <div class="custom-button-container">
        <div class="display-flex">
            <div class="custom-refresh" @click="getData">
                <i class="el-icon-refresh"></i>
            </div>
            <el-button type="primary" size="small" @click="operation('create',null)"><i class="el-icon-plus"></i> 新建分组</el-button>
        </div>
    </div>
    <div class="custom-table">
        <div class="custom-table-border">
            <el-table ref="multipleTable" :data="data" tooltip-effect="dark" style="width: 100%" border
                :row-class-name="tableRowClassName" :cell-class-name="tableCellClassName"
                :header-cell-class-name="tableCellClassName" @row-dblclick="operation">
                <el-table-column label="ID" prop="id" width="80">
                </el-table-column>
                <el-table-column label="组名" min-width="240">
                    <template slot-scope="scope">
                        <div class="ellipsis-item">
                            {{scope.row.name}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="更新时间" width="180">
                    <template slot-scope="scope">
                        <div>
                            {{moment(scope.row.updatetime*1000).format("YYYY-MM-DD HH:mm:ss")}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="状态" width="80">
                    <template slot-scope="scope">
                        <div class="display-flex">
                            <span v-if="scope.row.status=='normal'" class="display-flex">
                                <span class="shopro-status-dot shopro-status-normal-dot"></span>
                                <span class="shopro-status-normal">{{scope.row.status_text}}</span>
                            </span>
                            <span v-else class="display-flex">
                                <span class="shopro-status-dot shopro-status-default-dot"></span>
                                <span class="shopro-status-default">{{scope.row.status_text}}</span>
                            </span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" min-width="130">
                    <template slot-scope="scope">
                        <div>
                            <?php if($auth->check('shopro/user/group/edit')): ?>
                            <span class="table-edit-text" @click="operation('edit',scope.row.id)">编辑</span>
                            <?php endif; if($auth->check('shopro/user/group/del')): ?>
                            <span class="table-delete-text" @click="operation('del',scope.row.id)">删除</span>
                            <?php endif; ?>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="pagination-container">
            <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange"
                :current-page="currentPage" :page-sizes="[10, 20, 30, 40]" :page-size="limit"
                layout="total, sizes, prev, pager, next, jumper" :total="totalPage">
            </el-pagination>
        </div>
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
