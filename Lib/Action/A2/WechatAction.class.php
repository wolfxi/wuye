
<?php

class WechatAction extends CommonAction {
	Public function index(){

		$this->display();

	}
	//获取微信ID
	private function wechat_id(){
		return M('Account')->where('uid='.is_login())->getfield('wechat_id');
	}
	//微信设置
	Public function config(){

		$wid=$this->wechat_id();
		$w=M('Wechat');
		$wx=$w->where("id={$wid}")->find();

		if(IS_POST){

			$w->create();
			$w->id=$wx['id'];
			if(isset($_POST['wcert']) and $_POST['cert']==1){
				$w->wcert=1;
			}elseif($_POST['cert']==1){
				$w->wcert=0;
			}
			$w->save();
			$this->ajaxReturn(array('msg'=>'保存成功'),'JSON');
		}

		$this->assign('wx',$wx);
		$this->display();
	}
	//自定义菜单
	Public function menu(){

		$wid=$this->wechat_id();
		$wm=M('WechatMenu');
		$menu=$wm->where("wechat_id={$wid} and fid=0")->order('sort ,id desc')->select();
		//循环子菜单
		foreach ($menu as $k=>$v) {
			if($v['type']==2){continue;}//如果是链接，则跳出
		$menu[$k]['submenu']=$wm->where("wechat_id={$wid} and fid={$v[id]}")->order('sort ,id desc')->select();
		}



		$this->assign('menu',$menu);
		$this->display();
	}
	//自定义菜单 添加 编辑
	Public function menu_add(){

		$wid=$this->wechat_id();

		$wm=M('WechatMenu');

		if(IS_POST){

			if(empty($_POST['title'])){$this->ajaxReturn(array('msg'=>'菜单名称不能为空'),'JSON');}

		$wm->create();
		$wm->wechat_id=$wid;

		if(empty($_POST['id'])){
			$wm->add();//新增
		}else{
			$wm->save();//更新
		}

		$this->ajaxReturn(array('status'=>1,'msg'=>'保存成功'),'JSON');

		}

		//编辑
		if(isset($_GET['id'])){
			$menu_top=$wm->where("wechat_id={$wid} and fid=0 and type=1 and id!={$_GET[id]}")->order('sort desc,id asc')->select();
			$menu=$wm->where("wechat_id={$wid} and id={$_GET[id]}")->find();
			$this->assign('menu',$menu);
			$this->assign('title','修改菜单');
		}else{
			$menu_top=$wm->where("wechat_id={$wid} and fid=0 and type=1")->order('sort desc,id asc')->select();
			$this->assign('title','添加菜单');
		}

		$this->assign('menu_top',$menu_top);
		$this->display();
	}
	//自定义菜单 删除
	public function menu_del(){

		$wid=$this->wechat_id();

		if(empty($_POST['id'])){$this->ajaxReturn(array('msg'=>'参数错误'),'JSON');}


		$wm=M('WechatMenu');
		$menu=$wm->where("wechat_id={$wid} and id={$_POST[id]}")->find();
		if(!$menu){
			$this->ajaxReturn(array('msg'=>'无该菜单'),'JSON');
		}
		if($wm->where("id={$menu[id]}")->delete()){
			//顶级菜单 则删除子菜单
			if($menu['fid']==0){$wm->where("fid={$menu[id]}")->delete();}
		$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
		}else{
			$this->ajaxReturn(array('msg'=>'操作失败'),'JSON'); 
		}

	}
	//自定义菜单 排序
	public function menu_sort(){

		$wid=$this->wechat_id();

		if(empty($_POST['id']) or empty($_POST['type'])){$this->ajaxReturn(array('msg'=>'参数错误'),'JSON');}

		$wm=M('WechatMenu');
		$menu=$wm->where("wechat_id={$wid} and id={$_POST[id]}")->find();
		if(!$menu){
			$this->ajaxReturn(array('msg'=>'无该菜单'),'JSON');
		}
		//排序重组
		$table=C('DB_PREFIX').'wechat_menu';
		$r=SortUpDown($_POST['type'],$table,$_POST['id'],'id','sort',"wechat_id={$wid} and fid={$menu[fid]}");
		$this->ajaxReturn(array('status'=>1,'msg'=>$r),'JSON');
		/*
		$sort=$wm->where("wechat_id={$wid} and fid={$menu[fid]}")->order('sort desc,id asc')->getfield('id,sort');
		$i=100;
		foreach ($sort as $k => $v) {

			$wm->where("id={$id}")->save(array('sort'=>$i));//重新排序

			if($_POST['type']==1){//上升
				$sort_id=$i+1;
				$id=$wm->where("wechat_id={$wid} and fid={$menu[fid]} sort={$sort_id}")->getfield('id');
				$wm->where("id={$id}")->save(array('sort'=>$i));
				$wm->where("id={$menu[id]}")->save(array('sort'=>$sort_id));
			}
			$i--;
		}
		 */

		$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'.M()->getlastsql()),'JSON');

		/*
		if($wm->where("id={$menu[id]}")->delete()){
			//顶级菜单 则删除子菜单
			if($menu['fid']==0){$wm->where("fid={$menu[id]}")->delete();}
			$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
		}else{
		   $this->ajaxReturn(array('msg'=>'操作失败'),'JSON'); 
		}
		 */

	}
	//更新菜单
	public function menu_up(){

		//header("Content-Type:text/html;charset=utf-8");

		$sub =array();
		$data = array();
		$subs = array();

		$wid=$this->wechat_id();

		$wm=M('WechatMenu');
		//主菜单
		$menu=$wm->where("wechat_id={$wid} and fid=0")->order('sort desc,id desc')->select();
		//子菜单
		$menux=$wm->where("wechat_id={$wid} and fid<>0")->order('sort desc,id asc')->select();

		foreach ($menu as $v) {
			$data['button'][$v['id']] =  array('name'=>$v['title']);
		}
		foreach ($menux as $v) {
			if($v['type']==2){//链接
				$data['button'][$v['fid']]['sub_button'][] = array('type'=>'view','name'=>$v['title'],'url'=>$v['value']);
			}else{
				$data['button'][$v['fid']]['sub_button'][] = array('type'=>'click','name'=>$v['title'],'key'=>$v['value']);
			}
		}
		//重新修改key值 以0、1、2
		foreach ($data['button'] as $v) {
			$data_new['button'][]=$v;
		}

		$data = jsencode($data_new);
			
		/* 加载微信SDK */
		import('COM.ThinkWechat');

		/*获取微信TOKEN*/
		$weixn=M('Wechat')->where("id={$wid}")->find();

		C('WECHAT_TOKEN', $weixn['wtoken']);
		C('WECHAT_APPID',$weixn['wappid']);
		C('WECHAT_APPSECRET',$weixn['wsecret']);

		$weixin = new ThinkWechat ();
		
		$msg=$weixin->setMenu($data);

		$this->ajaxReturn(json_decode($msg,true),'JSON'); 
	}
	//删除菜单
	public function delmenu(){

		$wid=$this->wechat_id();
		/* 加载微信SDK */
		import('COM.ThinkWechat');

		/*获取微信TOKEN*/
		$weixn=M('Wechat')->where("id={$wid}")->find();

		C('WECHAT_TOKEN', $weixn['wtoken']);
		C('WECHAT_APPID',$weixn['wappid']);
		C('WECHAT_APPSECRET',$weixn['wsecret']);

		$weixin = new ThinkWechat ();
		
		$msg=$weixin->delMenu();

		$msg1=json_decode($msg,true);
		if($msg1['errcode']==0){

			$flag=M("WechatMenu")->where("wechat_id = %d",$wid)->delete();
			if($flag){
				$this->ajaxReturn(array("errcode"=>0,"errmsg"=>"操作成功"),'JSON'); 
			}else{
				$this->ajaxReturn(array("errcode"=>1,"errmsg"=>"操作失败"),'JSON'); 
			}
		}else{
			$this->ajaxReturn(json_decode($msg,true),'JSON'); 
		}


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
		$account=trim($_POST[account]);
		if($_POST['account']){
			 $data['weixin_name']=array('Like',"%{$account}%");
		}
		$m=M('Msg');
		$user=$u->where($data)->select();
		foreach ($user as $k => $v) {
			$user[$k]['hdtime']=$m->where("openid='{$v[openid]}'")->order('addtime desc')->getfield('addtime');
		}
		//echo $m->getlastsql();

		//dump($user);
		$this->assign('user',$user);
		$this->display();
	}

