/**
 *用于信息上下移动
 *
 *url :操作的链接地址
 *action:上移或下移的标志  UP,DOWN
 *id:要移动该类的信息id值
 */
function move(url,action,id){
    var postdata={id:id,action:action};
    $.post(url,postdata,function(data){
        if(data.flag==1){
            window.location.reload();
        }else if(data.slide==1){
            var append='<div class="alert alert-error"><button class="close" data-dismiss="alert">×</button><strong>提示：</strong>'+data.msg+'</div>';
            $(".widget-content").prepend(append);
        }else{
        	mmodal({content:data.msg});
        }
    },"json");
}



