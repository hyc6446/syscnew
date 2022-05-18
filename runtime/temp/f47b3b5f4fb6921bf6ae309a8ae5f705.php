<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:70:"D:\code\nft\public/../application/admin\view\shopro\coupons\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
    #indexPage {
        color: #444;
        background: #fff;
        padding: 0 20px 30px;
    }

    .create-btn {
        width: 110px;
        margin-right: 20px;
    }

    .delete-btn {
        width: 88px;
        height: 32px;
        border: 1px solid #E6E6E6;
        border-radius: 4px;
        color: #999;
        font-size: 13px;
        justify-content: center;
    }

    .delete-btn-active {
        color: #FF5959;
        border-color: #FF5959;
    }

    .el-input__inner,
    .el-input__icon {
        height: 32px;
        line-height: 32px;

    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="indexPage" v-cloak>
    <div class="custom-header display-flex">
        <div class="choose-status">
            优惠券
        </div>
        <div class="custom-search">
            <el-input placeholder="请输入标题" suffix-icon="el-icon-search" v-model="searchKey">
            </el-input>
        </div>
    </div>
    <div class="custom-table">
        <div class="custom-table-header display-flex-b">
            <div class="display-flex">
                <div class="custom-refresh display-flex-c" @click="getData">
                    <i class="el-icon-refresh"></i>
                </div>
                <?php if($auth->check('shopro/coupons/add')): ?>
                <div class="create-btn display-flex-c" @click="operation('create')">
                    <i class="el-icon-plus"></i>
                    <span>新建优惠券</span>
                </div>
                <?php endif; if($auth->check('shopro/coupons/del')): ?>
                <div class="delete-btn cursor-pointer display-flex"
                    :class="multipleSelection.length>0?'delete-btn-active':'' " @click="operation('del')">
                    删除
                </div>
                <?php endif; ?>
            </div>
            <?php if($auth->check('shopro/coupons/recyclebin')): ?>
            <div class="recycle-btn display-flex-c" @click="operation('recyclebin')">
                <i class="fa fa-recycle"></i>
                回收站
            </div>
            <?php endif; ?>
        </div>
        <div>
            <el-table ref="multipleTable" :data="data" tooltip-effect="dark" style="width: 100%" border
                @selection-change="handleSelectionChange" :row-class-name="tableRowClassName"
                :cell-class-name="tableCellClassName" :header-cell-class-name="tableCellClassName"
                @row-dblclick="operation">
                <el-table-column type="selection" min-width="40">
                </el-table-column>
                <el-table-column label="ID" min-width="60" prop="id">
                </el-table-column>
                <el-table-column label="优惠券名称" min-width="150">
                    <template slot-scope="scope">
                        <div class="ellipsis-item">
                            {{scope.row.name}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="优惠券描述" min-width="160">
                    <template slot-scope="scope">
                        <div class="ellipsis-item">
                            {{scope.row.description}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="优惠内容" min-width="180">
                    <template slot-scope="scope">
                        <div>
                            满{{scope.row.enough}}元,减{{scope.row.amount}}元
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="优惠券类型" min-width="100">
                    <template slot-scope="scope">
                        <div>
                            {{scope.row.type_text}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="getnum" label="已领取" min-width="80">
                </el-table-column>
                <el-table-column prop="usenum" label="已使用" min-width="80">
                </el-table-column>
                <el-table-column prop="stock" label="剩余" min-width="70">
                </el-table-column>
                <el-table-column prop="gettime" label="有效期" min-width="300">
                </el-table-column>
                <el-table-column fixed="right" label="操作" min-width="110">
                    <template slot-scope="scope">
                        <?php if($auth->check('shopro/coupons/edit')): ?>
                        <span class="table-edit-text" @click="operation('edit',scope.row.id)">编辑
                        </span>
                        <?php endif; if($auth->check('shopro/coupons/del')): ?>
                        <span class="table-delete-text" @click="operation('del',scope.row.id)">删除</span>
                        <?php endif; ?>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="pagination-container display-flex">
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
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
