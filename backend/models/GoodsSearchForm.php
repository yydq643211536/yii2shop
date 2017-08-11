<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/3
 * Time: 10:03
 */

namespace backend\models;


use frontend\components\SphinxClient;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class GoodsSearchForm extends Model
{
    public $name;
    public $sn;
    public $minPrice;
    public $maxPrice;

    public function rules()
    {
        return [
            ['name','string','max'=>50],
            ['sn','string'],
            ['minPrice','double'],
            ['maxPrice','double'],

        ];
    }

    public function search(ActiveQuery $query)
    {
        //加载表单提交的数据
        $this->load(\Yii::$app->request->get());
        if ($this->name) {
            $cl = new SphinxClient();
            $cl->SetServer ( '127.0.0.1', 9312);
            //$cl->SetConnectTimeout ( 10 );
            $cl->SetArrayResult ( true );
// $cl->SetMatchMode ( SPH_MATCH_ANY);
            $cl->SetMatchMode ( SPH_MATCH_ALL);
            $cl->SetLimits(0, 1000);
            $res = $cl->Query($this->name, 'goods');//shopstore_search
            if(isset($res['matches'])){
                $ids = ArrayHelper::getColumn($res['matches'],'id');
                $query->where(['in','id',$ids]);
            }else{
                $query->where(['id'=>0]);
                return;
            }


            if ($this->sn) {
                $query->andWhere(['like', 'sn', $this->sn]);
            }
            if ($this->maxPrice) {
                $query->andWhere(['<=', 'shop_price', $this->maxPrice]);
            }
            if ($this->minPrice) {
                $query->andWhere(['>=', 'shop_price', $this->minPrice]);
            }
        }
    }
}