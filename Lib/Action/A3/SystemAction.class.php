<?php

class SystemAction extends CommonAction {

    public function index(){

		$this->display();

    }
    //账户信息
    public function account(){

        $at=M('Account');
        $id=is_login();
        $account=$at->where("uid=%d",$id)->find();
        $name=trim($_POST[account]);
        $nick=trim($_POST[nickname]);
        //$data['fid_a3']=$id;
         if($_POST['account']){
            $data['username']=array('Like',"%{$name}%");
        }
         if($_POST['nickname']){
            $data['nickname']=array('LIKE',"%$nick%");
        }
        $data['group']="A4";
        $data['type_wuye']=$account['type_wuye'];
        $db_prefix=C('DB_PREFIX');
        import('ORG.Util.Page');// 导入分页类
        $count= $at->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $mtp=M('Account_type');
        $result=$at->where($data)->order("status desc,uid desc ")->limit($Page->firstRow.','.$Page->listRows)->select();

        $res=$mtp->where("id=%d",$account['type_wuye'])->find();

        $this->assign("result",$result);
        $this->assign('res',$res);
        $this->display();
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

        $at=M('Account_type');
        $account=M("Account");
        $aid=is_login();

        $resaccount=$account->where("uid=%d",$aid)->find();
        $wuye=$at->where("id=%d",$resaccount['type_wuye'])->find();

       // $wuye=$at->where("aid={$aid} and fid=0")->order('id desc')->select();
        $xiaoqu=$at->where("fid=%d",$resaccount['type_wuye'])->order('id desc')->select();

        if(IS_POST){
            if(empty($_POST['nickname'])){
                $this->ajaxReturn(array('msg'=>'真实姓名不能为空!'),'JSON');
            }else if(empty($_POST['email'])){
                $this->ajaxReturn(array('msg'=>'邮箱不能为空'),"JSON");
            }else if(!empty($_POST['password'])){
                $data['password']=MD5(C('WMD5').$_POST['password']);
            }
            else if($_POST['password']!=$_POST['repassword']){
                $this->ajaxReturn(array('msg'=>'两次输入密码不一致'),"JSON");
            }
            else if(empty($_POST['username'])){
                $this->ajaxReturn(array("msg"=>"登录账号不能为空"));
            }else if($account->where("username='{$_POST[username]}'")->find()){
                $this->ajaxReturn(array("msg"=>"该账号已存在，请您更换账户名，重新建立"),"JSON");
            }else{
               $data['username']=$_POST['username'];
               $data['group']="A4";
               $data['fid_a3']=$aid;
               $data['type_wuye']=$wuye['id'];
               $data['type_xiaoqu']=trim($_POST['type_xiaoqu']);
               
               $data['nickname']=$_POST['nickname'];
               $data['tel']=$_POST['tel'];
               $data['email']=$_POST['email'];
               $data['remark']=trim($_POST['remark']);
               $data['status']=trim($_POST['status']);
               $data['fid_a2']=$resaccount['fid_a2'];

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
                        $this->ajaxReturn(array('status'=>1,'msg'=>'操作成功'),'JSON');
                    }else{
                        $this->ajaxReturn(array('status'=>0,'msg'=>'编辑失败'),'JSON');
                    }
               }
            }
        }
        $this->assign('wuye',$wuye);
        $this->assign('xiaoqu',$xiaoqu);
        $this->display();

    }
    //物业列表
    public function wuye(){

    	$at=M('AccountType');
        $t=M("Account");
    	$aid=is_login(); //当前登录账号，根据这个账号去找所属的物业公司名称，也就是typewuye 这个值，再根据这个值，找account_type
        //对应的物业公司，再根据物业公司的编号找到这个物业
        $res=$t->where("uid=%d",$aid)->getField('type_wuye');
        
        $result=$at->where("id=%d",$res)->find();

        $this->assign("wuye",$result);

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

        $fid=$_GET['fid'];//获取fid的编号，就是每个物业下的小区的编号  
        session('fidd',$fid);  //设置session

        //$aid=is_login();
        $at=M('Account_type');
        //获取所属物业
        $name=$at->where("id=%d",$fid)->find();
        $this->assign('fid',$fid);
        $this->assign('name',$name);//$_POST['account']
        import('ORG.Util.Page');// 导入分页类
        $data['fid']=$fid;
        //$fadddd=$_POST['fid'];
        $nick=trim($_POST[account]);


        if($_POST['account']){
            $data['name']=array('Like',"%{$nick}%");
            $data['fid']=array('Like',"%{$fadddd}%");
        }
        $count= $at->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $xiaoqu=$at->where($data)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('xiaoqu',$xiaoqu);
        $this->display();

    }
    //添加小区
    public function xiaoqu_add(){

        $fid=$_GET['id'];
        $aid=$_GET['aid'];

        //$aid=is_login();
        $at=M('Account_type');
        //获取所属物业
        $name=$at->where("id={$fid}")->find();


        $this->assign('name',$name);

        if(IS_POST){
            $data['name']=$_POST['name'];
            if(empty($_POST['name'])){
                $this->ajaxReturn(array('msg'=>'小区名称不能为空'),'JSON');
            }
            $data['status']=$_POST['status'];
            $data['fid']=$_POST['fid'];
            $data['aid']=$_POST['aid'];
            $data['remark']=$_POST['remark'];
            $result=$at->data($data)->add();
            if($result){
                $this->ajaxReturn(array("status"=>1,"msg"=>"操作成功"),"JSON");
            }else{
                $this->ajaxReturn(array("msg"=>"操作失败"),"JSON");
            }

         if(isset($_GET['id'])){

        $xiaoqu=$at->where("aid={$aid} and id={$_GET[id]}")->find();
        $this->assign('xiaoqu',$xiaoqu);

        $this->assign('title','编辑小区');
        }else{
            $this->assign('title','添加小区');
        }

        }
        $this->display();
    }

    //编辑小区
    public function xiaoqu_edit(){
        $id=$_GET['id'];
        $at=M("Account_type");
        if(IS_POST){
            if(empty($_POST['name'])){
                $this->ajaxReturn(array("status"=>1,"msg"=>"小区名称不能为空"));
            }
            $data['name']=$_POST['name'];
            $typeid=$_POST['typeid'];
            $data['status']=$_POST['status'];
            $data['remark']=$_POST['remark'];

            $r=$at->where("id='%d'",$typeid)->data($data)->save();

            if($r){
                $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"));
            }else{
                $this->ajaxReturn(array("msg"=>"编辑失败或没改变项"));
            }

        }
        $result=$at->where("id='{$id}'")->find();
		//dump($result);
        $res=$at->where("id='%d'",$result['fid'])->find();
        
        $this->assign("res",$res);
        $this->assign("result",$result);
        $this->display();
    }

    //编辑账号信息ui
    public function user_edit(){
        $uid=$_GET['uid'];
        $aid=is_login();
        $at=M("Account");
      $mtp=M('Account_type');


        $resaccount=$at->where("uid=%d",$aid)->find();
        $xiaoqu=$mtp->where("fid=%d",$resaccount['type_wuye'])->order('id desc')->select();


        $result=$at->where("uid=%d",$uid)->find();
        $res=$mtp->where("id=%d",$result['type_wuye'])->find();
        $res1=$mtp->where("id=%d",$result['type_xiaoqu'])->find();
        $this->assign("res",$res);
        $this->assign("res1",$res1);
        $this->assign("result",$result);
        $this->assign("xiaoqu",$xiaoqu);
        $this->display();
    }

    //编辑账号信息data
    public function user_edit_data(){
        $at=M("Account");
        if(IS_POST){
            $id=$_POST['id'];
            $password=$_POST['password'];
            $repassword=$_POST['repassword'];
            $data['tel']=$_POST['tel'];
            $data['group']="A4";
            $data['type_xiaoqu']=$_POST['type_xiaoqu'];
            $data['status']=trim($_POST['status']);
            $data['email']=$_POST['email'];
            $data['remark']=$_POST['remark'];
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

    //归属物业表单
    public function wuyeselform(){
        $mt=M("Message_type");
        $form=C('FORUM_LIST.WY_FORM');
        $aid=is_login();

        //登陆账号所属物业公司ID
        $wuye_id=M("Account")->where("uid = %d",$aid)->getField("type_wuye");

        //账户下的小区
        $xiaoqu_ids=M("AccountType")->where('fid = %d',$wuye_id)->field('id')->select();
        $xiaoqu_id=array();
        foreach ($xiaoqu_ids as $key => $value) {
            array_push($xiaoqu_id, $value['id']);
        }
        
        //条件
        $wheres['xiaoqu_id']=array("IN",$xiaoqu_id);
        $wheres['aflag']=array("EQ",$form);
        if(isset($_POST['bdname'])){
            $name=trim($_POST['bdname']);
            $wheres['name']=array("Like","%{$name}%");
        }


        import('ORG.Util.Page');// 导入分页类
        $count= $mt->where($wheres)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出



        $res=$mt->where($wheres)->limit($Page->firstRow.','.$Page->listRows)->order("id")->select();

        foreach ($res as $key => $one) {
            $res[$key]['xiaoqu_name']=M("AccountType")->where("id = %d",$one['xiaoqu_id'])->getField("name");
        }

        $this->assign('res',$res);
        $this->display();
    }

    //归属物业表单数据列表
    public function wuyeformmodiled(){
        $id=$_GET['id'];
        $modelist=M("Message");

        import('ORG.Util.Page');// 导入分页类
        $data['aid']=$id;
        $this->assign("id",$id);
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

        }
        
        $count= $modelist->where($data)->count();// 查询满足要求的总记录数
        $Page= new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show= $Page->show();// 分页显示输出



        $this->assign('page',$show);// 赋值分页输出
        $result=$modelist->where($data)->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($result as $key => $value) {
            $result[$key]['form_content']=unserialize($value['form_content']);
        }

        $res=$modelist->where("aid='%d' AND is_delete=%d ",$data['aid'],0)->getField("form_content");
        $for=unserialize($res);

        $formdisplay=M("Form");
        $formresult=$formdisplay->where("aid='%d' AND is_display=%d",$data['aid'],1)->select();
        foreach ($formresult as $key => $value) {
            $formresult[$key]['options']=unserialize($value['options']);
        }
        //这里还差模型选项的内容，当显示选项列表的时候出现的内容

        $this->assign("formresult",$formresult);
        $this->assign("for",$for);
        $this->assign("result",$result);
        $this->assign("aid",$data['aid']);
        $this->display();

    }

       //批量删除数据列表内容
    public function delete_one_model(){
        $ids=$_POST['id'];
        $ids=explode(',',$ids);
        $me=M("message");
        $where['id']=array("IN",$ids);
        $flag=$me->where($where)->delete();
        if($flag){
            $this->ajaxReturn(array("status"=>1,"msg"=>"删除成功"),"JSON");
        }else{
            $this->ajaxReturn(array("msg"=>"删除失败"),"JSON");
        }
        $this->ajaxReturn($message);
    }


     //编辑物业表单
    public function elsemodeleditui(){
        $id=$_GET['id'];
        $dd=$_SESSION['modelidlistid'];
        $this->assign("id",$dd);
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
        $this->display();
    }

     //编辑选项模型data
    public function editelsemodel(){
        $mid=$_POST['mid'];
        $s=$_POST['model'];
        $options=serialize($s);
        $id=$_SESSION['modelidlistid'];
        $this->assign("id",$id);
        $data['account']="匿名";
        $data['handle_status']=$_POST['handle_status'];
        $data['assess']=$_POST['assess'];
        $data['remark']=$_POST['remark'];
        $data['post_time']=date("Y-m-d H:m:s",time());
        $data['form_content']=$options;
        $message=M("Message");
        $result=$message->where("id=%d",$mid)->data($data)->save();
        if($result){
            //$this->success("编辑成功");
            $this->ajaxReturn(array("status"=>1,"msg"=>"编辑成功"),"JSON"); 
        }else{
            // $this->error("编辑失败");
            $this->ajaxReturn(array("msg"=>"没有改变项或编辑失败"),"JSON");
        }
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