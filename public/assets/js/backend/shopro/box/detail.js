define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shopro/box/detail/index' + location.search,
                    add_url: 'shopro/box/detail/add' + Config.boxid_filter,
                    edit_url: 'shopro/box/detail/edit',
                    del_url: 'shopro/box/detail/del',
                    multi_url: 'shopro/box/detail/multi',
                    import_url: 'shopro/box/detail/import',
                    table: 'shopro_box_detail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'detail.weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'box_name', title: __('Box_name'), operate:false},
                        {field: 'goods_name', title: __('Goods_name'), operate:false},
                        {field: 'goods_image', title: __('Goods_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'rate', title: __('Rate'), operate:'BETWEEN'},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'weigh', title: __('Weigh'), operate: false},
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