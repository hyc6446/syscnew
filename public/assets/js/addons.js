define([], function () {
    if (Config.modulename == 'admin' && Config.controllername == 'index' && Config.actionname == 'index') {
    require.config({
        paths: {
            'vue': "../addons/shopro/libs/vue",
            'moment': "../addons/shopro/libs/moment",
            'text': "../addons/shopro/libs/require-text",
            'chat': '../addons/shopro/libs/chat',
            'ELEMENT': '../addons/shopro/libs/element/element',
        },
        shim: {
            'ELEMENT': {
                deps: ['css!../addons/shopro/libs/element/element.css']
            },
        },
    });
    require(['vue', 'jquery', 'chat', 'text!../addons/shopro/chat.html', 'ELEMENT', 'moment'], function (Vue, $, Chat, ChatTemp, ELEMENT, Moment) {

        Vue.use(ELEMENT);

        var wsUri;
        Fast.api.ajax({
            url: 'shopro/chat/index/init',
            loading: false,
            type: 'GET'
        }, function (ret, res) {
            if (res.data.config.type == 'shopro') {

                let wg = 'ws';
                if (res.data.config.system.is_ssl == 1) {
                    wg = 'wss';
                }
                wsUri = wg + '://' + window.location.hostname + ':' + res.data.config.system.gateway_port;
                // 反向代理
                if (res.data.config.system.is_ssl == 1 && res.data.config.system.ssl_type == 'reverse_proxy') {
                    wsUri = wg + '://' + window.location.hostname + '/websocket/';
                }
                $("body").append(`<div id="chatTemplateContainer" style="display:none"></div>
                    <div id="chatService"><Chat :passvalue="obj"></Chat></div>`);

                $("#chatTemplateContainer").append(ChatTemp);

                new Vue({
                    el: "#chatService",
                    data() {
                        return {
                            obj: {
                                commonWordsList: res.data.fast_reply,
                                token: res.data.token,
                                wsUri: wsUri,
                                expire_time: res.data.expire_time,
                                customer_service_id: res.data.customer_service.id,
                                adminData: res.data,
                                emoji_list: res.data.emoji
                            }
                        }
                    }
                });

            }
            return false;
        }, function (ret, res) {
            if (res.msg == '') {
                return false;
            }
        })
    });
}
window.UEDITOR_HOME_URL = Config.__CDN__ + "/assets/addons/ueditor/";
require.config({
    paths: {
        'ueditor.config': '../addons/ueditor/ueditor.config',
        'ueditor': '../addons/ueditor/ueditor.all.min',
        'ueditor.zh': '../addons/ueditor/i18n/zh-cn/zh-cn',
        'zeroclipboard': '../addons/ueditor/third-party/zeroclipboard/ZeroClipboard.min',
    },
    shim: {
        'ueditor': {
            deps: ['zeroclipboard', 'ueditor.config'],
            exports: 'UE',
            init: function (ZeroClipboard) {
                //导出到全局变量，供ueditor使用
                window.ZeroClipboard = ZeroClipboard;
            },
        },
        'ueditor.zh': ['ueditor']
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定editor事件
            require(['ueditor', 'ueditor.zh'], function (UE, undefined) {
                UE.list = [];
                window.UEDITOR_CONFIG['uploadService'] = function (context, editor) {
                    return {
                        Upload: () => { return Upload },
                        Fast: () => { return Fast },
                    }
                };
                $(Config.ueditor.classname || '.editor', form).each(function () {
                    var id = $(this).attr("id");
                    var name = $(this).attr("name");
                    $(this).removeClass('form-control');
                    UE.list[id] = UE.getEditor(id, {
                        allowDivTransToP: false, //阻止div自动转p标签
                        initialFrameWidth: '100%',
                        initialFrameHeight: 320,
                        autoFloatEnabled: false,
                        // autoHeightEnabled: true, //自动高度
                        zIndex: 90,
                        xssFilterRules: false,
                        outputXssFilter: false,
                        inputXssFilter: false,
                        catchRemoteImageEnable: true
                    });
                    UE.list[id].addListener("contentChange", function () {
                        $('#' + id).val(this.getContent());
                        $('textarea[name="' + name + '"]').val(this.getContent());
                    })
                });
            })
        } catch (e) {
            console.log('绑定editor事件', e)
        }
    }
});
});