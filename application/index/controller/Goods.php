<?php
namespace app\index\controller;
use \think\Controller;
use \think\Session;
use \think\Cookie;
use \think\Request;
use phpmailer\PHPMailer;
use \app\index\model\Goods as GoodsModel;
use \app\index\model\User as UserModel;
class Goods extends Controller
{
    public function index()
    {
    	$para=Request::instance()->param();
    	$start=$para['items'];
    		
        $model=new GoodsModel();
        $good=$model->where('expire','>=',date('Y-m-d H:i:s'))->limit($start,50)->order('id','asc')->field('id,name,path,price,description,likes')->select();
        $count=count($good);
       for($i=0;$i < $count;$i++)
       {
       		$goods[$i]=$good[$i]->toJson();
       }
	   $user_model=new UserModel();
	   $user_id=Session::get("id","personinfo");
	   if($user_id){
			$user=$user_model->get($user_id);
			$likes=explode(";",$user->likes);
	   }
	   else
	   {
			$likes=Array();
	   }
        return	json(['status'=>'ok','count'=>$count,'goods'=>$goods,"likes"=>$likes,'isAll'=>$count<50]);
    }
    public function detail()
    {
    		$para=Request::instance()->param();
    		$id=$para['id'];
    		
        $model=new GoodsModel();
        $detail=$model->find($id);
        if($detail->count()==0)
       		 return json(['status'=>'fail']);
       	else{
        	return	json(['status'=>'ok','goods'=>$detail->toJson()]);
      	}
    }
	public function search()
    {

		$para=Request::instance()->param();
    	$start=$para['items'];
		$tag=$para['tag'];
    	if(!$tag)
		{
			return json(["status"=>"fail"]);
		}
        $model=new GoodsModel();
        $good=$model->whereOr(
		'tag1|tag2|tag3','like',$tag)->where('expire','>=',date('Y-m-d H:i:s'))->limit($start,50)->order('id','asc')->field('id,name,path,price,description,likes')->select();
        $count=count($good);
       for($i=0;$i < $count;$i++)
       {
       		$goods[$i]=$good[$i]->toJson();
       }
       $user_model=new UserModel();
	   $user_id=Session::get("id","personinfo");
	   if($user_id){
			$user=$user_model->get($user_id);
			$likes=explode(";",$user->likes);
	   }
	   else
	   {
			$likes=Array();
	   }
        return	json(['status'=>'ok','count'=>$count,'goods'=>$goods,"likes"=>$likes,'isAll'=>$count<50]);
	}
	public function range()
    {

		$para=Request::instance()->param();
    	$start=$para['items'];
		$min=$para['min'];
		$max=$para['max'];
    	if(!($min&&$max))
		{
			return json(["status"=>"fail"]);
		}
        $model=new GoodsModel();
		if(!$model->where("price","BETWEEN","$min,$max")->select())
			return json(['status'=>'ok','count'=>0,'goods'=>null,"likes"=>null,'isAll'=>true]);
        $good=$model->where("price","BETWEEN","$min,$max")->where('expire','>=',date('Y-m-d H:i:s'))->limit($start,50)->order('id','asc')->field('id,name,path,price,description,likes')->select();
        $count=count($good);
       for($i=0;$i < $count;$i++)
       {
       		$goods[$i]=$good[$i]->toJson();
       }
       $user_model=new UserModel();
	   $user_id=Session::get("id","personinfo");
	   if($user_id){
			$user=$user_model->get($user_id);
			$likes=explode(";",$user->likes);
	   }
	   else
	   {
			$likes=Array();
	   }
        return	json(['status'=>'ok','count'=>$count,'goods'=>$goods,"likes"=>$likes,'isAll'=>$count<50]);
	}
	public function sort()
    {
		$user_model=new UserModel();
	   $user_id=Session::get("id","personinfo");
	   if($user_id){
			$user=$user_model->get($user_id);
			$likes=explode(";",$user->likes);
	   }
	   else
	   {
			$likes=Array();
	   }
		$para=Request::instance()->param();
		$start=$para['count'];
    	$state=$para['status'];//1降序，2升序
		$tag=$para['tag'];//0代表热度，1代表价格
		$search=$para["search_tag"];
    	if($tag!='1'&&$tag!='0')
		{
			return json(["status"=>"fail"]);
		}
        $model=new GoodsModel();
		if(!$search){
			if(0==$tag&&1==$state)
				$good=$model->where('expire','>=',date('Y-m-d H:i:s'))->order('likes','asc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if('0'==$tag&&'2'==$state)
				$good=$model->where('expire','>=',date('Y-m-d H:i:s'))->order('likes','desc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if(1==$tag&&1==$state)
				$good=$model->where('expire','>=',date('Y-m-d H:i:s'))->order('price','asc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if(1==$tag&&2==$state)
				$good=$model->where('expire','>=',date('Y-m-d H:i:s'))->order('price','desc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
		}
		else{
			if(0==$tag&&1==$state)
				$good=$model->whereOr(
		['tag1'=>$tag,
		'tag2'=>$tag,
		'tag3'=>$tag,
		])->where('expire','>=',date('Y-m-d H:i:s'))->order('likes','asc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if('0'==$tag&&'2'==$state)
				$good=$model->whereOr('tag1|tag2|tag3','like',$tag)->where('expire','>=',date('Y-m-d H:i:s'))->order('likes','desc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if(1==$tag&&1==$state)
				$good=$model->whereOr('tag1|tag2|tag3','like',$tag)->where('expire','>=',date('Y-m-d H:i:s'))->order('price','asc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
			else if(1==$tag&&2==$state)
				$good=$model->whereOr('tag1|tag2|tag3','like',$tag)->where('expire','>=',date('Y-m-d H:i:s'))->order('price','desc')->limit($start,50)->field('id,name,path,price,description,likes')->select();
		}
        $count=count($good);
       for($i=0;$i < $count;$i++)
       {
       		$goods[$i]=$good[$i]->toJson();
       }
       
        return	json(['status'=>'ok','count'=>$count,'goods'=>$goods,"likes"=>$likes,'isAll'=>$count<50]);
	}
	public function buy()
	{
		$para=Request::instance()->param();
		$good_id=$para["id"];
		$user_id=Session::get("id","personinfo");
		if(!$user_id)
			return json(["status"=>"fail","messege"=>"请先登录后再继续操作！"]);
		$user_model=new UserModel();
		$good_model=new GoodsModel();
		$user=$user_model->get($user_id);//买家
		$good=$good_model->get($good_id);//物品
		$saller=$user_model->get($good->owner_id);//卖家
		//买家与卖家邮箱验证
		
		if(!$user->email_state)
			return json(["status"=>"fail","messege"=>"您的邮箱未激活，请激活后再操作"]);
		if(!$saller->email_state)
			return json(["status"=>"fail","messege"=>"对方的邮箱未激活，请求失败！"]);
		//获取双方邮箱与用户名
		$user_name=$user->username;
		$user_email=$user->email;
		$user_school=$user->school;
		$user_contact=$user->contact?$user->contact:"对方没有提交联系方式，您可通过邮箱与其取得联系";
		$user_info="用户名：".$user_name." "."\n邮箱：".$user_email . "\n学校：" . $user_school ."\n联系方式：".$user_contact;

		$saller_name=$saller->username;
		$saller_email=$saller->email;
		$saller_school=$saller->school;
		$saller_contact=$saller->contact?$user->contact:"对方没有提交联系方式，您可通过邮箱与其取得联系";
		$saller_info="用户名：".$saller_name." "."\n邮箱：".$saller_email . "\n学校：" . $saller_school ."\n联系方式：".$saller_contact;
		//对买家进行操作
		$tempStr=$user->purchased;
		$purchased_ids=explode(';',$tempStr);
		if(in_array($good_id,$purchased_ids))
			return json(["status"=>"fail","messege"=>"此商品已经在您的已购清单中！"]);
		$count=count($purchased_ids);
		if($count>5)
		{
			for($i=1;$i<$count-4;$i++)
			{
				unset($purchased_ids[$i]);
			}
		};
		$tempStr=implode(';',$purchased_ids);
		$new_purchased_ids=$tempStr . ";" . $good_id;
		$user->purchased=$new_purchased_ids;
		if(!$user->save()){
			return json(["status"=>"fail","messege"=>"信息保存失败！"]);
			}
		//发送邮件买家
		$messege="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>W.T.X</title><meta name='viewport' content='width=device-width, initial-scale=1.0' /></head><body style='padding:0;margin:0'><table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td>
                <table align='center' border='1' cellpadding='0' cellspacing='0' width='600' style='border-collapse: collapse;'>
                    <tr>
                        <td bgcolor='#000000'>
                            <p style ='color:#ffffff;font-size:25px;margin:11px' >W.T.X</p>
                        </td>
                    </tr>
                    <tr style='border:0'>
                        <td  bgcolor='#ffffff' style='border:none'>
                            <p style='margin:5px;color:#000000;font-size:18px'>尊敬的:{$user_name}</p>
                            <p style='margin-left:50px;margin-top:50px;margin-right:50px;margin-bottom:40px;text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;欢迎使用W.T.X平台，您需要商品的卖家信息如下，祝您购物愉快：<b style='font-size:12px'><br/>（如非本人操作请联系网站管理员）</b></p>
                            <p style='text-align:center;margin-top:-20px;margin-bottom:50px' ><a href='#'>". $saller_info ."</a></p>
                        </td>
                    </tr>
                    <tr style='border:0'>
                        <td style='border:none'>
                            <p style='color:#808080 ;margin:0;margin-bottom:10px; font-size:12px;text-align:center' >------------(系统邮件，请勿回复)-----------</p>
                        </td>
                    </tr>
                    <tr style='border:0'>
                        <td style='border:none'>
                            
                            <p style='margin:0px;color:#808080;font-size:10px;text-align:center'>我们的邮箱(Email)：mrbeamcn@gmail.com&nbsp;&nbsp;&nbsp;&nbsp;我们的地址(Address):位置不在可测范围</p>
                            <p style='color:#808080 ;margin:0;font-size:12px;text-align:center;overflow:hidden'>-------------------------------------------------------------------------------------------------------------------</p>
                        </td>
                    </tr>
                    <tr style='border:0'>
                        <td bgcolor='#FFFFFF' style='border:none'>
                            <p style='margin-top:0.5px;margin-bottom:4px; text-align:center;font-size:12px'>COPYRIGHT © 2017 – {date('Y')} TENCENT. ALL RIGHTS RESERVED.</p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</body>
						</html>";
						$mail = new PHPMailer();  
  
            $mail->isSMTP();// 使用SMTP服务  
            $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码  
            $mail->Host = "smtp.mxhichina.com";// 发送方的SMTP服务器地址  
            $mail->SMTPAuth = true;// 是否使用身份验证  
            $mail->Username = "postmaster@funnywtx.xin";// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱</span><span style="color:#333333;">  
            $mail->Password = "Uestc171013";// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！</span><span style="color:#333333;">  
            $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">  
            $mail->Port = 465;// 163邮箱的ssl协议方式端口号是465/994  
  					$mail->IsHTML(true);
            $mail->setFrom("postmaster@funnywtx.xin","W.T.X");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示  
            $mail->addAddress($user_email);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)  
            $mail->addReplyTo("mrbeamcn@gmail.com","KOMO");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址  
            //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)  
            //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)  
            //$mail->addAttachment("bug0.jpg");// 添加附件  
  
            $mail->Subject = "W.T.X交易信息";// 邮件标题  
            $mail->Body = $messege;// 邮件正文  
            //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用  
  
            if($mail->send()){// 发送邮件

					$messege="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>W.T.X</title><meta name='viewport' content='width=device-width, initial-scale=1.0' /></head><body style='padding:0;margin:0'><table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td>
					<table align='center' border='1' cellpadding='0' cellspacing='0' width='600' style='border-collapse: collapse;'>
						<tr>
							<td bgcolor='#000000'>
								<p style ='color:#ffffff;font-size:25px;margin:11px' >W.T.X</p>
							</td>
						</tr>
						<tr style='border:0'>
							<td  bgcolor='#ffffff' style='border:none'>
								<p style='margin:5px;color:#000000;font-size:18px'>尊敬的:{$saller_name}</p>
								<p style='margin-left:50px;margin-top:50px;margin-right:50px;margin-bottom:40px;text-align:center'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;欢迎使用W.T.X平台，您商品的买家信息如下，祝您愉快：<b style='font-size:12px'><br/>（如非本人操作请联系网站管理员）</b></p>
								<p style='text-align:center;margin-top:-20px;margin-bottom:50px' ><a href='#'>". $user_info ."</a></p>
							</td>
						</tr>
						<tr style='border:0'>
							<td style='border:none'>
								<p style='color:#808080 ;margin:0;margin-bottom:10px; font-size:12px;text-align:center' >------------(系统邮件，请勿回复)-----------</p>
							</td>
						</tr>
						<tr style='border:0'>
							<td style='border:none'>
                            
								<p style='margin:0px;color:#808080;font-size:10px;text-align:center'>我们的邮箱(Email)：mrbeamcn@gmail.com&nbsp;&nbsp;&nbsp;&nbsp;我们的地址(Address):位置不在可测范围</p>
								<p style='color:#808080 ;margin:0;font-size:12px;text-align:center;overflow:hidden'>-------------------------------------------------------------------------------------------------------------------</p>
							</td>
						</tr>
						<tr style='border:0'>
							<td bgcolor='#FFFFFF' style='border:none'>
								<p style='margin-top:0.5px;margin-bottom:4px; text-align:center;font-size:12px'>COPYRIGHT © 2017 – {date('Y')} TENCENT. ALL RIGHTS RESERVED.</p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</body>
							</html>";
  
            
				$mail->addAddress($saller_email);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)  
				$mail->Body = $messege;// 邮件正文  
				//$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用  
  
				if($mail->send()){// 发送邮件
					 return  json(["status"=>'ok']);
				}
				else{  
            			return	json(['status'=>'fail','messege'=>'邮件发送卖家失败请联系网站管理员']);    
				}  
            }
			else{  
            		return	json(['status'=>'fail','messege'=>'邮件发送失败请联系网站管理员']);    
            }  
			//发送邮件卖家
		

	}
	public function like()
	{
		$para=Request::instance()->param();
		$good_id=$para["id"];
		$select=$para["select"];
		$user_id=Session::get("id","personinfo");
		if(!$user_id)
			return json(["status"=>"fail","messege"=>"请先登录后再继续操作！"]);
		$user_model=new UserModel();
		$good_model=new GoodsModel();
		$user=$user_model->get($user_id);//买家
		$good=$good_model->get($good_id);//物品

		//买家与卖家邮箱验证
		
		if(!$user->email_state)
			return json(["status"=>"fail","messege"=>"您的邮箱未激活，请激活后再操作"]);

		//获取双方邮箱与用户名
		$user_name=$user->username;
		$user_email=$user->email;
		



		if(!$select)
		{
			$info="商品名：".$good->name . "<br/>标签：" . "{$good->tag1} {$good->tag2} {$good->tag3}" ;
			//对买家进行操作
		$tempStr=$user->likes;
		$likes=explode(';',$tempStr);
		if(in_array($good_id,$likes))
			return json(["status"=>"fail","messege"=>"此商品已经在您的收藏清单中！"]);
		$count=count($likes);
		if($count>5)
		{
			for($i=1;$i<$count-4;$i++)
			{
				unset($likes[$i]);
			}
		}
		$tempStr=implode(';',$likes);
		$newlikes=$tempStr . ";" . $good_id;
		$user->likes=$newlikes;
		if(!$user->save()){
			return json(["status"=>"fail","messege"=>"信息保存失败！"]);
			}
			else{ 
			$good->setInc("likes");
				return json(["status"=>"ok"]);
			}
		}
		else{
			
		$info="商品名：".$good->name . "<br/>标签：" . "{$good->tag1} {$good->tag2} {$good->tag3}" ;
			//对买家进行操作
		$tempStr=$user->likes;
		$likes=explode(';',$tempStr);
		$key=array_search($good_id,$likes);
		if(!$key)
			return json(["status"=>"fail","messege"=>"此商品不在您的收藏清单中！"]);
		unset($likes[$key]);
		$tempStr=implode(';',$likes);
		$user->likes=$tempStr;
		if(!$user->save()){
			return json(["status"=>"fail","messege"=>"信息保存失败！"]);
			}
		else{
			$good->setDec("likes");
			return json(["status"=>"ok"]);
			}
		}
	}
}