	//关注用户
	public function user_edit(){

		$wid=$this->wechat_id();
		$id=M('Wechat')->where("id={$wid}")->getfield('wid');
		$u=M('User');
		if(IS_POST){
			$u_data['uname']=$_POST['uname'];
			$u_data['remark']=$_POST['remark'];
			$u->where("openid='{$_POST[openid]}' and openidWX='{$id}'")->save($u_data);
			$this->ajaxReturn(array('msg'=>'操作成功'),'JSON');
		}
		$user=$u->where("openid='{$_GET[openid]}' and openidWX='{$id}' and status=1")->find();
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
	//用户分组
	public function group(){

		$wid=$this->wechat_id();
		$wg=M('WechatGroup');
		/*
		$weixin=M('Wechat')->where("id={$wid}")->find();


		import('COM.ThinkWechat');

		C('WECHAT_TOKEN', $weixin['wtoken']);
		C('WECHAT_APPID',$weixin['wappid']);
		C('WECHAT_APPSECRET',$weixin['wsecret']);

		$weixin = new ThinkWechat ();

		$data=$weixin->getGroup();
		$data=json_decode($data,true);
		$data=$data['groups'];
		//dump($data);

		
		foreach ($data as $v) {
			if($v['id']<100){continue;}
			$wg_data_where['wechat_id']=$wid;
			$wg_data_where['wid']=$v['id'];
			$wg_data_where['wname']=$v['name'];
			if(!$wgid=$wg->where($wg_data_where)->getfield('id')){
				$wg_data['wechat_id']=$wid;
				$wg_data['wid']=$v['id'];
				$wg_data['wname']=$v['name'];
				$wg_data['wcount']=$v['count'];
				//echo $wg->getlastsql();
				$wg->add($wg_data);
			}else{
				$wg_data['id']=$wgid;
				$wg_data['wechat_id']=$wid;
				$wg_data['wid']=$v['id'];
				$wg_data['wname']=$v['name'];
				$wg_data['wcount']=$v['count'];
				$wg->save($wg_data);
			}
			echo M()->getlastsql().'<br/>';
		}
		*/
		$group=$wg->where("wechat_id={$wid}")->select();
		//echo $wg->getlastsql();
		$this->assign('group',$group);
		$this->display();
	}
	//用户分组 修改
	public function group_edit(){

		$wid=$this->wechat_id();

		$wg=M('WechatGroup');

		if(!IS_POST){
			$group=$wg->where("wechat_id={$wid} AND id={$_GET[id]}")->find();
			$this->assign('group',$group);
			$this->display();
		}else{
			if(empty($_POST['wname'])){$this->error('请输入分组名称');}
			$wg_data['id']=$_POST['id'];
			$wg_data['wname']=$_POST['wname'];
			$wg->save($wg_data);

			//修改微信名
			$weixin=M('Wechat')->where("id={$wid}")->find();

			import('COM.ThinkWechat');
			C('WECHAT_TOKEN', $weixin['wtoken']);
			C('WECHAT_APPID',$weixin['wappid']);
			C('WECHAT_APPSECRET',$weixin['wsecret']);
			$weixin = new ThinkWechat ();
			$edit['group']['id']=$wg->where("id={$_POST[id]}")->getfield('wid');
			$edit['group']['name']=$_POST['wname'];
			//echo jsencode($edit);exit;
			$data=$weixin->editGroup(jsencode($edit));

			//echo $data;exit;
			//echo $wg->getlastsql();
			$this->success('修改成功',U('wechat/group'));
		}
	}



    //关键字回复设置列表
    public function keyreply(){

    	$wid=$this->wechat_id();

        $kr=M("Keyreply");
        $id=$wid;
        import('ORG.Util.Page');// 导入分页类
        $count= $kr->where("uid={$id} AND is_tuwen=0")->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $res=$kr->where("uid=%d AND is_tuwen=%d ",$id,0)->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("page",$show);
        $this->assign("res",$res);
        $this->display();
    }

    //添加关键字回复
    public function reply_add(){

    	$wid=$this->wechat_id();

        $kr=M("keyreply");
        $id=is_login();


        if(IS_POST){
            if(empty($_POST['rule_name'])){
                $this->ajaxReturn(array('msg'=>"必须填写规则名称"),"JSON");
            }else if(empty($_POST['key_words'])){
                $this->ajaxReturn(array('msg'=>"必须填写关键字"),"JSON");
            }else if($_POST['group']==1){
                if(empty($_POST['image_description'])){
                    $this->ajaxReturn(array('msg'=>"必须填写回复内容"),"JSON");
                }else{
                    $data['image_description']=$_POST['image_description'];
                    $data['reply_type']=$_POST['group'];
                }
            }else if($_POST['group']==2){
                if(empty($_POST['image_title'])){
                    $this->ajaxReturn(array('msg'=>"必须填写图片标题"),"JSON");
                }else if(empty($_POST['image_description'])){
                    $this->ajaxReturn(array('msg'=>"必须填写图片内容"));
                }else if(empty($_POST['image'])){
                    $this->ajaxReturn(array("msg"=>"必须选择图片"));
                }else{
                    $data['reply_type']=$_POST['group'];
                    $data['reply_tucon']=$_POST['reply_tucon'];
                    $data['image_title']=$_POST['image_title'];
                    $data['image_description']=$_POST['image_description'];
                    $data['image_path']=$_POST['image'];
                    $data['image_url']=$_POST['image_url'];
                }
            }

            $data['rule_name']=$_POST['rule_name'];
            $data['key_words']=$_POST['key_words'];
            $data['rule_remark']=$_POST['rule_remark'];
            $data['match']=$_POST['match'];
            $data['is_status']=$_POST['is_status'];
            $data['create_time']=date("Y-m-d h:i:s", time());
            $data['update_time']=date("Y-m-d h:i:s",time());
            $data['uid']=$wid;
            $result=$kr->data($data)->add();
            if($result){
                $this->ajaxReturn(array("status"=>1,'msg'=>"添加成功"),"JSON");
            }else{
                $this->ajaxReturn(array("msg"=>"添加失败"),"JSON");
            }

        }
        $this->display();
    }

    //关键字回复编辑
    public function editreply(){

    	$wid=$this->wechat_id();

        $id=$_GET['id'];

        $aid=is_login();
        $kr=M("keyreply");
        $res=$kr->where("id=%d AND uid=%d",$id,$wid)->find();
        $this->assign("res",$res);
        $this->display();
    }

    //编辑图文内容
    public function editreplydata(){

    	$wid=$this->wechat_id();

        $kr=M("keyreply");
        $aid=is_login();
        if(IS_POST){
                if(empty($_POST['rule_name'])){
                $this->ajaxReturn(array('msg'=>"必须填写规则名称"),"JSON");
            }else if(empty($_POST['key_words'])){
                $this->ajaxReturn(array('msg'=>"必须填写关键字"),"JSON");
            }else if($_POST['group']==1){
            	 	$data['image_title']=null;
                    $data['image_path']=null;
                    $data['image_url']=null;
                    $data['image_description']=null;
                if(empty($_POST['image_description'])){
                    $this->ajaxReturn(array('msg'=>"必须填写回复内容"),"JSON");
                }else{
                    $data['image_description']=$_POST['image_description'];
                    $data['reply_type']=$_POST['group'];
                }
            }else if($_POST['group']==2){
                if(empty($_POST['image_title'])){
                    $this->ajaxReturn(array('msg'=>"必须填写图片标题"),"JSON");
                }else if(empty($_POST['image_description'])){
                    $this->ajaxReturn(array('msg'=>"必须填写图片内容"));
                }else if(empty($_POST['image'])){
                    $this->ajaxReturn(array("msg"=>"必须选择图片"));
                }else{
                    $data['reply_type']=$_POST['group'];
                    $data['reply_tucon']=$_POST['reply_tucon'];
                    $data['image_title']=$_POST['image_title'];
                    $data['image_description']=$_POST['image_description'];
                    $data['image_path']=$_POST['image'];
                    $data['image_url']=$_POST['image_url'];
                }
            }

            $data['rule_name']=$_POST['rule_name'];
            $data['key_words']=$_POST['key_words'];
            $data['rule_remark']=$_POST['rule_remark'];
            $data['match']=$_POST['match'];
            $data['is_status']=$_POST['is_status'];
            $data['update_time']=date("Y-m-d h:i:s",time());
            $data['uid']=$wid;
            $result=$kr->where("id=%d",$_POST['reply_id'])->data($data)->save();

            if($result){
                $this->ajaxReturn(array("status"=>1,'msg'=>"编辑成功"),"JSON");
            }else{
                $this->ajaxReturn(array("msg"=>"编辑失败"),"JSON");
            }
        }

    }
    //删除关键字
    public function keydel(){

    	$wid=$this->wechat_id();

        $id=$_POST['id'];
        $uid=is_login();
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>"参数错误"),"JSON");
        }else{
            $kr=M("Keyreply");
            $result=$kr->where("uid=%d AND id=%d",$wid,$id)->delete();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
            }else{
                $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
            }
        }
    }
	
	//批量删除
    public function del_manykey(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $where['id']=array("IN",$ids);
        $m=M("Keyreply");
		$flag1=$m->where($where)->delete();
		if($flag1){		
			$this->ajaxReturn(array("status"=>1,'msg'=>"删除成功"),"JSON");
		}else{	
			$this->ajaxReturn(array('msg'=>"操作失败"),"JSON");
		}

    }


    //搜索关键字设置根据rule_name or keywords 
    public  function search_key(){
        $wid=$this->wechat_id();
        $kr=M("Keyreply");
        $rule_name=trim($_POST['rule_name']);
        $key_words=trim($_POST['key_words']);
        if(empty($rule_name)&&empty($key_words)){
        	$where="uid='{$wid}' AND is_tuwen=0";
        }else{
        	$where="uid='{$wid}' AND is_tuwen=0 AND ";
        }
        if(!empty($rule_name)){
            $where.="rule_name LIKE '%{$rule_name}%'  ";
        }else if(!empty($rule_name)&&!empty($key_words)){
            $where.=" AND key_words LIKE '%{$key_words}%'";
        }else if(empty($rule_name)&&!empty($key_words)){
        	$where.="  key_words LIKE '%{$key_words}%'";
        }
        $res=$kr->where($where)->select();
        $this->assign("res",$res);
        $this->display("keyreply");
    }

	//图文回复设置列表
	public function tuwenreply(){

		$wid=$this->wechat_id();

		$ky=M("keyreply");

		$where['is_tuwen']=array("EQ",1);
		//$where['uid']=array("EQ",is_login());
		$where['uid']=$wid;

		import('ORG.Util.Page');// 导入分页类
		$count      = $ky->where($where)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出

		$list = $ky->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出


		$this->display();
	}

	//添加图文设置
	public function add_tuwen(){
		$this->display();
	}

	//保存添加图文
	public function save_tuwen(){
		$wid=$this->wechat_id();
		$data['rule_name']=$_POST['name'];
		$data['rule_remark']=$_POST['remark'];
		$data['key_words']=$_POST['keyword'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['match']=$_POST['pipei'];
		$data['is_shortcut']=0;
		$data['is_tuwen']=1;
		$data['create_time']=date("Y-m-d :H:i:s");
		$data['update_time']=date("Y-m-d :H:i:s");
		$data['uid']=$wid;

		if(!empty($data['rule_name']) && !empty($data['uid'])){
			$ky=M("keyreply");
			$flag=$ky->data($data)->add();
			if($flag){
				$this->ajaxsuccess($flag);
			}else{
				$this->ajaxerror("操作失败！！！");
			}
		}else{
			$this->ajaxerror("请填写相关信息");
		}

	}





	//添加图文下一步
	public function next(){
		$id=$_GET["id"];
		if(!empty($id)){
			$this->assign("aid",$id);
			$this->display();
		}else{
			exit();
		}
	}

	//保存下一步的图文信息
	public function save_next(){
		$image=$_POST["image"];
		$title=$_POST["title"];
		$url=$_POST["url"];
		$info=$_POST["info"];
		$details=$_POST["details"];

		$aid=$_POST["aid"];
		if(!empty($aid)){
			if($image && is_array($image)){
				$data=array();
				foreach($image as $key=>$one){
					$temp['image_path']=$one;
					$temp['image_title']=$title[$key];
					$temp['image_url']=$url[$key];
					$temp['image_description']=$info[$key];
					$temp['reply_tucon']=$details[$key];
					$temp['aid']=$aid;
					$temp['sort']=100;

					array_push($data,$temp);
					unset($temp);
				}
				$ty=M("tuwenreply");
				$flag=$ty->addAll($data);
				if($flag){
					$this->success("操作成功！！！");
				}else{
					$this->error("操作失败！！！");
				}
			}else{
				$this->success("操作成功！！！");
			}
		}else{
			exit();
		}
	}


	//获取一行内容
	public function get_oneui(){
		if(IS_AJAX){
			$time=time();
			$this->assign("time",$time);
			$data['content'] = $this->fetch('Public:tuwen'); 
			$data['flag']=1;
			$data['times']=$time;
			$this->ajaxReturn($data);
		}else{
			exit();
		}

	}


	//删除一个图文信息
	public function delete_one(){
	
			$id=$_POST["id"];


			if(!empty($id)){
				$m=M();
				$m->startTrans();
				$flag1=$m->table("wy_keyreply")->where("id = %d",$id)->delete();
				if($flag1){
					$flag2=$m->table("wy_tuwenreply")->where("aid = %d",$id)->delete();
					$m->commit();
					//$this->ajaxsuccess("操作成功");
					$this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
				}else{
					$m->rollback();
					$this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
				
				}
			}else{
				$this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
				//$this->ajaxsuccess("操作失败");
			}
	
	}



	//编辑图文
	public function edittuwen(){
		$id=$_GET["id"];
		if(!empty($id)){
			$ky=M("keyreply");
			$result=$ky->where("id = %d",$id)->find();
			if($result){
				$this->assign("result",$result);
				$this->display();
			}else{
				$this->error("信息不存在");
			}
		}else{
			$this->error("请选择要编辑的图文！！！");
		}
	}


	//保存编辑的图文
	public function update_tuwen(){
		$data['rule_name']=$_POST['name'];
		$data['rule_remark']=$_POST['remark'];
		$data['key_words']=$_POST['keyword'];
		$data['is_status']=$_POST['status'] ? 1 : 0;
		$data['match']=$_POST['pipei'];
		$data['update_time']=date("Y-m-d :H:i:s");

		if(!empty($data['rule_name']) && !empty($_POST['id'])){
			$ky=M("keyreply");
			$flag=$ky->where("id = %d",intval($_POST['id']))->data($data)->save();
			if($flag){
				$this->ajaxsuccess($_POST['id']);
			}else{
				$this->ajaxerror("操作失败！！！");
			}
		}else{
			$this->ajaxerror("请填写相关信息");
		}



	}

	//编辑图文下一步
	public function edit_tuwennext(){
		$id=$_GET["id"];
		if(!empty($id)){
			$ty=M("tuwenreply");
			$result=$ty->where("aid = %d",intval($id))->order("sort")->select();
			$this->assign("result",$result);
			$this->assign("aid",$id);
			$this->display();	
		}else{
			$this->error("请选择要编辑的信息");
		}
	}

	 public function move_tuwen(){
        $moveid=$_POST["id"];
        $action=$_POST["action"];
        $where['aid']=array("EQ",$_GET['aid']);
        $move=new MoveAction();
        $flag=$move->move("tuwenreply",$moveid,"id",$where,"sort",$action);
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


	//保存编辑图文下一步
	public function save_updatetuwennext(){
		$image=$_POST["image"];
		$title=$_POST["title"];
		$url=$_POST["url"];
		$info=$_POST["info"];
		$details=$_POST["details"];

		$aid=$_POST["aid"];
		if(!empty($aid)){

			$ty=M("tuwenreply");

			//删除之前的所有东西
			$removeall=$ty->where("aid = %d",intval($aid))->delete();



			if($image && is_array($image)){
				$data=array();
				foreach($image as $key=>$one){
					if(empty($one)){
						$this->error("请上传图片");
					}
					$temp['image_path']=$one;
					$temp['image_title']=$title[$key];
					$temp['image_url']=$url[$key];
					$temp['image_description']=$info[$key];
					$temp['reply_tucon']=$details[$key];
					$temp['aid']=$aid;
					$temp['sort']=100;

					array_push($data,$temp);
					unset($temp);
				}
				$flag=$ty->addAll($data);
				if($flag){
					$this->success("操作成功！！！");
				}else{
					$this->error("操作失败！！！");
				}
			}else{
				$this->success("操作成功！！！");
			}
		}else{
			exit();
		}


	}


	//批量删除
    public function delete_one_model(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $where['id']=array("IN",$ids);
        $where1['aid']=array("IN",$ids);
        $m=M();
		$m->startTrans();
		$flag1=$m->table("wy_keyreply")->where($where)->delete();
		if($flag1){
			$flag2=$m->table("wy_tuwenreply")->where($where1)->delete();
			$m->commit();
			//$this->ajaxsuccess("操作成功");
			$this->ajaxReturn(array("status"=>1,'msg'=>"删除成功"),"JSON");
		}else{
			$m->rollback();
			$this->ajaxReturn(array('msg'=>"操作失败"),"JSON");
		}

    }

    //条件搜索
    public function search_tuwen(){
    	$wid=$this->wechat_id();
    	$name=trim($_POST['name']);
    	$keword=trim($_POST['keyword']);

    	$tuwen=M("Keyreply");
    	if(empty($name)&&empty($keyword)){
    		$where="uid={$wid} AND is_tuwen=1";
    	}else{
    		$where="uid={$wid} AND is_tuwen=1 AND ";
    	}
    	if(!empty($name)){
    		$where.="rule_name LIKE '%{$name}%'  ";
    	}else if(!empty($tuwen)){
    		$where.=" AND key_words LIKE '%{$keword}%'";
    	}
    	import('ORG.Util.Page');// 导入分页类
		$count      = $tuwen->where($where)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出

		$list = $tuwen->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->display("tuwenreply");
    }



    /**
	 * 移动顶级分类
	 */
	public function move_menu_top(){
		$moveid=$_POST["id"];
		$action=$_POST["action"];
		$where['wechat_id']=array("EQ",$this->wechat_id());
		$where['fid']=array("EQ",0);
		$move=new MoveAction();
		$flag=$move->move("wechat_menu",$moveid,"id",$where,"sort",$action);
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
	 * 移动子分类
	 */
	public function move_menu_son(){
		$moveid=$_POST["id"];
		$action=$_POST["action"];
		$where['wechat_id']=array("EQ",$this->wechat_id());
		$where['fid']=array("EQ",$_GET['aid']);
		$where['type ']=array("EQ",1);
		$move=new MoveAction();
		$flag=$move->move("wechat_menu",$moveid,"id",$where,"sort",$action);
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






























}
