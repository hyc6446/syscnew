(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-nickName"],{2169:function(n,t,a){"use strict";a.r(t);var r=a("7fa5"),e=a.n(r);for(var i in r)"default"!==i&&function(n){a.d(t,n,(function(){return r[n]}))}(i);t["default"]=e.a},4135:function(n,t,a){"use strict";a.r(t);var r=a("5a4d"),e=a("2169");for(var i in e)"default"!==i&&function(n){a.d(t,n,(function(){return e[n]}))}(i);a("cbc4");var c,o=a("f0c5"),s=Object(o["a"])(e["default"],r["b"],r["c"],!1,null,"1f34c3c7",null,!1,r["a"],c);t["default"]=s.exports},"5a4d":function(n,t,a){"use strict";var r;a.d(t,"b",(function(){return e})),a.d(t,"c",(function(){return i})),a.d(t,"a",(function(){return r}));var e=function(){var n=this,t=n.$createElement,a=n._self._c||t;return a("v-uni-view",{staticClass:"container"},[a("v-uni-view",{staticClass:"card f13"},[a("v-uni-view",{staticClass:"row color_2c flex justify-between"},[n._v("昵称")]),a("v-uni-view",{staticClass:"row flex justify-between"},[a("v-uni-view",{staticClass:"input-wrap"},[a("v-uni-input",{staticClass:"input-value f13 color_2c",attrs:{maxlength:"50",placeholder:"请输入昵称","placeholder-class":"color_989898 f13"},model:{value:n.nickname,callback:function(t){n.nickname=t},expression:"nickname"}})],1)],1)],1)],1)},i=[]},"7fa5":function(n,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r={name:"setNickName",data:function(){return{nickname:""}},onLoad:function(n){var t=n.nickname,a=void 0===t?"":t;this.nickname=a},methods:{init:function(){}},onNavigationBarButtonTap:function(){if(!this.nickname)return uni.showToast({title:"昵称不能为空,请输入",icon:"none"});this.$model.updateUserInfo({nickname:this.nickname}).then((function(n){uni.showToast({title:"昵称修改成功"}),setTimeout((function(){uni.navigateBack()}),1500)}))}};t.default=r},a10c:function(n,t,a){var r=a("24fb");t=r(!1),t.push([n.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-1f34c3c7]{padding:%?20?% %?24?% 0;position:relative;background:#f2f2f2}.container .card[data-v-1f34c3c7]{background:#ededed;border-radius:%?20?%;padding:0 %?20?%}.container .card .row[data-v-1f34c3c7]{padding:%?30?% 0;border-bottom:%?1?% solid #151518}.container .card .row[data-v-1f34c3c7]:nth-last-of-type(1){border:none}.container .card .row[data-v-1f34c3c7]::after{border:none}.container .card .row .input-value[data-v-1f34c3c7]{width:100%}',""]),n.exports=t},cbc4:function(n,t,a){"use strict";var r=a("f97a"),e=a.n(r);e.a},f97a:function(n,t,a){var r=a("a10c");"string"===typeof r&&(r=[[n.i,r,""]]),r.locals&&(n.exports=r.locals);var e=a("4f06").default;e("6bff5ae0",r,!0,{sourceMap:!1,shadowMode:!1})}}]);