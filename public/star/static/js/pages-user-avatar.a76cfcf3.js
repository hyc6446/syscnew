(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-avatar"],{1605:function(t,i,e){"use strict";var n;e.d(i,"b",(function(){return a})),e.d(i,"c",(function(){return s})),e.d(i,"a",(function(){return n}));var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"container"},[e("v-uni-image",{staticClass:"avatar",attrs:{src:t.urls[0]},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.bindChooseImage(0)}}}),e("v-avatar",{ref:"avatar",on:{upload:function(i){arguments[0]=i=t.$handleEvent(i),t.myUpload.apply(void 0,arguments)}}}),e("v-uni-button",{staticClass:"btn color_f f14 f-weight flex align-center justify-center",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.bindChooseImage(0)}}},[t._v("选择图片")])],1)},s=[]},"1da1":function(t,i,e){"use strict";function n(t,i,e,n,a,s,r){try{var o=t[s](r),h=o.value}catch(c){return void e(c)}o.done?i(h):Promise.resolve(h).then(n,a)}function a(t){return function(){var i=this,e=arguments;return new Promise((function(a,s){var r=t.apply(i,e);function o(t){n(r,a,s,o,h,"next",t)}function h(t){n(r,a,s,o,h,"throw",t)}o(void 0)}))}}e("d3b7"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=a},"1f9f":function(t,i,e){"use strict";e.r(i);var n=e("983b"),a=e.n(n);for(var s in n)"default"!==s&&function(t){e.d(i,t,(function(){return n[t]}))}(s);i["default"]=a.a},"378b":function(t,i,e){var n=e("c447");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var a=e("4f06").default;a("6310e43d",n,!0,{sourceMap:!1,shadowMode:!1})},"5f2f":function(t,i,e){"use strict";var n=e("662b"),a=e.n(n);a.a},"662b":function(t,i,e){var n=e("baed");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var a=e("4f06").default;a("0435cc76",n,!0,{sourceMap:!1,shadowMode:!1})},7001:function(t,i,e){"use strict";var n=e("378b"),a=e.n(n);a.a},"8df5":function(t,i,e){"use strict";e.r(i);var n=e("ea2e"),a=e.n(n);for(var s in n)"default"!==s&&function(t){e.d(i,t,(function(){return n[t]}))}(s);i["default"]=a.a},"96cf":function(t,i){!function(i){"use strict";var e,n=Object.prototype,a=n.hasOwnProperty,s="function"===typeof Symbol?Symbol:{},r=s.iterator||"@@iterator",o=s.asyncIterator||"@@asyncIterator",h=s.toStringTag||"@@toStringTag",c="object"===typeof t,l=i.regeneratorRuntime;if(l)c&&(t.exports=l);else{l=i.regeneratorRuntime=c?t.exports:{},l.wrap=y;var u="suspendedStart",f="suspendedYield",p="executing",d="completed",v={},g={};g[r]=function(){return this};var m=Object.getPrototypeOf,w=m&&m(m(P([])));w&&w!==n&&a.call(w,r)&&(g=w);var x=W.prototype=S.prototype=Object.create(g);I.prototype=x.constructor=W,W.constructor=I,W[h]=I.displayName="GeneratorFunction",l.isGeneratorFunction=function(t){var i="function"===typeof t&&t.constructor;return!!i&&(i===I||"GeneratorFunction"===(i.displayName||i.name))},l.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,W):(t.__proto__=W,h in t||(t[h]="GeneratorFunction")),t.prototype=Object.create(x),t},l.awrap=function(t){return{__await:t}},H(k.prototype),k.prototype[o]=function(){return this},l.AsyncIterator=k,l.async=function(t,i,e,n){var a=new k(y(t,i,e,n));return l.isGeneratorFunction(i)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},H(x),x[h]="Generator",x[r]=function(){return this},x.toString=function(){return"[object Generator]"},l.keys=function(t){var i=[];for(var e in t)i.push(e);return i.reverse(),function e(){while(i.length){var n=i.pop();if(n in t)return e.value=n,e.done=!1,e}return e.done=!0,e}},l.values=P,E.prototype={constructor:E,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=e,this.done=!1,this.delegate=null,this.method="next",this.arg=e,this.tryEntries.forEach(D),!t)for(var i in this)"t"===i.charAt(0)&&a.call(this,i)&&!isNaN(+i.slice(1))&&(this[i]=e)},stop:function(){this.done=!0;var t=this.tryEntries[0],i=t.completion;if("throw"===i.type)throw i.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var i=this;function n(n,a){return o.type="throw",o.arg=t,i.next=n,a&&(i.method="next",i.arg=e),!!a}for(var s=this.tryEntries.length-1;s>=0;--s){var r=this.tryEntries[s],o=r.completion;if("root"===r.tryLoc)return n("end");if(r.tryLoc<=this.prev){var h=a.call(r,"catchLoc"),c=a.call(r,"finallyLoc");if(h&&c){if(this.prev<r.catchLoc)return n(r.catchLoc,!0);if(this.prev<r.finallyLoc)return n(r.finallyLoc)}else if(h){if(this.prev<r.catchLoc)return n(r.catchLoc,!0)}else{if(!c)throw new Error("try statement without catch or finally");if(this.prev<r.finallyLoc)return n(r.finallyLoc)}}}},abrupt:function(t,i){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc<=this.prev&&a.call(n,"finallyLoc")&&this.prev<n.finallyLoc){var s=n;break}}s&&("break"===t||"continue"===t)&&s.tryLoc<=i&&i<=s.finallyLoc&&(s=null);var r=s?s.completion:{};return r.type=t,r.arg=i,s?(this.method="next",this.next=s.finallyLoc,v):this.complete(r)},complete:function(t,i){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&i&&(this.next=i),v},finish:function(t){for(var i=this.tryEntries.length-1;i>=0;--i){var e=this.tryEntries[i];if(e.finallyLoc===t)return this.complete(e.completion,e.afterLoc),D(e),v}},catch:function(t){for(var i=this.tryEntries.length-1;i>=0;--i){var e=this.tryEntries[i];if(e.tryLoc===t){var n=e.completion;if("throw"===n.type){var a=n.arg;D(e)}return a}}throw new Error("illegal catch attempt")},delegateYield:function(t,i,n){return this.delegate={iterator:P(t),resultName:i,nextLoc:n},"next"===this.method&&(this.arg=e),v}}}function y(t,i,e,n){var a=i&&i.prototype instanceof S?i:S,s=Object.create(a.prototype),r=new E(n||[]);return s._invoke=T(t,e,r),s}function b(t,i,e){try{return{type:"normal",arg:t.call(i,e)}}catch(n){return{type:"throw",arg:n}}}function S(){}function I(){}function W(){}function H(t){["next","throw","return"].forEach((function(i){t[i]=function(t){return this._invoke(i,t)}}))}function k(t){function i(e,n,s,r){var o=b(t[e],t,n);if("throw"!==o.type){var h=o.arg,c=h.value;return c&&"object"===typeof c&&a.call(c,"__await")?Promise.resolve(c.__await).then((function(t){i("next",t,s,r)}),(function(t){i("throw",t,s,r)})):Promise.resolve(c).then((function(t){h.value=t,s(h)}),(function(t){return i("throw",t,s,r)}))}r(o.arg)}var e;function n(t,n){function a(){return new Promise((function(e,a){i(t,n,e,a)}))}return e=e?e.then(a,a):a()}this._invoke=n}function T(t,i,e){var n=u;return function(a,s){if(n===p)throw new Error("Generator is already running");if(n===d){if("throw"===a)throw s;return _()}e.method=a,e.arg=s;while(1){var r=e.delegate;if(r){var o=R(r,e);if(o){if(o===v)continue;return o}}if("next"===e.method)e.sent=e._sent=e.arg;else if("throw"===e.method){if(n===u)throw n=d,e.arg;e.dispatchException(e.arg)}else"return"===e.method&&e.abrupt("return",e.arg);n=p;var h=b(t,i,e);if("normal"===h.type){if(n=e.done?d:f,h.arg===v)continue;return{value:h.arg,done:e.done}}"throw"===h.type&&(n=d,e.method="throw",e.arg=h.arg)}}}function R(t,i){var n=t.iterator[i.method];if(n===e){if(i.delegate=null,"throw"===i.method){if(t.iterator.return&&(i.method="return",i.arg=e,R(t,i),"throw"===i.method))return v;i.method="throw",i.arg=new TypeError("The iterator does not provide a 'throw' method")}return v}var a=b(n,t.iterator,i.arg);if("throw"===a.type)return i.method="throw",i.arg=a.arg,i.delegate=null,v;var s=a.arg;return s?s.done?(i[t.resultName]=s.value,i.next=t.nextLoc,"return"!==i.method&&(i.method="next",i.arg=e),i.delegate=null,v):s:(i.method="throw",i.arg=new TypeError("iterator result is not an object"),i.delegate=null,v)}function L(t){var i={tryLoc:t[0]};1 in t&&(i.catchLoc=t[1]),2 in t&&(i.finallyLoc=t[2],i.afterLoc=t[3]),this.tryEntries.push(i)}function D(t){var i=t.completion||{};i.type="normal",delete i.arg,t.completion=i}function E(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(L,this),this.reset(!0)}function P(t){if(t){var i=t[r];if(i)return i.call(t);if("function"===typeof t.next)return t;if(!isNaN(t.length)){var n=-1,s=function i(){while(++n<t.length)if(a.call(t,n))return i.value=t[n],i.done=!1,i;return i.value=e,i.done=!0,i};return s.next=s}}return{next:_}}function _(){return{value:e,done:!0}}}(function(){return this||"object"===typeof self&&self}()||Function("return this")())},9795:function(t,i,e){"use strict";var n;e.d(i,"b",(function(){return a})),e.d(i,"c",(function(){return s})),e.d(i,"a",(function(){return n}));var a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",[e("v-uni-image",{staticClass:"my-avatar",style:[t.iS],attrs:{src:t.imgSrc.imgSrc},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fSelect.apply(void 0,arguments)}}}),e("v-uni-canvas",{staticClass:"my-canvas",style:{top:t.sT,height:t.csH},attrs:{"canvas-id":"avatar-canvas",id:"avatar-canvas","disable-scroll":"false"}}),e("v-uni-canvas",{staticClass:"oper-canvas",style:{top:t.sT,height:t.csH},attrs:{"canvas-id":"oper-canvas",id:"oper-canvas","disable-scroll":"false"},on:{touchstart:function(i){arguments[0]=i=t.$handleEvent(i),t.fStart.apply(void 0,arguments)},touchmove:function(i){arguments[0]=i=t.$handleEvent(i),t.fMove.apply(void 0,arguments)},touchend:function(i){arguments[0]=i=t.$handleEvent(i),t.fEnd.apply(void 0,arguments)}}}),e("v-uni-canvas",{staticClass:"prv-canvas",style:{height:t.csH,top:t.pT},attrs:{"canvas-id":"prv-canvas",id:"prv-canvas","disable-scroll":"false"},on:{touchstart:function(i){arguments[0]=i=t.$handleEvent(i),t.fHideImg.apply(void 0,arguments)}}}),e("v-uni-view",{staticClass:"oper-wrapper",style:{display:t.sD,top:t.tp}},[e("v-uni-view",{staticClass:"oper"},[t.sO?e("v-uni-view",{staticClass:"btn-wrapper"},[e("v-uni-view",{style:{width:t.bW},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fSelect.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("重选")])],1),e("v-uni-view",{style:{width:t.bW},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fClose.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("关闭")])],1),e("v-uni-view",{style:{width:t.bW,display:t.bD},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fRotate.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("旋转")])],1),e("v-uni-view",{style:{width:t.bW},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fPreview.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("预览")])],1),e("v-uni-view",{style:{width:t.bW},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fUpload.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("确认")])],1)],1):e("v-uni-view",{staticClass:"clr-wrapper"},[e("v-uni-slider",{staticClass:"my-slider",attrs:{"block-size":"25",value:"0",min:"-100",max:"100",activeColor:"red",backgroundColor:"green","block-color":"grey","show-value":!0},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.fColorChange.apply(void 0,arguments)}}}),e("v-uni-view",{style:{width:t.bW},attrs:{"hover-class":"hover"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.fPrvUpload.apply(void 0,arguments)}}},[e("v-uni-text",[t._v("确认")])],1)],1)],1)],1)],1)},s=[]},"983b":function(t,i,e){"use strict";var n=e("4ea4");e("c975"),e("ace4"),e("d3b7"),e("acd8"),e("e25e"),e("ac1f"),e("25f0"),e("3ca3"),e("466d"),e("1276"),e("498a"),e("5cc6"),e("8a59"),e("9a8c"),e("a975"),e("735e"),e("c1ac"),e("d139"),e("3a7b"),e("d5d6"),e("82f8"),e("e91f"),e("60bd"),e("5f96"),e("3280"),e("3fcc"),e("ca91"),e("25a1"),e("cd26"),e("2954"),e("649e"),e("219c"),e("b39a"),e("72f7"),e("ddb0"),e("2b3d"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0,e("96cf");var a=n(e("1da1")),s=n(e("b85c")),r=50,o={name:"yq-avatar",data:function(){return{csH:"0px",sD:"none",sT:"-10000px",pT:"-10000px",iS:{},sS:{},sO:!0,bW:"19%",bD:"flex",tp:0,imgSrc:{imgSrc:""}}},watch:{avatarSrc:function(){this.imgSrc.imgSrc=this.avatarSrc}},props:{avatarSrc:"",avatarStyle:"",selWidth:"",selHeight:"",expWidth:"",expHeight:"",minScale:"",maxScale:"",canScale:"",canRotate:"",lockWidth:"",lockHeight:"",stretch:"",lock:"",fileType:"",noTab:"",inner:"",quality:"",index:"",bgImage:""},created:function(){var t=this;this.cc=uni.createCanvasContext("avatar-canvas",this),this.cco=uni.createCanvasContext("oper-canvas",this),this.ccp=uni.createCanvasContext("prv-canvas",this),this.qlty=parseFloat(this.quality)||1,this.imgSrc.imgSrc=this.avatarSrc,this.letRotate=!1===this.canRotate||!0===this.inner||"true"===this.inner||"false"===this.canRotate?0:1,this.letScale=!1===this.canScale||"false"===this.canScale?0:1,this.isin=!0===this.inner||"true"===this.inner?1:0,this.indx=this.index||void 0,this.mnScale=parseFloat(this.minScale)||.3,this.mxScale=parseFloat(this.maxScale)||4,this.noBar=!0===this.noTab||"true"===this.noTab?1:0,this.stc=this.stretch,this.lck=this.lock,this.fType="jpg"===this.fileType?"jpg":"png",this.isin||!this.letRotate?(this.bW="24%",this.bD="none"):(this.bW="19%",this.bD="flex"),this.noBar?this.fWindowResize():uni.showTabBar({fail:function(){t.noBar=1},success:function(){t.noBar=0},complete:function(i){t.fWindowResize()}})},methods:{fWindowResize:function(){var t=uni.getSystemInfoSync();this.platform=t.platform,this.wW=t.windowWidth,this.drawTop=t.windowTop,this.wH=t.windowHeight,this.noBar||(this.wH+=r),this.csH=this.wH-r+"px",this.tp=this.csH,this.tp=t.windowTop+parseInt(this.csH)+"px",this.pxRatio=this.wW/750;var i=this.avatarStyle;if(i&&!0!==i&&(i=i.trim())){i=i.split(";");var e,n={},a=(0,s.default)(i);try{for(a.s();!(e=a.n()).done;){var o=e.value;if(o){if(o=o.trim().split(":"),o[1].toString().indexOf("upx")>=0){var h=o[1].trim().split(" ");for(var c in h)h[c]&&h[c].toString().indexOf("upx")>=0&&(h[c]=parseFloat(h[c])*this.pxRatio+"px");o[1]=h.join(" ")}n[o[0].trim()]=o[1].trim()}}}catch(l){a.e(l)}finally{a.f()}this.iS=n}this.expWidth&&(this.eW=this.expWidth.toString().indexOf("upx")>=0?parseInt(this.expWidth)*this.pxRatio:parseInt(this.expWidth)),this.expHeight&&(this.eH=this.expHeight.toString().indexOf("upx")>=0?parseInt(this.expHeight)*this.pxRatio:parseInt(this.expHeight)),"flex"===this.sD&&this.fDrawInit(!0),this.fHideImg()},fSelect:function(){var t=this;this.fSelecting||(this.fSelecting=!0,setTimeout((function(){t.fSelecting=!1}),500),uni.chooseImage({count:1,sizeType:["original","compressed"],sourceType:["album","camera"],success:function(i){uni.showLoading({title:"加载中...",mask:!0});var e=t.imgPath=i.tempFilePaths[0];uni.getImageInfo({src:e,success:function(i){if(t.imgWidth=i.width,t.imgHeight=i.height,t.path=e,!t.hasSel){var n=t.sS||{};if(!t.selWidth||!t.selHeight)return void uni.showModal({title:"裁剪框的宽或高没有设置",showCancel:!1});var a=t.selWidth.toString().indexOf("upx")>=0?parseInt(t.selWidth)*t.pxRatio:parseInt(t.selWidth),s=t.selHeight.toString().indexOf("upx")>=0?parseInt(t.selHeight)*t.pxRatio:parseInt(t.selHeight);n.width=a+"px",n.height=s+"px",n.top=(t.wH-s-r|0)/2+"px",n.left=(t.wW-a|0)/2+"px",t.sS=n}t.noBar?t.fDrawInit(!0):uni.hideTabBar({complete:function(){t.fDrawInit(!0)}})},fail:function(){uni.showToast({title:"请选择正确图片",duration:2e3})},complete:function(){uni.hideLoading()}})}}))},fUpload:function(){var t=this;if(!this.fUploading){this.fUploading=!0,setTimeout((function(){t.fUploading=!1}),1e3);var i=this.sS,e=parseInt(i.left),n=parseInt(i.top),a=parseInt(i.width),s=parseInt(i.height),r=this.eW||a*this.pixelRatio,o=this.eH||s*this.pixelRatio;uni.showLoading({title:"加载中...",mask:!0}),this.sD="none",this.sT="-10000px",this.hasSel=!1,this.fHideImg(),uni.canvasToTempFilePath({x:e,y:n,width:a,height:s,destWidth:r,destHeight:o,canvasId:"avatar-canvas",fileType:this.fType,quality:this.qlty,success:function(i){i=i.tempFilePath,t.btop(i).then((function(i){t.$emit("upload",{avatar:t.imgSrc,path:i,index:t.indx,data:t.rtn,base64:t.base64||null})}))},fail:function(t){uni.showToast({title:"error1",duration:2e3})},complete:function(){uni.hideLoading(),t.noBar||uni.showTabBar(),t.$emit("end")}},this)}},fPrvUpload:function(){var t=this;if(!this.fPrvUploading){this.fPrvUploading=!0,setTimeout((function(){t.fPrvUploading=!1}),1e3);var i=this.sS,e=(parseInt(i.width),parseInt(i.height),this.prvX),n=this.prvY,a=this.prvWidth,s=this.prvHeight,r=this.eW||parseInt(i.width)*this.pixelRatio,o=this.eH||parseInt(i.height)*this.pixelRatio;uni.showLoading({title:"加载中...",mask:!0}),this.sD="none",this.sT="-10000px",this.hasSel=!1,this.fHideImg(),uni.canvasToTempFilePath({x:e,y:n,width:a,height:s,destWidth:r,destHeight:o,canvasId:"prv-canvas",fileType:this.fType,quality:this.qlty,success:function(i){i=i.tempFilePath,t.btop(i).then((function(i){t.$emit("upload",{avatar:t.imgSrc,path:i,index:t.indx,data:t.rtn,base64:t.base64||null})}))},fail:function(){uni.showToast({title:"error_prv",duration:2e3})},complete:function(){uni.hideLoading(),t.noBar||uni.showTabBar(),t.$emit("end")}},this)}},fDrawInit:function(){var t=this,i=arguments.length>0&&void 0!==arguments[0]&&arguments[0],e=this.wW,n=this.wH,a=this.imgWidth,s=this.imgHeight,o=a/s,h=e-40,c=n-r-80,l=h/c,u=parseInt(this.sS.width),f=parseInt(this.sS.height);switch(this.fixWidth=0,this.fixHeight=0,this.lckWidth=0,this.lckHeight=0,this.stc){case"x":this.fixWidth=1;break;case"y":this.fixHeight=1;break;case"long":o>1?this.fixWidth=1:this.fixHeight=1;break;case"short":o>1?this.fixHeight=1:this.fixWidth=1;break;case"longSel":u>f?this.fixWidth=1:this.fixHeight=1;break;case"shortSel":u>f?this.fixHeight=1:this.fixWidth=1;break}switch(this.lck){case"x":this.lckWidth=1;break;case"y":this.lckHeight=1;break;case"long":o>1?this.lckWidth=1:this.lckHeight=1;break;case"short":o>1?this.lckHeight=1:this.lckWidth=1;break;case"longSel":u>f?this.lckWidth=1:this.lckHeight=1;break;case"shortSel":u>f?this.lckHeight=1:this.lckWidth=1;break}this.fixWidth?(h=u,c=h/o):this.fixHeight?(c=f,h=c*o):o<l?s<c?(h=a,c=s):h=c*o:a<h?(h=a,c=s):c=h/o,this.isin&&(h<u&&(h=u,c=h/o,this.lckHeight=0),c<f&&(c=f,h=c*o,this.lckWidth=0)),this.scaleSize=1,this.rotateDeg=0,this.posWidth=(e-h)/2|0,this.posHeight=(n-c-r)/2|0,this.useWidth=0|h,this.useHeight=0|c,this.centerX=this.posWidth+h/2,this.centerY=this.posHeight+c/2,this.focusX=0,this.focusY=0;var p=this.sS,d=parseInt(p.left),v=parseInt(p.top),g=parseInt(p.width),m=parseInt(p.height),w=(this.canvas,this.canvasOper,this.cc,this.cco);w.beginPath(),w.setLineWidth(3),w.setGlobalAlpha(1),w.setStrokeStyle("white"),w.strokeRect(d,v,g,m),w.setFillStyle("black"),w.setGlobalAlpha(.5),w.fillRect(0,0,this.wW,v),w.fillRect(0,v,d,m),w.fillRect(0,v+m,this.wW,this.wH-m-v-r),w.fillRect(d+g,v,this.wW-g-d,m),w.setGlobalAlpha(1),w.setStrokeStyle("red"),w.moveTo(d+15,v),w.lineTo(d,v),w.lineTo(d,v+15),w.moveTo(d+g-15,v),w.lineTo(d+g,v),w.lineTo(d+g,v+15),w.moveTo(d+15,v+m),w.lineTo(d,v+m),w.lineTo(d,v+m-15),w.moveTo(d+g-15,v+m),w.lineTo(d+g,v+m),w.lineTo(d+g,v+m-15),w.stroke(),w.draw(!1,(function(){i&&(t.sD="flex",t.sT=t.drawTop+"px",t.fDrawImage(!0))})),this.$emit("init")},fDrawImage:function(){var t=Date.now();if(!(t-this.drawTm<20)){this.drawTm=t;var i=this.cc,e=this.useWidth*this.scaleSize,n=this.useHeight*this.scaleSize;if(this.bgImage?i.drawImage(this.bgImage,0,0,this.wW,this.wH-r):i.fillRect(0,0,this.wW,this.wH-r),this.isin){var a=this.focusX*(this.scaleSize-1),s=this.focusY*(this.scaleSize-1);i.translate(this.centerX,this.centerY),i.rotate(this.rotateDeg*Math.PI/180),i.drawImage(this.imgPath,this.posWidth-this.centerX-a,this.posHeight-this.centerY-s,e,n)}else i.translate(this.posWidth+e/2,this.posHeight+n/2),i.rotate(this.rotateDeg*Math.PI/180),i.drawImage(this.imgPath,-e/2,-n/2,e,n);i.draw(!1)}},fPreview:function(){var t=this;if(!this.fPreviewing){this.fPreviewing=!0,setTimeout((function(){t.fPreviewing=!1}),1e3);var i=this.sS,e=parseInt(i.left),n=parseInt(i.top),a=parseInt(i.width),s=parseInt(i.height);uni.showLoading({title:"加载中...",mask:!0}),uni.canvasToTempFilePath({x:e,y:n,width:a,height:s,expWidth:a*this.pixelRatio,expHeight:s*this.pixelRatio,canvasId:"avatar-canvas",fileType:this.fType,quality:this.qlty,success:function(i){t.prvImgTmp=i=i.tempFilePath;var e=t.ccp,n=t.wW,a=parseInt(t.csH),s=parseInt(t.sS.width),r=parseInt(t.sS.height),o=n-40,h=a-80,c=o/s,l=r*c;l<h?(s=o,r=l):(c=h/r,s*=c,r=h),e.fillRect(0,0,n,a),t.prvX=n=(n-s)/2|0,t.prvY=a=(a-r)/2|0,t.prvWidth=s|=0,t.prvHeight=r|=0,e.drawImage(i,n,a,s,r),e.draw(!1),t.btop(i).then((function(i){t.sO=!1,t.pT=t.drawTop+"px"})),t.sO=!1,t.pT=t.drawTop+"px"},fail:function(){uni.showToast({title:"error2",duration:2e3})},complete:function(){uni.hideLoading()}},this)}},fChooseImg:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:void 0,i=arguments.length>1&&void 0!==arguments[1]?arguments[1]:void 0,e=arguments.length>2&&void 0!==arguments[2]?arguments[2]:void 0;if(i){var n=i.selWidth,a=i.selHeight,s=i.expWidth,o=i.expHeight,h=i.quality,c=i.canRotate,l=i.canScale,u=i.minScale,f=i.maxScale,p=i.stretch,d=i.fileType,v=i.inner,g=i.lock;s&&(this.eW=s.toString().indexOf("upx")>=0?parseInt(s)*this.pxRatio:parseInt(s)),o&&(this.eH=o.toString().indexOf("upx")>=0?parseInt(o)*this.pxRatio:parseInt(o)),this.letRotate=!1===c||!0===v||"true"===v||"false"===c?0:1,this.letScale=!1===l||"false"===l?0:1,this.qlty=parseFloat(h)||1,this.mnScale=parseFloat(u)||.3,this.mxScale=parseFloat(f)||4,this.stc=p,this.isin=!0===v||"true"===v?1:0,this.fType="jpg"===d?"jpg":"png",this.lck=g,this.isin||!this.letRotate?(this.bW="24%",this.bD="none"):(this.bW="19%",this.bD="flex"),n&&a&&(n=n.toString().indexOf("upx")>=0?parseInt(n)*this.pxRatio:parseInt(n),a=a.toString().indexOf("upx")>=0?parseInt(a)*this.pxRatio:parseInt(a),this.sS.width=n+"px",this.sS.height=a+"px",this.sS.top=(this.wH-a-r|0)/2+"px",this.sS.left=(this.wW-n|0)/2+"px",this.hasSel=!0)}this.rtn=e,this.indx=t,this.fSelect()},fRotate:function(){this.rotateDeg+=90-this.rotateDeg%90,this.fDrawImage()},fStart:function(t){var i=t.touches,e=i[0],n=i[1];if(this.touch0=e,this.touch1=n,n){var a=n.x-e.x,s=n.y-e.y;this.fgDistance=Math.sqrt(a*a+s*s)}},fMove:function(t){var i=t.touches,e=i[0],n=i[1];if(n){var a=n.x-e.x,s=n.y-e.y,r=Math.sqrt(a*a+s*s),o=.005*(r-this.fgDistance),h=this.scaleSize+o;do{if(!this.letScale)break;if(h<this.mnScale)break;if(h>this.mxScale)break;var c=this.useWidth*o/2,l=this.useHeight*o/2;if(this.isin){var u=this.useWidth*h,f=this.useHeight*h,p=(this.posWidth,this.posHeight,parseInt(this.sS.left)),d=parseInt(this.sS.top),v=parseInt(this.sS.width),g=parseInt(this.sS.height),m=p+v,w=d+g,x=void 0,y=void 0;if(u<=v||f<=g)break;this.cx=x=this.focusX*h-this.focusX,this.cy=y=this.focusY*h-this.focusY,this.posWidth-=c,this.posHeight-=l,this.posWidth-x>p&&(this.posWidth=p+x),this.posWidth+u-x<m&&(this.posWidth=m-u+x),this.posHeight-y>d&&(this.posHeight=d+y),this.posHeight+f-y<w&&(this.posHeight=w-f+y)}else this.posWidth-=c,this.posHeight-=l;this.scaleSize=h}while(0);this.fgDistance=r,n.x!==e.x&&this.letRotate&&(a=(this.touch1.y-this.touch0.y)/(this.touch1.x-this.touch0.x),s=(n.y-e.y)/(n.x-e.x),this.rotateDeg+=180*Math.atan((s-a)/(1+a*s))/Math.PI,this.touch0=e,this.touch1=n),this.fDrawImage()}else if(this.touch0){var b=e.x-this.touch0.x,S=e.y-this.touch0.y,I=this.posWidth+b,W=this.posHeight+S;if(this.isin){var H,k,T=this.useWidth*this.scaleSize,R=this.useHeight*this.scaleSize,L=I,D=W,E=L+T,P=D+R,_=parseInt(this.sS.left),C=parseInt(this.sS.top),O=_+parseInt(this.sS.width),j=C+parseInt(this.sS.height);this.cx=H=this.focusX*this.scaleSize-this.focusX,this.cy=k=this.focusY*this.scaleSize-this.focusY,!this.lckWidth&&Math.abs(b)<100&&(_<L-H?this.posWidth=_+H:O>E-H?this.posWidth=O-T+H:(this.posWidth=I,this.focusX-=b)),!this.lckHeight&&Math.abs(S)<100&&(C<D-k?(this.focusY-=C+k-this.posHeight,this.posHeight=C+k):j>P-k?(this.focusY-=j+k-(this.posHeight+R),this.posHeight=j-R+k):(this.posHeight=W,this.focusY-=S))}else Math.abs(b)<100&&!this.lckWidth&&(this.posWidth=I),Math.abs(S)<100&&!this.lckHeight&&(this.posHeight=W),this.focusX-=b,this.focusY-=S;this.touch0=e,this.fDrawImage()}},fEnd:function(t){var i=t.touches,e=i&&i[0];i&&i[1];e?this.touch0=e:(this.touch0=null,this.touch1=null)},fHideImg:function(){this.prvImg="",this.pT="-10000px",this.sO=!0,this.prvImgData=null,this.target=null},fClose:function(){this.sD="none",this.sT="-10000px",this.hasSel=!1,this.fHideImg(),this.noBar||uni.showTabBar(),this.$emit("end")},fGetImgData:function(){var t=this;return new Promise((function(i,e){var n=t.prvX,a=t.prvY,s=t.prvWidth,r=t.prvHeight;uni.canvasGetImageData({canvasId:"prv-canvas",x:n,y:a,width:s,height:r,success:function(t){i(t.data)},fail:function(t){e(t)}},t)}))},fColorChange:function(t){var i=this;return(0,a.default)(regeneratorRuntime.mark((function e(){var n,a,s,r,o,h,c,l,u,f,p,d,v,g,m,w,x,y,b,S,I,W,H,k,T,R,L;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(n=Date.now(),!(n-i.prvTm<100)){e.next=3;break}return e.abrupt("return");case 3:if(i.prvTm=n,uni.showLoading({title:"加载中...",mask:!0}),i.prvImgData){e.next=11;break}return e.next=8,i.fGetImgData().catch((function(){uni.showToast({title:"error_read",duration:2e3})}));case 8:if(i.prvImgData=e.sent){e.next=10;break}return e.abrupt("return");case 10:i.target=new Uint8ClampedArray(i.prvImgData.length);case 11:if(a=i.prvImgData,s=i.target,r=t.detail.value,0===r)s=a;else for(r=(r+100)/200,r<.005&&(r=0),r>.995&&(r=1),I=a.length-1;I>=0;I-=4)o=a[I-3]/255,h=a[I-2]/255,c=a[I-1]/255,w=Math.max(o,h,c),m=Math.min(o,h,c),d=w-m,w===m?u=0:w===o&&h>=c?u=(h-c)/d*60:w===o&&h<c?u=(h-c)/d*60+360:w===h?u=(c-o)/d*60+120:w===c&&(u=(o-h)/d*60+240),p=(w+m)/2,0===p||w===m?f=0:0<p&&p<=.5?f=d/(2*p):p>.5&&(f=d/(2-2*p)),a[I]&&(l=a[I]),r<.5?f=f*r/.5:r>.5&&(f=2*f+2*r-f*r/.5-1),0===f?o=h=c=Math.round(255*p):(p<.5?g=p*(1+f):p>=.5&&(g=p+f-p*f),v=2*p-g,x=u/360,y=x+1/3,b=x,S=x-1/3,W=function(t){return t<0?t+1:t>1?t-1:t},H=function(t){return t<1/6?v+6*(g-v)*t:t>=1/6&&t<.5?g:t>=.5&&t<2/3?v+6*(g-v)*(2/3-t):v},o=y=Math.round(255*H(W(y))),h=b=Math.round(255*H(W(b))),c=S=Math.round(255*H(W(S)))),l&&(s[I]=l),s[I-3]=o,s[I-2]=h,s[I-1]=c;k=i.prvX,T=i.prvY,R=i.prvWidth,L=i.prvHeight,uni.canvasPutImageData({canvasId:"prv-canvas",x:k,y:T,width:R,height:L,data:s,fail:function(){uni.showToast({title:"error_put",duration:2e3})},complete:function(){uni.hideLoading()}},i);case 15:case"end":return e.stop()}}),e)})))()},btop:function(t){return this.base64=t,new Promise((function(i,e){var n=t.split(","),a=n[0].match(/:(.*?);/)[1],s=atob(n[1]),r=s.length,o=new Uint8Array(r);while(r--)o[r]=s.charCodeAt(r);return i((window.URL||window.webkitURL).createObjectURL(new Blob([o],{type:a})))}))}}};i.default=o},b85c:function(t,i,e){"use strict";e("a4d3"),e("e01a"),e("d28b"),e("d3b7"),e("3ca3"),e("ddb0"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=s;var n=a(e("06c5"));function a(t){return t&&t.__esModule?t:{default:t}}function s(t,i){var e;if("undefined"===typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(e=(0,n.default)(t))||i&&t&&"number"===typeof t.length){e&&(t=e);var a=0,s=function(){};return{s:s,n:function(){return a>=t.length?{done:!0}:{done:!1,value:t[a++]}},e:function(t){throw t},f:s}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var r,o=!0,h=!1;return{s:function(){e=t[Symbol.iterator]()},n:function(){var t=e.next();return o=t.done,t},e:function(t){h=!0,r=t},f:function(){try{o||null==e["return"]||e["return"]()}finally{if(h)throw r}}}}},baed:function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.container[data-v-516cfb05]{padding:%?150?% 0 0}.container .avatar[data-v-516cfb05]{width:%?500?%;height:%?500?%;margin:0 auto}.container .btn[data-v-516cfb05]{width:%?500?%;height:%?70?%;background:#edbc5f;border-radius:%?35?%;margin:%?80?% auto 0}',""]),t.exports=i},c447:function(t,i,e){var n=e("24fb");i=n(!1),i.push([t.i,".my-canvas[data-v-48ae234a]{display:flex;position:fixed!important;background:#000;left:0;z-index:100000;width:100%}.my-avatar[data-v-48ae234a]{width:%?150?%;height:%?150?%;border-radius:100%}.oper-canvas[data-v-48ae234a]{display:flex;position:fixed!important;left:0;z-index:100001;width:100%}.prv-canvas[data-v-48ae234a]{display:flex;position:fixed!important;background:#000;left:0;z-index:200000;width:100%}.oper-wrapper[data-v-48ae234a]{height:50px;position:fixed!important;box-sizing:border-box;border:1px solid #f1f1f1;background:#fff;width:100%;left:0;bottom:0;z-index:100009;flex-direction:row}.oper[data-v-48ae234a]{display:flex;flex-direction:column;justify-content:center;padding:%?10?% %?20?%;width:100%;height:100%;box-sizing:border-box;align-self:center}.btn-wrapper[data-v-48ae234a]{display:flex;flex-direction:row;\n\n\nheight:50px;\njustify-content:space-between}.btn-wrapper uni-view[data-v-48ae234a]{display:flex;align-items:center;justify-content:center;font-size:16px;color:#333;border:1px solid #f1f1f1;border-radius:6%}.hover[data-v-48ae234a]{background:#f1f1f1;border-radius:6%}.clr-wrapper[data-v-48ae234a]{display:flex;flex-direction:row;flex-grow:1}.clr-wrapper uni-view[data-v-48ae234a]{display:flex;align-items:center;justify-content:center;font-size:16px;color:#333;border:1px solid #f1f1f1;border-radius:6%}.my-slider[data-v-48ae234a]{flex-grow:1}",""]),t.exports=i},c71f:function(t,i,e){"use strict";e.r(i);var n=e("9795"),a=e("1f9f");for(var s in a)"default"!==s&&function(t){e.d(i,t,(function(){return a[t]}))}(s);e("7001");var r,o=e("f0c5"),h=Object(o["a"])(a["default"],n["b"],n["c"],!1,null,"48ae234a",null,!1,n["a"],r);i["default"]=h.exports},db64:function(t,i,e){"use strict";e.r(i);var n=e("1605"),a=e("8df5");for(var s in a)"default"!==s&&function(t){e.d(i,t,(function(){return a[t]}))}(s);e("5f2f");var r,o=e("f0c5"),h=Object(o["a"])(a["default"],n["b"],n["c"],!1,null,"516cfb05",null,!1,n["a"],r);i["default"]=h.exports},ea2e:function(t,i,e){"use strict";var n=e("4ea4");Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a=n(e("c71f")),s=n(e("2422")),r={name:"setAvatar",data:function(){return{urls:[""]}},components:{"v-avatar":a.default},onLoad:function(t){var i=t.avatar;i&&(this.urls=[decodeURIComponent(i)]),this.init()},methods:{init:function(){},bindChooseImage:function(t){this.$refs.avatar.fChooseImg(t,{selWidth:"600upx",selHeight:"600upx",expWidth:"1200upx",expHeight:"1200upx",inner:!0})},myUpload:function(t){this.$set(this.urls,t.index,t.path)}},onNavigationBarButtonTap:function(){var t=this;try{if(!this.urls[0])return void uni.showToast({title:"请选择要修改的头像",icon:"none"});this.urls[0]}catch(i){}uni.uploadFile({url:"".concat(s.default.baseUrl,"/addons/shopro/index/upload"),filePath:this.urls[0],name:"file",header:{token:uni.getStorageSync("user").token||""},success:function(i){var e=JSON.parse(i.data);if(1!=e.code)return uni.showToast({title:e.msg,icon:"none"});t.$model.updateUserInfo({avatar:e.data.fullurl}).then((function(t){uni.showToast({title:"头像修改成功"}),setTimeout((function(){uni.navigateBack()}),1500)}))}})}};i.default=r}}]);