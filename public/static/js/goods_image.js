layui.use(['upload','layer', 'form','table'], function(){
    var  layer = layui.layer //弹层
        ,form = layui.form //表单
        ,upload = layui.upload
        ,table = layui.table //表格

    var i=count;
     upload.render({
        elem: '#images'
        ,url: image_url //改成您自己的上传接口
        ,multiple: true
        ,before: function(obj){
        }
        ,done: function(res){
            if(res.code == 0){
                showErrorMsg('上传失败');
            }else{
                //上传成功
                var html = '';
                html +='<tr><th><input type="radio" name="is_seleted['+i+']"></th>';
                html +='<th><img style="width:150px;" src="'+ res.url +'"></th>';
                html +='<th><input type="text" name="src['+i+']" value="'+res.url+'"  lay-verify="required" autocomplete="off" class="layui-input"></th>';
                html +='<th><button type="button" class="layui-btn layui-btn-sm layui-btn-normal del"><i class="layui-icon"></i> 删除</button></th></tr>';
                $('#addImage').append(html);
                form.render();
                ++i;
                showSuccessMsg('上传成功');
            }
        }
    });

    //添加
    $('#create-submit').click(function(){
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

    //删除
    $(document).on('click', '.del', function() {
        $(this).parent().parent().remove();
    });

});