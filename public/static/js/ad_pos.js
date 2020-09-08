layui.use(['layer', 'table'], function(){
    var  layer = layui.layer //弹层
        ,table = layui.table //表格
		,form = layui.form //表单

    //执行一个 table 实例
    table.render({
        elem: '#role'
        ,url: list_url //数据接口
        ,title: '广告分类列表'
        ,method: 'POST'
        ,page: true //开启分页
        ,loading: true
        ,skin: 'line'
        ,toolbar: '#toolbarDemo' //开启头部工具栏，并为其绑定左侧模板
        //,totalRow: true //开启合计行
        ,id:'testReload'
        ,cols: [[ //表头
            {type: 'checkbox', fixed: 'left'}
            ,{field: 'id', title: 'ID',  sort: true}
            ,{field: 'name', title: '分类名称',}
            ,{fixed: 'right', title: '操作',width: 165, align:'center', toolbar: '#barDemo'}
        ]]
    });
    //重载列表
    var active = {
        reload: function(){

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
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
			layer.open({
				type: 2,
				title: '修改广告类型',
				content: edit_url+"?id="+data.id,
				maxmin: true,
				area: ['50%','50%'],
				btn: ['确定', '取消'],
				yes: function(index, layero) {
					var submit = layero.find('iframe').contents().find("#save-submit");
					submit.click();
				}
			});
            //location.href = edit_url+"?id="+data.id;
        }

        if(layEvent === 'del'){
            var url = delete_url;
            del(data.id,url);
        }
    });

    //删除
    function del(id,url){
        $.ajax({
            url: url,
            type: 'post',
            data: {id:id},
            async: false,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showErrorMsg('网络繁忙');
            },
            success: function(data) {
                if ( data.code === 0 ) {
                    showErrorMsg(data.msg);
                } else {
                    showSuccessMsg(data.msg,function(){
                        location.reload();
                    });
                }
            }
        });
    }
	
    //添加
    form.on('submit(create-submit)', function() {
        $.ajax({
            url: add_url,
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
                    showSuccessMsg(data.msg,function(){
                        parent.location.reload();
                    });
                }
            }
        });
    });

    //编辑
	form.on('submit(save-submit)', function() {
        $.ajax({
            url: edit_url,
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
                    showSuccessMsg(data.msg,function(){
                        parent.location.reload();
                    });
                }
            }
        });
    });

});
