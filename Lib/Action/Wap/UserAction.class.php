 <?php

class UserAction extends CommonAction {

    //用户选择小区
    public function xiaoqu_select(){

        /*
        $stoken = S ( 'S_TOKEN' ); // 从缓存获取ACCESS_TOKEN
        $stoken['tokentime']=date('Y-m-d H:i:s',$stoken['tokentime']);
        dump($stoken);
        */

        if(isset($_POST['str'])){
            $where['name']=array("like","%".$_POST['str']."%");
        }
        $at=M("AccountType");
        $where['status']=array("eq",1);
        $where['fid']=array("neq",0);
        $where['aid']=array('eq',session('WY_DID'));

        import('ORG.Util.Page');// 导入分页类
        $count      = $at->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出



        $xiaoqu=$at->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //echo $at->getlastsql();
        $this->assign("xiaoqu",$xiaoqu);

        $this->display();
        

    }

    //绑定小区
    public function xiaoqu_join(){
        
        $uid=session('WY_UID');//用户编号
        $data['xiaoqu_id']=$_POST['id'];
        $data['propertyid']='';
        session("WY_FID",null);//房号ID

        M("user")->where("id = {$uid}")->data($data)->save();
        session("WY_XID",$_POST['id']);//小区ID
        

        $wg=M('WechatGroup');
        $wx['to_groupid']=$wg->where("xiaoqu_id={$_POST[id]}")->getField('wid');
        $wx['openid']=$_SESSION['WY_OPENID'];

        $weixin=M('Wechat')->where("id={$_SESSION[WY_WID]}")->find();
        import('COM.ThinkWechat');

        C('WECHAT_TOKEN', $weixin['wtoken']);
        C('WECHAT_APPID',$weixin['wappid']);
        C('WECHAT_APPSECRET',$weixin['wsecret']);
        $weixin = new ThinkWechat ();
        $weixin->goGroup(jsencode($wx));

        $ajax_data['status']=1;
        $ajax_data['msg']="绑定小区成功，您可以继续在微信主界面查看下方各个菜单内容，谢谢您的关注！";
        $ajax_data['xiaoqu_id']=$_POST["id"];

        $this->ajaxReturn($ajax_data);
    }


    //显示绑定的小区
    public function show_selectxiaoqu(){
        $result=M("AccountType")->where("id = %d",session("WY_XID"))->find();
        $this->assign("result",$result);
        $this->display();
    }

    //绑定房号
    public function wuye_bind(){

        $xid=session('WY_XID');
        if(empty($xid)){

            $this->redirect('User/xiaoqu_select');
        }
        //获取物业认证码说明
        $mt=M("message_type");
        $forumlist=C("FORUM_LIST");
        $where['xiaoqu_id']=array("eq",$xid);
        $where['aflag']=array("eq",$forumlist['WY_PROPERTY_AUTH']);
        $result=$mt->where($where)->find();
        $this->assign("result",$result);
       $this->display();

    }
    //绑定房号 
    public function wuye_bind_save(){

        $uid=session("WY_UID");

        $auth=$_POST['auth'];//认证码

        $u=M("User");
        $id=M("Property")->where("auth = '%s' AND xiaoqu_id = %d",$auth,session('WY_XID'))->getfield('id');
    
        if($id=M("Property")->where("auth = '%s' AND xiaoqu_id = %d",$auth,session('WY_XID'))->getfield('id')){
            $save['propertyid']=$id;
            $u->where("id = %d",$uid)->data($save)->save();
            session("WY_FID",$id);//房号ID
            $data['status']=1;
            $data['msg']='绑定成功';
        }else{
            $data['status']=0;
            $data['msg']="认证码错误";
        }
        $this->ajaxReturn($data);
    
    }

    //业主中心
    public function index(){

        $mt=M("MessageType");
        $data['xiaoqu_id']=session('WY_XID');
        $fl1=C("FORUM_LIST.WY_COST_LIST");
        $fl2=C("FORUM_LIST.WY_PROPERTY");

        $data['aflag'] =array("eq",$fl1);
        $list1=$mt->where($data)->select();//物业费用分类
        $this->assign('list1',$list1);

        $data['aflag'] =array("eq",$fl2);
        $list2=$mt->where($data)->select();//物业信息表
        $this->assign('list2',$list2);



        //查看绑定的房号
        $fid=session('WY_FID');
        $number=M('Property')->where("id={$fid}")->getfield('number');
        $this->assign('number',$number);
        
        $this->display();
    }


    //物业信息表
    public function property(){
        $aid=$_GET['id'];//类型编号
        //小区房号编号
        $title=M('Property')->where("id = %d",session("WY_FID"))->getField("number");

        //信息列表
        $property=M("Message")->where("aid = %d AND title = '%s'",$aid,$title)->select();
        foreach ($property as $key => $value) {
            $property[$key]['form_content']=unserialize($property[$key]['form_content']);
        }

        $this->assign("property",$property);



        //信息字段
        $fm=M("form");
        $fresult=$fm->where("aid = %d",$aid)->select();
        $this->assign("fresult",$fresult);

        

        
        //查看绑定的房号
        $fid=session('WY_FID');
        $number=M('Property')->where("id={$fid}")->getfield('number');
        $this->assign('number',$number);

        //信息类型名称
        $mresult=M("MessageType")->where("id = %d",$aid)->find();
        $this->assign("mresult",$mresult);

        $this->display();
    }


    //物业费用分类
    public function costlist(){

        $aid=$_GET['id'];//类型编号
        //小区房号编号
        $title=M('Property')->where("id = %d",session("WY_FID"))->getField("number");

        //小区编号
        $xiaoqu_id=session('WY_XID');

        //信息
        $costlist=M("Message")->where("aid = %d AND title = '%s'",$aid,$title)->order("updatetime desc")->find();
        $this->assign("costlist",$costlist);

        //费用明细
        $pay=M("pay")->where("aid = %d",$costlist['id'])->select();
        $this->assign("pay",$pay);

        $totalmoney=0;
        foreach($pay as $one){
            $totalmoney+=$one['countnum'];
        }

        $this->assign("totalmoney",$totalmoney);

        //查看绑定的房号
        $fid=session('WY_FID');
        $number=M('Property')->where("id={$fid}")->getfield('number');
        $this->assign('number',$number);

        //信息类型名称
        $mresult=M("MessageType")->where("id = %d",$aid)->find();
        $this->assign("mresult",$mresult);

        //信息字段
        $fm=M("form");
        $fresult=$fm->where("aid =%d",intval($aid))->select();
        $this->assign("fresult",$fresult);

        $this->display();


    }




}
