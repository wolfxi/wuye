<?php

class PropretyAction extends CommonAction {

	private $forum_name=null;

	private $xiaoqu_id=null;

	/**
	 * 公共的信息查询分配到界面
	 */
	public function _initialize(){
		parent::_initialize();

		//获取当前版块
		$forum_list=C("FORUM_LIST");
		$this->forum_name=$forum_list['WY_PROPERTY'];

		$this->assign("house_role",C("HOUSE_ROLE"));


		//分配出信息发布的所有种类

		//查找出登录人员属于的小区
		$at=M("Account");

		$user=$at->where("uid = ".is_login())->find();

		$this->xiaoqu_id=$user['type_xiaoqu'];
	}




	/**
	 * 物业列表
	 */
	public function index(){
		$py = M('property');
		import('ORG.Util.Page');// 导入分页类
		$count      = $py->where('xiaoqu_id = %d',$this->xiaoqu_id)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$data['xiaoqu_id']=$this->xiaoqu_id;
		$number=trim($_POST['number']);
		if($_POST['number']){
			$data['number']=array("Like","%{$number}%");
		}
	
		$list = $py->where($data)->limit($Page->firstRow.','.$Page->listRows)->select();

		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display(); // 输出模板
	}

	/**
	 * 新建物业UI
	 */
	public function addui(){
		$this->display();
	}

