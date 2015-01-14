<?php

class SystemAction extends CommonAction {

    public function index(){

		$this->display();

    }
    //账户信息
    public function account(){

        $a=M('Account');
        $id=is_login();
        $db_prefix=C('DB_PREFIX');
        $data['fid_a2']=$id;
        $account=trim($_POST[account]);
        $nickname=trim($_POST[nickname]);
        if($_POST['account']){
            $data['username']=array('Like',"%{$account}%");
        }
         if($_POST['nickname']){
            $data['nickname']=array('LIKE',"%{$nickname}%");
        }
        import('ORG.Util.Page');// 导入分页类
        $count= $a->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //$user=$a->alias('a')->where("fid_a2={$id}")->order('uid desc')->limit($Page->firstRow.','.$Page->listRows)->field("a.*,(SELECT name FROM {$db_prefix}account_type WHERE a.type_wuye=id) AS wuye,(SELECT name FROM {$db_prefix}account_type WHERE a.type_xiaoqu=id) AS xiaoqu")->select();
        $result=$a->where($data)->order('status desc , uid desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $at=M("Account_type");
        $res=$at->select();
        $this->assign("res",$res);
        $this->assign('result',$result);
        $this->display();

    }

    //权限
    public function account_auth(){

        if(IS_POST){
            $data['uid']=$_POST['uid'];
            $aga=M('AuthGroupAccess');
            $aga->where("uid={$account[uid]}")->delete();
            foreach ($_POST['menu'] as $v) {
                $data['group_id']=$v;
                $aga->add($data);
            }

            $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),"JSON");
        }

        $uid=$_GET['uid'];
        $at=M("Account");
        $account=$at->where("uid=%d",$uid)->find();
        $this->assign("account",$account);

        //读取全部权限
        $ag=M('AuthGroup');
        $group_data=$ag->where('status=1 AND `group`="A4"')->group('type')->order('id asc')->field('type')->select();
        foreach ($group_data as $k=>$v) {
            if(!empty($v['type'])){
                $group_list[$k]['name']=$v['type'];
                $group_list[$k]['list']=$ag->where("type='{$v[type]}' and status=1 AND `group`='A4'")->order('id asc')->select();
            }
        }
        $this->assign('group_list',$group_list);

        //读取权限列表
        $aga=M('AuthGroupAccess');
        $group_all=$aga->where("uid={$account[uid]}")->Field('group_id')->select();//获得用户组
        foreach ($group_all as $v) {
            $group[]=$v['group_id'];
        }
        $this->assign('group',$group);

