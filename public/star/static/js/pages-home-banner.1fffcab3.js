(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-home-banner"],{1318:function(n,t,r){var e=r("24fb");t=e(!1),t.push([n.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-2f73d694]{padding:%?40?% %?42?%}.container .date[data-v-2f73d694]{padding:%?24?% 0 %?28?%}.container .rich[data-v-2f73d694]{padding-top:%?20?%;color:#fff;font-size:%?28?%}',""]),n.exports=t},"2cc2":function(n,t,r){var e=r("1318");"string"===typeof e&&(e=[[n.i,e,""]]),e.locals&&(n.exports=e.locals);var i=r("4f06").default;i("40bed216",e,!0,{sourceMap:!1,shadowMode:!1})},6095:function(n,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var e=r("4669"),i={data:function(){return{id:"",element:{}}},onLoad:function(n){var t=n.id;this.id=t||"",uni.showLoading({title:"加载中..."}),this.init()},methods:{init:function(){var n=this;this.$model.getBannerDetail({id:this.id}).then((function(t){n.element=t.data,n.element.content=(0,e.formatRichText)(n.element.content)}))}},onPullDownRefresh:function(){uni.showLoading({title:"正在刷新"}),this.init()}};t.default=i},"69bf":function(n,t,r){"use strict";r.r(t);var e=r("6095"),i=r.n(e);for(var a in e)"default"!==a&&function(n){r.d(t,n,(function(){return e[n]}))}(a);t["default"]=i.a},aa96:function(n,t,r){"use strict";var e;r.d(t,"b",(function(){return i})),r.d(t,"c",(function(){return a})),r.d(t,"a",(function(){return e}));var i=function(){var n=this,t=n.$createElement,r=n._self._c||t;return r("v-uni-view",{staticClass:"container"},[r("v-uni-view",{staticClass:"rich"},[r("v-uni-rich-text",{attrs:{nodes:n.element.content}})],1)],1)},a=[]},c978:function(n,t,r){"use strict";r.r(t);var e=r("aa96"),i=r("69bf");for(var a in i)"default"!==a&&function(n){r.d(t,n,(function(){return i[n]}))}(a);r("dd6b");var o,c=r("f0c5"),s=Object(c["a"])(i["default"],e["b"],e["c"],!1,null,"2f73d694",null,!1,e["a"],o);t["default"]=s.exports},dd6b:function(n,t,r){"use strict";var e=r("2cc2"),i=r.n(e);i.a}}]);