	/**
	 * 删除一条信息
	 */
	public function delete_message11(){
		$id=$_POST['id'];
		if(!empty($id)){
			$me=M("property");
			$flag=$me->where("id = %d",$id)->delete();
			if($flag){
				$this->success("操作成功！！");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("选择要删除的信息");
		}

	}

		/**
	 * 删除多条信息
	 */
	public function delete_more_message1(){
		$ids=$_POST['id'];
		$ids=explode(',',$ids);
		$me=M("property");
		$where['id']=array("IN",$ids);
		//$data['is_delete']=1;
		$flag=$me->where($where)->delete();


		if($flag){
			//$message['flag']=1
			$this->ajaxReturn(array("status"=>1,"msg"=>"操作成功"),"JSON");
		}else{
			//$message['flag']=0;
			//$message['msg']="操作失败！！！";
			$this->ajaxReturn(array("msg"=>"操作失败"),"JSON");
		}
		//$this->ajaxReturn($message);
	}

/**
	 * 搜索信息   根据标题
	 */
	public function search_title(){
		$searchstr=trim($_POST['search']);
		if(!empty($searchstr)){
			$me=M("message");
			$where['title']=array("like","%".$searchstr."%");
			$where['aid']=array("eq",$_GET['aid']);
			$result=$me->where($where)->select();
			if($result && is_array($result)){
				$this->assign("list",$result);
				$this->assign("searchstr","\"".$searchstr."\"搜索结果如下");
				$this->display("search");
			}else{
				$this->error("搜索内容不存在");
			}
		}else{
			$this->error("请填写要搜索的内容");
		}
		$this->display("one_messagetype_list");
	}



	/**
	 * 新建物业
	 */
	public function add(){
		$data['number']=$_POST['number'];
		$data['auth']=$_POST['auth'];
		$data['status']=$_POST['status'];
		$data['title']=$_POST['title'];
		$data['yname']=$_POST['yname'];
		$data['yphone']=$_POST['yphone'];
		$data['zname']=$_POST['zname'];
		$data['zphone']=$_POST['zphone'] ? $_POST['zphone'] : 0;
		$data['content']=$_POST['content'] ? $_POST['content'] : "";
		$data['remark']=$_POST['remark'] ? $_POST['remark'] : "";
		if(!empty($data['number']) && !empty($data['auth'])){
			$py=M("property");
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$flag=$py->data($data)->add();
			if($flag){
				$this->success("添加成功！！！");
			}else{
				$this->error("操作失败！！！");
			}
		}else{
			$this->error("请填写相关信息！！！");
		}
	}


	/*
	 * 修改物业界面
	 */
	public function update_propertyui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$py=M('property');
			$result=$py->where("id = %d",intval($id))->find();
			if($result){
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("读取信息失败！！！");
			}

		}else{
			$this->error("选择要修改的物业");
		}


	}

	/**
	 *修改物业
	 */
	public function update_property(){
		$data['auth']=$_POST['auth'];
		$data['status']=$_POST['status'];
		$data['title']=$_POST['title'];
		$data['yname']=$_POST['yname'];
		$data['yphone']=$_POST['yphone'];
		$data['zname']=$_POST['zname'];
		$data['zphone']=$_POST['zphone'] ? $_POST['zphone'] : 0;
		$data['content']=$_POST['content'] ? $_POST['content'] : "";
		$data['remark']=$_POST['remark'] ? $_POST['remark'] : "";
		if(!empty($_POST['id']) && !empty($data['auth'])){
			$py=M("property");
			$flag=$py->where("id = %d",$_POST['id'])->data($data)->save();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败！！！");
			}
		}else{
			$this->error("请填写相关信息！！！");
		}


	}

	/**
	 * 删除物业信息
	 */
	public function delete_property(){
		if(IS_AJAX){

			$id=$_POST['id'];
			if(!empty($id)){
				$py=M("property");
				$flag=$py->where("id = %d",$id)->delete();
				if($flag){
					$data['flag']=1;
					$data['msg']="操作成功";
				}else{
					$data['flag']=0;
					$data['msg']="操作失败";
				}
				$this->ajaxReturn($data);
			}else{
				exit();
			}

		}else{
			exit();
		}
	}


	/**
	 * 我的物业设置UI
	 */
	public function myconfigui(){
		$mt=M("message_type");
		$forumlist=C("FORUM_LIST");
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=array("eq",$forumlist['WY_PROPERTY_AUTH']);
		$result=$mt->where($where)->find();
		$this->assign("result",$result);
		$this->display();
	}

	/**
	 * 修改我的物业设置
	 */
	public function myconfig(){
		$data['introduce']=$_POST['property_info'];//认证码获取说明

		$mt=M("message_type");
		if(!empty($_POST['id'])){
			//修改操作
			$flag=$mt->where("id=%d",$_POST['id'])->data($data)->save();

			if($flag){
			$this->success("编辑成功！！！");
			}else{
				$this->error("编辑失败！！");
			}
		}else{
			//添加操作
			$forumlist=C("FORUM_LIST");
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$data['aflag']=$forumlist['WY_PROPERTY_AUTH'];
			$flag=$mt->data($data)->add();
			if($flag){
			$this->success("操作成功！！！");
			}else{
				$this->error("操作失败！！");
			}
		}
		

	}


	/**
		 * 物业信息分类列表
		 */
		public function models(){
				$forumlist=C("FORUM_LIST");
				$mt=M("MessageType");
				$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
				$where['aflag']=array("eq",$forumlist['WY_PROPERTY']);
				$result=$mt->where($where)->order("sort")->select();
				$this->assign("result",$result);
				$this->display();
		}

	/**
	 * 移动信息分类排序
	 */
	public function move_message_type(){
		$moveid=$_POST["id"];
		$action=$_POST["action"];
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=array("eq",$forumlist['WY_PROPERTY']);
		$move=new MoveAction();
		$flag=$move->move("message_type",$moveid,"id",$where,"sort",$action);
		if($flag==1){
			$this->ajaxReturn(array("flag"=>1,"msg"=>"移动成功"),"JSON");
		}else{
			$msg="";
			switch ($flag) {
				case 0:
					$msg="移动失败";
					break;
				case 2:
					$msg="已经是第一个";
					break;
				case 3:
					$msg="已经是最后一个";
					break;
				case 4:
					$msg="请选择要如何操作";
					break;

				default:
					$msg="移动失败";
					break;
			}
			$this->ajaxReturn(array("flag"=>0,"msg"=>$msg),"JSON");
		}
	}


	/**
	 * 物业信息分类列表
	 */
	public function wy_type(){
		$forumlist=C("FORUM_LIST");
		$mt=M("message_type");
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=array("eq",$forumlist['WY_PROPERTY']);
		$result=$mt->where($where)->order("sort")->select();
		$this->assign("result",$result);
		$this->display();
	}

	/**
	 * 新建物业信息模型UI
	 */
	public function create_message_typeui(){
		$this->display();
	}


	/**
	 * 新建物业信息模型
	 */
	public function create_message_type(){
		$forumlist=C("FORUM_LIST");
		$data['name']=$_POST['name'];//物业信息名称
		$data['keyworks']=$_POST['keyword'];//物业状态属性
		$data['introduce']=$_POST['introduce'] ? $_POST['introduce'] : "";//备注
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['is_shortcut']=$_POST['shortcut'] ? 1 : 0;
		if(!empty($data['name']) && !empty($data['keyworks'])){
			$mt=M("message_type");
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$data['aflag']=$forumlist['WY_PROPERTY'];
			$data['sort']=1000;
			$flag=$mt->data($data)->add();

			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败！！！");
			}

		}else{
			$this->error("请填写相关信息");
		}
	}


	/**
	 * 修改分类信息UI
	 *
	 */
	public function update_messagetypeui(){

		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$result=$mt->where("id = %d",intval($id))->find();
			if($result){
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("读取信息失败！！！");
			}
		}else{
			$this->error("请选择要修改的分类");
		}

	}

	/**
	 * 修改分类信息
	 */
	public function update_messagetype(){

		$data['name']=$_POST['name'];
		$data['keyworks']=$_POST['keyword'];
		$data['introduce']=$_POST['introduce'] ? $_POST['introduce'] : "";
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['is_shortcut']=$_POST['shortcut'] ? 1 : 0;
		if(!empty($_POST['id']) && !empty($data['name']) && !empty($data['keyworks'])){
			$mt=M("message_type");
			$flag=$mt->where("id = %d",$_POST['id'])->data($data)->save();
			$this->success("操作成功！！！");	
		}else{
			$this->error("请填写相关信息！！！");
		}
	}

	/**
	 * 删除一个信息分类
	 */
	public function delete_messagetype1(){
		$id=$_POST['id'];	
		if(!empty($id)){
			$mm=M();
			$mm->startTrans(); 
			$flag=$mm->table("wy_message_type")->where("id = %d",$id)->delete();
			$flag1=$mm->table("wy_message")->where("aid = %d",$id)->delete();
			if($flag){
				$mm->commit();
				$data['flag']=1;
				$data['msg']="操作成功！！！";
			}else{
				$data['flag']=0;
				$data['msg']="操作失败！！！";
				$mm->rollback();
			}
			$this->ajaxReturn($data);
		}else{
			$this->error("请选择要操作的分类");
		}

	}

	/**
	 * 修改我的物也设置界面
	 */
	public function configs(){
		//读取其配置信息
		$where['xiaoqu_id']=$this->xiaoqu_id;
		$where['aflag']="proprety_self";
		$mt=M("message_type");
		$result=$mt->where($where)->find();
		$this->assign("result",$result);
		$this->display();

	}

	/**
	 *  保存修改配置---我的物业信息  图文
	 *           
	 */
	public function save_configs(){

		$mt=M("message_type");
		$data['keyworks']=$_POST['keywords'];//微信关键字
		$data['introduce']=$_POST['introduce'];//回复标题
		$data['details_info']=$_POST['details_info'];//回复内容
		$data['image']=$_POST['image'];
		if(isset($_POST['id'])){
			//若有记录就修改
			if(!empty($_POST['id'])){
				$flag=$mt->where("id = %d ",intval($_POST['id']))->data($data)->save();
				$this->success("操作成功!!!");
			}else{
				$this->error("操作失败！！！");
			}
		}else{
			//没有记录就添加
			$data['aflag']="proprety_self";
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$flag=$mt->data($data)->add();
			$this->success("操作成功！！！");
		}

	}

	/**
	 * 一个分类下的信息字段列表
	 */
	public function modelstype(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",intval($id))->find();

			$fm=M("form");
			$result=$fm->where("aid = %d",intval($id))->order("sort , id desc")->select();

			$this->assign("aresult",$aresult);
			$this->assign("result",$result);
			$this->display();

		}else{
			$this->error("请选择要查看的信息字段");
		}
	}

	public function move_form(){
        $moveid=$_POST["id"];
        $action=$_POST["action"];
        $where['aid']=array("EQ",$_GET['aid']);
        $where['is_delete']=array("EQ",0);
        $move=new MoveAction();
        $flag=$move->move("form",$moveid,"id",$where,"sort",$action);
        if($flag==1){
            $this->ajaxReturn(array("flag"=>1,"msg"=>"移动成功"),"JSON");
        }else{
            $msg="";
            switch ($flag) {
                case 0:
                    $msg="移动失败";
                    break;
                case 2:
                    $msg="已经是第一个";
                    break;
                case 3:
                    $msg="已经是最后一个";
                    break;
                case 4:
                    $msg="请选择要如何操作";
                    break;

                default:
                    $msg="移动失败";
                    break;
            }
            $this->ajaxReturn(array("flag"=>0,"msg"=>$msg),"JSON");
        }

    }


	/**
	 * 添加信息字段ui
	 */
	public function modelstype_addui(){

		$aid=$_GET['aid'];
		if(!empty($aid)){
			$mt=M(message_type);
			$aresult=$mt->where("id = %d",$aid)->find();
			$this->assign("aresult",$aresult);
			$this->display();
		}else{
			$this->error("选择要操作的类别");
		}
	}

	/**
	 *添加字段信息
	 */
	public function modelstype_add(){
		$data['aid']=$_POST['aid'];
		$data['name']=$_POST['name'];//字段名称
		$data['introduce']=$_POST['introduce'] ? $_POST['introduce'] : "";//字段说明
		$data['is_display']=$_POST['is_display'] ? 1 : 0;//是否列表显示
		$data['type']=$_POST['is_number'] ? "数字" : "字符" ;//是否是数字类型
		if(!empty($data['aid']) && !empty($data['name'])){
			$fm=M("form");
			$data['sort']=1000;
			$data['is_delete']=0;
			$flag=$fm->data($data)->add();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败！！！");
			}
		}else{
			$this->error("请填写相关信息！！！");	
		}

	}



	/**
	 * 修改字段信息UI
	 */
	public function update_modelstypeui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$fm=M("form");
			$result=$fm->where("id = %d",$id)->find();
			$this->assign("result",$result);
			$this->display();
		}else{
			$this->error("请选择要编辑的字段");
		}
	}

	/**
	 * 修改字段信息
	 */
	public function update_modelstype(){
		$data['name']=$_POST['name'];
		$data['introduce']=$_POST['introduce'] ? $_POST['introduce'] : "";
		$data['is_display']=$_POST['is_display'] ? 1 : 0;
		$data['type']=$_POST['is_number'] ? "数字" : "字符" ;
		if(!empty($_POST['id']) && !empty($data['name'])){
			$fm=M("form");
			$falg=$fm->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("已经保存！！！");
			}
		}else{
			$this->error("请填写相关信息！！！");	
		}
	}

	/**
	 * 删除字段信息
	 */
	public function delete_modelstype(){
		if(IS_AJAX && !empty($_POST['id'])){
			$fm=M("form");
			$flag=$fm->where("id = %d",$_POST['id'])->delete();
			if($flag){
				$data['flag']=1;
				$data['msg']="操作成功";
			}else{
				$data['flag']=0;
				$data['msg']="操作失败";
			}
			$this->ajaxReturn($data);
		}else{
			exit();
		}
	}

	/**
	 * 删除多条信息
	 */
	public function delete_more_message(){
		$ids=$_POST['id'];
		$ids=explode(',',$ids);
		$me=M("message");
		$where['id']=array("IN",$ids);
		$flag=$me->where($where)->delete();
		if($flag){
			$message['flag']=1;
		}else{
			$message['flag']=0;
			$message['msg']="操作失败！！！";
		}
		$this->ajaxReturn($message);
	}








	/**
	 * 一个分类下的信息
	 */
	public function one_messagetype_list(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",$id)->find();
			$aresult['keyworks']=explode(",",$aresult['keyworks']);
			$this->assign("aresult",$aresult);

			$fm=M("form");
			$fresult=$fm->where("aid = %d",$id)->select();
			$this->assign("fresult",$fresult);

			$me=M("Message");
			import('ORG.Util.Page');// 导入分页类
			$count      = $me->where('aid = %d',$id)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			//			进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $me->where('aid = %d',$id)->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=> $one){
				$list[$key]['form_content']=unserialize($one['form_content']);
			}
			$this->assign('list',$list);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$this->display(); // 输出模板
		}else{
			$this->error("请选择一个分类下的信息！！！");
		}
	}



	/**
	 * 新建信息ui
	 */
	public function add_messageui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",$id)->find();
			//状态选项--->如：已交，未交
			$aresult['keyworks']=explode(",",$aresult['keyworks']);
			$this->assign("aresult",$aresult);

			$fm=M("form");
			$result=$fm->where("aid = %d",$id)->order("sort")->select();
			$this->assign("result",$result);
			$this->display();

		}else{
			$this->error("请先选择分类");
		}
	}

	/**
	 * 新建信息
	 */
	public function add_message(){
		$data['title']=trim($_POST['title']);//物业编号
		$data['is_status']=$_POST['status'] ? 1 : 0;//设置是否吸纳是
		$data['assess']=$_POST['access'];//状态--》如：已交，未交
		$data['aid']=$_POST['aid'];
		$data['sort']=$_POST['sort'];
		$data['form_content']=serialize($_POST['form']);
		$data['post_time']=date("Y-m-d :H:i:s");//显示创建时间
		if(!empty($data['title']) && !empty($data['aid'])){
			//检测物业编号是否存在
			$py=M("property");
			$where['number']=array("eq",trim($_POST['title']));
			$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
			$presult=$py->where($where)->find();
			if(!$presult && !is_array($presult)){
				$this->error("物业编号不存在！！！");
			}
			//检测值类型是否与设定的值一样
			$fm=M("form");
			$fresult=$fm->where("aid = %d",$data['aid'])->select();
			$form=$_POST['form'];
			foreach($fresult as $one){
				if($one['type']=="数字" && !empty($form[$one['id']])){
					if(!is_numeric($form[$one['id']])){
						$this->error($one['name']."应为数字");	
					}	
				}	
			}
			//插入数据库
			$me=M("message");
			$flag=$me->data($data)->add();
			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
		}

	}


	/**
	 * 修改信息UI
	 */
	public function update_messageui(){
	
		$id=$_GET['id'];
		if(!empty($id)){
			$me=M("message");
			$result=$me->where("id = %d",$id)->find();
			$result['form_content']=unserialize($result['form_content']);
			$this->assign("result",$result);

			$fm=M("form");
			$fresult=$fm->where("aid = %d",$result['aid'])->select();
			$this->assign("fresult",$fresult);

			$mt=M("message_type");
			$aresult=$mt->where("id = %d",$result['aid'])->find();
			$aresult['keyworks']=explode(",",$aresult['keyworks']);
			$this->assign("aresult",$aresult);

			$this->display();
		
		}else{
			$this->error("请选择要操作的信息！！！");
		}
	}

	/**
	 * 修改信息
	 */
	public function update_message(){
	
		$data['title']=trim($_POST['title']);
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['assess']=$_POST['access'];
		$data['sort']=$_POST['sort'];
		$data['form_content']=serialize($_POST['form']);
		$data['post_time']=date("Y-m-d :H:i:s");
		if(!empty($data['title']) && !empty($_POST['id']) && !empty($_POST['aid'])){
			//检测物业编号是否存在
			$py=M("property");
			$where['number']=array("eq",trim($_POST['title']));
			$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
			$presult=$py->where($where)->find();
			if(!$presult && !is_array($presult)){
				$this->error("物业编号不存在！！！");
			}
			//检测值类型是否与设定的值一样
			$fm=M("form");
			$fresult=$fm->where("aid = %d",$_POST['aid'])->select();
			$form=$_POST['form'];
			foreach($fresult as $one){
				if($one['type']=="数字" && !empty($form[$one['id']])){
					if(!is_numeric($form[$one['id']])){
						$this->error($one['name']."应为数字");	
					}	
				}	
			}
			//更新数据库
			$me=M("message");
			$flag=$me->where("id =%d",$_POST['id'])->data($data)->save();
			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
		}
	
	}



	/**
	 * 删除一条信息
	 */
	public function delete_message1(){
		if(IS_AJAX && !empty($_POST['id'])){
			$me=M("message");
			$flag=$me->where("id = %d",$_POST['id'])->delete();
			if($flag){
				$data['flag']=1;
				$data['msg']="操作成功";
			}else{
				$data['flag']=0;
				$data['msg']="操作失败";
			}
			$this->ajaxReturn($data);
		}else{
			exit();
		}
	}



	/*==========================================excel导入导出=======================================*/

	//导入物业房号数据  UI
	public function import_wuyeui(){
		$this->display();

	}

	//导入物业房号数据 
	public function import_wuye(){
		$result=M("Property")->where("xiaoqu_id=%d",session('WY_ADMIN_XID'))->delete();
		$uploadfile=$_FILES["file"]['tmp_name'];
		if(is_uploaded_file($uploadfile)){
			Vendor('PhpExcel.PHPExcel');
			Vendor('PHPExcel.IOFactory');
			Vendor('PHPExcel.Reader.Excel5');

			$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
			$objPHPExcel = $objReader->load($uploadfile);


			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = 0; // 取得总行数
			$highestColumn = $sheet->getHighestColumn(); // 取得总列数

			$start=1;//从哪一行开始录入/删除

			if($_POST['diyihang']){
				//忽略第一行
				$highestRow = $sheet->getHighestRow(); // 取得总行数
				$start=2;
			}else{
				$highestRow= ($sheet->getHighestRow()); // 取得总行数
				
			}

			$data=array();


			for($start;$start<=$highestRow;$start++){ 
		     	//读取单元格
		  		$temp['number']=$objPHPExcel->getActiveSheet()->getCell("A".$start)->getValue();
		  		$temp['auth']=$objPHPExcel->getActiveSheet()->getCell("B".$start)->getValue();
		  		
				//物业编号，认证码为空 不要
				if(empty($temp['number']) || empty($temp['auth'])){
					continue;
				}

				$temp['xiaoqu_id']= session('WY_ADMIN_XID');

				//重复的不要
				$haved=M("Property")->where($temp)->find();
				if($haved){
					continue;
				}

				$temp['title']=$objPHPExcel->getActiveSheet()->getCell("C".$start)->getValue();
		  		$temp['yname']=$objPHPExcel->getActiveSheet()->getCell("D".$start)->getValue();
		  		$temp['yphone']=$objPHPExcel->getActiveSheet()->getCell("E".$start)->getValue();
		  		$temp['zname']=$objPHPExcel->getActiveSheet()->getCell("F".$start)->getValue();
				$temp['zphone']=$objPHPExcel->getActiveSheet()->getCell("G".$start)->getValue();
				$temp['status']=$objPHPExcel->getActiveSheet()->getCell("H".$start)->getValue();
				$temp['content']=$objPHPExcel->getActiveSheet()->getCell("I".$start)->getValue();
				$temp['remark']=$objPHPExcel->getActiveSheet()->getCell("J".$start)->getValue();

				array_push($data,$temp);
				unset($temp);
			}
			if($_POST['update_delete']){
				$flag=M("Property")->addAll($data);
				if($flag){
					$this->success("导入成功");
				}else{
					$this->error("导入失败");
				}

			}else{
				$delete=array();
				foreach ($data as $key => $value) {
					array_push($delete, $value['number']);
				}
				$where['number']=array("IN",$delete);
				$flag2=M("Property")->where($where)->delete();
				if($flag2){
					$this->success("操作成功");
				}else{
					$this->error("操作失败");
				}
			}
			

		}else{
			$this->error("文件上传失败");
		}

	}

	
	//导出物业房号数据 
	public function export_wuye(){

		$xiaoqu_id=M("Account")->where("uid= %d",is_login())->getField("type_xiaoqu");//小区编号
		$result=M("Property")->where("xiaoqu_id = %d",$xiaoqu_id)->select();//物业信息

		//if($result && is_array($result)){
			Vendor('PhpExcel.PHPExcel');
			Vendor("PhpExcel.PHPExcel.Style");
			Vendor("PHPExcel.PHPExcel.Style.Alignment");
			Vendor("PHPExcel.PHPExcel.Style.Fill");

			$objPHPExcel= new \PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
				->setLastModifiedBy("Maarten Balliauw")
				->setTitle("Office 2007 XLSX Test Document")
				->setSubject("Office 2007 XLSX Test Document")
				->setDescription("This document for Office 2007 XLSX.")
				->setKeywords("office 2007 ")
				->setCategory("office 2007");
			
			//设置默认行高
			$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);



			//设置列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth('12');
			


			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A1","物业编号")
				->setCellValue("B1","认证码")
				->setCellValue("C1","物业名称")
				->setCellValue("D1","业主姓名")
				->setCellValue("E1","业主电话")
				->setCellValue("F1","租客姓名")
				->setCellValue("G1","租客电话")
				->setCellValue("H1","状态")
				->setCellValue("I1","物业信息")
				->setCellValue("J1","备注");

			//填充数据
			$counter=2;
			foreach($result as $one){

				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A".$counter,$one['number'])
					->setCellValue("B".$counter,$one['auth'])
					->setCellValue("C".$counter,$one['title'])
					->setCellValue("D".$counter,$one['yname'])
					->setCellValue("E".$counter,$one['yphone'])
					->setCellValue("F".$counter,$one['zname'])
					->setCellValue("G".$counter,$one['zphone'])
					->setCellValue("H".$counter,$one['status'])
					->setCellValue("I".$counter,$one['content'])
					->setCellValue("J".$counter,$one['remark']);

				$counter++;
			}
			$objPHPExcel->getActiveSheet()->setTitle("物业列表");
			$filename="物业列表.xls";
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); //文件通过浏览器下载

		/*}else{
			$this->error("操作失败");
		}*/
	}




	//导出物业信息表
	public function export_models(){

		if(!isset($_GET['id'])){
			$this->error("请选择要导出的数据分类");
		}

		$id=$_GET["id"];

		$mt=M("message_type");
		$aresult=$mt->where("id = %d",$id)->find();
		$aresult['keyworks']=explode(",",$aresult['keyworks']);

		$fm=M("form");
		$fresult=$fm->where("aid = %d",$id)->select();

		$list = M("Message")->where('aid = %d',$id)->select();
		foreach($list as $key=> $one){
			$list[$key]['form_content']=unserialize($one['form_content']);
		}

		//if($list && is_array($list)){
			Vendor('PhpExcel.PHPExcel');
			Vendor("PhpExcel.PHPExcel.Style");
			Vendor("PHPExcel.PHPExcel.Style.Alignment");
			Vendor("PHPExcel.PHPExcel.Style.Fill");

			$objPHPExcel= new \PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
				->setLastModifiedBy("Maarten Balliauw")
				->setTitle("Office 2007 XLSX Test Document")
				->setSubject("Office 2007 XLSX Test Document")
				->setDescription("This document for Office 2007 XLSX.")
				->setKeywords("office 2007 ")
				->setCategory("office 2007");
			
			//设置默认行高
			$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);



			//设置列宽
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('12');
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('12');

			$coll0=ord("F");
			foreach ($fresult as $key => $value) {
				$objPHPExcel->getActiveSheet()->getColumnDimension(chr($coll0))->setWidth('12');
				$coll0++;
			}


			

			//设置表头
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A1","序号")
				->setCellValue("B1","物业编号")
				->setCellValue("C1","状态")
				->setCellValue("D1","显示")
				->setCellValue("E1","排序");
			
			$coll1=ord("F");
			foreach ($fresult as $key => $value) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($coll1)."1",$value['name']);
				$coll1++;
			}

				
			//填充数据
			$counter=2;
			foreach($list as $one){

				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A".$counter,$one['id'])
					->setCellValue("B".$counter,$one['title'])
					->setCellValue("C".$counter,$one['access'])
					->setCellValue("D".$counter,$one['is_status'])
					->setCellValue("E".$counter,$one['sort']);
				$coll2=ord("F");
				foreach ($fresult as $key => $value) {
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($coll2).$counter,$one['form_content'][$value['id']]);
					$coll2++;
				}
				$counter++;	
			
			}
			$objPHPExcel->getActiveSheet()->setTitle("物业信息表单");
			$filename="物业信息表单.xls";
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); //文件通过浏览器下载

		/*}else{
			$this->error("操作失败");
		}*/
	}


	//导入物业信息表  UI
	public function import_modelsui(){
		$id=$_GET['id'];

		$fm=M("form");
		$fresult=$fm->where("aid = %d",$id)->order("sort")->select();
		$this->assign("fresult",$fresult);
		$this->assign("aid",$id);

		$this->display();

	}

	//导入物业信息表
	public function import_models(){
		$uploadfile=$_FILES["file"]['tmp_name'];
		if(is_uploaded_file($uploadfile) && $_POST['aid']){
			Vendor('PhpExcel.PHPExcel');
			Vendor('PHPExcel.IOFactory');
			Vendor('PHPExcel.Reader.Excel5');

			$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
			$objPHPExcel = $objReader->load($uploadfile);


			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = 0; // 取得总行数
			$highestColumn = $sheet->getHighestColumn(); // 取得总列数

			$start=1;//从哪一行开始录入/删除

			if($_POST['diyihang']){
				//忽略第一行
				$highestRow = $sheet->getHighestRow(); // 取得总行数
				$start=2;
			}else{
				$highestRow= ($sheet->getHighestRow()); // 取得总行数
			}


			$update=array();
			$delete=array();
			//获取信息字段
			$fresult=M("form")->where("aid = %d",$_POST['aid'])->order("sort")->select();

			for($start;$start<=$highestRow;$start++){ 

		     	//读取单元格
		  		$temp['title']=$objPHPExcel->getActiveSheet()->getCell("B".$start)->getValue();
		  		
				//物业编号，认证码为空 不要
				if(empty($temp['title'])){
					continue;
				}

				$temp['assess']=$objPHPExcel->getActiveSheet()->getCell("C".$start)->getValue();
		  		$temp['is_status']=$objPHPExcel->getActiveSheet()->getCell("D".$start)->getValue();
		  		$temp['sort']=$objPHPExcel->getActiveSheet()->getCell("E".$start)->getValue();
		  		$temp['aid']=$_POST['aid'];
		  		$temp['post_time']=date("Y-m-d :H:i:s");
		  		$coll=ord("F");
		  		foreach($fresult as $key=>$one){
		  			$temp['form_content'][$one['id']]=$objPHPExcel->getActiveSheet()->getCell(chr($coll).$start)->getValue();
		  			$coll++;
		  		}

		  		$temp['form_content']=serialize($temp['form_content']);

		  		if($objPHPExcel->getActiveSheet()->getCell("A".$start)->getValue()){
		  			$temp['id']=$objPHPExcel->getActiveSheet()->getCell("A".$start)->getValue();
		  			array_push($delete, $temp);
		  		}else{
		  			array_push($update, $temp);

		  		}

				
				unset($temp);
				
			}


			if($_POST['update_delete']){
				//添加
				$m=M();
				$m->startTrans();
				$flag0=$m->table(C("DB_PREFIX")."message")->where("aid = %d",$_POST['aid'])->delete();

				$flag1=$m->table(C("DB_PREFIX")."message")->addAll($update);

				//更新
				$flag2=$m->table(C("DB_PREFIX")."message")->save($delete);

				if($flag1 || $flag2){
					$m->commit();
					$this->success("导入成功");
				}else{
					$m->rollback();
					$this->error("导入失败");
				}

			}else{
				$delete_id=array();
				foreach ($delete as $key => $value) {
					array_push($delete_id, $value['id']);
				}
				$where['id']=array("IN",$delete_id);
				$where['aid'] = array("EQ",$_POST['aid']);
				$flag2=M("Message")->where($where)->delete();
				if($flag2){
					$this->success("操作成功");
				}else{
					$this->error("操作失败");
				}
			}
			

		}else{
			$this->error("文件上传失败");
		}

	}


