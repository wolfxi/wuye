 <?php

class KeywordsAction extends CommonAction {

    private $forumlist=null;
    private $res=null;//物业所属
    private $res1=null;//账号所属

    //初始化查询参数
    public function _initialize(){

        parent::_initialize();


        $this->forumlist=C("FORUM_LIST");

        $uid=session('WY_UID');//用户编号
        $wuye_id=session('WY_ID');//物业编号


        $at=M("property");
        $this->res=$at->where("id={$wuye_id}")->find();//根据物业编号找到相关物业的所属账号

        $aid=$this->res['aid'];
        $this->id=$this->res['aid'];//登录账号的id

        $a=M("Account");
        $this->res1=$a->where("uid={$aid}")->find();//根据账号找到的内容
        $this->uid=$this->res1['uid'];

        $wetchid=$this->res1['wechat_id'];

    }



    //最想公告
    public function news(){
        $mt=M("Message_type");
       $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_INFO'],"小区公告")->find();
       $id=$mresult['id'];//message_type 里表单的的ID值
        $me=M("message");
        //$result=$me->where("title like '%最新公告' AND aid={$id} ")->select();
        $result=$me->where("aid={$id} ")->order("is_top desc,  sort desc,id desc")->select();

         //查找幻灯片
        $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);

        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
       
    }

    //最新公告详情
    public function elsenews(){
         $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $ms->where("id={$id}")->setInc('click_num'); //阅读量+1

        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }


    //报修申请
    public function repair(){
        if(empty($_SESSION["WY_FID"])){
            $this->redirect('User/wuye_bind');
       }
       $mt=M("Message_type");
       $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_FORM'],"报修投诉")->find();
       $id=$mresult['id'];//message_type 里表单的的ID值
       //从formAction中拷贝 elseModelListAddUi s //$id=$_SESSION['modelidlistid'];
        $modelui=M("Form");
        $result=$modelui->where("aid=%d  ",$id)->select();



        foreach ($result as $key=>$one) {
            $result[$key]['options']=unserialize($one['options']);
        }
        $this->assign("result",$result);//重formAction中拷贝 elseModelListAddUi e 
       $this->assign("repair",$mresult);
       $this->display('repair');

	  }


      //报修申请
    public function commitrepair(){
        if(IS_AJAX){
            $s=$_POST['model'];
            $options=serialize($s);
            $data['handle_status']=0;
            $data['assess']=0;
            $data['post_time']=date("Y-m-d H:m:s");
            $data['aid']= $_POST['aid'];
            $data['form_content']=$options;
            $me=M("Message");

            $fm=M("form");
            $fresult=$fm->where("aid = %d",$data['aid'])->select();

            foreach ($fresult as $key => $one) {
                if($one['is_must']){
                    if(empty($s[$one['id']])){
                         //$this->error($one['name']."不可以为空");
                        $this->ajaxReturn(array("msg"=>$one['name']."不可以为空"),"JSON");
                    }
                }
                if($one['selectpicker']==1){//验证邮件
                    if(!ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$s[$one['id']])){
                       // $this->error($one['name']."必须为邮件格式");
                        $this->ajaxReturn(array("msg"=>$one['name']."必须为邮件格式"),"JSON");
                    }
                }else if($one['selectpicker']==2){//验证手机
                    if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$s[$one['id']])){
                        //$this->error($one['name']."必须为手机号码");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为手机号码"),"JSON");
                    }

                }else if($one['selectpicker']==7){//验证邮编
                    if(!preg_match("/[0-9]{6}/",$s[$one['id']])){
                        //$this->error($one['name']."必须为邮编格式");
                        $this->ajaxReturn(array("msg"=>$one['name']."必须为邮编格式"),"JSON");
                    }
                }else if($one['selectpicker']==4){//验证qq
                    if(!preg_match('/^[1-9][0-9]{4,9}$/', $s[$one['id']])){
                        //$this->error($one['name']."必须为qq格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为qq格式"),"JSON");
                    }

                }else if($one['selectpicker']==3){//验证网址
                    if(!ereg("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$", $s[$one['id']])){
                        //$this->error($one['name']."必须为网址格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为网址格式"),"JSON");
                    }
                }else if($one['selectpicker']==6){//验证数字
                    if(!preg_match("^[0-9]*$/",$s[$one['id']])){
                       // $this->error($one['name']."必须为数字格式");
                          $this->ajaxReturn(array("msg"=>$one['name']."必须为数字格式"),"JSON");

                    }
                }else if($one['selectpicker']==5){//验证字母
                    if(!preg_match("^[a-zA-Z]+$", $s[$one['id']])){
                        //$this->error($one['name']."必须为字母格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为字母格式"),"JSON");
                    }
                }

            }


            //房号物业信息
            $presult=M("Property")->where("id = %d",session("WY_FID"))->find();
            $data['title']=$presult['number'];//物业编号
            $data['details']=$presult['title'];//物业名称


            //该表单类型的设置
            $message=M("Message_type")->where("id={$_POST[aid]}")->find();


            //检测允许是否匿名
            if($message['is_anon']){
                $data['account']="匿名";
            }else{
               $data['account']=M("User")->where("id = %d",session("WY_UID"))->getField("weixin_name"); 
            }
        

            $result=$me->data($data)->add();

            
            if($result){
                //向工作人员发送微信信息
                //1:获取工作人员openid
                if($message['worker_openid']!=""){
                    $worker_openid=explode(",", $message['worker_openid']);
                    if($message['accept_openid']!=""){
                        $accept_openid=explode(",", $message['accept_openid']);
                        $worker_openid=array_merge($worker_openid,$accept_openid);
                    }
                    //2:组装发送数据
                    $xiaoqu=M("AccountType")->where("id = '%s'",session("WY_XID"))->find();
                    $send_msg=$xiaoqu['name']."小区的用户提交了表单,\r\n";
                    //动态数据
                    foreach($fresult as $key=>$one){
                        $send_msg.=$one['name'].":".$s[$one['id']]."\r\n";
                    }
                    $send_msg.="请及时处理。";
                        foreach ($worker_openid as $key => $value) {
                            $r=$this->wechat_send($value,$send_msg);    
                        }
                        
                    }

                //发送微信信息
                $openid=$_SESSION['WY_OPENID'];
                $msg=$message['success_send_msg'];
                $r=$this->wechat_send($openid,$msg);

                

                $this->ajaxReturn(array("flag"=>1,"msg"=>$message['success_info']));
            }else{
                //$this->error("添加失败");
                 $this->ajaxReturn(array("msg"=>$message['faile_info']));
            }
                
        }

    }

    //建议/报修 详细介绍
    public function formintroduce(){
        $id=$_GET['id'];
        $result=M("MessageType")->where("id = %d",intval($id))->find();
        $this->assign("result",$result);
        $this->display();
    }


    //建议/报修 历史记录
    public function formhistory(){
        $aid=$_GET['id'];
        $account=M("User")->where("id = %d",session("WY_UID"))->getField("weixin_name");
        $me=M("Message");
        $result=$me->where("aid = %d AND account = '%s'",$aid,$account)->order("post_time")->select();
        $this->assign("result",$result);
      
        $this->display();
    }

    //建议/报修 历史记录详细
    public function formhistory_detail(){
        //获取信息本身
        $id=$_GET["id"];
        $result=M("Message")->where("id = %d",$id)->find();
        $result['form_content']=unserialize($result['form_content']);
        $this->assign("result",$result);

        $mtresult=M("MessageType")->where("id = %d",$result['aid'])->find();
        $this->assign("mtresult",$mtresult);

        //获取表单模型
        $fresult=M("form")->where("aid = %d",$mtresult['id'])->select();
        $this->assign("fresult",$fresult);



        $this->display();
    }

    //投诉建议
    public function jianyi(){

       if(empty($_SESSION["WY_FID"])){
            $this->redirect('User/wuye_bind');
       }
       $mt=M("Message_type");
       $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_FORM'],"投诉建议")->find();
       $id=$mresult['id'];//message_type 里表单的的ID值
       //从formAction中拷贝 elseModelListAddUi s //$id=$_SESSION['modelidlistid'];
        $modelui=M("Form");
        $result=$modelui->where("aid=%d",$id)->select();



        foreach ($result as $key=>$one) {
            $result[$key]['options']=unserialize($one['options']);
        }
        $this->assign("result",$result);//重formAction中拷贝 elseModelListAddUi e 
       $this->assign("repair",$mresult);
       $this->display();

    }


    //投诉建议
    public function toushu(){

        if(IS_AJAX){
        $s=$_POST['model'];
        $options=serialize($s);

       
        $data['handle_status']=0;
        $data['assess']=0;
        $data['post_time']=date("Y-m-d H:m:s");
        $data['aid']= $_POST['aid'];
        $data['form_content']=$options;
        $me=M("Message");

        $fm=M("form");
        $fresult=$fm->where("aid = %d",$data['aid'])->select();

        foreach ($fresult as $key => $one) {
            if($one['is_must']){
                if(empty($s[$one['id']])){
                     //$this->error($one['name']."不可以为空");
                    $this->ajaxReturn(array("msg"=>$one['name']."不可以为空"),"JSON");
                }
            }
            if($one['selectpicker']==1){//验证邮件
                if(!ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$s[$one['id']])){
                   // $this->error($one['name']."必须为邮件格式");
                    $this->ajaxReturn(array("msg"=>$one['name']."必须为邮件格式"),"JSON");
                }
            }else if($one['selectpicker']==2){//验证手机
                if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$s[$one['id']])){
                    //$this->error($one['name']."必须为手机号码");
                     $this->ajaxReturn(array("msg"=>$one['name']."必须为手机号码"),"JSON");
                }

            }else if($one['selectpicker']==7){//验证邮编
                if(!preg_match("/[0-9]{6}/",$s[$one['id']])){
                    //$this->error($one['name']."必须为邮编格式");
                    $this->ajaxReturn(array("msg"=>$one['name']."必须为邮编格式"),"JSON");
                }
            }else if($one['selectpicker']==4){//验证qq
                if(!preg_match('/^[1-9][0-9]{4,9}$/', $s[$one['id']])){
                    //$this->error($one['name']."必须为qq格式");
                     $this->ajaxReturn(array("msg"=>$one['name']."必须为qq格式"),"JSON");
                }

            }else if($one['selectpicker']==3){//验证网址
                if(!ereg("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$", $s[$one['id']])){
                    //$this->error($one['name']."必须为网址格式");
                     $this->ajaxReturn(array("msg"=>$one['name']."必须为网址格式"),"JSON");
                }
            }else if($one['selectpicker']==6){//验证数字
                if(!preg_match("^[0-9]*$/",$s[$one['id']])){
                   // $this->error($one['name']."必须为数字格式");
                      $this->ajaxReturn(array("msg"=>$one['name']."必须为数字格式"),"JSON");

                }
            }else if($one['selectpicker']==5){//验证字母
                if(!preg_match("^[a-zA-Z]+$", $s[$one['id']])){
                    //$this->error($one['name']."必须为字母格式");
                     $this->ajaxReturn(array("msg"=>$one['name']."必须为字母格式"),"JSON");
                }
            }

        }

        //房号物业信息
        $presult=M("Property")->where("id = %d",session("WY_FID"))->find();
        $data['title']=$presult['number'];//物业编号
        $data['details']=$presult['title'];//物业名称

        //该表单类型的设置
        $message=M("Message_type")->where("id={$_POST[aid]}")->find();


        //检测允许是否匿名
        if($message['is_anon']){
            $data['account']="匿名";
        }else{
           $data['account']=M("User")->where("id = %d",session("WY_UID"))->getField("weixin_name"); 
        }
        
        
        $result=$me->data($data)->add();

        
        if($result){

            //向工作人员发送微信信息
            //1:获取工作人员openid
            if($message['worker_openid']!=""){
                $worker_openid=explode(",", $message['worker_openid']);
                if($message['accept_openid']!=""){
                        $accept_openid=explode(",", $message['accept_openid']);
                        $worker_openid=array_merge($worker_openid,$accept_openid);
                }
                //2:组装发送数据
                $xiaoqu=M("AccountType")->where("id = '%s'",session("WY_XID"))->find();
                $send_msg=$xiaoqu['name']."小区的用户提交了表单,\r\n";
                //动态数据
                foreach($fresult as $key=>$one){
                    $send_msg.=$one['name'].":".$s[$one['id']]."\r\n";
                }
                $send_msg.="请及时处理。";

                foreach ($worker_openid as $key => $value) {
                    $r=$this->wechat_send($value,$send_msg);    
                }
                
            }

            
            //向用户发送提交成功信息发送微信信息
            $openid=$_SESSION['WY_OPENID'];
            $msg=$message['success_send_msg'];
            $r=$this->wechat_send($openid,$msg);

            

            $this->ajaxReturn(array("flag"=>1,"msg"=>$message['success_info']));
        }else{
            //$this->error("添加失败");
             $this->ajaxReturn(array("msg"=>$message['faile_info']));
        }
            
        }
    }

    //物业介绍
    public function introduce(){
       $mt=M("Message_type");
       $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_INFO'],"社区风采")->find();
       
       $id=$mresult['id'];//message_type 里表单的的ID值
        $me=M("message");
        $result=$me->where("aid={$id} ")->select();

        //查找幻灯片
        $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);

        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
       
    }




    //物业介绍详情
    public function elseintroduce(){
        $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $ms->where("id={$id}")->setInc('click_num'); //阅读量+1
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    //邻里互动，start
    public function interaction(){
         $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s'",session("WY_XID"),$this->forumlist['WY_BBS'])->select();
         $this->assign("result",$mresult);
         $this->display();

    }



    public function interactionlist(){

        $id=$_GET['id'];
        $me=M("message");
        $ry=M("replay");
        $result=$me->where("aid = %d",$id)->select();
        foreach($result as $key=>$one){
            $result[$key]['reply']=$ry->where("mid = %d",$one['id'])->count();
        }

        //查找幻灯片
         $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);

        $this->assign("id",$id);
        $this->assign("result",$result);
        $this->display();

    }

    public function interactionone(){
        $id=$_GET['id'];
        $me=M("message");
        $result=$me->where("id = %d",$id)->find();

        $me->where("id = %d",$id)->setInc('click_num'); // 点击量加1

        $ry=M("replay");
        $rresult=$ry->where("mid = %d",$id)->order("time")->select();

        $result['reply']=count($rresult);

        $this->assign("result",$result);
        $this->assign("rresult",$rresult);
        $this->display();

    }


    public function replayone(){

        $id=$_POST['id'];
        $content=$_POST["content"];
        if(!empty($id) && !empty($content)){
            $ur=M("user");
            $uresult=$ur->where("id = %d",session("WY_UID"))->find();
            $add['mid']=$id;
            $add['replay_info']=$content;
            $add['time']=date("Y-m-d :H:i:s");
            $add['name']=$uresult['weixin_name'];
            $ry=M("replay");
            $flag=$ry->data($add)->add();

            if($flag){
                $data['flag']=1;
                  $this->ajaxReturn($data);
            }else{
                $data['flag']=0;
                $data['msg']="回复失败!!!";
                  $this->ajaxReturn($data);
            }
        }else{
            $data['flag']=0;
            $data['msg']="请填写相关内容!!!";
            $this->ajaxReturn($data);
        }


    }


    public function addmessage(){
        $id=$_GET["id"];
        $mt=M("message_type");
        $result=$mt->where("id = %d",$id)->find();

        $this->assign("result",$result);
        $this->display();

    }

    public function savemessage(){
        $title=$_POST['title'];
        $content=$_POST['content'];
        $id=$_POST['id'];
        if(!empty($id) && !empty($content) && !empty($title)){

            $uid=session("WY_UID");
            $ur=M("user");
            $uresult=$ur->where("id = %d",$uid)->find();
            $add['aid']=$id;
            $add['click_num']=0;
            $add['details']=$content;
            $add['title']=$title;
            $add['name']=$uresult['weixi_name'] ? $uresult['weixi_name'] : '';
            $add['post_time']=date("Y-m-d :H:i:s");
            $add['remark']=$_POST['image'];//图片
            $me=M("message");
            $flag=$me->add($add);
          
            if($flag){
                $data['flag']=1;
                $data['msg']=U("Keywords/interactionone?id=".$flag);
                $this->ajaxReturn($data);
            }else{
                $data['flag']=0;
                $data['msg']="添加失败!!!";
                $this->ajaxReturn($data);
            }



        }else{
            $data['flag']=0;
            $data['msg']="请填写您的帖子内容";
            $this->ajaxReturn($data);
        }
    }
