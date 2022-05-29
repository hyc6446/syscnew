define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shopro/box/box/index' + location.search,
                    add_url: 'shopro/box/box/add',
                    edit_url: 'shopro/box/box/edit',
                    del_url: 'shopro/box/box/del',
                    multi_url: 'shopro/box/box/multi',
                    import_url: 'shopro/box/box/import',
                    table: 'shopro_box',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'box.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'category.name', title: __('Category_name'), operate: 'LIKE', visible: false},
                        {field: 'category_name', title: __('Category_name'), operate: false},
                        {field: 'box_name', title: __('Box_name'), operate: 'LIKE'},
                        {field: 'box_banner_images', title: __('Box_banner_images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'coin_price', title: __('Coin_price')},
                        {field: 'box.is_hot', title: __('Is_hot'), searchList: {"1":__('Is_hot 1'),"0":__("Is_hot 0")}, visible: false},
                        {field: 'is_hot', title: __('Is_hot'), searchList: {"1":__('Is_hot 1'),"0":""}, custom: {1:"warning"}, formatter: Table.api.formatter.flag, operate: false},
                        {field: 'box.is_cheap', title: __('Is_cheap'), searchList: {"1":__('Is_cheap 1'),"0":__("Is_cheap 0")}, visible: false},
                        {field: 'is_cheap', title: __('Is_cheap'), searchList: {"1":__('Is_cheap 1'),"0":""}, custom: {1:"warning"}, formatter: Table.api.formatter.flag, operate: false},
                        {field: 'box.is_try', title: __('Is_try'), searchList: {"1":__('Is_try 1'),"0":__('Is_try 0')}, formatter: Table.api.formatter.flag, visible: false},
                        {field: 'switch', title: __('开关'), searchList: {"1":__('显示'),"0":__('隐藏')}, formatter: Table.api.formatter.toggle},
                        
                        {field: 'is_try', title: __('Is_try'), searchList: {"1":__('Is_try 1'),"0":""}, custom: {1:"success"}, formatter: Table.api.formatter.flag, operate: false},
                        {
                            field: 'buttons',
                            title: __('查看商品'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'show',
                                    text: __('查看商品'),
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'shopro/box/detail?boxid={id}',
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                searchFormVisible: true,
                search:false,
                showToggle: false,
                showColumns: false
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});