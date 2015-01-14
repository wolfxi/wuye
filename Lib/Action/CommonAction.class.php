<?php

class CommonAction extends Action {


	public function _initialize(){

		if(!is_login()){
			$this->redirect('Public/login');
		}

		
		if(GROUP_NAME!='A1' AND GROUP_NAME!='A3' AND ACTION_NAME!='ajaxupload'){

			if($_SESSION['QUERY_LOGIN']==1 and GROUP_NAME=='A4' ){//如果A2有登陆的话，开放所有权限
				$menu_group=M('AuthGroup')->where("`group`='A4'")->field('title,type')->select();
			}else{
				import('ORG.Util.Auth');//加载类库
				$auth=new Auth();
				$menu_group=$auth->getGroups(is_login());
			}

			foreach ($menu_group as $k=>$v) {
				$menu_group1[]=$v['type'];
				$menu_group1[]=$v['title'];
			}
			//dump($menu_group1);
			$this->assign('menu_group',$menu_group1);

			/*
			if(MODULE_NAME!='Index'){
				import('ORG.Util.Auth');//加载类库
				$auth=new Auth();
				if(!$auth->check(GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME,is_login())){
					//echo GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
					//echo '<script>alert("没有权限[在上级修改]");</script>';
				  	//$this->error('你没有权限');
				}
			}
			*/
		}

		//消息
		if(GROUP_NAME=='A3' OR GROUP_NAME=='A4'){

			$m=M('Msg');
			if(GROUP_NAME=='A3'){
				$m_data['group_wuye']=array(array('eq',$_SESSION['WY_ADMIN_WID']),array('neq',0), 'and');
				$m_data['a3']=0;
			}elseif(GROUP_NAME=='A4'){
				$m_data['group_xiaoqu']=array(array('eq',$_SESSION['WY_ADMIN_XID']),array('neq',0), 'and');
				$m_data['a4']=0;
			}
			$msg_count=$m->where($m_data)->count();
			//echo $m->getlastsql();
			$this->assign('msg_count',$msg_count);

		}
		

		$this->assign("nowdate",date("Y-m-d"));

		//快捷
		$this->shortcut();

	}



	/*
	*快捷
	*/
	protected function shortcut(){
		$mt=M("message_type");
		$forumlist=C("FORUM_LIST");

		//查找出登录人员属于的小区
		$at=M("Account");
		$user=$at->where("uid = ".is_login())->find();
		if($user['group']=="A2"){
			$xiaoqu_id=is_login();
		}else{
			$xiaoqu_id=$user['type_xiaoqu'];
		}
		

		//分配出信息发布的所有种类中有快捷键的种类
		$mt=M("message_type");
		$where['xiaoqu_id']=array("EQ",$xiaoqu_id);
		$where['is_status']=array("EQ",1);
		$where['is_shortcut']=array("EQ",1);

		//信息发布管理模块
		$where['aflag']=$forumlist["WY_INFO"];
		$leftnav['wyinfo']=$mt->where($where)->order("sort,id desc")->select();

		//表单管理模块
		$where['aflag']=$forumlist["WY_FORM"];
		$leftnav["wyform"]=$mt->where($where)->order("sort,id desc")->select();

		//业务互动模块
		$where['aflag']=$forumlist["WY_BBS"];
		$leftnav["wybbs"]=$mt->where($where)->order("sort,id desc")->select();

		//广告信息模块
		$where['aflag']=$forumlist["WY_ADS"];
		$leftnav["wyads"]=$mt->where($where)->order("sort,id desc")->select();


		//便民信息模块
		unset($where['xiaoqu_id']);
		$where['aflag']=$forumlist["WY_BM"];
		$where['daili_id']=is_login();
		$leftnav["wybm"]=$mt->where($where)->order("sort,id desc")->select();



		$this->assign("leftnav",$leftnav);


		//forumlist
		$this->assign("forumlist",$forumlist);


	}


	/**返回上传图片的信息
	 * 返回的是一个关联数组
	 * 包含了文件名和文件的路劲               
	 */
	public function upload_img(){
		$file=$_FILES;
		if($file && count($file)>0){
			import('ORG.Net.UploadFile');
			$upload = new UploadFile();// 实例化上传类
			$upload->maxSize  = 3145728  ;// 设置附件上传大小150kb
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->savePath  =     C("UPLOADIMG_DIR");; // 设置附件上传目录
			$upload->rootPath  =     '';
			// 上传文件 
			$result   =   $upload->upload();
			if(!$result) {// 上传错误提示错误信息
				return false;
			}else{// 上传成功 获取上传文件信息
				return $upload->getUploadFileInfo();
			}
		}else{
			$file=array();
			return $file;
		}

	}


	/**
	 * ajax 图片上传 
	 */
	public function ajaxupload(){
		if(IS_POST){
			$fileresult=$this->upload_img();
			if($fileresult && count($fileresult)>0){
				$message['flag']=1;
				$message['msg']=$fileresult[0]['savepath'].$fileresult[0]['savename'];
			}else{
				$message['flag']=0;
				$message['msg']="图片上传失败！！！";
			}
			$this->ajaxReturn($message);	
		}else{
			exit();
		}
	}

	/**
	*ajax error 
	*/
	public function ajaxerror($msg){
		if(IS_AJAX){
			$data['flag']=0;
			$data['msg']=$msg;
			$this->ajaxReturn($data);
		}else{
			exit();
		}
	}

	/**
	*ajax success
	*/
	public function ajaxsuccess($msg){
		if(IS_AJAX){
			$data['flag']=1;
			$data['msg']=$msg;
			$this->ajaxReturn($data);
		}else{
			exit();
		}
	}





}