//邻里互动 end 


    //关爱成长
    public function care(){




         $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id = %d AND aflag='%s' AND keyworks='%s'",session("WY_DID"),$this->forumlist['WY_INFO'],"关爱成长")->find();

         $id=$mresult['id'];//message_type 里表单的的ID值
         $ms=M("Message");
         $result=$ms->where("aid={$id}")->select();
         $this->assign("mresult",$mresult);
         $this->assign("result",$result);
         $this->display();
    }


     //关爱成长详情
    public function elsecare(){
        $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }




    //健康父母
    public function parents(){
       
         $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_DID"),$this->forumlist['WY_INFO'],"科普健康")->find();
        
         $id=$mresult['id'];//message_type 里表单的的ID值
         $ms=M("Message");
         $result=$ms->where("aid={$id}")->select();
        
         //查找幻灯片
         $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);
        
         $this->assign("mresult",$mresult);
         $this->assign("result",$result);
         $this->display();
    }

     //健康父母详情
    public function elseparent(){
        $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    //和睦夫妻
    public  function harmony(){


          $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id = %d AND aflag='%s' AND keyworks='%s'",session("WY_DID"),$this->forumlist['WY_INFO'],"和睦夫妻")->find();

         $id=$mresult['id'];//message_type 里表单的的ID值
         $ms=M("Message");
         $result=$ms->where("aid={$id}")->select();

         $this->assign("mresult",$mresult);
         $this->assign("result",$result);
         $this->display();
    }

      //和睦夫妻详情
    public function elseharmony(){
        $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }


    //舌尖美食
    public function food(){


       $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id = %d AND aflag='%s' AND keyworks='%s'",session("WY_DID"),$this->forumlist['WY_INFO'],"舌尖美食")->find();

         $id=$mresult['id'];//message_type 里表单的的ID值
         $ms=M("Message");
         $result=$ms->where("aid={$id}")->select();

         $this->assign("mresult",$mresult);
         $this->assign("result",$result);
         $this->display();
    }

       //舌尖美食详情
    public function elsefood(){
        $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }


    //办证指南
    public function guide(){
          $mt=M("Message_type");
         $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_DID"),$this->forumlist['WY_INFO'],"办证指南")->find();

         $id=$mresult['id'];//message_type 里表单的的ID值
         $ms=M("Message");
         $result=$ms->where("aid={$id}")->select();

         $this->assign("mresult",$mresult);
         $this->assign("result",$result);
         $this->display();
    }

    //智慧查询
    public function search(){
        $this->display();
    }
    //业主中心
    public function usercenter(){
        $mt=M("message_type");
          $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' ",session("WY_XID"),$this->forumlist['WY_COST_LIST'])->select();
        $this->assign("result",$mresult);
       $result=$mt->where("xiaoqu_id=%d AND aflag='%s' ",session("WY_XID"),$this->forumlist['WY_PROPERTY'])->select();
        $this->assign("list",$result);
        $this->display();
    }

    //天天特价
    public function tejia(){
        $mt=M("Message_type");
        $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_ADS'],"生活超市")->find();
        $id=$mresult['id'];//message_type 里表单的的ID值
        $ms=M("Message");
        $result=$ms->where("aid={$id}")->select();
        
         //查找幻灯片
         $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);

        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
    }

    //天天特价详情
    public function elsetejia(){
         $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $ms->where("id={$id}")->setInc('click_num'); //阅读量+1
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    //常用电话
    public function phone(){
        $mt=M("Message_type");
        $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_ADS'],"常用电话")->find();

        $id=$mresult['id'];//message_type 里表单的的ID值
        $ms=M("Message");
        $result=$ms->where("aid={$id}")->select();

        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
    }

    //常用电话详情
    public function elsephone(){
         $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    //服务到家
    public function fuwehome(){
         $mt=M("Message_type");
        $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_ADS'],"服务到家")->find();

        $id=$mresult['id'];//message_type 里表单的的ID值
        $ms=M("Message");
        $result=$ms->where("aid={$id}")->select();
  
        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
    }

    //服务到家详情
    public function elsehome(){
         $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    //业主尊享
    public function yezhu(){
          $mt=M("Message_type");
        $mresult=$mt->where("xiaoqu_id=%d AND aflag='%s' AND keyworks='%s'",session("WY_XID"),$this->forumlist['WY_ADS'],"业主尊享")->find();
      
        $id=$mresult['id'];//message_type 里表单的的ID值
        $ms=M("Message");
        $result=$ms->where("aid={$id}")->select();
  
        $this->assign("mresult",$mresult);
        $this->assign("result",$result);
        $this->display();
    }
    //每个业主尊享详情
    public function elseyezhu(){
         $id=$_GET['id'];
        $ms=M('Message');
        $result=$ms->where("id={$id}")->find();
        $this->assign("result",$result);
        $mt=M("Message_type");
        $res=$mt->where("id='%d'",$result['aid'])->find();
        $this->assign("res",$res);
        $this->display();
    }

    


    





    /********************************************wolf*************************/

	//用户选择小区
	public function chosexiaoqu(){

		$uid=session("WY_UID");
		$ur=M("user");
		$user=$ur->where("id = %d",$uid)->find();
		if($user['uid'] && $user['propertyid']){
			$this->redirect("Keywords/usercenter");
		}elseif($user['uid'] && !$user['propertyid']){
			$this->redirect("Keywords/bindwuye");
		}else{
            if(isset($_POST['str'])){
                $where['name']=array("like","%".$_POST['str']."%");
                $this->assign("search",$_POST['str']);
            }
			$ae=M("account_type");
			$where['status']=array("eq",1);
			$where['fid']=array("neq",0);
			$result=$ae->where($where)->select();
            var_dump($result);
            echo $ae->getlastsql();
            exit();
			$this->assign("result",$result);

			$this->display();
		}

	}

	//绑定小区
	public function savexiaoqu(){
		
		$xiaoqu_id=$_POST['id'];//小区编号
		$uid=session('WY_UID');//用户编号

		$data['uid']=$xiaoqu_id;

		$ur=M("user");
		$flag=$ur->where("id = %d",$uid)->data($data)->save();

		if($flag){
			$data['flag']=1;
			$data['msg']=U("Keywords/bindwuye?id=".$uid);
		}else{
			$data['flag']=0;
			$data['msg']="请重新选择小区";
		}
		$this->ajaxReturn($data);

		

	}

	//绑定房号界面
	public function bindwuye(){
	   $uid=session('WY_UID');//用户编号
       $ur=M("user");
       $uresult=$ur->where("id = %d",$uid)->find();
       if($uresult['propertyid']){
            $this->redirect("Keywords/usercenter");
       }

	   $this->display();

	}

		
	//保存绑定房号
	public function savebindwuye(){

		$uid=session("WY_UID");

		$auth=$_POST['auth'];

        $ur=M("user");
        $uresult=$ur->where("id = %d",$uid)->find();

		$py=M("property");
		$presult=$py->where("auth = '%s' AND id = %d",$auth,$uresult['uid'])->find();

		if($presult){
			
			$save['propertyid']=$presult['id'];
			$flag=$ur->where("id = %d",$uid)->data($save)->save();
			if($flag){
				$data['flag']=1;
				$data['msg']=U("Keywords/usercenter/");
				$this->ajaxReturn($data);
			}else{
				$data['flag']=0;
				$data['msg']="绑定失败！！";
				$this->ajaxReturn($data);
			}
		}else{
			$data['flag']=0;
			$data['msg']="认证码错误";
			$this->ajaxReturn($data);
		}
	
	}

    //口袋商城
    public function mall(){
        $xiaoqu_id=session("WY_XID");//小区ID

        $tl=M("Tool");
        $result=$tl->where("type=%d AND aid=%d AND status",2,session("WY_DID"),1)->order("top_if desc,tel desc,id desc")->select();

        $this->assign("result",$result);
        $this->display();

    }

    //常用电话
    public function mobile(){
        $xiaoqu_id=session("WY_XID");//小区ID
        $tl=M("Tool");
        $result=$tl->where("type=%d AND atid=%d AND status",1,$xiaoqu_id,1)->order("top_if desc,tel desc,id desc")->select();
        $this->assign("result",$result);
        $this->display();
    }
    public function elsemobile(){
         $id=$_GET['id'];
         $tl=M("Tool");
        $result=$tl->where("id={$id}")->find();
        $this->assign("result",$result);
        $this->display();
    }







//便民信息，start
    public function bianming(){
        $mt=M("Message_type");
        $where['aflag']=array("EQ",$this->forumlist['WY_BM']);
        $where['daili_id']=array("EQ",session("WY_DID"));

        import('ORG.Util.Page');// 导入分页类
        $count      = $mt->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,6);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $mresult = $mt->where($where)->order("sort desc ,id desc ")->limit($Page->firstRow.','.$Page->listRows)->select();


        $this->assign("result",$mresult);
        $this->assign("page",$show);
        $this->display();

    }



    public function bianminglist(){

        $id=$_GET['id'];
        $me=M("message");

        $where['aid']=array("EQ",$id);
        $where['is_status']=array("EQ",1);

        import('ORG.Util.Page');// 导入分页类
        $count      = $me->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $result = $me->where($where)->order("is_top desc,id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

         //查找幻灯片
         $sresult=M("slide")->where("aid = %d AND is_status = %d",$id,1)->order("sort")->select();
        $this->assign("sresult",$sresult);

        $this->assign("result",$result);
        $this->assign("page",$show);
        $this->display();

    }

    public function bianmingone(){
        $id=$_GET['id'];

        $flag=M("Message")->where("id = %d",$id)->setInc('click_num'); //+1

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

    }


    //微信用户发便民布信息UI
    public function post_bianmingui(){
        $aid=$_GET['aid'];

        //信息所属类型
        $aresult=M("MessageType")->where("id = %d",$aid)->find();
        $this->assign("aresult",$aresult);

        //分类表单模型
        $fresult=M("form")->where("aid  = %d",$aid)->select();
        $this->assign("fresult",$fresult);

        $this->display();


    }

    //保存微信用户发布的信息
    public function save_bianming(){


        if(IS_AJAX){
            $s=$_POST['model'];
            $options=serialize($s);
            $data['post_time']=date("Y-m-d H:m:s");
            $data['aid']= $_POST['aid'];
            $data['form_content']=$options;
            $data['title']=$_POST['title'];
            $data['is_status']=1;
            $data['click_num']=0;
            $data['is_diaplay_click']=1;
            $data['is_user_post']=session("WY_UID"); 
            $data['name']=M("User")->where("id = %d",session("WY_UID"))->getField("weixin_name");
           
            $me=M("Message");

            $fm=M("form");
            $fresult=$fm->where("aid = %d",$data['aid'])->select();

            foreach ($fresult as $key => $one) {
                if($one['is_must']){
                    if(empty($s[$one['id']])){
                         //$this->error($one['name']."不可以为空");
                        $this->ajaxReturn(array("msg"=>$one['name']."不可以为空"),"JSON");
                    }
                }
                if($one['selectpicker']==1){//验证邮件
                    if(!ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+",$s[$one['id']])){
                       // $this->error($one['name']."必须为邮件格式");
                        $this->ajaxReturn(array("msg"=>$one['name']."必须为邮件格式"),"JSON");
                    }
                }else if($one['selectpicker']==2){//验证手机
                    if(!preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$s[$one['id']])){
                        //$this->error($one['name']."必须为手机号码");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为手机号码"),"JSON");
                    }

                }else if($one['selectpicker']==7){//验证邮编
                    if(!preg_match("/[0-9]{6}/",$s[$one['id']])){
                        //$this->error($one['name']."必须为邮编格式");
                        $this->ajaxReturn(array("msg"=>$one['name']."必须为邮编格式"),"JSON");
                    }
                }else if($one['selectpicker']==4){//验证qq
                    if(!preg_match('/^[1-9][0-9]{4,9}$/', $s[$one['id']])){
                        //$this->error($one['name']."必须为qq格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为qq格式"),"JSON");
                    }

                }else if($one['selectpicker']==3){//验证网址
                    if(!ereg("^http://[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$", $s[$one['id']])){
                        //$this->error($one['name']."必须为网址格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为网址格式"),"JSON");
                    }
                }else if($one['selectpicker']==6){//验证数字
                    if(!preg_match("^[0-9]*$/",$s[$one['id']])){
                       // $this->error($one['name']."必须为数字格式");
                          $this->ajaxReturn(array("msg"=>$one['name']."必须为数字格式"),"JSON");

                    }
                }else if($one['selectpicker']==5){//验证字母
                    if(!preg_match("^[a-zA-Z]+$", $s[$one['id']])){
                        //$this->error($one['name']."必须为字母格式");
                         $this->ajaxReturn(array("msg"=>$one['name']."必须为字母格式"),"JSON");
                    }
                }

            }

            $result=$me->data($data)->add();

            
            if($result){
                $this->ajaxReturn(array("flag"=>1,"msg"=>$result));
            }else{
                //$this->error("添加失败");
                 $this->ajaxReturn(array("msg"=>"信息发布失败"));
            }
            
        }else{
            exit();
        }
    }


    
//便民信息 end 


    //点赞
    public function praise(){
        if(IS_AJAX){
            $flag=M("Message")->where("id = %d",$_GET['id'])->setInc('praise'); //点赞
            if($flag){
                $this->ajaxReturn(array("flag"=>1,"msg"=>"操作成功"),"JSON");
            }else{
                $this->ajaxReturn(array("flag"=>0,"msg"=>"操作失败"),"JSON");
            }
        }else{
            exit();
        }
    }










}
