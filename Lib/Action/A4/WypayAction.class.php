<?php

class WypayAction extends CommonAction {

	private $formalist=null;

	 private $xiaoqu_id=null;

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

		//查找出登录人员属于的小区
		$at=M("Account");
		$user=$at->where("uid = ".is_login())->find();
		$this->xiaoqu_id=$user['type_xiaoqu'];
	}

	/**
	 *在线收费分类列表
	 */
	Public function index(){

		$mt=M("message_type");
		$where['aflag']=array("eq",$this->formalist['WY_ONLINE_PAY']);
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$result=$mt->where($where)->select();
		$this->assign("result",$result);
		$this->display();

	}

	/**
	 * 新建在线物业收费分类界面
	 */
	public function create_online_typeui(){
		$this->display();
	
	}

	/**
	 * 新建在线物业收费分类
	 */
	public function create_online_type(){
		$data['worker_openid']=$_POST['worker_openid'];
		$data['name']=$_POST['name'];
		$data['introduce']=$_POST['introduce'];
		
		if(!empty($data['name'])){
			$mt=M("message_type");
			$data['aflag']=$this->formalist['WY_ONLINE_PAY'];
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$data['is_status']=1;
			$data['is_shortcut']=1;
			$flag=$mt->data($data)->add();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("物业收费名称必须填写！！！");
		}
	}


	/**
	 * 修改在线物业收费分类界面
	 */
	public function update_online_typeUi(){
		$id=$_GET['id'];
		$mt=M("message_type");
		$result=$mt->where("id = %d",intval($id))->find();
		$this->assign("result",$result);
		$this->display();
	
	}

	/**
	 * 修改在线物业收费分类
	 */
	public function update_online_type(){
		$data['worker_openid']=$_POST['worker_openid'];
		$data['name']=$_POST['name'];
		$data['introduce']=$_POST['introduce'];
		
		if(!empty($data['name']) && !empty($_POST['id'])){
			$mt=M("message_type");
			$flag=$mt->where("id = %d ",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->success("操作成功！！！");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this->error("物业收费名称必须填写！！！");
		}
	}


	/**
	 * 物业在线收费一个分类下的收费用户
	 */
	public function one_onlinetype_list(){
		$kind_id=$_GET['id'];
		if(!empty($kind_id)){
			$me = M('message'); 
			import('ORG.Util.Page');// 导入分页类
			$count      = $me->where('aid = %d AND is_delete = %d',$kind_id,0)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $me->where('aid= %d AND is_delete = %d ',$kind_id,0)->order('sort')->limit($Page->firstRow.','.$Page->listRows)->select();
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
	 * 新建在线收费的信息UI
	 */
	public function add_onlinepayui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",intval($id))->find();
			$this->assign("aresult",$aresult);
			$this->display();
		}else{
			$this->error("请选择要添加的分类");
		}	
	
	
	}

	/**
	 * 新建在线收费的信息
	 */
	public function add_onlinepay(){
		$data['aid']=$_POST['aid'];
		$data['title']=$_POST['title'];//物业编号
		$data['money']=$_POST['money'];
		$data['lasttime']=date("Y-m-d",strtotime($_POST['lasttime']));
		$data['remark']=$_POST['remark'];
		$data['details']=$_POST['details'];
		if(!empty($data['aid']) && !empty($data['title']) && !empty($data['money'])){
			//检测物业编号
			$py=M("property");
			$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
			$where['number']=array("eq",$data['title']);
			$presult=$py->where($where)->find();
			if(!$presult || !is_array($presult)){
				$this->error("物业编号不存在");
			}

			//检测money
			if(!is_numeric($data['money']) || intval($data['money']) < 0 ){
				$this->error("收费金额必须大于0!");
			}

			//插入数据
			$data['updatetime']=date("Y-m-d :H:i:s");
			$data['post_time']=date("Y-m-d :H:i:s");
			$me=M("message");
			$flag=$me->data($data)->add();

			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}	
		}else{
			$this->error("请填写相关信息！！！");
		}
	}


	/**
	 * 修改在线收费的信息UI
	 */
	public function update_onlinepayui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$me=M("message");
			$result=$me->where("id = %d",intval($id))->find();
			$this->assign("result",$result);

			$mt=M("message_type");
			$aresult=$mt->where("id =%d",$result['aid'])->find();
			$this->assign("aresult",$aresult);


			$this->display();
		}else{
			$this->error("请选择要修改的信息");
		}	
	
	
	}

	/**
	 * 修改在线收费的信息
	 */
	public function update_onlinepay(){
		$data['title']=$_POST['title'];//物业编号
		$data['money']=$_POST['money'];
		$data['lasttime']=date("Y-m",strtotime($_POST['lasttime']));
		$data['remark']=$_POST['remark'];
		$data['details']=$_POST['details'];
		if(!empty($_POST['id']) && !empty($data['title']) && !empty($data['money'])){
			//检测物业编号
			$py=M("property");
			$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
			$where['number']=array("eq",$data['title']);
			$presult=$py->where($where)->find();
			if(!$presult || !is_array($presult)){
				$this->error("物业编号不存在");
			}

			//检测money
			if(!is_numeric($data['money']) || intval($data['money']) < 0 ){
				$this->error("收费金额必须大于0!");
			}

			//插入数据
			$data['updatetime']=date("Y-m-d :H:i:s");
			$me=M("message");
			$flag=$me->where("id = %d",intval($_POST['id']))->data($data)->save();

			if($flag){
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}	
		}else{
			$this->error("请填写相关信息！！！");
		}
	}





	/***********************************************费用分类模块**************************************************/


	/**
	 * 物业费用分类列表
	 
	public function models(){

		$mt=M("message_type");
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=$this->formalist['WY_COST_LIST'];
		$result=$mt->where($where)->select();
		$this->assign("result",$result);
		$this->display();
	}
	*/

	/**
	 * 物业信息分类列表
	 */
	public function wy_type(){
		$forumlist=C("FORUM_LIST");
		$mt=M("message_type");
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=array("eq",$forumlist['WY_COST_LIST']);
		$result=$mt->where($where)->order("sort , id desc")->select();
		$this->assign("result",$result);
		$this->display();
	}

	/**
	 * 新建费用分类界面
	 */
	Public function create_message_typeUi(){
		$this->display();	
	}

	/*
	 * 新建费用分类
	 */
	public function create_message_type(){
		$data['name']=$_POST['name'];//费用分类名称
		$data['keyworks']=$_POST['keyword'];//收费项目
		$data['image']=$_POST['image'];//图片
		$data['success_send_msg']=$_POST['lastname'];//上期名
		$data['is_diapley']=$_POST['is_showlast'] ? 1 : 0;//上期名是否显示
		$data['success_info']=$_POST['nowname'];//本期名
		$data['is_bind']=$_POST['is_shownow'] ? 1 : 0;//本期名是否显示
		$data['faile_info']=$_POST['unitname'];//用量名
		$data['worker_openid']=$_POST['pricename'];//单价名
		$data['accept_openid']=$_POST['countname'];//总价名
		$data['details_info']=$_POST['details'];//附加内容
		$data['introduce']=$_POST['introduce'];//备注
		$data['is_stages']=$_POST['stages'] ? 1 : 0;//是否分期录入
		$data['is_anon']=$_POST['isall'] ? 1 : 0;//是否全体物业缴费
		$data['is_status']=$_POST['status'] ? 1 : 0;//是否启用
		$data['is_shortcut']=$_POST['shortcut'] ? 1 : 0;//是否快捷
		if(!empty($data['name']) && !empty($data['keyworks'])){
			$data['xiaoqu_id']=$this->xiaoqu_id;
			$data['aflag']=$this->formalist['WY_COST_LIST'];
			$data['sort']=1000;

			$m=M();
			$m->startTrans();

			$flag1=$m->table("wy_message_type")->data($data)->add();

			//插入收费项目
			$option=explode(",",$data['keyworks']);
			$form=array();
			foreach($option as $one){
				$temp['name']=$one;
				$temp['aid']=$flag1;
				array_push($form,$temp);
			}
			$flag2=$m->table("wy_form")->addAll($form);


			if($flag1 || $flag2 ){
				$m->commit();
				$this->success("添加成功");
			}else{
				$m->rollback();
				$this->error("添加失败！！！");
			}

		}else{
			$this->error("请填写信息分类名称！");
		}

	}


	/**
	 * 修改一个费用分类界面
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
	 * 保存一个费用分类
	 */
	public function update_messagetype(){
		$data['name']=$_POST['name'];//费用分类名称
		$data['keyworks']=$_POST['keyword'];//收费项目
		$data['image']=$_POST['image'];//图片
		$data['success_send_msg']=$_POST['lastname'];//上期名
		$data['is_diapley']=$_POST['is_showlast'] ? 1 : 0;//上期名是否显示
		$data['success_info']=$_POST['nowname'];//本期名
		$data['is_bind']=$_POST['is_shownow'] ? 1 : 0;//本期名是否显示
		$data['faile_info']=$_POST['unitname'];//用量名
		$data['worker_openid']=$_POST['pricename'];//单价名
		$data['accept_openid']=$_POST['countname'];//总价名
		$data['details_info']=$_POST['details'];//附加内容
		$data['introduce']=$_POST['introduce'];//备注
		$data['is_stages']=$_POST['stages'] ? 1 : 0;//是否分期录入
		$data['is_anon']=$_POST['isall'] ? 1 : 0;//是否全体物业缴费
		$data['is_status']=$_POST['status'] ? 1 : 0;//是否启用
		$data['is_shortcut']=$_POST['shortcut'] ? 1 : 0;//是否快捷
		if(!empty($data['name']) && !empty($data['keyworks']) && !empty($_POST['id'])){


			$m=M();
			$m->startTrans();

			$flag1=$m->table("wy_message_type")->where("id = %d",intval($_POST['id']))->data($data)->save();

			if($flag1){
				//修改收费项目
				$flag2=true;$flag3=true;
				$option=explode(",",$data['keyworks']);
				foreach($option as $one){
					$tempwhere['aid']=array("eq",intval($_POST['id']));
					$tempwhere['name']=array("eq",$one);
					$flag2=$m->table("wy_form")->where($tempwhere)->find();
					if(!$flag2){
						$tempdata['aid']=intval($_POST['id']);
						$tempdata['name']=$one;
						$flag3=$m->table("wy_form")->data($tempdata)->add();
						if(!$flag3){
							break;
						}
					}
					unset($tempwhere);
				}
				if($flag3){
					$m->commit();
					$this->success("操作成功");
				}else{
					$m->rollback();
					$m->error("操作失败");
				}
			}else{
				$m->rollback();
				$this->error("操作失败或没有修改任何东西");
			}
		}else{
			$this->error("请填写信息分类名称！");
		}
	}


	/**
	 * 分期列表
	 */
	public function stageslist(){
		$aid=$_GET["id"];
		if(!empty($aid)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",$aid)->find();
			$this->assign("aresult",$aresult);

			//查出该类下的分期列表
			$result=$mt->where("xiaoqu_id = %d AND aflag = '%s'",$this->xiaoqu_id,$aid)->select();
			$this->assign("result",$result);

			$this->display();
		}else{
			$this->error("请选择要查看的分期列表");
		}
	}



	/**
	 * 添加分期类目界面
	 */
	public function add_stagesui(){
		$aid=$_GET["id"];
		if(!empty($aid)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d",$aid)->find();
			$this->assign("aresult",$aresult);
			$this->display();
		}else{
			$this->error("请选择要查看的分期列表");
		}
	}

	/**
	 * 添加分期类目
	 */
	public function add_stages(){
		$data['xiaoqu_id']=$this->xiaoqu_id;
		$data['aflag']=$_POST['aid'];
		$data['name']=$_POST['name'];//分期名称
		$data['keyworks']=$_POST['keyword'];//收费期间
		$data['introduce']=$_POST['introduce'];//备注
		if(!empty($data['xiaoqu_id']) && !empty($data['aflag']) && !empty($data['keyworks']) && !empty($data['name'])){
			$mt=M("message_type");
			//2:总分期下的一个子分期
			$data['is_stages']=2;
			$flag=$mt->data($data)->add();
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
	 * 修改分期界面
	 */
	public function update_stagesui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$result=$mt->where("id = %d",intval($id))->find();
			$this->assign("result",$result);

			$aresult=$mt->where("id = %d",$result['aflag'])->find();
			$this->assign("aresult",$aresult);
			$this->display();
		}else{
			$this->error("请选择要操作的分期类目");
		}


	}

	/**
	 * 修改分期
	 */
	public function update_stages(){
		$data['name']=$_POST['name'];//分期名称
		$data['keyworks']=$_POST['keyword'];//收费期间
		$data['introduce']=$_POST['introduce'];//备注
		if(!empty($data['keyworks']) && !empty($data['name']) && !empty($_POST['id'])){
			$mt=M("message_type");
			$flag=$mt->where("id = %d",intval($_POST['id']))->data($data)->save();
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
	 * 一个分类下的信息列表
	 */
	public function one_messagetype_list(){
		$kind_id=$_GET['aid'] ? $_GET['aid'] : $_GET['id'];
		if(!empty($kind_id)){
			
			//是否全体付费
			$is_quanti=null;

			//真正的分类ID
			$id= null;

			//读取根分类信息
			$mresult=M("MessageType")->where("id = %d",$kind_id)->find();
			if($mresult['is_stages'] == 0){
				//没有分期付款
				$is_quanti=$mresult['is_anon'];
				$id=$mresult["id"];
			}else{
				//分期付款
				$is_quanti=$mresult['is_anon'];
				$id=$_GET['id'];
			}


			//读取message的条目
			$result=M("Message")->where("aid = %d",$id)->select();
			//已有的物业编号
			$wuye_number=array();
			foreach ($result as $one) {
				array_push($wuye_number, $one['title']);
			}

			//去掉重复的物业编号
			$wuye_number=array_unique($wuye_number);

			$show=null;
			$list=null;
			import('ORG.Util.Page');// 导入分页类

			if($is_quanti){
				//全体缴费
				$count      = M("Property")->where("xiaoqu_id = %d",$this->xiaoqu_id)->count();// 查询满足要求的总记录数
				$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
				$show       = $Page->show();// 分页显示输出
				// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
				$list = M("Property")->where("xiaoqu_id = %d",$this->xiaoqu_id)->limit($Page->firstRow.','.$Page->listRows)->select();
				foreach($list as $key=>$one){
					if(in_array($one['number'],$wuye_number)){
						$temp=M("Message")->where("aid = %d AND title= '%s'",$id,$one['number'])->order("id")->find();
						$list[$key]['message']=$temp;
						$list[$key]['is_list']=1;
						unset($temp);
					}
				}


			}else{
				$where1['Property']=array("EQ",$this->xiaoqu_id);
				$where1['number']=array("IN",$wuye_number);
				$where1['xiaoqu_id']=array("EQ",$this->xiaoqu_id);
				$count      = M("Property")->where($where1)->count();// 查询满足要求的总记录数
				$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
				$show       = $Page->show();// 分页显示输出
				// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
				$list = M("Property")->where($where1)->limit($Page->firstRow.','.$Page->listRows)->select();
				foreach($list as $key=>$one){
					if(in_array($one['number'],$wuye_number)){
						$temp=M("Message")->where("aid = %d AND title= '%s'",$id,$one['number'])->order("id")->find();
						$list[$key]['message']=$temp;
						$list[$key]['is_list']=1;
						unset($temp);
					}
				}


			}

			$this->assign("list",$list);
			$this->assign("page",$show);

			$aresult=M("MessageType")->where("id = %d ",$id)->find();
			$this->assign("aresult",$aresult);
			$this->display(); // 输出模板	
			
			
		}else{
			$this->error("请选择一个分类!!!");
		}
	}


	/**
	*一个物业编号下的信息
	*/
	public function one_number_list(){
		if(!empty($_GET['aid']) && !empty($_GET['number'])){
			$where['aid']=array("eq",$_GET['aid']);
			$where['title']=array("eq",$_GET['number']);
			import('ORG.Util.Page');// 导入分页类
			$count      = M("Message")->where($where)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = M("Message")->where($where)->order('sort')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign("page",$show);
			$this->assign("list",$list);

			$aresult=M("MessageType")->where("id = %d",$_GET['aid'])->find();
			$this->assign("aresult",$aresult);

			$this->display();
		}else{
			$this->error("该请选择一个物业编号查看");
		}
	}

	/**
	 * 添加一条信息界面
	 */
	public function add_messageui(){ 
		$id=$_GET['id'];
		if(!empty($id)){
			$mt=M("message_type");
			$aresult=$mt->where("id = %d ",intval($id))->find();

			//是否类型为分期显示
			if($aresult['is_stages']==2){
				//分期显示
				$aaresult=$mt->where("id = %d ",$aresult['aflag'])->find();	
				$aresult['accept_openid']=$aaresult["accept_openid"];
				$aresult['faile_info']=$aaresult["faile_info"];
				$aresult['worker_openid']=$aaresult["worker_openid"];
				$aresult['success_send_msg']=$aaresult['success_send_msg'];//上期名
				$aresult['is_diapley']=$aaresult['is_diapley'];//上期名是否显示
				$aresult['success_info']=$aaresult['success_info'];//本期名
				$aresult['is_bind']=$aaresult['is_bind'];//本期名是否显示
				$id=$aaresult['id'];
			}
			
			$fm=M("form");
			$fresult=$fm->where("aid =%d",intval($id))->select();

			$this->assign("title",$_GET["title"]);
			$this->assign("fresult",$fresult);

			$this->assign("aresult",$aresult);
			$this->display();		
		}else{
			$this->error("请选择一个栏目");
		}
	}

	/**
	 * 添加一条信息
	 */
	public function add_message(){
		
		$data['title']=$_POST["title"];//物业编号
		$data['assess']=$_POST["createdate"];//收费期间
		$data['account']=$_POST["danhao"] ? $_POST['danhao'] : date("Y-m-d-H-i-s");//缴费单号
		$data['money']=$_POST["money"] ? $_POST['money'] : 0;//预交额
		$data['lasttime']=$_POST["tstime"];//提示时间
		$data['updatetime']=date("Y-m-d :H:i:s");//更新时间
		$data['is_status']=$_POST["status"];//状态--》已/未缴
		$data['aid']=$_POST["aid"];
		if(!empty($data['title']) && !empty($data['account']) && !empty($data['aid'])){
			$nuitnum=$_POST["nuitnum"];
			$pricenum=$_POST["pricenum"];
			$xsnum=$_POST["xsnum"];
			$countnum=$_POST["countnum"];
			$remark=$_POST["remark"];
			if(isset($_POST['shangqi'])){
				$shangqi=$_POST['shangqi'];
			}
			if(isset($_POST['benqi'])){
				$benqi=$_POST['benqi'];
			}

			$m=M();
			$m->startTrans();

			$flag=$m->table("wy_property")->where("xiaoqu_id = %d AND number = '%s'",$this->xiaoqu_id,$data['title'])->find();
			if(!$flag){
				$this->error("物业编号不存在");
			}

			//插入信息表
			$flag1=$m->table("wy_message")->data($data)->add();
			$flag2=true;

			if($flag1){
				foreach($nuitnum as $key=>$one){
					$tempdata['pricenum']=$pricenum[$key];
					$tempdata['countnum']=$countnum[$key];
					$tempdata['unitnum']=$nuitnum[$key];
					$tempdata['remark']=$remark[$key];
					$tempdata['xsnum']=$xsnum[$key];
					$tempdata['shangqi']=$shangqi[$key] ? $shangqi[$key] : "";
					$tempdata['benqi']=$benqi[$key] ? $benqi[$key] : "";
					$tempdata['time']=date("Y-m-d :H:i:s");
					$tempdata['aid']=$flag1;
					$tempdata['tid']=$key;
					$flag2=$m->table("wy_pay")->data($tempdata)->add();
					if(!$flag2){
						break;
					}
				}

				if($flag2){
					$m->commit();
					$this->success("操作成功");
				}else{
					$m->rollback();
					$this->error("操作失败");
				}
			}else{
				$m->rollback();
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
		}
	}


	/**
	 * 修改一条信息
	 */
	public function update_messageui(){
		$id=$_GET['id'];
		if(!empty($id)){
			$me=M("Message");
			$result=$me->where("id = %d",$id)->find();
			if($result && is_array($result)){
				$mt=M("message_type");
				$aresult=$mt->where("id = %d ",$result['aid'])->find();
				if($aresult['is_stages']!=0 && $aresult['is_stages']==2){
					//分期类目
					$aresult=$mt->where("id = %d",$aresult['aflag'])->find();
				}

				$fresult=M("form")->where("aid =%d",$aresult['id'])->select();
				$this->assign("fresult",$fresult);

				//查找费用明细
				$payresult=M("Pay")->where("aid = %d",$id)->select();
				$this->assign("payresult",$payresult);

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
		$data['title']=$_POST["title"];//物业编号
		$data['assess']=$_POST["createdate"];//收费期间
		$data['account']=$_POST["danhao"] ? $_POST['danhao'] : date("Y-m-d-H-i-s");//缴费单号
		$data['money']=$_POST["money"] ? $_POST['money'] : 0;//预交额
		$data['lasttime']=$_POST["tstime"];//提示时间
		$data['updatetime']=date("Y-m-d :H:i:s");//更新时间
		$data['is_status']=$_POST["status"];//状态--》已/未缴

		if(!empty($data['title']) && !empty($data['account']) && !empty($_POST["id"])){
			$nuitnum=$_POST["nuitnum"];
			$pricenum=$_POST["pricenum"];
			$xsnum=$_POST["xsnum"];
			$countnum=$_POST["countnum"];
			$remark=$_POST["remark"];
			if(isset($_POST['shangqi'])){
				$shangqi=$_POST['shangqi'];
			}
			if(isset($_POST['benqi'])){
				$benqi=$_POST['benqi'];
			}

			$m=M();
			$m->startTrans();

			$flag=$m->table("wy_property")->where("xiaoqu_id = %d AND number = '%s'",$this->xiaoqu_id,$data['title'])->find();
			if(!$flag){
				$this->error("物业编号不存在");
			}

			//信息表
			$flag1=$m->table("wy_message")->where("id = %d",intval($_POST['id']))->data($data)->save();
			$flag2=true;

			$delete1=$m->table("wy_pay")->where("aid = %d",intval($_POST['id']))->delete();

			if($flag1){
				foreach($nuitnum as $key=>$one){
					$tempdata['pricenum']=$pricenum[$key];
					$tempdata['countnum']=$countnum[$key];
					$tempdata['unitnum']=$nuitnum[$key];
					$tempdata['remark']=$remark[$key];
					$tempdata['xsnum']=$xsnum[$key];
					$tempdata['time']=date("Y-m-d :H:i:s");
					$tempdata['shangqi']=$shangqi[$key] ? $shangqi[$key] : "";
					$tempdata['benqi']=$benqi[$key] ? $benqi[$key] : "";
					$tempdata['aid']=intval($_POST['id']);
					$tempdata['tid']=$key;
					$flag2=$m->table("wy_pay")->data($tempdata)->add();
					if(!$flag2){
						break;
					}
				}

				if($flag2){
					$m->commit();
					$this->success("操作成功");
				}else{
					$m->rollback();
					$this->error("操作失败");
				}
			}else{
				$m->rollback();
				$this->error("操作失败");
			}
		}else{
			$this->error("请填写相关信息");
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
			$flag1=$m->table(C("DB_PREFIX")."message")->where("aid = %d",$id)->delete();
			if($flag || $flag1){
				$m->commit();
				$data['flag']=1;
				$data['msg']="操作成功！！！";
			}else{
				$m->rollback();
				$data['flag']=0;
				$data['msg']="操作失败！！！";
			}
			$this->ajaxReturn($data);
		}else{
			$this->error("选择要删除的类别");
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
				$data['flag']=1;
			}else{
				$data['flag']=0;
				$data['msg']="操作失败！！！";
			}
			$this->ajaxReturn($data);
		}else{
			exit();
		}

	}



	/**
	 * 搜索信息   根据标题
	 */
	public function search_title(){
		$searchstr=$_POST['search'];
		if(!empty($searchstr)){

			//获取该平台所有该模块下的类型编号
			$mt=M("message_type");
			$mtresult=$mt->where("xiaoqu_id = %d AND aflag ='%s'  AND is_delete =%d",$this->xiaoqu_id,$this->formalist['WY_INFO'],0)->order("sort")->getField("id");
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
		$where['xiaoqu_id']=array("eq",$this->xiaoqu_id);
		$where['aflag']=array("eq",$this->forumlist['WY_COST_LIST']);
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






/*==========================================excel导入导出=======================================*/

	//导入物业房号数据  UI
	public function import_wuyezaixianui(){
		$this->display();

	}

	//导入物业房号数据 
	public function import_wuyezaixian(){
		$uploadfile=$_FILES["file"]['tmp_name'];
		if(is_uploaded_file($uploadfile)){
			Vendor('PhpExcel.PHPExcel');
			Vendor('PHPExcel.IOFactory');
			Vendor('PHPExcel.Reader.Excel5');

			$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
			$objPHPExcel = $objReader->load($uploadfile);


			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow(); // 取得总行数
			$highestColumn = $sheet->getHighestColumn(); // 取得总列数

			$start=1;//从哪一行开始录入/删除

			if($_POST['diyihang']){
				//忽略第一行
				
				$start=2;
			}

			$data=array();

		
			for($start;$start<=$highestRow;$start++){ 
				
		     	//读取单元格
		  		$temp['title']=$objPHPExcel->getActiveSheet()->getCell("A".$start)->getValue();
		  		$temp['money']=$objPHPExcel->getActiveSheet()->getCell("B".$start)->getValue();
		  		
				//物业编号，认证码为空 不要
				if(empty($temp['title']) || empty($temp['money'])){
					continue;
				}


				$temp['aid']=$_POST['aid'];
		  		$temp['lasttime']=date("Y-m-d",strtotime($objPHPExcel->getActiveSheet()->getCell("C".$start)->getValue()));
		  		$temp['post_time']=$objPHPExcel->getActiveSheet()->getCell("D".$start)->getValue();
		  		$temp['updatetime']=$objPHPExcel->getActiveSheet()->getCell("E".$start)->getValue();
				$temp['assess']=$objPHPExcel->getActiveSheet()->getCell("F".$start)->getValue() ? $objPHPExcel->getActiveSheet()->getCell("F".$start)->getValue() : date("Y-m-d H:i:s") ;//支付时间
				$temp['details']=$objPHPExcel->getActiveSheet()->getCell("G".$start)->getValue();
			

				array_push($data,$temp);
				unset($temp);
			}
			
			if($_POST['update_delete']){
				$flag=true;
				$me=M("Message");
				foreach ($data as $key => $value) {
					$have=$me->where("title= '%s' AND aid= %d",$value['title'],$value['aid'])->find();
					if($have){
						$flag=$me->where("id = %d",$value['id'])->data($value)->save();
						
					}else{
						$flag=$me->data($value)->add();


					}
					if(!$flag){
						break;
					}
				}

				if($flag){
					$this->success("导入成功");
				}else{
					$this->error("导入失败");
				}

			}else{
				$delete=array();
				foreach ($data as $key => $value) {
					array_push($delete, $value['title']);
				}
				$where['number']=array("IN",$delete);
				$where['aid']=array("EQ",$_POST['aid']);
				$flag2=M("message")->where($where)->delete();
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
	public function export_wuyezaixian(){

		$kind_id=$_GET['id'];
		if(empty($kind_id)){
			$this->error("请选择一个分类!!!");
		}

		$me = M('message'); 
			
		$result = $me->where('aid= %d AND is_delete = %d ',$kind_id,0)->select();


		if($result && is_array($result)){
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
			
			


			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A1","物业编号")
				->setCellValue("B1","金额")
				->setCellValue("C1","缴费月份")
				->setCellValue("D1","创建时间")
				->setCellValue("E1","更新时间")
				->setCellValue("F1","支付时间")
				->setCellValue("G1","缴费说明");

			//填充数据
			$counter=2;
			foreach($result as $one){

				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A".$counter,$one['title'])
					->setCellValue("B".$counter,$one['money'])
					->setCellValue("C".$counter,$one['lasttime'])
					->setCellValue("D".$counter,$one['post_time'])
					->setCellValue("E".$counter,$one['updatetime'])
					->setCellValue("F".$counter,date("Y-m-d H:i:s"))
					->setCellValue("G".$counter,$one['details']);

				$counter++;
			}
			$objPHPExcel->getActiveSheet()->setTitle("在线收费列表");
			$filename="在线收费列表.xls";
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); //文件通过浏览器下载

		}else{
			$this->error("操作失败");
		}
	}
















}
