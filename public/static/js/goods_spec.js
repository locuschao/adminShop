layui.use(['layer', 'form'], function(){
    var  layer = layui.layer //弹层
        ,form = layui.form //表单

    // 添加规格值
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

    // 编辑规格值
    $('#edit-submit').click(function(){
        $.ajax({
            url: save_url,
            type: 'post',
            data: $("#edit-form").serialize(),
            async: false,
            dataType: 'json',
            error: function() {
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

    //页面加载时执行
    $(function () {
        loadSpec(type_id,goods_id);
        //组合sku
        loadSku(goods_id);
    })

    //加载已经选择的sku
    function loadSku(goods_id) {
        $.ajax({
            url: ajax_sku_value,
            type: 'post',
            data: {goods_id:goods_id},
            async: false,
            dataType: 'json',
            error: function() {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    $(".sku").empty();
                    if(result.data.length > 0) {
                        var html = "";
                        html +='<table class="layui-table" lay-size="sm">';
                        html +='        <thead>';
                        html +='         <tr>';
                        html +='           <td>规格</td>';
                        html +='          <td>价格</td>';
                        html +='           <td>库存</td>';
                        html +='      </tr>';
                        html +='      </thead>';
                        html +='       <tbody>';
                        $.each(result.data, function (index, item) {
                            html +='       <tr>';
                            html +='           <td>';
                            html +='               <input type="hidden" name="sku['+index+'][id]" value="'+item.item_id+'">';
                            html +='               <input type="hidden" name="sku['+index+'][key]" value="'+item.key+'">';
                            html +='               <input type="text" name="sku['+index+'][key_name]" class="layui-input" value="'+item.key_name+'" readonly>'
                            html +='           </td>';
                            html +='          <td>';
                            html +='               <div class="layui-form-item" style="margin-bottom:0px;">';
                            html +='                  <div class="layui-input-inline">';
                            html +='                      <input type="text" name="sku['+index+'][price]" class="layui-input" style="width:100px;"  value="'+item.price+'" placeholder="价格">';
                            html +='                   </div>';
                            html +='             </div>';
                            html +='           </td>';
                            html +='           <td>';
                            html +='               <div class="layui-form-item">'
                            html +='                   <div class="layui-input-inline">';
                            html +='                      <input type="text" name="sku['+index+'][store_count]" value="'+item.store_count+'" style="width:100px;" class="layui-input" placeholder="库存">';
                            html +='                   </div>';
                            html +='               </div>';
                            html +='            </td>';
                            html +='       </tr>';
                        });
                        html +='       </tbody>';
                        html +='   </table>';
                        $(".sku").append(html);
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });
    }
    //加载规格
    function loadSpec(goods_type,goods_id=0) {
        $.ajax({
            url: ajax_url,
            type: 'post',
            data: {goods_type:goods_type,goods_id:goods_id},
            async: false,
            dataType: 'json',
            error: function() {
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
                            html += '<input type="hidden" name="spec['+value.id+'][name]" value="'+value.name+'"  placeholder="规格" lay-verify="required" autocomplete="off" class="layui-input" style="width:100px; float: left;">';
                            html += '<div class="layui-form-mid layui-word-aux"><span style="font-weight: bolder;font-size:18px; ">'+value.name+'</span></div>';
                            html += '           </div>';
                            html += '           <div class="son" style="margin-left:60px;margin-top:-24px;">';

                            if(value.specItem.length >0){
                                $.each(value.specItem, function (index2, value2) {
                                    html += '<div class="layui-inline" style="margin-bottom:-10px;margin-right: 35px">';
                                    html += '<input type="checkbox" class="combine" name="specItem['+value.id+']['+value2.id+'][id]" style="display: block;width: 16px;height: 16px;line-height: 16px;position: relative;right: 15px;top: 27px;"'+(value2.is_check?"checked":"")+'>';
                                    html += '<input type="hidden"  name="specItem['+value.id+']['+value2.id+'][name]" placeholder="规格" lay-verify="required" autocomplete="off" value="'+value2.item+'" class="layui-input" style="width:100px; float: left;margin-right: 32px;">';
                                    html += '<div class="layui-form-mid layui-word-aux"><span style="font-size:16px;">'+value2.item+'</span></div>';
                                    html += '</div>';
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

    //组合sku
    $(document).on('click', '.combine', function() {
        skuCombine(ajax_sku)
    });

    //组合sku
    function skuCombine(ajax_sku){
        $.ajax({
            url: ajax_sku+"?goods_id="+goods_id,
            type: 'post',
            data: $("#create-form").serialize(),
            async: false,
            dataType: 'json',
            error: function() {
                showErrorMsg('网络繁忙');
            },
            success: function(result) {
                if ( result.code == 1 ) {
                    $(".sku").empty();
                    if(result.data.length > 0) {
                        var html = "";
                        html +='<table class="layui-table" lay-size="sm">';
                        html +='        <thead>';
                        html +='         <tr>';
                        html +='           <td>规格</td>';
                        html +='          <td>价格</td>';
                        html +='           <td>库存</td>';
                        html +='      </tr>';
                        html +='      </thead>';
                        html +='       <tbody>';
                        $.each(result.data, function (index, item) {
                            html +='       <tr>';
                            html +='           <td>';
                            html +='               <input type="hidden" name="sku['+index+'][id]" value="">';
                            html +='               <input type="hidden" name="sku['+index+'][key]" value="'+item.key+'">';
                            html +='               <input type="text" name="sku['+index+'][key_name]" class="layui-input" value="'+item.key_name+'" readonly>'
                            html +='           </td>';
                            html +='          <td>';
                            html +='               <div class="layui-form-item" style="margin-bottom:0px;">';
                            html +='                  <div class="layui-input-inline">';
                            html +='                      <input type="text" name="sku['+index+'][price]" class="layui-input" style="width:100px;"  value="'+item.price+'" placeholder="价格">';
                            html +='                   </div>';
                            html +='             </div>';
                            html +='           </td>';
                            html +='           <td>';
                            html +='               <div class="layui-form-item">'
                            html +='                   <div class="layui-input-inline">';
                            html +='                      <input type="text" name="sku['+index+'][store_count]" style="width:100px;" class="layui-input" placeholder="库存" value="0">';
                            html +='                   </div>';
                            html +='               </div>';
                            html +='            </td>';
                            html +='       </tr>';
                        });
                        html +='       </tbody>';
                        html +='   </table>';
                        $(".sku").append(html);
                    }
                } else {
                    showErrorMsg(result.msg);
                }
            }
        });
    }

    $(document).on('click', '.addspec', function() {
        var title = "编辑/添加规格";
        var url = edit_spec_url+"?id="+type_id;
        layer.open({
            type: 2,
            title: title,
            content: url,
            area: ['80%', '80%'],
            offset:'t',
            scrollbar: true,
            btn: ['全部关闭'],
            yes: function(){
                layer.closeAll();
            },
        });
    });

});