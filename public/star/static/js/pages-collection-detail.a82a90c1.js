(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-collection-detail"],{"1b86":function(e,t,a){"use strict";var n;a.d(t,"b",(function(){return i})),a.d(t,"c",(function(){return r})),a.d(t,"a",(function(){return n}));var i=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("v-uni-view",{staticClass:"container",style:{backgroundImage:"url("+a("2fd8")+")"}},[n("v-uni-image",{staticClass:"nft-img",attrs:{src:e.element.image}}),n("v-uni-view",{staticClass:"header flex align-center justify-center"},[n("v-uni-image",{staticClass:"header-icon left",attrs:{src:a("5203")}}),n("v-uni-view",{staticClass:"color_f"},[n("v-uni-view",{staticClass:"nft-name f16 f-weight"},[e._v(e._s(e.element.title))]),n("v-uni-view",{staticClass:"flex align-center justify-center f12"},[e._l(e.element.tags,(function(t,a){return n("v-uni-view",{key:a,staticClass:"tag color_3 bg_edbc"},[e._v(e._s(t))])})),n("v-uni-view",{staticClass:"num color_edbc"},[e._v(e._s(e.element.issue_num||"0")+"份")])],2)],1),n("v-uni-image",{staticClass:"header-icon right",attrs:{src:a("5203")}})],1),n("v-uni-view",{staticClass:"card color_fafafa"},[n("v-uni-view",{staticClass:"title f16"},[e._v("创作方")]),n("v-uni-view",{staticClass:"flex align-center"},e._l(e.element.brand_arr,(function(t,a){return n("v-uni-view",{key:a,staticClass:"nft-author flex align-center f14"},[n("v-uni-image",{staticClass:"avatar",attrs:{src:t.image}}),n("v-uni-view",[e._v(e._s(t.name))]),n("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:e.element.brand_arr&&a<e.element.brand_arr.length-1,expression:"element.brand_arr && ia < element.brand_arr.length - 1"}],staticClass:"and-next"},[e._v("&")])],1)})),1)],1),n("v-uni-view",{staticClass:"card color_fafafa"},[n("v-uni-view",{staticClass:"title f16"},[e._v("藏品描述")]),n("v-uni-view",{staticClass:"rich"},[n("v-uni-rich-text",{attrs:{nodes:e.element.content}})],1)],1),n("v-uni-view",{staticClass:"card color_fafafa"},[n("v-uni-view",{staticClass:"title f16"},[e._v("购买须知")]),n("v-uni-view",{staticClass:"rich f12",staticStyle:{"line-height":"1.6"}},[e._v(e._s(e.element.note||"-"))])],1),1==e.user.is_auth?n("v-uni-view",{staticClass:"footer flex justify-between align-center"},[n("v-uni-view",{staticClass:"price f-weight color_f f16 flex align-center flex-wid"},[e._v("￥ "+e._s(e.element.price||"0.00"))]),e.element.can_sales?n("v-uni-button",{staticClass:"btn",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.bindBuyNft.apply(void 0,arguments)}}},[e._v("立即购买")]):e._e()],1):e._e(),1!=e.user.is_auth?n("v-uni-view",{staticClass:"footer flex justify-between align-center"},[n("v-uni-view",{staticClass:"price f-weight color_f f16 flex align-center flex-wid"},[e._v("￥ "+e._s(e.element.price||"0.00"))]),n("v-uni-button",{staticClass:"btn",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.bindJump("/pages/realname/index")}}},[e._v("立即认证")])],1):e._e(),n("v-share",{attrs:{is_favorite:e.element.is_favorite,content:e.element},on:{close:function(t){arguments[0]=t=e.$handleEvent(t),e.show=!1},changeFavor:function(t){arguments[0]=t=e.$handleEvent(t),e.bindChangeFavor.apply(void 0,arguments)}},model:{value:e.show,callback:function(t){e.show=t},expression:"show"}})],1)},r=[]},"1e08":function(e,t,a){"use strict";a.r(t);var n=a("f832"),i=a.n(n);for(var r in n)"default"!==r&&function(e){a.d(t,e,(function(){return n[e]}))}(r);t["default"]=i.a},"8f3c":function(e,t,a){var n=a("cd92");"string"===typeof n&&(n=[[e.i,n,""]]),n.locals&&(e.exports=n.locals);var i=a("4f06").default;i("3053d9f3",n,!0,{sourceMap:!1,shadowMode:!1})},cba9:function(e,t,a){"use strict";var n=a("8f3c"),i=a.n(n);i.a},cd92:function(e,t,a){var n=a("24fb");t=n(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-05ccc5aa]{background-size:%?700?% %?618?%;background-position:%?25?% %?-74?%;background-repeat:no-repeat;padding:%?200?% %?24?% calc(%?120?% + env(safe-area-inset-bottom));background-color:#f2f2f2}.container .buy-need-know[data-v-05ccc5aa]{position:fixed;width:%?750?%;height:%?92?%;background:#171816;background:rgba(0,0,0,.6);left:0;bottom:%?98?%;display:flex;align-items:center;padding:0 %?24?%}.container .buy-need-know .msg[data-v-05ccc5aa]{font-size:%?28?%;font-family:Source Han Sans CN-Regular,Source Han Sans CN;font-weight:400;color:#fff;margin:0 %?93?% 0 %?10?%}.container .buy-need-know .btn[data-v-05ccc5aa]{width:%?152?%;height:%?68?%;background:#afdc4a;border-radius:%?39?%;opacity:1;border:1px solid #707070;font-size:%?26?%;font-family:Source Han Sans CN-Regular,Source Han Sans CN;font-weight:400;color:#171816;display:flex;justify-content:center;align-items:center}.container .nft-img[data-v-05ccc5aa]{width:100%;border-radius:%?20?%;margin:0 auto}.container .header[data-v-05ccc5aa]{padding:%?80?% 0 %?12?%}.container .header .header-icon[data-v-05ccc5aa]{width:%?52?%;height:%?94?%;margin:0 %?10?%}.container .header .left[data-v-05ccc5aa]{-webkit-transform:rotateY(180deg);transform:rotateY(180deg)}.container .header .nft-name[data-v-05ccc5aa]{padding:0 0 %?10?%;text-align:center}.container .header .tag[data-v-05ccc5aa]{line-height:1;padding:%?6?% %?16?%;border-radius:%?2?%;margin:0 %?10?%}.container .header .num[data-v-05ccc5aa]{background:#36363a;border-radius:0 %?3?% %?3?% 0;margin-left:%?-10?%;line-height:1;padding:%?6?% %?16?%}.container .card[data-v-05ccc5aa]{background:#ededed;box-shadow:0 %?4?% %?7?% %?1?% rgba(29,28,26,.4);border-radius:%?20?%;padding:%?20?%;margin:%?20?% auto 0}.container .card .title[data-v-05ccc5aa]{padding:0 0 %?20?%;border-bottom:%?1?% solid #151518}.container .card .nft-author[data-v-05ccc5aa]{padding:%?20?% 0 0}.container .card .nft-author .avatar[data-v-05ccc5aa]{width:%?50?%;height:%?50?%;border-radius:50%;margin-right:%?20?%}.container .card .nft-author .and-next[data-v-05ccc5aa]{padding:0 %?20?%}.container .rich[data-v-05ccc5aa]{padding:%?20?% 0 0}.container .nft-user[data-v-05ccc5aa]{padding:%?14?% 0}.container .footer[data-v-05ccc5aa]{height:%?98?%;padding:%?14?% %?24?% calc(%?10?% + env(safe-area-inset-bottom));background-color:#0a0b0c;position:fixed;width:100%;left:0;bottom:0}.container .footer .price[data-v-05ccc5aa]{padding-left:%?80?%}.container .footer .btn[data-v-05ccc5aa]{width:%?300?%;height:%?70?%;background:#edbc5f;border-radius:%?35?%;display:flex;align-items:center;justify-content:center;font-size:%?24?%;font-weight:700;color:#fff}',""]),e.exports=t},d6f7:function(e,t,a){"use strict";a.r(t);var n=a("1b86"),i=a("1e08");for(var r in i)"default"!==r&&function(e){a.d(t,e,(function(){return i[e]}))}(r);a("cba9");var c,s=a("f0c5"),o=Object(s["a"])(i["default"],n["b"],n["c"],!1,null,"05ccc5aa",null,!1,n["a"],c);t["default"]=o.exports},f832:function(e,t,a){"use strict";var n=a("4ea4");a("99af"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=n(a("db24")),r={name:"nftDetail",data:function(){return{id:"",element:{},show:!1,user:{}}},components:{"v-share":i.default},onLoad:function(e){var t=e.id;this.id=t||"",uni.showLoading({title:"加载中..."}),this.init()},methods:{init:function(){var e=this;this.$model.getNftDetail({id:this.id}).then((function(t){e.element=t.data}))},getUserInfo:function(){var e=this;this.$model.getUserInfo().then((function(t){e.user=t.data}))},bindBuyNft:function(){var e=this;if(1==this.element.is_while_sales)return uni.showToast({title:"".concat(this.element.title,"还没到出售时间哟~")});0!=this.can_sales&&(uni.showLoading({title:"创建订单中...",mask:!0}),this.$model.saveHomeCreateOrder({goods_id:this.element.id,sku_price_id:this.element.sku_price[0].id,goods_price:this.element.price}).then((function(t){e.bindJump("/pages/pay/index?key=order_sn&value=".concat(t.data.order_sn,"&price=").concat(e.element.price,"&http=payHomeOrder"))})))},bindChangeFavor:function(e){this.element.is_favorite=e}},onNavigationBarButtonTap:function(){this.show=!0},onPullDownRefresh:function(){uni.showLoading({title:"刷新中...",mask:!0}),this.init()}};t.default=r}}]);