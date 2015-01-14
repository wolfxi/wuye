<?php

class UserAction extends CommonAction {

	private $formalist=null;

	//获取微信ID
	private function wechat_id(){
		return M('Account')->where('uid='.is_login())->getfield('wechat_id');
	}

	/**
	 * 公共的信息查询分配到界面
	 */
	public function _initialize(){

		parent::_initialize();

		//获取配置中的版块列表
		$this->formalist=C("FORUM_LIST");

		//分配出信息发布的所有种类中有快捷键的种类
		$mt=M("message_type");
	//	$navbarresult=$mt->where("uid = %d AND aflag ='%s' AND is_status= %d AND is_delete =%d AND is_shortcut = %d",session(C("SysUserSessionUid")),C("FORUM_LIST")['WY_INFO'],1,0,1)->order("sort")->select();
	//	$this->assign("navbar",$navbarresult);
	}


	//关注用户
	public function user(){

		$wid=$this->wechat_id();
		//$data['id']=$wid;
		
		$id=M('Wechat')->where("id={$wid}")->getfield('wid');
		$u=M('User');
		//$data['openidWX']=$id;
		$data['openidWX']=array('eq',"{$id}");
		$data['status']=array("eq","1");
		$name=trim($_POST['account']);
		if($_POST['account']){
			 $data['weixin_name']=array('Like',"%{$name}%");
		}
		$user=$u->where($data)->select();
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}



	//发送短信
	public function user_send(){

		$wid=$this->wechat_id();
		$id=M('Wechat')->where("id={$wid}")->getfield('wid');
		$u=M('User');
		$user=$u->where("openid='{$_GET[openid]}' and openidWX='{$id}' and status=1")->find();
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}



	//更新用户微信资料
	public function user_user_updata(){

		$wid=$this->wechat_id();
		$weixn=M('Wechat')->where("id={$wid}")->find();
		$u=M('User');
		$user=$u->where("openid='{$_POST[openid]}' and openidWX='{$weixn[wid]}' and status=1")->find();

		import('COM.ThinkWechat');

		C('WECHAT_TOKEN', $weixn['wtoken']);
		C('WECHAT_APPID',$weixn['wappid']);
		C('WECHAT_APPSECRET',$weixn['wsecret']);

		$weixin = new ThinkWechat ();

		if($_POST['sendmsg']){//发送信息

			if(empty($_POST['msg'])){
				$this->ajaxReturn(array('msg'=>'请输入要发送的内容'),'JSON');
			}
			$msg=$weixin->sendMsg($_POST['msg'],$user['openid']);
			$this->ajaxReturn(json_decode($msg,true),'JSON');
		}
		
		$data=$weixin->user($user['openid']);

		$data_u['status']=$data['subscribe'];
		if($data_u['status']==1){
			$data_u['weixin_name']=$data['nickname'];
			$data_u['weixin_from']=$data['country'].'_'.$data['province'].'_'.$data['city'];
			$data_u['weixin_sex']=$data['sex'];
			$data_u['addtime']=$data['subscribe_time'];
		}
		if($u->where("id={$user[id]}")->save($data_u));

		$this->ajaxReturn(array('msg'=>"更新成功"),"JSON");


	}














}