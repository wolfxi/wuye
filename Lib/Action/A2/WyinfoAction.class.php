<?php

class WyinfoAction extends CommonAction {

	private $forumlist=null;

	/**
	 * 公共的信息查询分配到界面
	 */
	public function _initialize(){
		parent::_initialize();

		$this->forumlist=C("FORUM_LIST");

		//当前所在分类
		$navid=$_GET['navid'] ? $_GET["navid"] : $_POST["navid"];
		if(empty($navid)){
			$navid=$this->forumlist["WY_INFO"];
		}
		$this->assign("navid",$navid);
	}

	/**
	 *信息分类列表
	 */
	Public function index(){
		$mt=M("message_type");
		$result=$mt->where("xiaoqu_id = %d AND aflag ='%s' AND is_delete = %d ",session(C("SysUserSessionUid")),$this->forumlist['WY_INFO'],0)->order("sort")->select();
		$this->assign("result",$result);
		$this->display();
	}



	/**
	 * 新建信息分类界面
	 */
	Public function create_message_typeui(){
		$this->display();	
	}

	/*
	 * 新建信息分类
	 */
	public function create_message_type(){
		$data['name']=$_POST['name'];
		$data['keyworks']=$_POST['keyword'];
		$data['image']=$_POST['image'];
		$data['introduce']=$_POST['introduce'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['is_shortcut']=$_POST['shortcut'] ? 1 : 0;
		if(!empty($data['name'])){
			$data['xiaoqu_id']=session(C('SysUserSessionUid'));
			$data['aflag']=$this->forumlist['WY_INFO'];
			$data['sort']=1000;
			$mt=M("message_type");
			$flag=$mt->data($data)->add();
			if($flag){
				$this->success("添加成功");
			}else{
				$this->error("添加失败！！！");
			}

		}else{
			$this->error("请填写信息分类名称！");
		}
	}

	

	/**
	 * 一个分类下的信息列表
	 */
	public function one_messagetype_list(){
		$kind_id=$_GET['id'];
		if(!empty($kind_id)){
			$me = M('message'); 
			import('ORG.Util.Page');// 导入分页类
			$count      = $me->where('aid = %d AND is_delete = %d',$kind_id,0)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $me->where('aid= %d AND is_delete = %d ',$kind_id,0)->order('is_top desc,sort desc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);// 赋值数据集
			$this->assign('page',$show);// 赋值分页输出
			$mt=M("message_type");
			$aresult=$mt->where("id = %d ",$kind_id)->find();
			$this->assign("aresult",$aresult);
			$this->display(); // 输出模板	
		}else{
			$this->error("请选择一个分类!!!");
		}
	}

	/**
	 * 删除一个分类
	 */
	public function delete_messagetype1(){
		$id=$_POST['id'];
		if(!empty($id)){
			$m=M();
			$m->startTrans();
			$flag=$m->table(C("DB_PREFIX")."message_type")->where("id = %d",$id)->delete();
			if($flag){
				$result=$m->table(C("DB_PREFIX")."message")->where("aid = %d",$id)->find();
				if($result){
					$flag1=$m->table(C("DB_PREFIX")."message")->where("aid = %d",$id)->delete();
					if($flag1){
						$m->commit();
						$data['flag']=1;
						$data['msg']="操作成功！！！";
					}else{
						$m->rollback();
						$data['flag']=0;
						$data['msg']="操作失败！！！";
					}
				}else{
					$m->commit();
					$data['flag']=1;
					$data['msg']="操作成功！！！";
				}
			}else{
						$m->rollback();
						$data['flag']=0;
						$data['msg']="操作失败！！！";
			}
			
			$this->ajaxReturn($data);
		}else{
			$data['flag']=0;
			$data['msg']="操作失败！！！";
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 删除一条信息
	 */
	public function delete_message1(){
		$id=$_POST['id'];
		if(!empty($id)){
			$me=M("message");
			$flag=$me->where("id = %d",$id)->delete();
			if($flag){
				$this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
			}else{
				$this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
			}
			
		}else{
			exit();
		}

	}

	/**
	 * 修改一个类型的界面
	 */
	public function update_messagetypeui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$result=$mt->where("id = %d",$id)->find();
			if($result && is_array($result)){
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("读取失败");
			}
		}else{
			$this->error("选择要编辑的类别");
		}

	}

	/**
	 * 保存一个类型信息
	 */
	public function update_messagetype(){
		$data['name']=$_POST['name'];
		$data['keyworks']=$_POST['keyword'];
		$data['image']=$_POST['image'];
		$data['introduce']=$_POST['introduce'];
		$data['is_status']=isset($_POST['status']) ? 1 : 0;
		$data['is_shortcut']=isset($_POST['shortcut']) ? 1 : 0;
		if(!empty($data['name'])){
			$mt=M("message_type");
			$flag=$mt->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败！！！");
			}

		}else{
			$this->error("请填写信息分类名称！");
		}
	}

	/**
	 * 修改一条信息
	 */
	public function update_messageui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$me=M("message");
			$result=$me->where("id = %d",$id)->find();
			if($result && is_array($result)){
				$mt=M("message_type");
				$aresult=$mt->where("id = %d ",$result['aid'])->find();
				$this->assign("aresult",$aresult);
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("读取失败");
			}
		}else{
			$this->error("选择要编辑的信息");
		}



	}


	/**
	 * 修改一条信息
	 */
	public function update_message(){
		$data['title']=$_POST['title'];
		$data['sort']=$_POST['sort'] ? $_POST['sort'] : 1000;
		$data['post_time']=$_POST['createdate'];
		$data['click_num']=$_POST['viewcount'];
		$data['is_status']=$_POST['status'] ? $_POST['status'] : 0;
		$data['is_top']=$_POST['top'] ? $_POST['top'] : 0;
		$data['details']= $_POST['details'];
		$data['is_diaplay_click']=$_POST['is_viewcount'];
		if(!empty($data['title']) && !empty($data['details'])){
			$me=M("message");
			$flag=$me->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
		}

	}

	/**
	 * 添加一条信息界面
	 */
	public function add_messageui(){ 
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$result=$mt->where("id = %d ",intval($id))->find();
			$this->assign("result",$result);
			$this->display();		
		}else{
			$this->error("请选择一个栏目");
		}
	}

	/**
	 * 添加一条信息
	 */
	public function add_message(){
		$data['title']=$_POST['title'];
		$data['sort']=$_POST['sort'] ? $_POST['sort'] : 1000;
		$data['post_time']=$_POST['createdate'];
		$data['click_num']=$_POST['viewcount'];
		$data['is_status']=$_POST['status'] ? $_POST['status'] : 0;
		$data['is_top']=$_POST['top'] ? $_POST['top'] : 0;
		$data['details']= $_POST['details'];
		$data['is_diaplay_click']=$_POST['is_viewcount'] ? 1 : 0;
		$data['aid']=$_POST['aid'];
		$data['is_delete']=0;
		if(!empty($data['aid']) && !empty($data['title']) && !empty($data['details'])){
			$me=M("message");
			$flag=$me->data($data)->add();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
		}
	}

	/**
	 * 搜索信息   根据标题
	 */
	public function search_title(){
		$searchstr=trim($_POST['search']);
		if(!empty($searchstr)){

			//获取该平台所有该模块下的类型编号
			$mt=M("message_type");
			$mtresult=$mt->where("xiaoqu_id = %d AND aflag ='%s'  AND is_delete =%d",session(C("SysUserSessionUid")),$this->forumlist['WY_INFO'],0)->order("sort")->getField("id",true);
			//查找信息


			$me=M("message");
			$where['is_delete']=array('eq',0);
			$where['title']=array("like","%".$searchstr."%");
			$where['aid']=array("IN",$mtresult);
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
	 * 删除多条信息
	 */
	public function delete_more_message(){
		$ids=$_POST['id'];
		$ids=explode(',',$ids);
		$me=M("message");
		$where['id']=array("IN",$ids);
		$data['is_delete']=1;
		$flag=$me->where($where)->data($data)->save();
		if($flag){
			$message['flag']=1;
		}else{
			$message['flag']=0;
			$message['msg']="操作失败！！！";
		}
		$this->ajaxReturn($message);
	}

	/**
	 * 移动信息分类排序
	 */
	public function move_message_type(){
		$moveid=$_POST["id"];
		$action=$_POST["action"];
		$where['aflag']=array("EQ",$this->forumlist['WY_INFO']);
		$where['xiaoqu_id']=array("EQ",is_login());
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
	 * 显示一个分类下幻灯片
	 */
	public function one_type_slide(){
		$id=$_GET['id'];
		if(!empty($id)){
			$se=M("slide");

			import('ORG.Util.Page');// 导入分页类
			$count      = $se->where("aid = %d AND is_delete =%d",$id,0)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			 $list = $se->where("aid = %d AND is_delete = %d",$id,0)->order('sort , id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			 $this->assign('result',$list);// 赋值数据集
			 $this->assign('page',$show);// 赋值分页输出
			$this->assign("aid",$id);
			$this->display();
		}else{
			exit();
		}
	}

	/**
	 * 新建一个幻灯片ui
	 */
	public function add_slideui(){
		$aid=$_GET['aid'];
		if(!empty($aid)){
			$this->assign("aid",$aid);
			$this->display();	
		}else{
			exit();
		}
	}

	/**
	 *新建幻灯片
	 */
	public function add_slide(){
		$data['name']=$_POST['name'];
		$data['image']=$_POST['image'];
		$data['href']=$_POST['url'];
		$data['remark']=$_POST['remark'];
		$data['aid']=$_POST['aid'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		if(!empty($data['aid']) && !empty($data['name']) && !empty($data['image']) ){
			$se=M("slide");
			$data['is_delete']=0;
			$data['sort']=1000;
			$flag=$se->data($data)->add();
			if($flag){
				$msg['flag']=1;
				$msg['msg']="操作成功";
				$this->ajaxReturn($msg);
			}else{
				$msg['flag']=0;
				$msg['msg']="操作失败";
				$this->ajaxReturn($msg);
				
			}
		}else{
			$msg['flag']=0;
			$msg['msg']="请填写信息";
			$this->ajaxReturn($msg);
			
		}
	}

	/**
	 * 编辑幻灯片界面
	 */
	public function editor_slideui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$se=M("slide");
			$result=$se->where("id= %d",$id)->find();
			if($result && is_array($result)){
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("读取数据失败");
			}
		}else{
			$this->error("请选择要编辑的幻灯片");
		}
	}

	/**
	 * 编辑幻灯片
	 */
	public function editor_slide(){
		$data['name']=$_POST['name'];
		$data['image']=$_POST['image'];
		$data['href']=$_POST['url'];
		$data['remark']=$_POST['remark'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		if(!empty($_POST['id']) && !empty($data['name']) && !empty($data['image']) ){
			$se=M("slide");
			$flag=$se->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->success("修改成功！！");
			}else{
				$this->error("修改失败！！");
			}
		}else{
			$this->error("请填写相关信息！！！");
		}
	}

	/**
	 * 删除幻灯片
	 */
	public function delete_slide(){
		$id=$_POST['id'];
		if(!empty($id)){
			$se=M("slide");
			
			$flag=$se->where("id = %d",intval($id))->delete();
			if($flag){
				$data['flag']=1;
				$data['msg']="操作成功！！！";
				$this->ajaxReturn($data);
			}else{
				$data['flag']=1;
				$data['msg']="操作失败！！";
				$this->ajaxReturn($data);
			}
		}else{
			$data['flag']=1;
			$data['msg']="请选择要操作的幻灯片！！！";
			$this->ajaxReturn($data);

		}
	}

	/**
	 * 删除多张幻灯片
	 */
	public function delete_moreslide(){
		$ids=$_POST['id'];
		$ids=explode(',',$ids);
		$se=M("slide");
		$where['id']=array("IN",$ids);
		
		$flag=$se->where($where)->delete();
		if($flag){
			$message['flag']=1;
		}else{
			$message['flag']=0;
			$message['msg']="操作失败！！！";
		}
		$this->ajaxReturn($message);
	}
		
	/**
	 * 移动幻灯片
	 */
	public function move_slide(){
		$moveid=$_POST["id"];
		$action=$_POST["action"];
		$where['aid']=array("EQ",$_GET['aid']);
		$where['is_delete']=array("EQ",0);
		$move=new MoveAction();
		$flag=$move->move("slide",$moveid,"id",$where,"sort",$action);
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
			$this->ajaxReturn(array("flag"=>0,"slide"=>1,"msg"=>$msg),"JSON");
		}
		
	}










}
