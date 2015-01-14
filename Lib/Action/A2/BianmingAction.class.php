<?php

class BianmingAction extends CommonAction {

	 private $_forum_name=null;//该控制管理的模块编号

	/**
	 * 公共的信息查询分配到界面
	 */
	public function _initialize(){

		parent::_initialize();
		//获取该控制器管理的模块编号
		$forum_list=C("FORUM_LIST");
		$this->_forum_name=$forum_list['WY_BM'];


		//当前所在分类
		$navid=$_GET['navid'] ? $_GET["navid"] : $_POST["navid"];
		if(empty($navid)){
			$navid=$this->forumlist["WY_BM"];
		}
		$this->assign("navid",$navid);




	}

	/**
	 *信息分类列表
	 */
	Public function index(){
		$mt=M("message_type");
		$where['daili_id']=array("EQ",is_login());
		$where['aflag']=array("EQ",$this->_forum_name);
		$name=trim($_POST['search']);
		if($_POST['search']){
			$where['name']=array("like","%{$name}%");
		}
		$result=$mt->where($where)->order("sort , id desc")->select();
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
		//$data['url']=$_POST['url'];
		$data['image']=$_POST['image'];
		$data['introduce']=$_POST['introduce'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['is_shortcut']=$_POST['shortcut'] ? 1 :0;
		$data['aflag']=$this->_forum_name;
		$data['daili_id']=is_login();//
		if(!empty($data['name'])  && !empty($data['image'])){
			$mt=M("MessageType");
			$flag=$mt->add($data);
			if($flag){
				$this->ajaxReturn(array("flag"=>1,"msg"=>"操作成功"),'JSON');
			}else{
				$this->ajaxReturn(array("flag"=>0,"msg"=>"操作失败"),'JSON');
			}
		}else{
			$this->ajaxReturn(array("flag"=>0,"msg"=>"请填写必要信息"),'JSON');
		}

	}


	/**
	 * 一个分类下的信息列表
	 */
	public function one_messagetype_list(){
		$kind_id=$_GET['id'] ? $_GET['id'] : $_POST['id'];
		if(!empty($kind_id)){
			$me = M('message'); 
			import('ORG.Util.Page');// 导入分页类
			if(isset($_POST['search'])){
				$title=trim($_POST['title']);
				//$where['name']=$_POST['name'] ? array("like","%".$_POST['name']."%") : null;
				$where['title']=$_POST['title'] ? array("like","%{$title}%") : null;
				//$where['post_time']=($_POST['lasttime'] || $_POST['firsttime']) ? array(array('gt',$_POST['firsttime']),array('lt',$_POST['lasttime'])) : null;
				//$this->assign("sname",$_POST['name']);
				$this->assign("stitle",$_POST['title']);
				
			}else{
				$where['aid']=$kind_id;
				$where['is_delete']=0;
			}
			$count      = $me->where($where)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			$where['aid']=$kind_id;
		    $where['is_delete']=0;
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$list = $me->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("is_top desc,sort desc,id desc")->select();
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
		//$data['url']=$_POST['url'];
		$data['image']=$_POST['image'];
		$data['introduce']=$_POST['introduce'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['is_shortcut']=$_POST['shortcut'] ? 1 :0;
		$data['aflag']=$this->_forum_name;
		$data['agtime']=time();//编辑时间
		if(!empty($data['name']) && !empty($data['image']) && !empty($_POST['id'])){
			$mt=M("MessageType");
			$flag=$mt->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->ajaxReturn(array("flag"=>1,"msg"=>"操作成功"),'JSON');
			}else{
				$this->ajaxReturn(array("flag"=>0,"msg"=>"操作失败"),'JSON');
			}
		}else{
			$this->ajaxReturn(array("flag"=>0,"msg"=>"请填写必要信息"),'JSON');
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
		$data['updatetime ']=time();//编辑时间
		if(!empty($data['title']) && !empty($data['details']) && !empty($_POST['id'])){
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
			$this->assign("aresult",$result);
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
	 * 搜索信息   根据标题,用户，创建时间
	 */
	public function search(){
		$searchstr=$_POST['search'];
		if(!empty($searchstr)){

			//获取该平台所有该模块下的类型编号
			$mt=M("message_type");
			$mtresult=$mt->where("xiaoqu_id = %d AND aflag ='%s'  AND is_delete =%d",session(C("SysUserSessionUid")),$this->_forum_name,0)->order("sort")->getField("id");
			//查找信息
			$me=M("message");
			$where['is_delete']=array('eq',0);
			$where['name']=array("like","%".$searchstr."%");
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
		$where['daili_id']=array("EQ",is_login());
		$where['aflag']=array("EQ",$this->_forum_name);
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


	/*
	 * 修改配置界面-->BBS的配置
	 */
	public function configs(){
		//读取其配置信息
		$where['xiaoqu_id']=session(C('SysUserSessionUid'));
		$where['aflag']="self_bm";
		$mt=M("message_type");
		$result=$mt->where($where)->find();
		$this->assign("result",$result);
		$this->display();
	}


	/*
	 * 保存修改配置---》BBS的配置
	 */
	public function save_configs(){
		
		$mt=M("message_type");
		$data['keyworks']=$_POST['keywords'];
		$data['introduce']=$_POST['introduce'];
		$data['details_info']=$_POST['details_info'];
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
			$data['aflag']="self_bm";
			$data['xiaoqu_id']=session(C("SysUserSessionUid"));
			$flag=$mt->data($data)->add();
			$this->success("操作成功！！！");
		}
	
	}

	/*
	 * 修改一条信息是否置顶
	 */
	public function change_top(){
		if(IS_AJAX){
			$data['is_top']=$_POST['value'];
			if(!empty($_POST['id'])){
				$me=M("message");
				$flag=$me->where("id = %d",$_POST['id'])->data($data)->save();
				if($flag){
					$msg['flag']=1;
					$msg['msg']="操作成功！！！";
				}else{
					$msg['flag']=0;
					$msg['msg']="操作失败！！！";
				}
				$this->ajaxReturn($msg);
					
			}else{
				exit();
			}
				
		}else{
			exit();
		}
	}


	/*
	 * 修改一条信息是否启用
	 */
	public function change_status(){
		if(IS_AJAX){
			$data['is_status']=$_POST['value'];
			if(!empty($_POST['id'])){
				$me=M("message");
				$flag=$me->where("id = %d",$_POST['id'])->data($data)->save();
				if($flag){
					$msg['flag']=1;
					$msg['msg']="操作成功！！！";
				}else{
					$msg['flag']=0;
					$msg['msg']="操作失败！！！";
				}
				$this->ajaxReturn($msg);
					
			}else{
				exit();
			}
				
		}else{
			exit();
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
				$this->ajaxReturn(array("flag"=>1,"msg"=>"修改成功！！"),"JSON");
			}else{
				$this->ajaxReturn(array("flag"=>0,"msg"=>"修改失败！！"),"JSON");
			}
		}else{
			$this->ajaxReturn(array("flag"=>0,"msg"=>"请填写相关信息！！！"),"JSON");
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
		$data['is_delete']=1;
		$flag=$se->where($where)->data($data)->save();
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

	 



/*************=========================modles==============================********/


    //模型选项列表
    public function modellist(){
        $id=$_GET['id'];
        $modelid=M("Form");
        $result=$modelid->where("aid='%s' AND is_delete=%d",$id,0)->order("sort  , id desc")->select();
        $this->assign("result",$result);
        $this->display();
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

    //新建模型选项UI部分
    public function add_new_model_ui(){
    	$this->assign("aid",$_GET['aid']);
        $this->display();
    }

    //新建模型选项 data部分or 编辑模型
    public function add_new_model(){
       
        $s=$_POST['options'];
        $as=explode("\r", $s);
        //var_dump($as);
        $options=serialize($as);
        
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $data['defaultvalue']=$_POST['defaultvalue'];
        $data['options']=$options;

        $data['selectpicker']=$_POST['selectpicker'];
        $data['introduce']=$_POST['introduce'];
        if($_POST['is_must']=="on"){
            $data['is_must']=1;
        }else{
            $data['is_must']=0;
        }
        if($_POST['is_display']=="on"){
            $data['is_display']=1;
        }else{
            $data['is_display']=0;
        }
        $form_model=M('Form');

        //echo $form_model->getlastsql();exit();
        if(empty($_POST['modelid'])){
        	$data['aid']=$_POST['aid'];
            $result=$form_model->data($data)->add();
           
            if($result){
                //$this->success("添加成功");
                $this->ajaxReturn(array("status"=>1,"msg"=>"添加成功"),"JSON");
            }else{
                //$this->error("添加失败");
                $this->ajaxReturn(array("msg"=>"添加失败"),"JSON");
            }
        }else{
            $result=$form_model->where("id=%d",$_POST['modelid'])->data($data)->save();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"),"JSON");
            }else{
                //$this->error("编辑失败");
                $this->ajaxReturn(array("msg"=>"编辑失败或没有操作选项"),"JSON");
            }
        }
    }



    //编辑模型选项
    public function edit_model(){
        $id=$_GET['id'];
        $form_model=M("Form");
        $result=$form_model->where("id=%d",$id)->find();
        $res=unserialize($result['options']);

        $this->assign("res",$res);
        $this->assign("result",$result);

        $this->display();
    }

    //删除模型
    public function del_model(){
        $id=$_POST['id'];
        $form_model=M("Form");
        $result=$form_model->where("id='%d'",$id)->delete();
        if($result){
            //$this->success("删除成功");
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
    }



    //查看一条信息详情
    public function view_message(){
    	$id=$_GET['id'];
    	if(!empty($id)){
    		//信息本身
    		$mresult=M("Message")->where("id = %d",$id)->find();
    		$mresult['form_content']=unserialize($mresult['form_content']);
    		$this->assign("mresult",$mresult);

    		//信息所属类型
    		$aresult=M("MessageType")->where("id = %d",$mresult['aid'])->find();
    		$this->assign("aresult",$aresult);

    		$fresult=M("form")->where("aid  = %d",$mresult['aid'])->select();
    		$this->assign("fresult",$fresult);

    		import('ORG.Util.Page');// 导入分页类
			$count      = M("replay")->where("mid = %d",$id)->count();// 查询满足要求的总记录数
			$Page       = new Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
    		//互动信息
    		$replay=M("replay")->where("mid = %d",$id)->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
    		$this->assign("replay",$replay);
    		$this->assign("page",$show);

    		$this->display();
    		
    	}else{
    		$this->error("请选择要查看的信息");
    	}
    }

    //删除一条回复信息
    public function delete_reply(){
    	if(IS_AJAX){
    		$flag=M("replay")->where("id = %d",$_POST['id'])->delete();
    		if($flag){
    			$this->ajaxReturn(array("flag"=>1,"msg"=>"操作成功"),"JSON");
    		}else{
    			$this->ajaxReturn(array("flag"=>0,"msg"=>"操作失败"),"JSON");
    		}
    	}else{
    		exit();
    	}

    }



    //修改阅读量
    public function change_clicknum(){
    	$data['click_num']=$_POST['viewcount'];
    	$flag=M("Message")->where("id = %d",intval($_POST['id']))->save($data);
    	if($flag){
    		$this->ajaxReturn(array("status"=>1,"msg"=>"操作成功"),"JSON");
    	}else{
    		$this->ajaxReturn(array("status"=>0,"msg"=>"操作失败"),"JSON");
    	}
    }







    




}
