<?php

class IndexAction extends Action {

    public function index(){

		/* 加载微信SDK */
		import('COM.ThinkWechat');
		/*获取微信TOKEN*/
		$weixn=M('Wechat')->where("id={$_GET[wechat_id]}")->find();

		C('WECHAT_TOKEN', $weixn['wtoken']);
		C('WECHAT_APPID',$weixn['wappid']);
		C('WECHAT_APPSECRET',$weixn['wsecret']);

		$weixin = new ThinkWechat();

		/* 获取请求信息 */
		$data = $weixin->request();
		
		/* 获取回复信息 */
		list($content, $type) = $this->reply($data,$_GET['wechat_id']);

		/* 响应当前请求 */
		$weixin->response($content, $type);
	}

	private function reply($data,$id){

		$openid = $data['FromUserName']; //OPENID
		$openidWX = $data['ToUserName'];//公众账户原始ID

		$url=C('Wurl');

		//return $reply=array('针对微信接收慢的问题，现在技术排除中……请稍后访问。', 'text');


		if('text' == $data['MsgType']){
			
			$reply=$this->key_reply($data,$id);//查找关键字
			
			if($reply[0]=='欢迎使用一站社区，您的消息已收到'){
				$reply=$this->reply_default($data,$id,'wtext');//是否有默认回复
			}

		}elseif('image' == $data['MsgType']){

			$reply=$this->reply_default($data,$id,'wimage');//是否有默认回复

		}elseif('event' == $data['MsgType'] && 'subscribe' == $data['Event']){//关注后回复
			
			$u=M('User');
			$time=time();

			$udata['openid']=$openid;

			if($uid=$u->where($udata)->getfield('id')){
				$udata['status']=1;
				$udata['id']=$uid;
				$u->save($udata);//重新关注
			}else{

				$weixin=M('Wechat')->where("wid='{$openidWX}'")->find();
				import('COM.ThinkWechat');
				C('WECHAT_TOKEN', $weixin['wtoken']);
                C('WECHAT_APPID',$weixin['wappid']);
                C('WECHAT_APPSECRET',$weixin['wsecret']);
                $weixin = new ThinkWechat ();
                $data_wei=$weixin->user($openid);

                $udata['weixin_name']=$data_wei['nickname'];
                $udata['weixin_from']=$data_wei['country'].'_'.$data_wei['province'].'_'.$data_wei['city'];
                $udata['weixin_sex']=$data_wei['sex'];
                $udata['status']=1;
                $udata['addtime']=$data_wei['subscribe_time'];
				$udata['openidWX']=$openidWX;
				$u->add($udata);//添加关注
			}
			
			//return $reply=array('欢迎您的关注'.$u->getlastsql(), 'text');

			$wsubscribe=M('Wechat')->where("id={$id}")->getfield('wsubscribe');

			if($wsubscribe){
				$data['Content']=$wsubscribe;
				$reply=$this->key_reply($data,$id);
			}else{
				$reply=array('欢迎您的关注', 'text');
			}
			
		}elseif('event' == $data['MsgType'] && 'CLICK' == $data['Event']){//自定义菜单

			$data['Content']=$data['EventKey'];
			$reply=$this->key_reply($data,$id);//查找关键字
			
			if($reply[0]=='欢迎使用一站社区，您的消息已收到'){
				$reply=$this->reply_default($data,$id,'wtext');//是否有默认回复
			}

		}elseif ('unsubscribe' == $data['Event']) {//解绑后
				$udata['status']=0;
				$udata['uid']=0;
				$udata['propertyid']=0;
				M('User')->where('openid="'.$openid.'"')->save($udata);
        } else {

			$reply=$this->reply_default($data,$id,'wother');//是否有默认回复
			
		}


		return $reply;

	}

	//默认回复
	private function reply_default($data,$id,$type){

		$reply_default=M('Wechat')->where("id={$id}")->getfield($type);

		if($reply_default){
			$data['Content']=$reply_default;
			return $reply=$this->key_reply($data,$id);
		}else{
			$reply=array('欢迎使用一站社区，您的消息已收到', 'text');
		}

	}

	//关键字回复
	private function key_reply($data,$id){

		$openid = $data['FromUserName']; //OPENID
		$openidWX = $data['ToUserName'];//公众账户原始ID
		$time=time();

		//保存消息记录
		if(!empty($data['Content'])){
			$m=M('Msg');
			$user=M('User')->where("openid='{$openid}' and openidWX='{$openidWX}'")->field('id,weixin_name,xiaoqu_id')->find();
			$m_data['openid']=$openid;
			$m_data['openidWX']=$openidWX;
			if($user['xiaoqu_id']){
				$m_data['group_wuye']=M('AccountType')->where("id={$user[xiaoqu_id]}")->getfield('fid');//所属物业
				$m_data['group_xiaoqu']=$user['xiaoqu_id'];//小区
			}
			$m_data['uid']=$user['id'];
			$m_data['name']=$user['weixin_name'];
			$m_data['content']=$data['Content'];
			$m_data['addtime']=$time;
			$m->add($m_data);
			//return $reply=array('测试SQL'.$m->getlastsql(), 'text');
		}


		$url=C('Wurl');

		$msg_default='欢迎使用一站社区，您的消息已收到';

	
		$key=M('Keyreply')->where("key_words LIKE '%{$data[Content]}%' AND uid={$id} AND is_status=1")->field("*,REPLACE(key_words,CHAR(10),',') as key_words")->find();


		//return $reply=array('ddddaaaaa'.$type.M()->getlastsql(), 'text');

		if(!$key){

			if(!empty($user['xiaoqu_id'])){
				$xiaoqu_id=' AND xiaoqu_id = '.$user['xiaoqu_id'];
			}
			$message=M('MessageType')->where("keyworks ='{$data[Content]}' AND is_status=1 {$xiaoqu_id}")->field('image,introduce,url,name')->find();
			//return array('无关键字，请重新输入'.M()->getlastsql(), 'text');
			if($message){
				$news[]=array($message['name'],$message['introduce'],$url.$message['image'],$message['url']."/openid/{$openid}/openidWX/{$openidWX}");
				return array($news,'news');
			}

			return $reply = array($msg_default, 'text');
		}elseif($key['match']==1){//精确搜索

			$key_words=explode(',', $key['key_words']);
			foreach ($key_words as $v) {
				if($v==$data['Content']){
					$key_words_if=1;continue;
				}
			}
			if(!$key_words_if){
				return $reply = array($msg_default, 'text');
			}

		}
			
		//检查类型
		if($key['is_tuwen']==1){//图文回复

			$tuwen=M('Tuwenreply')->where("aid={$key[id]}")->order('`sort` desc,id desc')->select();

			if($tuwen){
				foreach ($tuwen as $k => $v) {
					$news[]=array($v['image_title'],$v['image_description'],$url.$v['image_path'],$v['image_url']."/openid/{$openid}/openidWX/{$openidWX}");
				}
				$reply=array($news,'news');
			}else{
				$reply = array($msg_default, 'text');
			}

		}elseif($key['is_tuwen']==0){//关键字回复

			if ($key['reply_type']==1) {//文字
				$reply = array($key['image_description'], 'text');
			}else{//图片
				$news[]=array($key['image_title'],$key['image_description'],$url.$key['image_path'],$key['image_url']."/openid/{$openid}/openidWX/{$openidWX}");
				$reply=array($news,'news');
			}

		}

		return $reply;

	}

}