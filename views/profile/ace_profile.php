<?php $this->pageTitle=Yii::app()->name . ' - '.UserModule::t("Profile");

?>
<h2><?php echo UserModule::t('Your profile'); ?></h2>
<div class="row">
    <div class="span7">
        
<?php if(Yii::app()->user->hasFlash('profileMessage')): ?>
<div class="success">
	<?php echo Yii::app()->user->getFlash('profileMessage'); ?>
</div>
<?php endif; ?>
        
<?php 
        $this->widget(
            'TbDetailView', 
            array(
                'data' => $model,
                'attributes' => array(
                array(
                    'name' => 'username',
                    'type' => 'raw',
                ),

    )
        )
);
?>
      
        

    </div></div>