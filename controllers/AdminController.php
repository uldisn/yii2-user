<?php

class AdminController extends Controller
{
	public $defaultAction = 'admin';
	public $layout='//layouts/column2';
	
	private $_model;
    public $menu_route = "user/admin";  
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return CMap::mergeArray(parent::filters(),array(
			'accessControl', // perform access control for CRUD operations
		));
	}
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete','create','update','view'),
				'users'=>UserModule::getAdmins(),
			),
			array('allow', // for UserAdmin
				'actions'=>array('admin','create','update','view'),
				'expression'=>"Yii::app()->user->checkAccess('UserAdmin')",
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
        $this->layout='';
        
        $view = 'index';       
        if(Yii::app()->getModule('user')->view){
            $alt_view = Yii::app()->getModule('user')->view . '.admin.'.$view;
            if (is_readable(Yii::getPathOfAlias($alt_view) . '.php')) {
                $view = $alt_view;
                $this->layout=Yii::app()->getModule('user')->layout;
            }
        }          
        
		$model=new User('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['User']))
            $model->attributes=$_GET['User'];

        $this->render($view,array(
            'model'=>$model,
        ));

	}


	/**
	 * Displays a particular model.
	 */
	public function actionView()
	{
        $this->layout='';
        $model = $this->loadModel();
        
        //update record
        if (isset($_POST['user_role_name']) || isset($_POST['user_sys_ccmp_id'])) {

            //cheked roles
            $aChecked = Authassignment::model()->getUserRoles($model->id);            
            
            //get in form checked
            $aPostRole = array();
            if (isset($_POST['user_role_name'])) {
                foreach ($_POST['user_role_name'] as $nRoleId) {
                    $aPostRole[] = $nRoleId;
                }
            }
            $aDelRole = array_diff($aChecked, $aPostRole);
            $aNewRole = array_diff($aPostRole, $aChecked);

            $UserAdminRoles = Yii::app()->getModule('user')->UserAdminRoles;
            foreach ($aNewRole as $sRoleName) {
                // can not add no User Admin roles defined in main config
                if(!in_array($sRoleName,$UserAdminRoles)){
                    continue;
                }
                $aa_model = new Authassignment;
                $aa_model->itemname = $sRoleName;
                $aa_model->userid = $model->id;
                if (!$aa_model->save()) {
                    print_r($aa_model->errors);
                    exit;
                }
            }

            if(!empty($aDelRole)){
                Authassignment::model()->deleteAll(
                    "`userid` = :userid AND itemname in('".implode("','",$aDelRole)."')",
                array(':userid' => $model->id)
                );            
            }

            //checked companies
            $aUserCompanies = CcucUserCompany::model()->getUserCompnies($model->id,CcucUserCompany::CCUC_STATUS_SYS);
            $aChecked = array();
            foreach($aUserCompanies as $UC){
                $aChecked[] = $UC->ccuc_ccmp_id;
            }
            
            //get in form checked
            $aPostSysCcmp = array();
            if (isset($_POST['user_sys_ccmp_id'])) {
                foreach ($_POST['user_sys_ccmp_id'] as $ccmp_id) {
                    $aPostSysCcmp[] = $ccmp_id;
                }
            }
            $aDelSysCcmpid = array_diff($aChecked, $aPostSysCcmp);
            $aNewSysCcmpid = array_diff($aPostSysCcmp, $aChecked);

            $list = array();
            if(UserModule::isAdmin()){
                //for admin get all sys companies
                $criteria = new CDbCriteria;
                $criteria->compare('t.ccxg_ccgr_id', 1); //1 - syscompany
                $model_ccxg = CcxgCompanyXGroup::model()->findAll($criteria);                
                foreach ($model_ccxg as $mCcxg) {
                    $list[$mCcxg->ccxg_ccmp_id] = 1;
                }            
            }else{            
                foreach (Yii::app()->sysCompany->getClientCompanies() as $mCcmp) {
                    $list[$mCcmp->ccucCcmp->ccmp_id] = 1;
                } 
            }
            
            foreach ($aNewSysCcmpid as $cmmp_id) {
                // can not add no User Admin sys ccmp
                if(!isset($list[$cmmp_id])){
                    continue;
                }
                
                        //create ccuc (company <==> person)
                $mCcuc = new CcucUserCompany;
                $mCcuc->ccuc_ccmp_id = $cmmp_id;
                $mCcuc->ccuc_status = CcucUserCompany::CCUC_STATUS_SYS;
                $mCcuc->ccuc_person_id = $model->profile->person_id;
                $mCcuc->save();    
                if (!$mCcuc->save()) {
                    print_r($mCcuc->errors);
                    exit;
                }
            }

            if(!empty($aDelSysCcmpid)){
                CcucUserCompany::model()->deleteAll(
                    "`ccuc_status` = :ccuc_status "
                        . " AND `ccuc_person_id` = :ccuc_person_id "
                        . " AND ccuc_ccmp_id in('".implode("','",$aDelSysCcmpid)."')",
                array(
                    ':ccuc_person_id' => $model->profile->person_id,
                    ':ccuc_status' => CcucUserCompany::CCUC_STATUS_SYS
                    )
                );            
            }
        }
        
        $view = 'view';       
        if(Yii::app()->getModule('user')->view){
            $alt_view = Yii::app()->getModule('user')->view . '.admin.'.$view;
            if (is_readable(Yii::getPathOfAlias($alt_view) . '.php')) {
                $view = $alt_view;
                $this->layout=Yii::app()->getModule('user')->layout;
            }
        }           
        
		$model = $this->loadModel();
		$this->render($view,array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
        $this->layout='';
		$model=new User;
		$profile=new Profile;
		$this->performAjaxValidation(array($model,$profile));
		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			$model->activkey=Yii::app()->controller->module->encrypting(microtime().$model->password);
			$profile->attributes=$_POST['Profile'];
			$profile->user_id=0;
			if($model->validate()&&$profile->validate()) {
				$model->password=Yii::app()->controller->module->encrypting($model->password);
				if($model->save()) {
                    if (Yii::app()->sysCompany->getActiveCompany()){
                        
                        //create person
                        $model_person = new PprsPerson;
                        $model_person->pprs_first_name = $profile->first_name;
                        $model_person->pprs_second_name = $profile->last_name;
                        $model_person->pprs_ccmp_id = Yii::app()->sysCompany->getActiveCompany();

                        $model_person->save();
                        

                    }
					$profile->user_id=$model->id;
					$profile->person_id=$model_person->primaryKey;
					$profile->save();                    
				}
				$this->redirect(array('view','id'=>$model->id));
			} else $profile->validate();
		}

        $view = 'create';       
        if(Yii::app()->getModule('user')->view){
            $alt_view = Yii::app()->getModule('user')->view . '.admin.'.$view;
            if (is_readable(Yii::getPathOfAlias($alt_view) . '.php')) {
                $view = $alt_view;
                $this->layout=Yii::app()->getModule('user')->layout;
            }
        }         
        
		$this->render($view,array(
			'model'=>$model,
			'profile'=>$profile,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel();
		$profile=$model->profile;
		$this->performAjaxValidation(array($model,$profile));
		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			$profile->attributes=$_POST['Profile'];
			
			if($model->validate()&&$profile->validate()) {
				$old_password = User::model()->notsafe()->findByPk($model->id);
				if ($old_password->password!=$model->password) {
					$model->password=Yii::app()->controller->module->encrypting($model->password);
					$model->activkey=Yii::app()->controller->module->encrypting(microtime().$model->password);
				}
				$model->save();
				$profile->save();
				$this->redirect(array('view','id'=>$model->id));
			} else $profile->validate();
		}

        $view = 'update';       
        if(Yii::app()->getModule('user')->view){
            $alt_view = Yii::app()->getModule('user')->view . '.admin.'.$view;
            if (is_readable(Yii::getPathOfAlias($alt_view) . '.php')) {
                $view = $alt_view;
                $this->layout=Yii::app()->getModule('user')->layout;
            }
        }                  
        
		$this->render($view,array(
			'model'=>$model,
			'profile'=>$profile,
		));
	}


	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$model = $this->loadModel();
			$profile = Profile::model()->findByPk($model->id);
			
			// Make sure profile exists
			if ($profile)
				$profile->delete();

			$model->delete();
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_POST['ajax']))
				$this->redirect(array('/user/admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}
	
	/**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($validate)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
        {
            echo CActiveForm::validate($validate);
            Yii::app()->end();
        }
    }
	
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
            {
                $this->_model=User::model()->is_sys_user()->notsafe()->findbyPk($_GET['id']);
            }    
			if($this->_model===null )
				throw new CHttpException(404,'The requested page does not exist.');
            
            
		}
		return $this->_model;
	}
	
}
