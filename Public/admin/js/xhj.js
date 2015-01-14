/**
 * debug.cn@gmail.com
**/
$(document).ready(function(){
	$('textarea.autogrow').autogrow();
	// 全选        
	$("#allselect").click(function(){
		$("[name=dels[]]:checkbox").each(function(){
			$(this).prop("checked",true);	
			$.uniform.update();		
		}); 
	});

	//反选
	$("#invert").click(function(){
		$("[name=dels[]]:checkbox").each(function(){
			if ($(this).is(":checked")){
				$(this).prop("checked",false);	
				$.uniform.update();	
			}else{
				$(this).prop("checked",true);	
				$.uniform.update();	
			}
		}); 
	});
	$("#cancel").click(function(){
		$("[name=dels[]]:checkbox").each(function(){
			$(this).prop("checked",false);	
			$.uniform.update();		
		}); 
	});

});
//$('.selectpicker').selectpicker();

function openWxUser(openid){
	$.jBox("iframe:"+APP+"/Home/WxMsg/msg/openid/"+openid, {
		title: "给用户发消息",
		width: 600,
		height: 587,
		buttons:{ '完整信息': true, '关闭窗口': false},
		submit: function (v, h, f) {
		if (v == true) {
			 if(openid.indexOf(",")>0){
				  $.jBox.info('多用户无法进入完整模式');
				  return;
			 }else{
			  window.location.href=APP+"/Home/WxMsg/msglist/openid/"+openid;
			 }
			 return true;
        }
        return true;
		}
	});
}

function openAdminWxUser(openid){
	$.jBox("iframe:"+APP+"/Admin/WxUser/msg/openid/"+openid, {
		title: "给用户发消息",
		width: 600,
		height: 580,
		buttons: { '关闭': true }
	});
}

function openWebModelAds(model){
	$.jBox("iframe:"+APP+"/Home/WebInfo/model_ads/md/"+model, {
		title: "模块幻灯片管理",
		width: 600,
		height: 580,
		buttons: { '关闭': true }
	});
}
function openAdminWebModelAds(model){
	$.jBox("iframe:"+APP+"/Admin/WebInfo/model_ads/md/"+model, {
		title: "模块幻灯片管理",
		width: 600,
		height: 580,
		buttons: { '关闭': true }
	});
}
