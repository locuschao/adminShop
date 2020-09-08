layui.use(['layer', 'table','form','upload','laydate'], function(){
    var  layer = layui.layer //弹层
        ,table = layui.table //表格
        ,form = layui.form //表单
        ,upload = layui.upload
        ,laydate = layui.laydate;

    //执行一个 table 实例
    table.render({
        elem: '#role'
        ,url: list_url //数据接口
        ,title: '广告列表'
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
            ,{field: 'name', title: '广告名称',align:'center'}
            ,{field: 'pos_id', title: '广告位id',align:'center'}
            ,{field: 'brief', title: '广告简介',align:'center'}
            ,{field: 'image', title: '广告图',align:'center'}
            ,{field: 'link_url', title: '跳转地址',align:'center'}
            ,{field: 'type', title: '跳转类型',align:'center'}
            ,{field: 'orderby', title: '排序',align:'center'}
            ,{field: 'is_show', title: '是否显示',align:'center',templet:function(d){
                    return d.is_show == 1 ? "是" : "否";
                }}
            ,{field: 'start_time', title: '开始时间',align:'center'}
            ,{field: 'end_time', title: '结束时间',align:'center'}
            ,{fixed: 'right', title: '操作',width: 165, align:'center', toolbar: '#barDemo'}
        ]]
    });


    var $ = layui.$, active = {
        reload: function(){

            //执行重载
            table.reload('testReload', {
                page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,where: {
                    pos_id: $("#pos_id").val(),
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
				title: '修改广告',
				content: edit_url+"?id="+data.id,
				maxmin: true,
				area: ['80%','80%'],
				btn: ['确定', '取消'],
				yes: function(index, layero) {
					var submit = layero.find('iframe').contents().find("#edit-submit");
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


    //日期
    var start = laydate.render({
        elem: '#start_time'
        ,trigger: 'click' //自动弹出控件的事件，采用click弹出
        ,done: function(value, date){
            endMax = end.config.max;
            end.config.min = date;
            end.config.min.month = date.month -1;
        }
    });
    var end = laydate.render({
        elem: '#end_time'
        ,trigger: 'click' //自动弹出控件的事件，采用click弹出
        ,done: function(value, date){
            start.config.max = date;
            start.config.max.month = date.month -1;
        }
    });

    var uploadInst = upload.render({
        elem: '#adImage'
        ,url: image_url //改成您自己的上传接口
        ,before: function(obj){
        }
        ,done: function(res){
            //如果上传失败
            if(res.code == 0){
                return showErrorMsg('上传失败');
            }else{
                //上传成功
                $('.image').val(res.url); //图片链接（base64）
                return showSuccessMsg('上传成功');
            }
        }
        ,error: function(){
            showErrorMsg('上传失败');
        }
    });

    //添加
	form.on('submit(create-submit)', function() {
		$.ajax({
            url: add_url,
            type: 'post',
            data: $('#create-form').serialize(),
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
    form.on('submit(edit-submit)', function() {
        $.ajax({
            url: edit_url,
            type: 'post',
            data: $('#edit-form').serialize(),
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