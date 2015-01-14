function mmodal(param){
    var html='<div class="modal fade" id="';
    if(param.id){html+=param.id;}else{html+='myModal';}
    html+='" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">';
    if(param.header){
        html+='<div class="modal-header"><h4 class="modal-title" id="myModalLabel">';
        html+='<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        html+=param.header;
        html+='</h4></div>';
    }
    if(param.content){html+='<div class="modal-body">';html+=param.content;html+='</div>';}
    if(param.close!=0 || param.ok==1){
        html+='<div class="modal-footer">'
        if(param.close!=0){
            html+='<button type="button" class="btn" data-dismiss="modal">';
            if(param.closen){html+=param.closen;}else{html+='确定';}
            html+='</button>';
        }
        if(param.ok==1){
            html+='<button type="button" class="btn btn-success" id="mmodal_ajax_ok">';
            if(param.okn){html+=param.okn;}else{html+='确定';}
            html+='</button>';
        }
        html+='</div>';
        }
    html+='</div></div></div>';
    $(html).modal();
}

function ajaxuploadimage(id,url,app){
    $.ajaxFileUpload({
        secureuri:false,
        dataType: "json",//返回json格式的数据  
        url:url+"/ajaxupload",//要访问的后台地址  
        fileElementId:id,
        success: function(data){
            var flag=data.flag;
            if(flag==1){
                $("#image").val(data.msg);
                $("#uploadimg").attr("src",app+"/"+data.msg);
                return ;    
            }else{
                window.alert(data.msg);
                return ;
            }
        }
    });
}
