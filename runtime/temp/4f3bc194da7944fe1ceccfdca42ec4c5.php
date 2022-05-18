<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:77:"D:\code\nft\public/../application/admin\view\shopro\app\score_shop\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
    #scoreShopIndex {
        color: #444;
        background: #fff;
        border-radius: 4px;
        padding: 0px 20px 30px;
    }

    .table-img {
        width: 30px;
        height: 30px;
        margin-right: 16px;
    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="scoreShopIndex" v-cloak>
    <div class="custom-index">
        <div class="custom-header display-flex">
            <div class="custom-header-title">
                积分商城
            </div>
            <div class="custom-search">
                <el-input placeholder="请输入标题" suffix-icon="el-icon-search" v-model="searchKey" size="small">
                </el-input>
            </div>
        </div>
        <div class="custom-button-container">
            <div class="display-flex">
                <div class="custom-refresh display-flex-c" @click="getData">
                    <i class="el-icon-refresh"></i>
                </div>
                <?php if($auth->check('shopro/app/score_shop/add')): ?>
                <div class="create-btn" @click="operation('create')">
                    <i class="el-icon-plus"></i>
                    <span>添加</span>
                </div>
                <?php endif; ?>
            </div>
            <?php if($auth->check('shopro/app/score_shop/recyclebin')): ?>
            <div class="recycle-btn" @click="operation('recycle')">
                <i class="fa fa-recycle"></i>
                回收站
            </div>
            <?php endif; ?>
        </div>
        <div>
            <el-table ref="multipleTable" :data="scoreShopData" tooltip-effect="dark" style="width: 100%" border
                :row-class-name="tableRowClassName" :cell-class-name="tableCellClassName"
                :header-cell-class-name="tableCellClassName" @row-dblclick="operation">
                <el-table-column label="ID" prop="id" width="60">
                </el-table-column>
                <el-table-column label="商品信息" min-width="520">
                    <template slot-scope="scope">
                        <div class="display-flex">
                            <img class="table-img" :src="Fast.api.cdnurl(scope.row.image)">
                            <div class="ellipsis-item flex-1">{{scope.row.title}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="积分现金" min-width="140">
                    <template slot-scope="scope">
                        <div class="display-flex">
                            <span>{{scope.row.score}}积分</span><span v-if="scope.row.price>0">+{{scope.row.price}}元</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="status_text" label="上架状态" min-width="80">
                    <template slot-scope="scope">
                        <div class="display-flex">
                            <span v-if="scope.row.status=='up'" class="display-flex">
                                <span class="shopro-status-dot shopro-status-normal-dot"></span>
                                <span class="shopro-status-normal">{{scope.row.status_text}}</span>
                            </span>
                            <span v-else-if="scope.row.status=='down'" class="display-flex">
                                <span class="shopro-status-dot shopro-status-nonormal-dot"></span>
                                <span class="shopro-status-nonormal">{{scope.row.status_text}}</span>
                            </span>
                            <span v-else-if="scope.row.status=='hidden'" class="display-flex">
                                <span class="shopro-status-dot shopro-status-default-dot"></span>
                                <span class="shopro-status-default">{{scope.row.status_text}}</span>
                            </span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" width="120">
                    <template slot-scope="scope">
                        <?php if($auth->check('shopro/app/score_shop/edit')): ?>
                        <span class="table-edit-text" @click="operation('edit',scope.row.id)">编辑
                        </span>
                        <?php endif; if($auth->check('shopro/app/score_shop/del')): ?>
                        <span class="table-delete-text" @click.stop="operation('del',scope.row.id)">删除</span>
                        <?php endif; ?>
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
