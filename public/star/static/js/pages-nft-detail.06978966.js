(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-nft-detail"],{2188:function(t,n,e){"use strict";e.r(n);var a=e("acd0"),i=e.n(a);for(var r in a)"default"!==r&&function(t){e.d(n,t,(function(){return a[t]}))}(r);n["default"]=i.a},"6c32":function(t,n,e){"use strict";var a=e("89d4"),i=e.n(a);i.a},"89d4":function(t,n,e){var a=e("c91b");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var i=e("4f06").default;i("57e76128",a,!0,{sourceMap:!1,shadowMode:!1})},acd0:function(t,n,e){"use strict";(function(t){var a=e("4ea4");e("99af"),e("ac1f"),e("5319"),Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var i=a(e("c35f")),r={name:"nftDetail",data:function(){return{id:"",element:{},show:!1,user:{}}},components:{"v-share":i.default},onLoad:function(t){var n=t.id;this.id=n||"",uni.showLoading({title:"加载中..."}),this.init(),this.getUserInfo()},methods:{init:function(){var t=this;this.$model.getNftDetail({id:this.id}).then((function(n){t.element=n.data;var e=t.element.content;t.element.content=e.replace(/\<img/gi,'<img style="width:100%;height:auto"')}))},getUserInfo:function(){var n=this;this.$model.getUserInfo().then((function(e){t.log(e),n.user=e.data}))},bindBuyNft:function(){var t=this;if(1==this.element.is_while_sales)return uni.showToast({title:"".concat(this.element.title,"还没到出售时间哟~")});0!=this.can_sales&&(uni.showLoading({title:"创建订单中...",mask:!0}),this.$model.saveHomeCreateOrder({goods_id:this.element.id,sku_price_id:this.element.sku_price[0].id,goods_price:this.element.price}).then((function(n){t.bindJump("/pages/pay/index?key=order_sn&value=".concat(n.data.order_sn,"&price=").concat(t.element.price,"&http=payHomeOrder&order_id=").concat(n.data.id))})))},bindChangeFavor:function(t){this.element.is_favorite=t}},onNavigationBarButtonTap:function(){this.show=!0},onPullDownRefresh:function(){uni.showLoading({title:"刷新中...",mask:!0}),this.init()}};n.default=r}).call(this,e("5a52")["default"])},c16a:function(t,n,e){"use strict";var a;e.d(n,"b",(function(){return i})),e.d(n,"c",(function(){return r})),e.d(n,"a",(function(){return a}));var i=function(){var t=this,n=t.$createElement,a=t._self._c||n;return a("v-uni-view",{staticClass:"container",style:{backgroundImage:"url("+e("a6ce")+")"}},[a("v-uni-image",{staticClass:"nft-img",attrs:{src:t.element.image,mode:"widthFix"}}),a("v-uni-view",{staticClass:"header flex align-center justify-center"},[a("v-uni-image",{staticClass:"header-icon left",attrs:{src:e("c511")}}),a("v-uni-view",{staticClass:"color_2c"},[a("v-uni-view",{staticClass:"nft-name f16 f-weight"},[t._v(t._s(t.element.title))]),a("v-uni-view",{staticClass:"flex align-center justify-center f12"},[t._l(t.element.tags,(function(n,e){return a("v-uni-view",{key:e,staticClass:"tag color_3 bg_edbc"},[t._v(t._s(n))])})),a("v-uni-view",{staticClass:"num color_edbc"},[t._v(t._s(t.element.issue_num||"0")+"份")])],2)],1),a("v-uni-image",{staticClass:"header-icon right",attrs:{src:e("c511")}})],1),a("v-uni-view",{staticClass:"card color-2c"},[a("v-uni-view",{staticClass:"title f16"},[t._v("创作方")]),a("v-uni-view",{staticClass:"flex align-center"},t._l(t.element.brand_arr,(function(n,e){return a("v-uni-view",{key:e,staticClass:"nft-author flex align-center f14"},[a("v-uni-image",{staticClass:"avatar",attrs:{src:n.image}}),a("v-uni-view",[t._v(t._s(n.name))]),a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:t.element.brand_arr&&e<t.element.brand_arr.length-1,expression:"element.brand_arr && ia < element.brand_arr.length - 1"}],staticClass:"and-next"},[t._v("&")])],1)})),1)],1),a("v-uni-view",{staticClass:"card color-2c"},[a("v-uni-view",{staticClass:"title f16"},[t._v("藏品描述")]),a("v-uni-view",{staticClass:"rich"},[a("v-uni-rich-text",{attrs:{nodes:t.element.content}})],1)],1),a("v-uni-view",{staticClass:"card color-2c"},[a("v-uni-view",{staticClass:"title f16"},[t._v("购买须知")]),a("v-uni-view",{staticClass:"rich f12",staticStyle:{"line-height":"1.6"}},[t._v(t._s(t.element.note||"-"))])],1),1==t.user.is_auth?a("v-uni-view",{staticClass:"footer flex justify-between align-center"},[a("v-uni-view",{staticClass:"price f-weight color_f f16 flex align-center flex-wid"},[t._v("￥ "+t._s(t.element.price||"0.00"))]),a("v-uni-button",{staticClass:"btn",on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.bindBuyNft.apply(void 0,arguments)}}},[t._v("立即购买")])],1):t._e(),1!=t.user.is_auth?a("v-uni-view",{staticClass:"footer flex justify-between align-center"},[a("v-uni-view",{staticClass:"price f-weight color_f f16 flex align-center flex-wid"},[t._v("￥ "+t._s(t.element.price||"0.00"))]),a("v-uni-button",{staticClass:"btn",on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.bindJump("/pages/realname/index")}}},[t._v("立即认证")])],1):t._e(),a("v-share",{attrs:{is_favorite:t.element.is_favorite,content:t.element},on:{close:function(n){arguments[0]=n=t.$handleEvent(n),t.show=!1},changeFavor:function(n){arguments[0]=n=t.$handleEvent(n),t.bindChangeFavor.apply(void 0,arguments)}},model:{value:t.show,callback:function(n){t.show=n},expression:"show"}})],1)},r=[]},c91b:function(t,n,e){var a=e("24fb");n=a(!1),n.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-215b6152]{background-size:%?700?% %?618?%;background-position:%?25?% %?-74?%;background-repeat:no-repeat;padding:%?200?% %?24?% calc(%?120?% + env(safe-area-inset-bottom));background-color:#f2f2f2}.container .buy-need-know[data-v-215b6152]{position:fixed;width:%?750?%;height:%?92?%;background:#171816;background:rgba(0,0,0,.6);left:0;bottom:%?98?%;display:flex;align-items:center;padding:0 %?24?%}.container .buy-need-know .msg[data-v-215b6152]{font-size:%?28?%;font-family:Source Han Sans CN-Regular,Source Han Sans CN;font-weight:400;color:#fff;margin:0 %?93?% 0 %?10?%}.container .buy-need-know .btn[data-v-215b6152]{width:%?152?%;height:%?68?%;background:#afdc4a;border-radius:%?39?%;opacity:1;border:1px solid #707070;font-size:%?26?%;font-family:Source Han Sans CN-Regular,Source Han Sans CN;font-weight:400;color:#171816;display:flex;justify-content:center;align-items:center}.container .nft-img[data-v-215b6152]{width:100%;border-radius:%?20?%;margin:0 auto}.container .header[data-v-215b6152]{padding:%?80?% 0 %?12?%}.container .header .header-icon[data-v-215b6152]{width:%?52?%;height:%?94?%;margin:0 %?10?%}.container .header .left[data-v-215b6152]{-webkit-transform:rotateY(180deg);transform:rotateY(180deg)}.container .header .nft-name[data-v-215b6152]{padding:0 0 %?10?%;text-align:center}.container .header .tag[data-v-215b6152]{line-height:1;padding:%?6?% %?16?%;border-radius:%?2?%;margin:0 %?10?%}.container .header .num[data-v-215b6152]{background:#36363a;border-radius:0 %?3?% %?3?% 0;margin-left:%?-10?%;line-height:1;padding:%?6?% %?16?%}.container .card[data-v-215b6152]{background:#ededed;box-shadow:0 %?4?% %?7?% %?1?% rgba(29,28,26,.4);border-radius:%?20?%;padding:%?20?%;margin:%?20?% auto 0}.container .card .title[data-v-215b6152]{padding:0 0 %?20?%;border-bottom:%?1?% solid #151518}.container .card .nft-author[data-v-215b6152]{padding:%?20?% 0 0}.container .card .nft-author .avatar[data-v-215b6152]{width:%?50?%;height:%?50?%;border-radius:50%;margin-right:%?20?%}.container .card .nft-author .and-next[data-v-215b6152]{padding:0 %?20?%}.container .rich[data-v-215b6152]{padding:%?20?% 0 0}.container .nft-user[data-v-215b6152]{padding:%?14?% 0}.container .footer[data-v-215b6152]{height:%?98?%;padding:%?14?% %?24?% calc(%?10?% + env(safe-area-inset-bottom));background-color:#0a0b0c;position:fixed;width:100%;left:0;bottom:0}.container .footer .price[data-v-215b6152]{padding-left:%?80?%}.container .footer .btn[data-v-215b6152]{width:%?300?%;height:%?70?%;background:#edbc5f;border-radius:%?35?%;display:flex;align-items:center;justify-content:center;font-size:%?24?%;font-weight:700;color:#fff}.nft-img[data-v-215b6152]{width:100%;-webkit-animation-name:turnY;animation-name:turnY;-webkit-animation-duration:10s;animation-duration:10s;-webkit-animation-timing-function:linear;animation-timing-function:linear;-webkit-animation-delay:2s;animation-delay:2s;-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite;-webkit-animation-direction:alternate;animation-direction:alternate;-webkit-animation-play-state:running;animation-play-state:running}.btn[data-v-215b6152]{border-radius:%?6?%!important;background:#3571be!important;color:#fff}',""]),t.exports=n},ca69:function(t,n,e){"use strict";e.r(n);var a=e("c16a"),i=e("2188");for(var r in i)"default"!==r&&function(t){e.d(n,t,(function(){return i[t]}))}(r);e("6c32");var o,s=e("f0c5"),c=Object(s["a"])(i["default"],a["b"],a["c"],!1,null,"215b6152",null,!1,a["a"],o);n["default"]=c.exports}}]);