/*===================================excel导入导出end=====================================*/


	//消息管理
	public function msg(){

		$m=M('Msg');
		$m_data['group_xiaoqu']=array(array('eq',$_SESSION['WY_ADMIN_XID']),array('neq',0), 'and');


		if(!empty($_GET['openid'])){
			$_GET['openid']=trim($_GET['openid']);
			$m_data['openid']=$_GET['openid'];
		}else{
			$m_data['reply']=array('neq',1);
		}
		if(!empty($_GET['weixin_name'])){
			$_GET['weixin_name']=trim($_GET['weixin_name']);
			$m_data['name']=array('LIKE',"%$_GET[weixin_name]%");
		}
		import('ORG.Util.Page');// 导入分页类
		$count      = $m->where($m_data)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$this->assign('page',$show);

		$msg=$m->where($m_data)->order('a4 asc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		//echo $m->getlastsql();
		$m->where($m_data)->save(array('a4'=>1));
		//echo $m->getlastsql();
		$this->assign('msg',$msg);

		//聊天
		if(!empty($_GET['openid'])){
			$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];
			$u=M('User');
			$user=$u->where("openid='{$_GET[openid]}' and xiaoqu_id='{$xiaoqu_id}' and status=1")->find();
			$this->assign('user',$user);
		}
		$this->display();
	}
	//关注用户
	public function user(){

		$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];
		//$data['id']=$wid;
		$u=M('User');
		//$data['openidWX']=$id;
		$data['xiaoqu_id']=array('eq',"{$xiaoqu_id}");
		$data['status']=array("eq","1");
		$name=trim($_POST['account']);
		if($_POST['account']){
			 $data['weixin_name']=array('Like',"%{$name}%");
		}
		$m=M('Msg');
		$user=$u->where($data)->select();
		foreach ($user as $k => $v) {
			$user[$k]['hdtime']=$m->where("openid='{$v[openid]}'")->order('addtime desc')->getfield('addtime');
		}
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}

	//关注用户
	public function user_edit(){

		$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];
		$u=M('User');

		if(IS_POST){
			$u_data['uname']=$_POST['uname'];
			$u_data['remark']=$_POST['remark'];
			$u->where("openid='{$_POST[openid]}' and xiaoqu_id='{$xiaoqu_id}' and status=1")->save($u_data);
			$this->ajaxReturn(array('msg'=>'操作成功'),'JSON');
		}
		$user=$u->where("openid='{$_GET[openid]}' and xiaoqu_id='{$xiaoqu_id}' and status=1")->find();
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}

	//关注用户
	public function user_binding(){

		$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];
		//$data['id']=$wid;
		$u=M('User');

		if($_GET['delete']){//删除
			$data['propertyid']=0;
			if($u->where("openid='$_GET[openid]'")->save($data)){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
			exit;
		}
		//$data['openidWX']=$id;
		$data['xiaoqu_id']=array('eq',"{$xiaoqu_id}");
		$data['status']=array("eq","1");
		$data['propertyid']=array('neq',0);
		$name=trim($_POST['account']);
		if($_POST['account']){
			 $data['weixin_name']=array('Like',"%{$name}%");
		}

		$user=$u->alias('u')->where($data)->field('u.*,(SELECT `number` FROM `wy_property` where u.propertyid=id) as wy_number,(SELECT `title` FROM `wy_property` where u.propertyid=id) as wy_title')->select();
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}
	//发送短信


	public function user_send(){

		$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];

		$u=M('User');
		$user=$u->where("openid='{$_GET[openid]}' and xiaoqu_id='{$xiaoqu_id}' and status=1")->find();
		//echo $u->getlastsql();
		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}
	//更新用户微信资料
	public function user_user_updata(){

		$xiaoqu_id=$_SESSION['WY_ADMIN_XID'];

		$u=M('User');
		$user=$u->where("openid='{$_POST[openid]}' and xiaoqu_id='{$xiaoqu_id}' and status=1")->find();

		import('COM.ThinkWechat');
		$uid=M('Account')->where('type_xiaoqu='.$xiaoqu_id)->getfield('fid_a2');
		$wid=M('Account')->where('uid='.$uid)->getfield('wechat_id');
		$weixn=M('Wechat')->where("id={$wid}")->find();
		

		C('WECHAT_TOKEN', $weixn['wtoken']);
		C('WECHAT_APPID',$weixn['wappid']);
		C('WECHAT_APPSECRET',$weixn['wsecret']);

		$weixin = new ThinkWechat ();

		if($_POST['sendmsg']){//发送信息

			if(empty($_POST['msg'])){
				$this->ajaxReturn(array('msg'=>'请输入要发送的内容'),'JSON');
			}
			//$this->ajaxReturn(array('msg'=>"更新成功".$weixn['wsecret']),"JSON");
			$msg=$weixin->sendMsg($_POST['msg'],$user['openid']);
			//添加回复信息
			$m=M('Msg');

			$m_data['group_xiaoqu']=$xiaoqu_id;//所属小区
			$m_data['addtime']=time();
			$m_data['content']=$_POST['msg'];
			$m_data['openid']=$user['openid'];
			$m_data['reply']=1;
			$m->add($m_data);

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
