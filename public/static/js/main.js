var layerLoading;   //load

//图片弹层
function showImg(src) {
    layer.open({
        type: 10,
        title: false,
        closeBtn: 0,
        shade: [0.8, '#FFFFFF'],
        shadeClose: true,
        offset: '50px',
        content: '<img src="' + src + '" width="300">'
    });
}

//取文件的后缀
function getFileSuffix(filename) {
    pos = filename.lastIndexOf('.');
    suffix = '';
    if (pos != -1) {
        suffix = filename.substring(pos);
    }
    return suffix;
}

//上传的图片文件名
function uploadFileName(member_id) {
    str = member_id + Date.parse(new Date()) + Math.random();
    return $.md5(str.toString());
}

loadingFlag = 0;

//loading
function loading() {
    loadingFlag = 1;
    layerLoading = layer.load(0, {shade: 0.8});
}

//取消loading
function unloading() {
    loadingFlag = 0;
    layer.close(layerLoading);
}

//错误提示窗
function showErrorMsg(msg, callBack) {
	msg = msg ? msg : '';
    layer.msg(msg, {icon: 5, shift: 6}, callBack);
}

//正确提示窗
function showSuccessMsg(msg, callBack) {
    layer.msg(msg, {icon: 1, time: 1000}, callBack);
}

//询问框
function showConfirm(msg, callBack) {
    layer.confirm(msg, {
        closeBtn: 0,
        title:'',
        shade: [0.8, '#FFFFFF'],
        btn: ['确定','取消'] //按钮
        }, callBack
    );
}

function showUrl(title,url) {
    $.post(url,{},function(data) {
        var myLayer = window.parent.layer;
        if(!myLayer) {
            myLayer = layer;
        }
        myLayer.open({
            type: 1,
            area: ['800px',"500px"],
            title:title,
            content: data
        });
        },"html");
}

function msg(content) {
    var myLayer = window.parent.layer;
    if(!myLayer) {
        myLayer = layer;
    }
    myLayer.msg(content, {time: 2000});
}

function hide() {

    var myLayer = window.parent.layer;
    if(!myLayer) {
        myLayer = layer;
    }
    myLayer.closeAll();
}

function error(content) {
    var myLayer = window.parent.layer;
    if(!myLayer) {
        myLayer = layer;
    }
    myLayer.msg(content, {time: 2000});
}

function success(content) {
    var myLayer = window.parent.layer;
    if(!myLayer) {
        myLayer = layer;
    }
    myLayer.msg(content, {time: 2000});
}

function tip(content) {
    var myLayer = window.parent.layer;
    if(!myLayer) {
        myLayer = layer;
    }
    myLayer.msg(content, {time: 2000});
}

//确认对话框
function confirm(url,msg) {
    var myLayer = layer;
    myLayer.open({
        content:msg,
        yes:function() {
            window.location.href=url;
            return false;
        }
    });
}

function showDialog(title,msg,callBack) {
    var myLayer = layer;
    myLayer.open({
        title:title,
        content:msg,
        yes:function(index) {
            myLayer.close(index);
            if(callBack) {
                callBack();
            }
            return false;
        }
    });
}


function resizeShowTab() {
    if(window.parent) {
        window.parent.$(".layui-show").find("iframe").load();
    }
    else {
        $(".layui-show").find("iframe").load();
    }
}

/**
 * 时间戳转换日期
 */
function timestampToTime(timestamp) {
    var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    var Y = date.getFullYear() + '-';
    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    var D = (date.getDate()>9 ? date.getDate() : "0" + date.getDate()) + ' ';
    var h = (date.getHours()>9 ? date.getHours() : "0" + date.getHours()) + ':';
    var m = (date.getMinutes()>9 ? date.getMinutes() : "0" + date.getMinutes()) + ':';
    var s = (date.getSeconds()>9 ? date.getSeconds() : "0"+ date.getSeconds());
    return Y+M+D+h+m+s;
}

/**
 * array_keys
 */
function array_keys(jsonObj, index) {
    var arr = [];
    $.each(jsonObj, function(key, val) {
        arr.push(val[index]);
    });
    arr.reverse();
    return arr;
}

/**
 * ajax 请求
 * @param type 请求方法
 * @param url 请求url
 * @param data 请求参数
 * @param buttonObj 点击按钮，点击后让其失效，防止重复点击
 * @param callBack 回调函数
 */
function ajaxRequest(type,url,data,buttonObj,callBack){
    if(buttonObj){
        $(buttonObj).attr("disabled","disabled");
    }

    data = data ? data : '';
    $.ajax({ type: type, url: url, data: data, dataType: "json",
        success: function (res) {
            if(buttonObj){
                $(buttonObj).removeAttr("disabled");
            }
            if( callBack ){//回调
                callBack(res);
            }else{
                if (res.code == 1) {
                    showSuccessMsg(res.msg);
                }else{
                    showErrorMsg(res.msg);
                }
            }
        },
        error: function () {
            if(buttonObj){
                $(buttonObj).removeAttr("disabled");
            }
            layer.msg("请求失败",{time:1000});
        }
    });
}