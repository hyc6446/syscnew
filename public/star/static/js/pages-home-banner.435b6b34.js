(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-home-banner"],{"001e":function(n,t,e){var r=e("19e5");"string"===typeof r&&(r=[[n.i,r,""]]),r.locals&&(n.exports=r.locals);var i=e("4f06").default;i("3dfd4d42",r,!0,{sourceMap:!1,shadowMode:!1})},"19e5":function(n,t,e){var r=e("24fb");t=r(!1),t.push([n.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-2f73d694]{padding:%?40?% %?42?%}.container .date[data-v-2f73d694]{padding:%?24?% 0 %?28?%}.container .rich[data-v-2f73d694]{padding-top:%?20?%;color:#fff;font-size:%?28?%}',""]),n.exports=t},"923f":function(n,t,e){"use strict";e.r(t);var r=e("e63e"),i=e("fd39");for(var a in i)"default"!==a&&function(n){e.d(t,n,(function(){return i[n]}))}(a);e("a6c6");var o,c=e("f0c5"),s=Object(c["a"])(i["default"],r["b"],r["c"],!1,null,"2f73d694",null,!1,r["a"],o);t["default"]=s.exports},a6c6:function(n,t,e){"use strict";var r=e("001e"),i=e.n(r);i.a},b76a:function(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=e("5301"),i={data:function(){return{id:"",element:{}}},onLoad:function(n){var t=n.id;this.id=t||"",uni.showLoading({title:"加载中..."}),this.init()},methods:{init:function(){var n=this;this.$model.getBannerDetail({id:this.id}).then((function(t){n.element=t.data,n.element.content=(0,r.formatRichText)(n.element.content)}))}},onPullDownRefresh:function(){uni.showLoading({title:"正在刷新"}),this.init()}};t.default=i},e63e:function(n,t,e){"use strict";var r;e.d(t,"b",(function(){return i})),e.d(t,"c",(function(){return a})),e.d(t,"a",(function(){return r}));var i=function(){var n=this,t=n.$createElement,e=n._self._c||t;return e("v-uni-view",{staticClass:"container"},[e("v-uni-view",{staticClass:"rich"},[e("v-uni-rich-text",{attrs:{nodes:n.element.content}})],1)],1)},a=[]},fd39:function(n,t,e){"use strict";e.r(t);var r=e("b76a"),i=e.n(r);for(var a in r)"default"!==a&&function(n){e.d(t,n,(function(){return r[n]}))}(a);t["default"]=i.a}}]);