layui.use(['layer', 'table','form'], function(){
    var  layer = layui.layer //弹层
        ,table = layui.table //表格
        ,form = layui.form //表单

    //执行一个 table 实例
    table.render({
        elem: '#role'
        ,url: list_url //数据接口
        ,title: '属性分类列表'
        ,method: 'POST'
        ,page: true //开启分页
        ,loading: true
        ,cellMinWidth: 80
        ,skin: 'line'
        ,toolbar: '#typebarDemo' //开启头部工具栏，并为其绑定左侧模板
        ,totalRow: true //开启合计行
        ,id:'testReload'
        ,cols: [[ //表头
            {type: 'checkbox', fixed: 'left'}
            ,{field: 'id', title: 'ID',  sort: true,width:80}
            ,{field: 'name', title: '分类属性名称',align:'center'}
            ,{fixed: 'right', title: '操作',width: 165, align:'center', toolbar: '#barDemo'}
        ]]
    });


    var active = {
        reload: function(){

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    name: $("#name").val(),
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
            var url = edit_url+"?id="+data.id;
            editAttrType({
                title:'编辑属性分类',
                url: url
            });
        }
    });

    //编辑属性分类
    function editAttrType(option) {
        layer.open({
			type: 2,
			title: '修改分类',
			content: option.url,
			maxmin: true,
			area: ['100%','100%'],
			btn: ['确定', '取消'],
			yes: function(index, layero) {
				var submit = layero.find('iframe').contents().find("#save-submit");
				submit.click();
			}
		});
    }
    //添加属性分类
    $(document).on('click', '.addType', function() {
		layer.open({
			type: 2,
			title: '添加分类',
			content: add_url,
			maxmin: true,
			area: ['100%','100%'],
			btn: ['确定', '取消'],
			yes: function(index, layero) {
				var submit = layero.find('iframe').contents().find("#create-submit");
				submit.click();
			}
		});
    });


    //添加属性
    $('#create-submit').click(function(){
        var url = add_url;
        $.ajax({
            url: url,
            type: 'post',
            data: $("#create-form").serialize(),
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(data) {
                if ( data.code === 0 ) {
                    showErrorMsg(data.msg);
                } else {
                    showSuccessMsg(data.msg, function() {
						parent.location.reload();
					});
                }
            }
        });
    });

    //编辑
    $('#save-submit').click(function(){
        var url = save_url;
        $.ajax({
            url: url,
            type: 'post',
            data: $("#save-form").serialize(),
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(data) {
                if ( data.code === 0 ) {
                    showErrorMsg(data.msg);
                } else {
                    showSuccessMsg(data.msg, function() {
						parent.location.reload();
					});
                }
            }
        });
    });

    //添加属性参数
    $(document).on('click', '.addattr', function() {
        var html = '<div class="layui-form-item">\
        <label class="layui-form-label"></label>\
        <div class="layui-input-block">\
            <input type="text" name="attr['+attr_idx+'][attr_name]" placeholder="产品参数" lay-verify="required" autocomplete="off" class="layui-input" style="width:35%; float: left;">\
            <input type="text" name="attr['+attr_idx+'][order]" placeholder="排序" value="" lay-verify="required" autocomplete="off" class="layui-input" style="width:10%; float: left;">\
            <button type="button" class="layui-btn layui-btn-danger delete_attr_btn" style="width:38px; padding:0; margin-left:0.5rem;">\
                <i class="layui-icon layui-icon-close"></i>\
            </button>\
        </div>\
    </div>';
        $('.attr').append(html);
        attr_idx++;
    });

    //删除内容
    $(document).on('click', '.delete_attr_btn', function() {
        $(this).parent().parent().remove();
    });

    //添加规格
    $(document).on('click', '.addspec', function() {
        if($(".spec > .layui-form-item").length>=3){
            layer.msg('最多3个规格');return false;
        }
        var html = '<div class="layui-form-item">\
        <div class="layui-input-block">\
            <input type="text" name="spec['+spec_idx+'][name]" placeholder="规格值" lay-verify="required" autocomplete="off" class="layui-input spec-value-id" style="width:100px; float: left;" attr_id="'+spec_idx+'">\
            <input type="text" name="spec['+spec_idx+'][order]" placeholder="排序" value="" lay-verify="required" autocomplete="off" class="layui-input" style="width:100px; float: left;">\
            <button type="button" style="margin-left:10px;" class="layui-btn layui-btn-warm addDetail">添加详细规格</button>\
            <button type="button" class="layui-btn layui-btn-danger copyCreate">复制创建规格</button>\
            <button type="button" class="layui-btn layui-btn-danger delete_spec_btn" style="width:38px; padding:0; margin-left:0.5rem;">\
                <i class="layui-icon layui-icon-close"></i>\
            </button>\
        </div>\
        <div class="son" style="margin-left:60px;margin-top:10px;">\
        </div>\
    </div>';
        $('.spec').append(html);
        spec_idx++;
    });

    //删除内容
    $(document).on('click', '.delete_spec_btn', function() {
        $(this).parent().parent().remove();
    });

    var spec_item_idx = 1;
    $(document).on('click', '.addDetail', function() {
        var spec_idx = $(this).parent().parent().find('.spec-value-id').attr('attr_id');
        var html = '<div class="layui-form-item">\
        <div class="layui-input-block">\
            <input type="text" name="spec['+spec_idx+'][specItem]['+spec_item_idx+'][value]" placeholder="规格" lay-verify="required" value="" autocomplete="off" class="layui-input" style="width:100px; float: left;">\
            <input type="text" name="spec['+spec_idx+'][specItem]['+spec_item_idx+'][order]" value="10" placeholder="排序" lay-verify="required" autocomplete="off" class="layui-input" style="width:100px; float: left;">\
            <button type="button" class="layui-btn layui-btn-danger delete_spec_btn" style="width:38px; padding:0; margin-left:0.5rem;">\
                <i class="layui-icon layui-icon-close"></i>\
            </button>\
        </div>\
    </div>';
        $(this).parent().nextAll('.son').append(html);
        spec_item_idx++;
    });

    //删除内容
    $(document).on('click', '.delete_spec_btn', function() {
        $(this).parent().parent().remove();
    });

    //页面加载时执行
    $(function () {
        loadSpec(type_id);
    })

    //加载规格
    function loadSpec(goods_type) {
        $.ajax({
            url: ajax_url,
            type: 'post',
            data: {goods_type:goods_type},
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    $(".spec").empty();
                    var html = "";
                    if(result.data.length > 0) {
                        $.each(result.data, function (index, value) {
                            html += '<div class="layui-form-item">';
                            html += '<div class="layui-input-block">';
                            html += '<input type="hidden" name="spec['+value.id+'][id]" value="'+value.id+'"  lay-verify="required" autocomplete="off" class="layui-input" style="width:100px; float: left;">';
                            html += '<input type="text" name="spec['+value.id+'][name]" value="'+value.name+'"  placeholder="规格值" lay-verify="required" autocomplete="off" class="layui-input spec-value-id " attr_id="'+value.id+'" style="width:100px; float: left;">';
                            html += '<input type="text" name="spec['+value.id+'][order]" value="'+value.order+'"  placeholder="排序" lay-verify="required" autocomplete="off" class="layui-input" style="width:100px; float: left;">';
                            html += '             <button type="button" style="margin-left:10px;" class="layui-btn layui-btn-warm addDetail">添加详细规格</button>';
                            html += '                <button type="button" class="layui-btn layui-btn-danger copyCreate">复制创建规格</button>';
                            html += '                <button type="button" class="layui-btn layui-btn-danger delete_spec_btn" style="width:38px; padding:0; margin-left:0.5rem;">';
                            html += '                    <i class="layui-icon layui-icon-close"></i>';
                            html += '                </button>';
                            html += '           </div>';
                            html += '           <div class="son" style="margin-left:60px;margin-top:10px;">';

                            if(value.specItem.length >0){
                                $.each(value.specItem, function (index2, value2) {
                                    html += '              <div class="layui-form-item">';
                                    html += '                   <div class="layui-input-block">';
                                    html += '                      <input type="hidden" name="spec['+value.id+'][specItem]['+value2.id+'][id]" value="'+value2.id+'">';
                                    html += '                     <input type="text" name="spec['+value.id+'][specItem]['+value2.id+'][value]" placeholder="规格" lay-verify="required" autocomplete="off" value="'+value2.item+'" class="layui-input" style="width:100px; float: left;">';
                                    html += '                     <input type="text" name="spec['+value.id+'][specItem]['+value2.id+'][order]" placeholder="排序" lay-verify="required" autocomplete="off" value="'+value2.order_index+'" class="layui-input" style="width:100px; float: left;">';
                                    html += '                      <button type="button" class="layui-btn layui-btn-danger delete_spec_btn" style="width:38px; padding:0; margin-left:0.5rem;">                <i class="layui-icon layui-icon-close"></i>';
                                    html += '                    </button>';
                                    html += '                 </div>';
                                    html += '             </div>';
                                });
                            }

                            html += '         </div>';
                            html += '      </div>';
                        });
                        $(".spec").append(html);
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });
    }

});