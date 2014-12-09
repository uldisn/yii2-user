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
			array('allow', // allow admin user to view other users
				//'actions'=>array('admin','delete','create','update','view'),
                'actions'=>array('admin','view'),
				'users'=>UserModule::getAdmins(),
			),
			array('allow', // for UserAdmin
				'actions'=>array('admin','delete','create','update','view','genCodeCard','emailInvitation'),
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
        if (Yii::app()->user->checkAccess("UserAdmin")
            && (
                isset($_POST['user_role_name']) ||
                isset($_POST['user_sys_ccmp_id']) ||
                isset($_POST['ip_tables'])
            )
        ) {

            //cheked roles
            $aChecked = Authassignment::model()->getUserRoles($model->id);            
            $admin_role = Yii::app()->getModule('rights')->superuserName;
            
            //for administrator can not save changes of roles
            if(!in_array($admin_role, $aChecked)){            
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
                    $list[$mCcmp['ccmp_id']] = 1;
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
                //$mCcuc->save();    
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
            
            $security_policy = Yii::app()->getModule('user')->SecurityPolicy;
            
            if ($security_policy['useIpTables']) {
                
                UxipUserXIpTable::model()->deleteAll(
                    "`uxip_user_id` = :uxip_user_id ",
                    [':uxip_user_id' => $model->id]
                );
                
                if (!empty($_POST['ip_tables'])) {
                    foreach($_POST['ip_tables'] as $ip) {
                        $Iptb = new UxipUserXIpTable;
                        $Iptb->uxip_user_id = $model->id;
                        $Iptb->uxip_iptb_id = $ip;

                        if (!$Iptb->save()) {
                            print_r($Iptb->errors);
                            exit;
                        }
                    }
                }
                
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
    
    public function actionGenCodeCard($request_type)
    {
        
        // Validate settings
        if (!Yii::app()->user->checkAccess("UserAdmin")) {
            $this->redirect(array('view','id'=>$model->id));
        }
        
        $code_card = Yii::app()->getModule('user')->codeCard;
        
        if (empty($code_card['host']) || empty($code_card['apy_key']) || empty($code_card['crypt_key'])) {
            $this->redirect(array('view','id'=>$model->id));
        }
        
		$model   = $this->loadModel();
        $profile = $model->profile;
        
        $error = '';
        
        if ($request_type == 'validate_code') {
            if (empty($_POST['code']) || empty($_POST['session_id'])) {
                $this->redirect(array('view', 'id'=>$model->id));
            }
            $add_data   = $_POST['code'];
            $session_id = $_POST['session_id'];
        } else {
            $add_data   = $model->id;
            $session_id = '';
        }
        
        $request = array(
            'request_type' => $request_type,
            'user_id'      => Yii::app()->user->getId(),
            'add_data'     => $add_data,
            'session_id'   => $session_id
        );
        
        $reply = array (
            'error' => ''
        );
        
        CodeCard::request($request, $reply);
        
        if ($reply['error']) {
            $error = UserModule::t($reply['error']);
        } elseif ($reply['reply_type'] == 'code_card') {
            
            // Savec codeCard expire date
            $profile->setAttribute(
                'code_card_expire_date',
                $reply['add_data']['expire_date']
            );
            $profile->save();
            
            // Save codeCard as PDF
            $pdf = new TCPDF('L', PDF_UNIT, 'BUSINESS_CARD_ES', true, 'UTF-8', false);
            
            //Basic setup
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(0, 2, 4, true);
            $pdf->SetHeaderMargin(0);
            $pdf->SetFooterMargin(0);
            
            // set font
            $pdf->SetFont('helvetica', '', 8);
            
            $pdf->setCellHeightRatio(1.1);
            
            // add a page
            $pdf->AddPage();
            
            $html = $this->renderPartial(
                'codeCard',
                array('reply' => $reply),
                true
            );
            
            //echo $html;
            //exit;
            
            // output the HTML content
            $pdf->writeHTML($html, false);
            
            // reset pointer to the last page
            $pdf->lastPage();
            
            $pdf->Output('CodeCard.pdf', 'D');
            
            exit;
            
        }
        
        if ($reply['reply_type'] == 'validate_code') {
            $view = 'validate_code';
        } else {
            $view = 'codeCard_empty';
        }
        
        if (Yii::app()->getModule('user')->view) {
            $alt_view = Yii::app()->getModule('user')->view . '.admin.' . $view;
            if (is_readable(Yii::getPathOfAlias($alt_view) . '.php')) {
                $view = $alt_view;
                $this->layout=Yii::app()->getModule('user')->layout;
            }
        }
        
		$this->render($view,
            array(
                'model' => $model,
                'reply' => $reply,
                'error' => $error,
            )
        );
        
    }
	
    /**
     * send invitation/password reset to user email and redirect ot view with message
     * 
     */
    public function actionEmailInvitation(){
        
        //generate password
        $password = DbrLib::rand_string(8);
        
        //save password
        $model = $this->loadModel();
        $model->password=Yii::app()->controller->module->encrypting($password);
        $model->save();        
        
        //message
        $subject = Yii::app()->name;        
        $message = 'For access to system please use. <br />
                    link: '.Yii::app()->getBaseUrl(true) . '/<br />
                    username: <b>'. $model->username.'</b>,
                    password:<b> '.$password.'</b>';

        
        //create message
        $swiftMessage = Swift_Message::newInstance($subject);
        $swiftMessage->setBody($message, 'text/html');
        $swiftMessage->setFrom(Yii::app()->emailManager->fromEmail, Yii::app()->emailManager->fromName);
        $swiftMessage->setTo($model->email, $model->profile->first_name . ' ' . $model->profile->last_name);

        //send
        if(Yii::app()->emailManager->deliver($swiftMessage, 'smtp')){
            //redirecto view as ok
            $this->redirect(array('view','id'=>$model->id,'sent' => 'ok'));        
        }else{
            //redirecto view as error
            $this->redirect(array('view','id'=>$model->id,'sent' => 'error'));                    
        }
        
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
