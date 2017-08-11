<?php

namespace backend\controllers;
use backend\filters\RbacFilter;
use backend\models\GoodsCategory;
use backend\models\GoodsGallery;
use backend\models\GoodsForm;
use backend\models\GoodsSearchForm;
use flyok666\uploadifive\UploadAction;
use backend\models\Goods;
use backend\models\GoodsDayCount;
use backend\models\GoodsIntro;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\Request;


class GoodsController extends \yii\web\Controller
{
    public function actionIndex()
    {
//        return false;
        $model = new GoodsSearchForm();
//        var_dump($model);exit;
        $query = Goods::find();
//        var_dump($query);exit;

        //接收表单提交的查询参数
        $model->search($query);


        //商品名称含有"耳机"的  name like "%耳机%"
        //$query = Goods::find()->where(['like','name','耳机']);
        $pager = new Pagination([
            'totalCount'=>$query->count(),
            'pageSize'=>5
        ]);

        $models = $query->limit($pager->limit)->offset($pager->offset)->all();


        return $this->render('index',['models'=>$models,'pager'=>$pager,'model'=>$model]);
    }

    /**
     * @return string|\yii\web\Response
     * 添加商品
     */
    public function actionAdd(){
        $model=new Goods(['goods_category_id'=>0]);
        $nowtime=date('Ymd');
        $goodsintro=new GoodsIntro();//文章详细

        //加载数据并验证
        if($model->load(\yii::$app->request->post()) && $goodsintro->load(\yii::$app->request->post()) && $model->validate() && $goodsintro->validate()){
            //找到当天添加的对象
            $goodsdaycount=GoodsDayCount::findOne(['day'=>$nowtime]);
            //每日添加初始化
            if(!$goodsdaycount){
                $goodsdaycount=new GoodsDayCount();
                //时间和商品数量初始化
                $goodsdaycount->day=$nowtime;
                $goodsdaycount->count=0;
                $goodsdaycount->save();
            }
            $goodsdaycount->count++;
            //根据时间生成商品货号
            $model->sn=$nowtime.substr(strval($goodsdaycount->count+10000),1,4);
//            var_dump($model->logo);exit;
            $model->status=1;
            $model->save();//保存商品信息
            $goodsdaycount->save();//保存每日每次添加商品数量
            $goodsintro->goods_id=$model->id;
            $goodsintro->save();//保存商品详细信息
            \yii::$app->session->setFlash('success','添加成功');
            return $this->redirect(['goods/gallery','id'=>$model->id]);
        }
        $categorys=\backend\models\GoodsCategory::find()->select(['id','name','parent_id'])->asArray()->all();
        //调用视图显示添加页面
        return $this->render('add',['model'=>$model,'goodsintro'=>$goodsintro,'categorys'=>$categorys]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * 修改商品信息
     */
    public function actionEdit($id){
        $model=Goods::findOne($id);
        $goodsintro=GoodsIntro::findOne($id);
        //加载数据并验证
        if($model->load(\yii::$app->request->post()) && $goodsintro->load(\yii::$app->request->post()) && $model->validate() && $goodsintro->validate()){
            $model->save();//保存商品信息
            $goodsintro->goods_id=$model->id;
            $goodsintro->save();//保存商品详细信息
            \yii::$app->session->setFlash('success','修改成功');
            return $this->redirect(['index']);
        }
        $categorys=\backend\models\GoodsCategory::find()->select(['id','name','parent_id'])->asArray()->all();
        //调用视图显示添加页面
        return $this->render('add',['model'=>$model,'goodsintro'=>$goodsintro,'categorys'=>$categorys]);
    }

    /**
     * @param $id
     * 删除将
     */
    public function actionDel($id){
        $model=Goods::findOne($id);
        $model->status=0;
        \yii::$app->session->setFlash('success','删除成功');
        $model->save();
        //跳转
        return $this->redirect(['index']);
        /*$sourceNumber = "112";
        echo  $newNumber = substr(strval($sourceNumber+10000),1,4);*/
    }

    public function actionGallery($id){
        $goods=Goods::findOne(['id'=>$id]);
        if($goods == null){
            throw new NotFoundHttpException('商品不存在');
        }
        //调用视图显示页面
        return $this->render('gallery',['goods'=>$goods]);
    }

    /*
    * AJAX删除图片
    */
    public function actionDelGallery(){
        $id = \Yii::$app->request->post('id');
        $model = GoodsGallery::findOne(['id'=>$id]);
        if($model && $model->delete()){
            return 'success';
        }else{
            return 'fail';
        }

    }


    public function actions() {
        return [
            's-upload' => [
                'class' => UploadAction::className(),
                'basePath' => '@webroot/upload',
                'baseUrl' => '@web/upload',
                'enableCsrf' => true, // default
                'postFieldName' => 'Filedata', // default
                //BEGIN METHOD
                'format' => [$this, 'methodName'],
                //END METHOD
                //BEGIN CLOSURE BY-HASH
                'overwriteIfExist' => true,
                'format' => function (UploadAction $action) {
                    $fileext = $action->uploadfile->getExtension();
                    $filename = sha1_file($action->uploadfile->tempName);
                    return "{$filename}.{$fileext}";
                },
                //END CLOSURE BY-HASH
                //BEGIN CLOSURE BY TIME
                'format' => function (UploadAction $action) {
                    $fileext = $action->uploadfile->getExtension();
                    $filehash = sha1(uniqid() . time());
                    $p1 = substr($filehash, 0, 2);
                    $p2 = substr($filehash, 2, 2);
                    return "{$p1}/{$p2}/{$filehash}.{$fileext}";
                },
                //END CLOSURE BY TIME
                'validateOptions' => [
                    'extensions' => ['jpg', 'png'],
                    'maxSize' => 1 * 1024 * 1024, //file size
                ],
                'beforeValidate' => function (UploadAction $action) {
                    //throw new Exception('test error');
                },
                'afterValidate' => function (UploadAction $action) {},
                'beforeSave' => function (UploadAction $action) {},
                'afterSave' => function (UploadAction $action) {
                    $goods_id = \Yii::$app->request->post('goods_id');
                    if($goods_id){
                        $model = new GoodsGallery();
                        $model->goods_id = $goods_id;
                        $model->path = $action->getWebUrl();
                        $model->save();
                        $action->output['fileUrl'] = $model->path;
                        $action->output['id'] = $model->id;
                    }else{
                        $action->output['fileUrl'] = $action->getWebUrl();//输出文件的相对路径
                    }
//                   $action->getFilename(); // "image/yyyymmddtimerand.jpg"
//                   $action->getWebUrl(); //  "baseUrl + filename, /upload/image/yyyymmddtimerand.jpg"
//                   $action->getSavePath(); // "/var/www/htdocs/upload/image/yyyymmddtimerand.jpg"
                },
            ],
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    "imageUrlPrefix"  => "http://admin.yiishop.com",//图片访问路径前缀
                    "imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}" ,//上传保存路径
                    "imageRoot" => \Yii::getAlias("@webroot"),
                ],
            ],
        ];
    }

    //预览商品信息
    public function actionView($id)
    {
        $model = Goods::findOne(['id'=>$id]);
        if($model==null){
            throw new NotFoundHttpException('商品不存在');
        }
        return $this->render('view',['model'=>$model]);

    }

    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>RbacFilter::className(),
                'only'=>['index','add','edit','delete'],
            ]
        ];
    }


}
