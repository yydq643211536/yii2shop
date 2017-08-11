<?php
namespace frontend\controllers;
use frontend\models\Address;
use frontend\models\LoginForm;
use frontend\models\Member;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    //接口开发必须关闭
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
        \Yii::$app->response->format = Response::FORMAT_JSON;
    }

//        1.会员
//        -会员注册
    public function actionMemberRegister()
    {
        if(\Yii::$app->request->isPost){
            $model = new Member();
            $model->username = \Yii::$app->request->post('username');
            $model->password = \Yii::$app->request->post('password');
            $model->tel = \Yii::$app->request->post('tel');
            $model->email = \Yii::$app->request->post('email');
            if($model->validate()){
                $model->save();
                //注册成功
                $result = [
                    'errorCode'=>0,
                    'errorMag'=>'注册成功',
                    'date'=>[]
                ];
            }else{
                //验证不通过
                $result = [
                  'errorCode'=>1,
                    'errorMag'=>'注册失败，请检测错误信息',
                    'date'=>$model->getErrors(),
                ];
            }
        }else{
            $result = [
              'errorCode'=>2,
              'errorMag'=>'请求方式错误，请使用POST提交数据',
              'date'=>[]
            ];
        }
        return $result;
    }


    //用户登录
    public function actionMemberLogin()
    {
        if(\Yii::$app->request->isPost){
            $model = new LoginForm();
            $model->username = \Yii::$app->request->post('username');
            $model->password = \Yii::$app->request->post('password');
            if($model->validate() && $model->login()){
                $result = [
                    'errorCode'=>0,
                    'errorMag'=>'登录成功',
                    'date'=>[]
                ];
            }else{
                $result = [
                    'errorCode'=>1,
                    'errorMag'=>'登录失败,请检测错误信息',
                    'date'=>$model->getErrors(),
                ];
            }
        }else{
            $result = [
                'errorCode'=>2,
                'errorMag'=>'请求方式错误，请使用POST提交数据',
                'date'=>[]
            ];
        }
        return $result;
    }
    //修改密码
    public function actionMemberEdit()
    {

    }

    //获取当前登录的用户信息
    public function actionMemberInfo()
    {
        //判断用户是否登录
        if(!\Yii::$app->user->isGuest) {
            $member = Member::findOne(['id'=>\Yii::$app->user->getId()]);
            $result = [
                'isGuest'=>0,
                'mag'=>'用户登录状态',
                'date'=>$member
            ];
        }else{
            $result = [
                'isGuest'=>1,
                'mag'=>'没有登录请登录',
            ];
        }
        return $result;
    }


    //注销登录
    public function actionMemberLogout(){
        \Yii::$app->user->logout();
        return ['status'=>1,'msg'=>'注销成功'];
    }


    //2.收货地址
    //添加地址
    public function actionAddressAdd(){
        //判断用户是否登录
        $request=\Yii::$app->request;
        if(!\Yii::$app->user->isGuest){
            $member_id =\Yii::$app->user->id;
            if($request->isPost){
                $address= new Address();
                $address->user_id=$member_id;
                $address->name=$request->post('name');
                $address->province=$request->post('province');
                $address->city=$request->post('city');
                $address->area=$request->post('area');
                $address->detail=$request->post('detail');
                $address->tel=$request->post('tel');
                if($address->validate()){
                    $address->save();
                    return ['status'=>0,'msg'=>'添加地址成功','data'=>$address];
                }
                return ['status'=>1,'msg'=>'添加地址失败','data'=>$address->getErrors()];
            }
            return ['status'=>1,'msg'=>'提交方式错误'];
        }
        return ['status'=>1,'msg'=>'没有登录请登录'];
    }

    //修改地址
    public function actionAddressEdit()
    {
        //先查询出这条数据
        if(\Yii::$app->request->isPost){
            $id = \Yii::$app->request->post('id');
            $address = Address::findOne(['id'=>$id]);
            $address->name=\Yii::$app->request->post('name');
            $address->province=\Yii::$app->request->post('province');
            $address->city=\Yii::$app->request->post('city');
            $address->area=\Yii::$app->request->post('area');
            $address->detail=\Yii::$app->request->post('detail');
            $address->tel=\Yii::$app->request->post('tel');
            if($address->validate()){
                $address->save();
                return ['status'=>0,'mag'=>'修改地址成功','date'=>$address];
            }
            return ['status'=>1,'msg'=>'修改地址失败','data'=>$address->getErrors()];
        }
        return ['status'=>1,'msg'=>'提交方式错误'];
    }

    //删除地址
    public function actionAddressDelete()
    {
        if($id = \Yii::$app->request->get('id')){
            $address = Address::find()->where(['id'=>$id])->one();
            $address->delete();
            return ['status'=>0,'mag'=>'删除成功','date'=>$address];
        }
        return ['status'=>1,'mag'=>'参数不正确'];
    }

    //地址列表
    public function actionAddressList()
    {
        //判断是否登录
        if(!\Yii::$app->user->isGuest){
            //获取他的地址列表
            $user_id = \Yii::$app->user->id;
            $address = Address::findAll(['user_id'=>$user_id]);
            return ['status'=>0,'mag'=>'查询成功','date'=>$address];
        }
        return ['status'=>1,'mag'=>'没有登录请登录'];
    }

    
}