
	<body class="login-layout">
		<div class="main-container container-fluid">
			<div class="main-content">
				<div class="row-fluid">
					<div class="span12">
						<div class="login-container">
							<div class="row-fluid">
								<div class="center">
									<h1>
										<i class="icon-leaf green"></i>
										<span class="red"><?php echo Yii::app()->name;?></span>
									</h1>
								</div>
							</div>

							<div class="space-6"></div>

							<div class="row-fluid">
								<div class="position-relative">
									<div id="login-box" class="login-box visible widget-box no-border">
										<div class="widget-body">
											<div class="widget-main">
												<h4 class="header blue lighter bigger">
													<i class="icon-coffee green"></i>
													<?php echo UserModule::t("Fill in your login credentials:"); ?>
												</h4>
                                                <?php echo CHtml::errorSummary($model); ?>
												<div class="space-6"></div>

												<?php echo CHtml::beginForm(); ?>
													<fieldset>
														<label>
															<span class="block input-icon input-icon-right">
																<input type="text" class="span12" placeholder="Username" name="UserLogin[username]" maxlength="128" />
																<i class="icon-user"></i>
															</span>
														</label>

														<label>
															<span class="block input-icon input-icon-right">
																<input type="password" class="span12" placeholder="Password" name="UserLogin[password]"/>
																<i class="icon-lock"></i>
															</span>
														</label>

														<div class="space"></div>

														<div class="clearfix">
                                                            <?php $this->widget('bootstrap.widgets.TbButton', array(
                                                                'label'=>UserModule::t("Login"),
                                                                'buttonType'=>'submit', 
                                                                'type'=>'primary', // null, 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'
                                                                //'size'=>'large', // null, 'large', 'small' or 'mini'
                                                                'icon'=>'icon-key',
                                                                'htmlOptions'=> array('class' => 'width-35 pull-right btn btn-small btn-primary'),
                                                            )); ?>                                                            
														</div>

														<div class="space-4"></div>
													</fieldset>
												</form><?php echo CHtml::endForm(); ?>

                                                <?php
                                                    $form = new CForm(array(
                                                        'elements'=>array(
                                                            'username'=>array(
                                                                'type'=>'text',
                                                                'maxlength'=>200,
                                                            ),
                                                            'password'=>array(
                                                                'type'=>'password',
                                                                'maxlength'=>32,
                                                            ),
                                                        ),

                                                        'buttons'=>array(
                                                            'login'=>array(
                                                                'type'=>'submit',
                                                                'label'=>'Login',
                                                            ),
                                                        ),
                                                    ), $model);
                                                    ?>
                                                
											</div><!-- /widget-main -->

										</div><!-- /widget-body -->
									</div><!-- /login-box -->
								</div><!-- /position-relative -->
							</div>
						</div>
					</div><!-- /.span -->
				</div><!-- /.row-fluid -->
			</div>
		</div><!-- /.main-container -->
