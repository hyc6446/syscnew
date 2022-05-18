<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:76:"D:\code\nft\public/../application/admin\view\shopro\goods\comment\index.html";i:1652880047;s:54:"D:\code\nft\application\admin\view\layout\default.html";i:1642752126;s:51:"D:\code\nft\application\admin\view\common\meta.html";i:1642752126;s:53:"D:\code\nft\application\admin\view\common\script.html";i:1642752126;}*/ ?>
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
    #commentView>div {
        width: 100%;
    }

    .custom-table {
        padding: 20px 20px 30px;
        background: #fff;
    }

    .page-container {
        margin-top: 30px;
        justify-content: space-between;
    }

    .create-btn {
        width: fit-content;
        padding: 10px;
    }

    .shopro-screen-container {
        background: #fff;
        padding-top: 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .shopro-screen-container-left {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
    }

    .shopro-screen-tip {
        flex-shrink: 0;
    }

    .recycle-btn {
        margin-bottom: 20px;
    }

    .shopro-reset-button {
        margin-right: 10px;
    }

    .table-image {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin-right: 8px;
        float: left;
    }

    .goods {
        border-radius: 4px;
    }

    .images {
        margin: 0 8px 8px 0;
        border-radius: 4px;
    }

    .ellipsis-item {
        line-height: 32px;
    }

    [v-cloak] {
        display: none
    }
</style>
<script src="/assets/addons/shopro/libs/vue.js"></script>
<script src="/assets/addons/shopro/libs/element/element.js"></script>
<script src="/assets/addons/shopro/libs/moment.js"></script>
<div id="commentView" v-cloak v-loading="allAjax">
    <div class="shopro-screen-container">
        <div class="shopro-screen-container-left">
            <div class="shopro-button shopro-refresh-button" @click="getData">
                <i class="el-icon-refresh"></i>
            </div>
            <div class="display-flex shopro-screen-item">
                <div class="shopro-screen-tip">商品名称</div>
                <div class="shopro-screen-condition">
                    <el-input placeholder="请输入商品名称" v-model="searchForm.goods_title" class="screen-item-input"
                        size="small">
                    </el-input>
                </div>
            </div>
            <div class="display-flex shopro-screen-item">
                <div class="shopro-screen-tip">评价状态</div>
                <div class="shopro-screen-condition">
                    <el-select v-model="searchForm.comment_status" placeholder="请选择" size="small">
                        <el-option label="全部" value="all"></el-option>
                        <el-option label="显示" value="show"></el-option>
                        <el-option label="隐藏" value="hidden"></el-option>
                    </el-select>
                </div>
            </div>
            <div class="display-flex shopro-screen-item-button">
                <div class="shopro-button shopro-reset-button" @click="screenEmpty">重置</div>
                <div class="shopro-button shopro-screen-button" @click="screenFilter">筛选</div>
            </div>
        </div>
        <div class="shopro-screen-container-right">
            <div class="recycle-btn" @click="recyclebin">
                <i class="fa fa-recycle"></i> 回收站
            </div>
        </div>
    </div>
    <div class="custom-table" v-loading="tableAjax">
        <el-table ref="multipleTable" :data="data" tooltip-effect="dark" style="width: 100%" border
            @selection-change="handleSelectionChange">
            <el-table-column type="selection" min-width="36">
            </el-table-column>
            <el-table-column label="ID" prop="id" min-width="148">
            </el-table-column>
            <el-table-column label="商品" min-width="148" align="left">
                <template slot-scope="scope">
                    <div class="display-flex">
                        <div class="ellipsis-item-wrap" v-if="scope.row.goods">
                            <el-image class="table-image goods" :src="scope.row.goods.image"></el-image>
                            <span class="ellipsis-item">{{scope.row.goods.title}}</span>
                        </div>
                        <div v-else>{{scope.row.goods_id}}</div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="评论用户" min-width="148" align="left">
                <template slot-scope="scope">
                    <div class="display-flex">
                        <div class="ellipsis-item-wrap" v-if="scope.row.user">
                            <el-image class="table-image" :src="scope.row.user.avatar"></el-image>
                            <span class="ellipsis-item">{{scope.row.user.nickname}}</span>
                        </div>
                        <div v-else>{{scope.row.user_id}}</div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="评价星级" min-width="148">
                <template slot-scope="scope">
                    <div>
                        <el-rate v-model="scope.row.level" disabled> </el-rate>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="评价内容" min-width="148" align="left">
                <template slot-scope="scope">
                    <div>
                        <span class="ellipsis-item">{{scope.row.content}}</span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="评价图片" min-width="148">
                <template slot-scope="scope">
                    <div class="ellipsis-item" v-if="scope.row.images">
                        <el-image class="table-image images" v-for="image in scope.row.images.split(',')" :src="image">
                        </el-image>
                    </div>
                    <span v-if="!scope.row.images">-</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="显示状态" min-width="100">
                <template slot-scope="scope">
                    <div>
                        <span class="shopro-status-dot"
                            :class="scope.row.status=='show'?'shopro-status-normal-dot':'shopro-status-default-dot'"></span>
                        <span :class="scope.row.status=='show'?'shopro-status-normal':'shopro-status-default'">
                            {{scope.row.status_text}}
                        </span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="评论时间" min-width="148">
                <template slot-scope="scope">
                    <div>
                        {{scope.row.replytime_text || "-"}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" min-width="120">
                <template slot-scope="scope">
                    <span class="shopro-edit-text" @click="editRow(scope.row)">编辑
                    </span>
                    <span class="shopro-delete-text" @click="deleteRow(scope.row.id)">删除</span>
                </template>
            </el-table-column>
        </el-table>
        <div class="page-container display-flex">
            <div class="display-flex">
                <el-button :disabled="selectedData.length==0" @click="setStatus('show')" type="primary" size="small"
                    plain>显示
                </el-button>
                <el-button :disabled="selectedData.length==0" @click="setStatus('hidden')" size="small" plain>
                    隐藏
                </el-button>
                <el-button :disabled="selectedData.length==0" @click="batchDelete" type="danger" size="small">删除
                </el-button>
            </div>
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