        $this->display();
    }

    //权限分配
    public function account_auth_updata(){

        $data['uid']=$_POST['uid'];
        $aga=M('AuthGroupAccess');
        $aga->where("uid={$data[uid]}")->delete();
        foreach ($_POST['menu'] as $v) {
            $data['group_id']=$v;
            $aga->add($data);
        }

        $this->ajaxReturn(array('status'=>1,'msg'=>"操作成功"),"JSON");

    }

      //删除账号
    public function account_del(){
        $id=$_POST['id'];
        $data['status']='-1';
        $at=M("Account");
        $result=$at->where("uid={$id}")->data($data)->save();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"));
        }
    }
    //恢复账号
    public function account_huifu(){
        $id=$_POST['id'];
        $data['status']='1';
        $at=M("Account");
        $result=$at->where("uid={$id}")->data($data)->save();
        if($result){
            $this->ajaxReturn(array("status"=>1,"msg"=>"恢复成功"));
        }else{
            $this->ajaxReturn(array("msg"=>"恢复失败"));
        }
    }


    //添加账户
    public function account_add(){

        $at=M('AccountType');
        $account=M('Account');
        $aid=is_login();

        $wuye=$at->where("aid={$aid} and fid=0")->order('id desc')->select();
        if(IS_POST){
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>'真实姓名不能为空!'),'JSON');
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if($_POST['password']!=$_POST['repassword']){
                $this->ajaxReturn(array('msg'=>'两次输入密码不一致'),"JSON");
            }else if(empty($_POST['username'])){
                $this->ajaxReturn(array("msg"=>"登录账号不能为空"));
            }else if($account->where("username='{$_POST[username]}'")->find()){
                $this->ajaxReturn(array("msg"=>"该账号已存在，请您更换账户名，重新建立"),"JSON");
            }
            else{
            $data['username']=$_POST['username'];
              $data['group']=$_POST['group'];
               $data['status']=$_POST['status'];
               $data['fid_a2']=$aid;

               //$data['fid_a3']=$_POST['type_wuye'];
               $data['type_wuye']=$_POST['type_wuye'];

               if($_POST['group']=="A4"){
                 $wuye_accout=M("Account")->where("fid_a2 = %d AND type_wuye = %d",$aid,$_POST['type_wuye'])->find();
      
               $data['fid_a3']=$wuye_accout["uid"];
                $data['type_xiaoqu']=$_POST['type_xiaoqu'];
               }

               if(!empty($_POST['password'])){
                $data['password']=MD5(C('WMD5').$_POST['password']);
               }
               $data['nickname']=$_POST['nickname'];
               $data['tel']=$_POST['tel'];
               $data['email']=$_POST['email'];
               $data['remark']=$_POST['remark'];
               if(empty($_POST['id'])){//添加
                    $result=$account->data($data)->add();
            
                    if($result){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
                    }else{
                        $this->ajaxReturn(array('status'=>0,'msg'=>'添加失败'),'JSON');
                    }
               }else{//编辑
                    $result=$account->where('id=%d',$_POST['id'])->data($data)->save();
                    if($result){
                        $this->ajaxReturn(array('status'=>1,'msg'=>'编辑成功'),'JSON');
                    }else{
                        $this->ajaxReturn(array('status'=>0,'msg'=>'编辑失败'),'JSON');
                    }
               }
            }
        }
        $this->assign('wuye',$wuye);
        $this->display();

    }


     //编辑账号信息ui
    public function user_edit(){
        $uid=$_GET['uid'];
        $aid=is_login();
        $at=M("Account");
        $mtp=M('Account_type');
        $result=$at->where("uid=%d",$uid)->find();
        $res=$mtp->where("id=%d",$result['type_wuye'])->find();
        $res1=$mtp->where("id=%d",$result['type_xiaoqu'])->find();
      $atp=M('AccountType');
        $aid=is_login();
        $wuye=$atp->where("aid={$aid} and fid=0")->order('id desc')->select();
        $this->assign("wuye",$wuye);

        $this->assign("res",$res);
        $this->assign("res1",$res1);
        $this->assign("result",$result);
        $this->display();
    }

     //编辑账号信息data
    public function user_edit_data(){
        $at=M("Account");
        $aid=is_login();
        if(IS_POST){
            $id=$_POST['id'];
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
           
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
            $data['status']=$_POST['status'];
            $data['nickname']=$_POST['nickname'];
            $data['email']=$_POST['email'];
            $data['fid_a2']=$aid;
            if(!empty($_POST['type_wuye'])){
                $data['type_wuye']=$_POST['type_wuye'];
            }
            $data['group']=$_POST['group'];
            if($_POST['group']=="A4"){
               $wuye_accout=M("Account")->where("fid_a2 = %d AND type_wuye = %d",$aid,$_POST['type_wuye'])->find();     
               $data['fid_a3']=$wuye_accout["uid"];
               if(!empty($_POST['type_xiaoqu'])){
                    $data['type_xiaoqu']=$_POST['type_xiaoqu'];
               }
            }
           
           
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>"真实姓名不能为空"),"JSON");
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if(empty($password)){
                $result=$at->where("uid=%d",$id)->data($data)->save();


                if($result){
                    $this->ajaxReturn(array('status'=>1,'msg'=>'编辑成功'),"JSON");
                }else{
                     $this->ajaxReturn(array('msg'=>'操作失败或没有修改项'),"JSON");
                }
            }else if(!empty($password)){
                if(empty($repassword)){
                    $this->ajaxReturn(array('msg'=>"确认密码不能为空"),"JSON");
                }else if($password!=$repassword){
                    $this->ajaxReturn(array('msg'=>"两次输入密码不一致"),"JSON");
                }else{
                    $data['password']=MD5(C('WMD5').$_POST['password']);
                    $result=$at->where('uid=%d',$id)->data($data)->save();

                    $this->ajaxReturn(array('status'=>1,'msg'=>"编辑成功"),"JSON");
                }
            }
        }
    }



    
    //物业列表
    public function wuye(){

    	$at=M('AccountType');
    	$aid=is_login();
        $data['aid']=$aid;
        $data['fid']=0;
        $name=trim($_POST[account]);
        if($_POST['account']){
            $data['name']=array('Like',"%{$name}%");
        }
        import('ORG.Util.Page');// 导入分页类
        $count= $at->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
    	$wuye=$at->where($data)->limit($Page->firstRow.','.$Page->listRows)->order('status desc,id desc')->select();

    	$this->assign('wuye',$wuye);
		$this->display();

    }



    //添加 编辑 物业
    public function wuye_add(){

        $at=M('AccountType');
        $aid=is_login();

    	if(IS_POST){

	    	if(empty($_POST['name'])){$this->ajaxReturn(array('msg'=>'物业公司名称不能为空'),'JSON');}
	    	
	    	$at->create();
	    	$at->aid=$aid;
            if($_POST['new_account']==1){
                $at->new_account=1;
            }else{
                 $at->new_account=0;
            }
            if($_POST['status']==1){
                $at->status=1;
            }else{
                 $at->status=0;
            }
            if(empty($_POST['id'])){
                $at->add();//新增
            }else{
                $at->save();//更新
            }

	    	$this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
    	}

        if(isset($_GET['id'])){

            $wuye=$at->where("aid={$aid} and id={$_GET[id]}")->find();
            $this->assign('wuye',$wuye);
            $this->assign('title','编辑物业');
        }else{
            $this->assign('title','添加物业');
        }
		$this->display();

    }
    //小区列表
    public function xiaoqu(){

        $fid=$_GET['fid'];
        $aid=is_login();
        $at=M('AccountType');
        //获取所属物业
        $name=$at->where("aid={$aid} and id={$fid}")->getfield('name');
        $this->assign('name',$name);
		
		$data['aid']=$aid;
		$data['fid']=$fid;
		$name=trim($_POST['account']);
        if($_POST['account']){
			$data1['aid']=$aid;
			$data1['name']=array("LIKE","%{$name}%");
			$xiaoqu=$at->where($data1)->order('id desc')->select();
		}else{
			$xiaoqu=$at->where($data)->order('id desc')->select();
		}

        

        $this->assign('xiaoqu',$xiaoqu);
        $this->display();

    }

    //添加小区
    public function xiaoqu_add(){

        $fid=$_GET['fid'];
        $aid=is_login();
        $at=M('AccountType');
        //获取所属物业
        $name=$at->where("aid={$aid} and id={$fid}")->getfield('name');
        $this->assign('name',$name);

        if(IS_POST){

            if(empty($_POST['name'])){$this->ajaxReturn(array('msg'=>'小区名称不能为空'),'JSON');}

            $at->create();
            $at->aid=$aid;
            if($_POST['status']==1){
                $at->status=1;
            }else{
                 $at->status=0;
            }

            $wg=M('WechatGroup');

            $wid=M('Account')->where('uid='.is_login())->getfield('wechat_id');
            $weixin=M('Wechat')->where("id={$wid}")->find();
            import('COM.ThinkWechat');
            C('WECHAT_TOKEN', $weixin['wtoken']);
            C('WECHAT_APPID',$weixin['wappid']);
            C('WECHAT_APPSECRET',$weixin['wsecret']);
            $weixin = new ThinkWechat ();

            if(empty($_POST['id'])){

                $xiaoqu_id=$at->add();//新增

                $add['group']['name']=$_POST['name'];
                $group=$weixin->addGroup(jsencode($add));

                $group=json_decode($group,true);
                $group=$group['group'];
                $wg_data['wechat_id']=$wid;
                $wg_data['wid']=$group['id'];
                $wg_data['wname']=$group['name'];
                $wg_data['xiaoqu_id']=$xiaoqu_id;
                $wg_data['xiaoqu_name']=$_POST['name'];
                $wg->add($wg_data);

            }else{
                $at->save();//更新

                $wid=$wg->where("xiaoqu_id={$_POST[id]}")->getfield('wid');
                $edit['group']['id']=$wid;
                $edit['group']['name']=$_POST['name'];
                $data=$weixin->editGroup(jsencode($edit));

                $wg_data['wname']=$_POST['name'];
                $wg_data['xiaoqu_name']=$_POST['name'];
                $wg->where("wid={$wid}")->save($wg_data);

            }

            

            $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
            

        }

        if(isset($_GET['id'])){

            $xiaoqu=$at->where("aid={$aid} and id={$_GET[id]}")->find();
            $this->assign('xiaoqu',$xiaoqu);

            $this->assign('title','编辑小区');
        }else{
            $this->assign('title','添加小区');
        }
        $this->display();

    }

     //归属物业表单
    public function wuyeselform(){
        $mt=M("MessageType");
        $form=C('FORUM_LIST.WY_FORM');
        $aid=is_login();
        import('ORG.Util.Page');// 导入分页类

        //找出小区编号
        $xiaoqu_ids=M("AccountType")->where("aid = %d",$aid)->field("id")->select();
        $xiaoqu_id=array();
        foreach ($xiaoqu_ids as $key => $value) {
            array_push($xiaoqu_id, $value['id']);
        }
        

        //条件
        $wheres['xiaoqu_id']=array("IN",$xiaoqu_id);
        $wheres['aflag']=array("EQ",$form);
        $name=trim($_POST['name']);
        $keyword=trim($_POST['keyword']);
        if($_POST['name']){
            $wheres['name']=array('Like',"%{$name}%");
        }
         if($_POST['keyword']){
            $wheres['keyworks']=array('LIKE',"%{$keyword}%");
        }

        $count= $mt->where($wheres)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        $res=$mt->where($wheres)->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($res as $key => $one) {
            //找出小区名称
            $res[$key]["xiaoqu_name"]=M("AccountType")->where("id = %d",$one['xiaoqu_id'])->getField("name");
        }
        $this->assign('res',$res);
        $this->display();
    }


    public function deletewuyeselform(){
        $id=$_POST['id'];
        if(!empty($id)){
            $m=M();
            $m->startTrans(); 

            $flag=$m->table(C('DB_PREFIX').'message_type')->where("id = %d",$id)->delete();

            if($flag){

                $flag1=$m->table(C('DB_PREFIX').'message')->where("aid = %d",$id)->delete();
                $m->commit();
                $this->ajaxReturn(array('flag'=>1,'msg'=>'操作成功'),"JSON");
            }else{
                $m->rollback();
                $this->ajaxReturn(array('flag'=>0,'msg'=>'操作失败'),"JSON");
            }

        }else{
            $this->ajaxReturn(array('flag'=>0,'msg'=>'请选择要操作的类别'),"JSON");
        }

    }



    //归属物业表单数据列表
    public function wuyeformmodiled(){
        $id=$_GET['id'];
        $this->assign("id",$id);
        $modelist=M("Message");

        import('ORG.Util.Page');// 导入分页类

        $data['aid']=$id;
        $data['is_delete']=0;
           if($_POST['type']){
            switch ($_POST['type']) {
                case 'type0':
                    $data['handle_status']=array("eq","0");//未处理
                    break;
                case 'type1':
                    $data['handle_status']=array("eq","1");//已处理
                    break;
                case 'type2':
                    $data['handle_status']=array("eq","2");//处理中
                    break;
            }
            /*
            if($_POST['type']!= -1){
                $handle=trim($_POST['type']);
                $data['handle_status']=array("eq","{$handle}");
            }
            */
        }
        
        $count= $modelist->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出


  
 
        $this->assign('page',$show);// 赋值分页输出
        $result=$modelist->where($data)->limit($Page->firstRow.','.$Page->listRows)->select();



        foreach ($result as $key => $value) {
            $result[$key]['form_content']=unserialize($value['form_content']);
        }

       

        $formdisplay=M("Form");
        $formresult=$formdisplay->where("aid='%d' AND is_display=%d",$id,1)->select();
        foreach ($formresult as $key => $value) {
            $formresult[$key]['options']=unserialize($value['options']);
        }
        //这里还差模型选项的内容，当显示选项列表的时候出现的内容

        $this->assign("formresult",$formresult);
        $this->assign("result",$result);
        $this->display();

    }


    //编辑物业表单
    public function elsemodeledit(){
        $id=$_GET['id'];
        $eidtmodel=M("Message");
        $result=$eidtmodel->where("id=%d",$id)->find();

        $formmoeld=M("form");
        $form_model=$formmoeld->where("aid = %d",$result['aid'])->select();

        foreach ($form_model as $key => $value) {
            $form_model[$key]['options']=unserialize($value['options']);
        }
        $result1=$result['form_content'];
        $form_content=unserialize($result1);
        $this->assign("form_model",$form_model);
        $this->assign("form_content",$form_content);
        $this->assign("result",$result);
        $this->display('elseModelEdit');
    }


    //编辑小区表单类型
    public function xiaoqu_editform(){
        $id=$_GET["id"];

        $mtresult=M("MessageType")->where("id = %d",$id)->find();

        $atresult=M("AccountType")->where("id = %d",$mtresult['xiaoqu_id'])->find();

        $this->assign("mtresult",$mtresult);

        $this->assign("atresult",$atresult);

        $this->display();


    }


    //保存表单类型编辑
    public function xiaoqu_saveeditform(){
        $data['is_status']=$_POST['status'] ? 1 : 0;
        $data['allow_delete']=$_POST['allow_delete'] ? 1 : 0;
        $data['allow_hide']=$_POST['allow_hide'] ? 1 : 0;
        if(!empty($_POST['id'])){
            $flag=M("MessageType")->where("id = %d",$_POST['id'])->save($data);
            if($flag){
                $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功!'),"JSON");
            }else{
                $this->ajaxReturn(array('status'=>0,'msg'=>'操作失败!'),"JSON");
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'msg'=>'请选择要操作的类型'),"JSON");
        }
        
    }

     //编辑选项模型data
    public function editelsemodel(){
        $mid=$_POST['mid'];

        $s=$_POST['model'];
        $options=serialize($s);
        $data['account']="匿名";
        $data['handle_status']=trim($_POST['handle_status']);
        $data['assess']=trim($_POST['assess']);
        $data['remark']=$_POST['remark'];
        $data['post_time']=date("Y-m-d H:m:s",time());
        $data['form_content']=$options;
        $message=M("Message");
        $result=$message->where("id=%d",$mid)->data($data)->save();

        if($result){
            $this->success("编辑成功");
        }else{
            $this->error("没有选择项或编辑失败");
        }
    }


     //删除选项
    public function del_elsemodel(){
        $id=$_GET['id'];
        $delmodel=M("Message");
        $result=$delmodel->where("id=%d",$id)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }

    }


    //删除多个选项
    public function del_moremodle(){
        $id=$_POST['id'];
        $delmodel=M("Message");
        $id=explode(",", $id);
        $where['id']=array("IN",$id);
        $result=$delmodel->where($where)->delete();
        if($result){
             $this->ajaxReturn(array('status'=>1,'msg'=>'删除成功'),"JSON");
     
        }else{
             $this->ajaxReturn(array('status'=>0,'msg'=>'删除失败'),"JSON");

        }
    }

    //小区联动
    public function chagexiaoqu(){
        if(IS_POST){
            $id=$_POST['id'];
            $at=M("Account_type");
            $result=$at->where("fid={$id}")->select();
            $data=array();
            foreach ($result as $key => $value) {
                $data[$key]['id']=$value['id'];
                $data[$key]['name']=$value['name'];
            }
            $this->ajaxReturn($data);
        }
    }

    //修改资料
    public function simplify(){
        $id=$_GET['id'];
        $mt=M("Account");
        $result=$mt->where("uid={$id}")->find();

        $this->assign("result",$result);
        $this->display();
    }


    //修改资料data
    public function simpledata(){
        $at=M("Account");
        if(IS_POST){
            $id=$_POST['id'];
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
            $data['status']=$_POST['status'];
            $data['nickname']=$_POST['nickname'];
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>"真实姓名不能为空"),"JSON");
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if(empty($password)){
                $result=$at->where("uid=%d",$id)->data($data)->save();
                if($result){
                    $this->ajaxReturn(array('status'=>1,'msg'=>'编辑成功'),"JSON");
                }else{
                     $this->ajaxReturn(array('msg'=>'编辑失败'),"JSON");
                }
            }else if(!empty($password)){
                if(empty($repassword)){
                    $this->ajaxReturn(array('msg'=>"确认密码不能为空"),"JSON");
                }else if($password!=$repassword){
                    $this->ajaxReturn(array('msg'=>"两次输入密码不一致"),"JSON");
                }else{
                    $data['password']=MD5(C('WMD5').$_POST['password']);
                    $result=$at->where('uid=%d',$id)->data($data)->save();
                    $this->ajaxReturn(array('status'=>1,'msg'=>"编辑成功"),"JSON");
                }
            }
        }
    }



































}