layui.use(['layer', 'table','form','upload'], function(){
    var  layer = layui.layer //弹层
    var form = layui.form //表单
    var upload = layui.upload;
    var table = layui.table;

    var uploadInst = upload.render({
        elem: '#adImage'
        ,url: "{:url('upload/image')}" //改成您自己的上传接口
        ,before: function(obj){
        }
        ,done: function(res){
            //如果上传失败
            if(res.code == 0){
                return layer.msg('上传失败');
            }else{
                //上传成功
                $('.image').val(res.url); //图片链接（base64）
                return layer.msg('上传成功');
            }
        }
        ,error: function(){
            layer.msg('上传失败');
        }
    });
});

