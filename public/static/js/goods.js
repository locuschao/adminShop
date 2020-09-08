layui.use(['layer', 'layedit','form','element','upload','table'], function(){
    var  layer = layui.layer //弹层
    var form = layui.form //表单
    var layedit = layui.layedit
    var element = layui.element;
    var upload = layui.upload;
    var table = layui.table;

    //监听下拉框
    form.on('select(cat_id_1)', function(dataObj){
        $("#cat_id_2").empty();
        var Html_2 = '<option value="">请选择分类</option>';
        $("#cat_id_2").html(Html_2);
        var Html_3 = '<option value="">请选择分类</option>';
        $("#cat_id_3").html(Html_3);
        Text = $("#cat_id_1").find("option:selected").text();
        var value = $("#cat_id_1").val();

        $.ajax({
            url: ajax_category,
            type: 'post',
            data: {"id":value},
            async: false,
            dataType: 'json',
            error: function() {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    var $html = "";
                    if(result.data != null) {
                        $.each(result.data, function (index, item) {
                            $html += "<option value='" + item.id + "'>" + item.name + "</option>";
                        });
                        $("#cat_id_2").append($html);
                        //append后必须从新渲染
                        form.render('select');
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });

    });

    form.on('select(cat_id_2)', function(dataObj){
        $("#cat_id_3").empty();
        var Html_3 = '<option value="">请选择分类</option>';
        $("#cat_id_3").html(Html_3);
        Text = $("#cat_id_2").find("option:selected").text();
        var value = $("#cat_id_2").val();

        $.ajax({
            url: ajax_category,
            type: 'post',
            data: {"id":value},
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    var $html = "";
                    if(result.data != null) {
                        $.each(result.data, function (index, item) {
                            $html += "<option value='" + item.id + "'>" + item.name + "</option>";
                        });
                        $("#cat_id_3").append($html);
                        form.render('select');
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });

    });

    //查询分类
    form.on('select(goods_type)', function(data){
        var goods_type=data.value;
        if(goods_type<=0){
            $("#attr").empty();
            showErrorMsg('请选择属性分类');return;
        }

        $.ajax({
            url: ajax_attr,
            type: 'post',
            data: {goods_type:goods_type,goods_id:goods_id},
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    $("#attr").empty();
                    var $html = "";
                    if(result.data != null) {
                        $.each(result.data, function (index, item) {
                            $html += '<div class="layui-form-item">\
        <label class="layui-form-label">'+item.attr_name+'</label>\
        <div class="layui-input-inline">\
            <input type="text" name="attr['+item.attr_id+'][attr_value]" placeholder="属性值" value="'+item.attr_value+'"  class="layui-input">\
        </div>\
    </div>';
                        });
                        $("#attr").append($html);
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });
    });

    form.on('radio(check)', function(data){
        if(data.value == '1'){
            $(".is_on_sale").css("display", "block");
        } else {
            $(".is_on_sale").css("display", "none");
        }
    });

    //运费控制
    form.on('radio(freeRadio)', function(data){
        if(data.value == '1'){
            $("#template_id").css("display", "block");
        } else {
            $("#template_id").css("display", "none");
        }
    });

    //执行一个 table 实例
    table.render({
        elem: '#role'
        ,url: list_url //数据接口
        ,title: '商品列表'
        ,method: 'POST'
        ,page: true //开启分页
        ,limit:30
        ,loading: true
        ,skin: 'line'
        ,toolbar: '#toolbarDemo' //开启头部工具栏，并为其绑定左侧模板
        ,defaultToolbar: []
        //,totalRow: true //开启合计行
        ,id:'goods_id'
        ,parseData: function (res) {
            $.each(res.data, function (i, d) {
                res.data[i].shop_price = parseFloat(res.data[i].shop_price);
            });
        }
        ,cols: [[ //表头
            {type: 'checkbox', fixed: 'left'}
            ,{field: 'goods_id', title: 'ID',  sort: true}
            ,{field: 'goods_name', title: '商品名称',align:'center'}
            ,{field: 'goods_sn', title: '货号',align:'center'}
            ,{field: 'shop_price', title: '出售价（元）',align:'center',sort: true}
            ,{field: 'store_count', title: '库存',align:'center',sort: true}
            ,{field: 'check', title: '审核',align:'center',templet:'#check-status'}
            ,{field: 'is_on_sale', title: '发布状态',align:'center',templet:'#public-status'}
            ,{field: 'create_time', title: '创建时间',align:'center',sort: true}
            ,{fixed: 'right', title: '操作',width:365, align:'center', toolbar: '#barDemo'}
        ]]
    });


    var active = {
        reload: function(){
            //执行重载
            table.reload('goods_id', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    keywords:$("#keywords").val(),
                    check:$("#check").val(),
                    is_on_sale:$("#is_on_sale").val(),
                }
            }, 'data');
        }
        
    };

    $('#search').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    //监听行工具事件
    table.on('tool(test)', function(obj){ //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
        var data = obj.data //获得当前行数据
            ,layEvent = obj.event; //获得 lay-event 对应的值

        if(layEvent === 'edit'){
            editGoods({
                title:'编辑商品',
                url: edit_url+"?id="+data.goods_id
            });
        }

        //复制商品
        if(layEvent === 'copyGoods'){
            // var clipboard = new ClipboardJS('.copybtn', {
            //     text: function() {
            //         return data.goods_name;
            //     }
            // });
            //
            // clipboard.on('success', function(e) {
            //     layer.msg("复制成功");
            // });
            //
            // clipboard.on('error', function(e) {
            //     layer.msg("复制失败");
            // });
            copyGoods({
                title:'添加商品',
                url: copy_goods_url+"?id="+data.goods_id
            });
        }

        //添加规格
        if(layEvent === 'addSpec'){
            addSpec({
                title:'添加/编辑商品规格',
                url: edit_spec_url+"?goods_id="+data.goods_id+"&type_id="+data.goods_type
            });
        }

        //添加图片
        if(layEvent === 'addImage'){
            addImage({
                title:'添加/编辑图片',
                url: add_image_url+"?goods_id="+data.goods_id
            });
        }
    });

    //编辑商品
    function editGoods(option) {
        layer.open({
            type: 2,
            title: option.title,
            content: option.url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var submitBtn = layero.find('iframe').contents().find("#edit-submit");
                console.log(submitBtn);
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    }

    //复制商品
    function copyGoods(option) {
        layer.open({
            type: 2,
            title: option.title,
            content: option.url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var submitBtn = layero.find('iframe').contents().find("#create-submit");
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    }

    //添加商品规格
    function addSpec(option) {
        layer.open({
            type: 2,
            title: option.title,
            content: option.url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var submitBtn = layero.find('iframe').contents().find("#edit-submit");
                if(submitBtn.length==0){
                    submitBtn = layero.find('iframe').contents().find("#create-submit");
                }
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    }

    //添加图片
    function addImage(option) {
        layer.open({
            type: 2,
            title: option.title,
            content: option.url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var submitBtn = layero.find('iframe').contents().find("#create-submit");
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    }

    //批量审核
    $(document).on('click', '.batchCheck', function() {
        var checkStatus = table.checkStatus('goods_id')
            ,data = checkStatus.data;
        var ids = [];
        for (var i=0;i<data.length;i++){
            ids.push(data[i].goods_id);
        }

        if(ids.length<=0){
            layer.msg("请选择商品");return;
        }

        layer.open({
            type: 2,
            title: '商品批量审核',
            content:batch_check_url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var check = layero.find('iframe').contents().find("input[type='radio']:checked").val();
                $.ajax({
                    url: ajax_batch_save_url,
                    type: 'post',
                    data: {ids:ids,type:1,check:check},
                    async: false,
                    dataType: 'json',
                    error: function() {
                        showErrorMsg('网络繁忙');
                    },
                    success: function(result) {
                        if ( result.code == 1 ) {
                            layer.msg("更新成功")
                            layer.closeAll();
                            location.reload();
                        } else {
                            layer.msg("更新失败")
                        }
                    }
                });
            },
            id: 'LAY_layuipro',
        });


    });

    //批量发布
    $(document).on('click', '.batchPublish', function() {
        var checkStatus = table.checkStatus('goods_id')
            ,data = checkStatus.data;
        var ids = [];
        for (var i=0;i<data.length;i++){
            ids.push(data[i].goods_id);
        }
        if(ids.length<=0){
            layer.msg("请选择商品");return;
        }

        layer.open({
            type: 2,
            title: '商品批量上下架',
            content:batch_sale_url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                var check = layero.find('iframe').contents().find("input[type='radio']:checked").val();
                $.ajax({
                    url: ajax_batch_save_url,
                    type: 'post',
                    data: {ids:ids,type:2,check:check},
                    async: false,
                    dataType: 'json',
                    error: function() {
                        showErrorMsg('网络繁忙');
                    },
                    success: function(result) {
                        if ( result.code == 1 ) {
                            layer.msg("更新成功")
                            layer.closeAll();
                            location.reload();
                        } else {
                            layer.msg("更新失败")
                        }
                    }
                });
            },
            id: 'LAY_layuipro',
        });
    });


    //批量创建商品
    $(document).on('click', '.batchAddGoods', function() {
        layer.open({
            type: 2,
            title:'批量创建商品',
            content:batch_add_goods_url,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                submitBtn = layero.find('iframe').contents().find("#batch-create-submit");
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    });

    //批量导入规格
    $(document).on('click', '.batchAddSpec', function() {
        var checkStatus = table.checkStatus('goods_id')
            ,data = checkStatus.data;
        var ids = [];
        for (var i=0;i<data.length;i++){
            ids.push(data[i].goods_id);
        }

        if(ids.length<=0){
            layer.msg("请选择商品");return;
        }
        layer.open({
            type: 2,
            title:'批量导入规格',
            content:batch_add_spec_url+"?goods_id="+ids,
            area: ['100%', '100%'],
            maxmin: true,
            btn:  ['确认','取消'],
            yes: function(index, layero) {
                submitBtn = layero.find('iframe').contents().find("#batch-create-submit");
                submitBtn.click();
            },
            id: 'LAY_layuipro',
        });
    });




});