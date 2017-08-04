<?php
$form = \yii\bootstrap\ActiveForm::begin();
echo $form->field($model,'name');
echo $form->field($model,'intro')->textarea();
echo $form->field($model,'article_category_id')->dropDownList(\backend\models\Article::getArray());
echo $form->field($model,'sort');
echo $form->field($model1,'content')->widget('kucha\ueditor\UEditor',[]);
//echo $form->field($model1,'content')->textarea(['rows'=>16]);
echo $form->field($model,'status',['inline'=>true])->radioList(\backend\models\Article::getStatusOptions());
echo \yii\bootstrap\Html::submitButton('提交',['class'=>'btn btn-sm btn-primary']);
\yii\bootstrap\ActiveForm::end();