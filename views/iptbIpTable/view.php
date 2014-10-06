<?php
    $this->setPageTitle(
        UserModule::t('IP Table')
        . ' - '
        . UserModule::t('View')
        . ': '   
        . $model->getItemLabel()            
);    
$this->breadcrumbs[UserModule::t('IP Tables')] = array('admin');
$this->breadcrumbs[$model->{$model->tableSchema->primaryKey}] = array('view','id' => $model->{$model->tableSchema->primaryKey});
$this->breadcrumbs[] = UserModule::t('View');
$cancel_buton = $this->widget("bootstrap.widgets.TbButton", array(
    #"label"=>Yii::t("","Cancel"),
    "icon"=>"chevron-left",
    "size"=>"large",
    "url"=>(isset($_GET["returnUrl"]))?$_GET["returnUrl"]:array("{$this->id}/admin"),
    "visible"=>(Yii::app()->user->checkAccess("User.IptbIpTable.*") || Yii::app()->user->checkAccess("User.IptbIpTable.View")),
    "htmlOptions"=>array(
                    "class"=>"search-button",
                    "data-toggle"=>"tooltip",
                    "title"=>Yii::t("","Back"),
                )
 ),true);
    
?>
<?php $this->widget("TbBreadcrumbs", array("links"=>$this->breadcrumbs)) ?>
<div class="clearfix">
    <div class="btn-toolbar pull-left">
        <div class="btn-group"><?php echo $cancel_buton;?></div>
        <div class="btn-group">
            <h1>
                <i class=""></i>
                <?php echo UserModule::t('IP Table');?>                <small><?php echo$model->itemLabel?></small>
            </h1>
        </div>
        <div class="btn-group">
            <?php
            
            $this->widget("bootstrap.widgets.TbButton", array(
                "label"=>Yii::t("","Delete"),
                "type"=>"danger",
                "icon"=>"icon-trash icon-white",
                "size"=>"large",
                "htmlOptions"=> array(
                    "submit"=>array("delete","iptb_id"=>$model->{$model->tableSchema->primaryKey}, "returnUrl"=>(Yii::app()->request->getParam("returnUrl"))?Yii::app()->request->getParam("returnUrl"):$this->createUrl("admin")),
                    "confirm"=>Yii::t("","Do you want to delete this item?")
                ),
                "visible"=> (Yii::app()->request->getParam("iptb_id")) && (Yii::app()->user->checkAccess("User.IptbIpTable.*") || Yii::app()->user->checkAccess("User.IptbIpTable.Delete"))
            ));
            ?>
        </div>
    </div>
</div>



<div class="row">
    <div class="span12">
        <h2>
            <?php echo UserModule::t('Data')?>            <small>
                #<?php echo $model->iptb_id ?>            </small>
        </h2>

        <?php
        $this->widget(
            'TbDetailView',
            array(
                'data' => $model,
                'attributes' => array(
                
                array(
                    'name' => 'iptb_id',
                    'type' => 'raw',
                    'value' => $this->widget(
                        'EditableField',
                        array(
                            'model' => $model,
                            'attribute' => 'iptb_id',
                            'url' => $this->createUrl('/user/iptbIpTable/editableSaver'),
                        ),
                        true
                    )
                ),

                array(
                    'name' => 'iptb_name',
                    'type' => 'raw',
                    'value' => $this->widget(
                        'EditableField',
                        array(
                            'model' => $model,
                            'attribute' => 'iptb_name',
                            'url' => $this->createUrl('/user/iptbIpTable/editableSaver'),
                        ),
                        true
                    )
                ),

                array(
                    'name' => 'iptb_from',
                    'type' => 'raw',
                    'value' => $this->widget(
                        'EditableField',
                        array(
                            'model' => $model,
                            'attribute' => 'iptb_from',
                            'url' => $this->createUrl('/user/iptbIpTable/editableSaver'),
                        ),
                        true
                    )
                ),

                array(
                    'name' => 'iptb_to',
                    'type' => 'raw',
                    'value' => $this->widget(
                        'EditableField',
                        array(
                            'model' => $model,
                            'attribute' => 'iptb_to',
                            'url' => $this->createUrl('/user/iptbIpTable/editableSaver'),
                        ),
                        true
                    )
                ),

                array(
                    'name' => 'iptb_status',
                    'type' => 'raw',
                    'value' => $this->widget(
                        'EditableField',
                        array(
                            'model' => $model,
                            'type' => 'select',
                            'url' => $this->createUrl('/user/iptbIpTable/editableSaver'),
                            'source' => $model->getEnumFieldLabels('iptb_status'),
                            'attribute' => 'iptb_status',
                            //'placement' => 'right',
                        ),
                        true
                    )
                ),
           ),
        )); ?>
    </div>

    </div>
    <div class="row">

    <div class="span12">
        <?php //$this->renderPartial('_view-relations_grids',array('modelMain' => $model, 'ajax' => false,)); ?>    </div>
</div>

<?php echo $cancel_buton; ?>