<?php
if(!$ajax){
    Yii::app()->clientScript->registerCss('rel_grid',' 
            .rel-grid-view {margin-top:-60px;}
            .rel-grid-view div.summary {height: 60px;}
            ');     
}
?>
<?php
if(!$ajax || $ajax == 'uxip-user-xip-table-grid'){
    Yii::beginProfile('uxip_iptb_id.view.grid');
        
    $grid_error = '';
    $grid_warning = '';
    
    if (empty($modelMain->uxipUserXIpTables)) {
        $model = new UxipUserXIpTable;
        $model->uxip_iptb_id = $modelMain->primaryKey;
        if(!$model->save()){
            $grid_error .= implode('<br/>',$model->errors);
        }
        unset($model);
    }     
?>

<div class="table-header">
    <?=UserModule::t('User IP')?>
    <?php    
        
    $this->widget(
        'bootstrap.widgets.TbButton',
        array(
            'buttonType' => 'ajaxButton', 
            'type' => 'primary', // '', 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'
            'size' => 'mini',
            'icon' => 'icon-plus',
            'url' => array(
                '//user/uxipUserXIpTable/ajaxCreate',
                'field' => 'uxip_iptb_id',
                'value' => $modelMain->primaryKey,
                'ajax' => 'uxip-user-xip-table-grid',
            ),
            'ajaxOptions' => array(
                    'success' => 'function(html) {$.fn.yiiGridView.update(\'uxip-user-xip-table-grid\');}'
                    ),
            'htmlOptions' => array(
                'title' => UserModule::t('Add new record'),
                'data-toggle' => 'tooltip',
            ),                 
        )
    );        
    ?>
</div>
 
<?php 

    if(!empty($grid_error)){
        ?>
        <div class="alert alert-error"><?php echo $grid_error?></div>
        <?php
    }  

    if(!empty($grid_warning)){
        ?>
        <div class="alert alert-warning"><?php echo $grid_warning?></div>
        <?php
    }  

    $model = new UxipUserXIpTable();
    $model->uxip_iptb_id = $modelMain->primaryKey;

    // render grid view

    $this->widget('TbGridView',
        array(
            'id' => 'uxip-user-xip-table-grid',
            'dataProvider' => $model->search(),
            'template' => '{summary}{items}',
            'summaryText' => '&nbsp;',
            'htmlOptions' => array(
                'class' => 'rel-grid-view'
            ),            
            'columns' => array(
                array(
                'class' => 'editable.EditableColumn',
                'name' => 'uxip_user_id',
                'editable' => array(
                    'type' => 'select',
                    'url' => $this->createUrl('//user/uxipUserXIpTable/editableSaver'),
                    'source' => CHtml::listData(Users::model()->findAll(array('limit' => 1000)), 'id', 'itemLabel'),
                    //'placement' => 'right',
                )
            ),

                array(
                    'class' => 'TbButtonColumn',
                    'buttons' => array(
                        'view' => array('visible' => 'FALSE'),
                        'update' => array('visible' => 'FALSE'),
                        'delete' => array('visible' => 'Yii::app()->user->checkAccess("User.IptbIpTable.DeleteuxipUserXIpTables")'),
                    ),
                    'deleteButtonUrl' => 'Yii::app()->controller->createUrl("/user/uxipUserXIpTable/delete", array("" => $data->))',
                    'deleteConfirmation'=>Yii::t('','Do you want to delete this item?'),   
                    'deleteButtonOptions'=>array('data-toggle'=>'tooltip'),                    
                ),
            )
        )
    );
    ?>

<?php
    Yii::endProfile('uxip_iptb_id.view.grid');
}    
?>
