(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-rich-index"],{"0235":function(n,t,r){"use strict";var e=r("da8b"),i=r.n(e);i.a},"0c5b":function(n,t,r){"use strict";var e;r.d(t,"b",(function(){return i})),r.d(t,"c",(function(){return a})),r.d(t,"a",(function(){return e}));var i=function(){var n=this,t=n.$createElement,r=n._self._c||t;return r("v-uni-view",{staticClass:"container f13"},[r("v-uni-rich-text",{staticClass:"color_f",attrs:{nodes:n.element.content}})],1)},a=[]},"0f1c":function(n,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var e=r("5301"),i={data:function(){return{id:"",element:{}}},onLoad:function(n){var t=n.id,r=void 0===t?"":t;this.id=r,this.init()},methods:{init:function(){var n=this;this.$model.getRich({id:this.id}).then((function(t){t.data.content=(0,e.formatRichText)(t.data.content),n.element=t.data,uni.setNavigationBarTitle({title:t.data.title})}))}}};t.default=i},"96a2":function(n,t,r){"use strict";r.r(t);var e=r("0f1c"),i=r.n(e);for(var a in e)"default"!==a&&function(n){r.d(t,n,(function(){return e[n]}))}(a);t["default"]=i.a},a03f:function(n,t,r){var e=r("24fb");t=e(!1),t.push([n.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-55d8e40f]{padding:%?32?%}',""]),n.exports=t},da8b:function(n,t,r){var e=r("a03f");"string"===typeof e&&(e=[[n.i,e,""]]),e.locals&&(n.exports=e.locals);var i=r("4f06").default;i("7cc2e617",e,!0,{sourceMap:!1,shadowMode:!1})},f157:function(n,t,r){"use strict";r.r(t);var e=r("0c5b"),i=r("96a2");for(var a in i)"default"!==a&&function(n){r.d(t,n,(function(){return i[n]}))}(a);r("0235");var c,o=r("f0c5"),s=Object(o["a"])(i["default"],e["b"],e["c"],!1,null,"55d8e40f",null,!1,e["a"],c);t["default"]=s.exports}}]);