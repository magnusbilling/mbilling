<?php header ('Content-type: text/html; charset=utf-8'); ?>
<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />

<style type="text/css">
	
.styled-select
{       
        width:300px;
}

</style>
<?php $form=$this->beginWidget('CActiveForm', array(
					'id'=>'contactform',
					'htmlOptions'=>array('class'=>'rounded'),
					'enableAjaxValidation'=>false,
					'clientOptions'=>array('validateOnSubmit'=>true),
					'errorMessageCssClass'=>'error',
					));?>

<br/>

<?php if(isset($message)):
	echo Yii::t('yii',$message);
?>
<?php else:?>



	<?php $modelMethodPay = CHtml::listData($modelMethodPay,'id','show_name'); ?>


	<div class="field">
		<?php echo $form->labelEx($model,Yii::t('yii','MethodPay'))?>
		<div class="styled-select">
		<?php echo $form->dropDownList($model, 'id_method', $modelMethodPay, array(
					'prompt' => Yii::t('yii','Select a Method')
					)
					); ?>
		</div>
	</div>
	<br>
	<?php $totalPrice =0;?>
	<?php for ($i=0; $i < count($modelServicesUse); $i++):?>
		<?php $totalPrice += $modelServicesUse[$i]->idServices->price?>
		<div class="field">

			<?php echo $form->labelEx($model,Yii::t('yii','Service'). ' '.$modelServicesUse[$i]->idServices->name)?>
			<?php echo $form->textField($model,'service0', array(
										'class' => 'input',
										'value' => $currency . ' '. number_format($modelServicesUse[$i]->idServices->price,2),
										'readOnly' => true
										))
									?>

		</div>
	<?php endfor;?>
	<br>

	<?php if($modelServicesUse[0]->idUser->credit > 0):?>
	<div class="field">
		<?php echo $form->labelEx($model,Yii::t('yii','Your credit'))?>
		<?php echo $form->checkBox($model,'use_credit',  array('checked'=>false)); ?>
		<?php echo ' '.$currency . ' '. number_format($modelServicesUse[0]->idUser->credit,2) . ' '.Yii::t('yii','Use that')?>

	</div>	
	<?php endif;?>	
	<br><br><br>

	<div class="field">
		<?php echo $form->labelEx($model,Yii::t('yii','Total Price'))?>
		<?php echo $form->textField($model,'total', array(
										'class' => 'input',
										'value' => $currency . ' '. number_format($totalPrice,2),
										'readOnly' => true
										 ))?>
		<p>&nbsp;</p>
	</div>

	<?php echo CHtml::submitButton(Yii::t('yii','Pay Now'), array('class' => 'button'));?>
<?php endif;?>
<?php $this->endWidget(); ?>