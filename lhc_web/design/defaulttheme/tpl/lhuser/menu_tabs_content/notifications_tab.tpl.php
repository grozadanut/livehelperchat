<div role="tabpanel" class="tab-pane <?php if ($tab == 'tab_notifications') : ?>active<?php endif;?>" id="notifications">
     <div class="form-group">
         <div class="float-start">
            <?php include(erLhcoreClassDesign::designtpl('lhchat/user_settings.tpl.php'));?>
		 </div>
		 
		 <br/>
	     <br/>
	     
	     <form action="<?php echo erLhcoreClassDesign::baseurl('user/account')?>#notifications" method="post" enctype="multipart/form-data">
	         <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

             <div class="row pb-2">
                 <div class="col-12">
                     <div class="input-group">
                         <input type="button" class="btn btn-sm btn-primary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Request notification permission')?>" onclick="lhinst.requestNotificationPermission()" />
                         <input type="number" id="test_chat_id" class="form-control form-control-sm" value="" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','You can test notification by entering chat ID')?>"/>
                         <input type="button" class="btn btn-sm btn-primary" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Test notification')?>" onclick="ee.emitEvent('svelteTestNotification',[document.getElementById('test_chat_id').value])" />
                         <span class="input-group-text fs12"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','If you do not see a notification after a click it means your settings are not correct, or you do not allow notifications.')?></span>
                     </div>
                 </div>
             </div>

             <div class="row">
                <div class="col-6">
            	     <div class="form-group">
                        <label><input type="checkbox" name="ownntfonly" value="on" <?php erLhcoreClassModelUserSetting::getSetting('ownntfonly',0) == 1 ? print 'checked="checked"' : '' ?>> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Show notification only if I am an owner pending chat')?></label>
                        <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','You will stop receive notifications for pending chats if you are not an owner')?></i></small>
            	     </div>
            	     
                     <div class="form-group">
                        <label><input type="checkbox" name="sn_off" value="on" <?php erLhcoreClassModelUserSetting::getSetting('sn_off',1) == 1 ? print 'checked="checked"' : '' ?>> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Show notifications if I am offline')?></label>
                        <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','You will not receive notifications if you are not online')?></i></small>
            	     </div>
            	        	     
            	     
            	     <div class="form-group">
                        <label><input type="checkbox" name="show_alert_chat" value="on" <?php erLhcoreClassModelUserSetting::getSetting('show_alert_chat',0) == 1 ? print 'checked="checked"' : '' ?>> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Show alert for new chats')?></label>
            	     </div>

            	     <div class="form-group">
                        <label><input type="checkbox" name="show_alert_transfer" value="on" <?php erLhcoreClassModelUserSetting::getSetting('show_alert_transfer',1) == 1 ? print 'checked="checked"' : '' ?>> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Show alerts for transferred chats')?></label>
                         <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','You will receive alert notification if chat is transferred directly to you. You will be able to accept it directly from alert.')?></i></small>
            	     </div>

                    <div class="form-group">
                        <label><input type="checkbox" name="hide_quick_notifications" value="1" <?php erLhcoreClassModelUserSetting::getSetting('hide_quick_notifications',0) == 1 ? print 'checked="checked"' : '' ?> /> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Hide quick notifications');?></label>
                        <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Quick notifications are the ones that you see at the top left corner of the application.')?></i></small>
                    </div>

                    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhnotifications','use_operator')) : ?>
                    <div class="form-group">
                        <label><input type="checkbox" name="hide_pers_chat" value="1" <?php erLhcoreClassModelUserSetting::getSetting('hide_pers_chat',0) == 1 ? print 'checked="checked"' : '' ?> /> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Do not show persistent notifications for chat actions');?></label>
                    </div>
                    <?php endif; ?>


                </div>
                <div class="col-6">
                
                     <?php if ((int)erLhcoreClassModelChatConfig::fetchCache('activity_track_all')->current_value == 1) : ?>
                     <div class="alert alert-warning"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Activity tracking is set at global level. Your settings will be be ignored. Timeout value still will be taken from your account settings.')?></div>
                     <?php endif; ?>
                     
                     <div class="form-group">
                        <label><input type="checkbox" name="trackactivity" value="on" <?php erLhcoreClassModelUserSetting::getSetting('trackactivity',0) == 1 ? print 'checked="checked"' : '' ?>> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Change my online/offline status based on my activity')?></label>
                        <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','After certain period of time if no actions are detected you will be marked as offline automatically')?></i></small>
            	     </div>
            	     
                     <div class="form-group">
                        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Choose timeout value')?></label>
                        <br/><small><i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Select after how long of inactivity you will be marked as offline automatically')?></i></small>
                        <select class="form-control form-control-sm" name="trackactivitytimeout">
                            <option value="-1" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == -1 ? 'selected="selected"' : ''?>><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','Use default system value')?> (<?php echo (int)erLhcoreClassModelChatConfig::fetchCache('activity_timeout')->current_value?> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','minutes')?>)</option>
                            <option value="300" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 300 ? 'selected="selected"' : ''?>>5 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','minutes')?></option>
                            <option value="600" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 600 ? 'selected="selected"' : ''?>>10 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','minutes')?></option>
                            <option value="1800" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 1800 ? 'selected="selected"' : ''?>>30 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','minutes')?></option>
                            <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhuser','largeactivitytimeout')) : ?>
                            <option value="3600" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 3600 ? 'selected="selected"' : ''?>>1 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','hour')?>
                            <option value="14400" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 14400 ? 'selected="selected"' : ''?>>4 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','hours')?>
                            <option value="28800" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 28800 ? 'selected="selected"' : ''?>>8 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','hours')?>
                            <option value="43200" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 43200 ? 'selected="selected"' : ''?>>12 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','hours')?>
                            <option value="86400" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 86400 ? 'selected="selected"' : ''?>>1 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','day')?>
                            <option value="432000" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 432000 ? 'selected="selected"' : ''?>>5 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','days')?>
                            <option value="864000" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 864000 ? 'selected="selected"' : ''?>>10 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','days')?>
                            <option value="1296000" <?php echo erLhcoreClassModelUserSetting::getSetting('trackactivitytimeout',-1) == 1296000 ? 'selected="selected"' : ''?>>15 <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/configuration','days')?>
                            <?php endif; ?>
                        </select>
            	     </div>
            	     
                </div>	
             </div>	
                	     
    	     <input type="submit" class="btn btn-secondary" name="UpdateNotifications_account" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Update');?>" />
         </form>
         
	 </div>

    <?php if (erLhcoreClassUser::instance()->hasAccessTo('lhnotifications','use_operator')) : ?>

    <script src="<?php echo erLhcoreClassDesign::designJS('js/lhc.notifications.min.js');?>"></script>

    <?php $notificationsSettings = (array)erLhcoreClassModelChatConfig::fetch('notifications_settings_op')->data; ?>

    <?php if ($notificationsSettings['enabled'] == 1) : ?>

    <hr>
    <h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('notifications/accounts','Persistent notifications');?></h3>

    <?php if (isset($_GET['notification']) && $_GET['notification'] == 'fail' && isset($_GET['notification_reason'])) : ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['notification_reason'])?></div>
    <?php endif; ?>

    <?php if (isset($_GET['notification']) && $_GET['notification'] == 'success') : ?>
    <div class="alert alert-success"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('notifications/accounts','Success');?></div>
    <?php endif; ?>

    <p><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('notifications/accounts','Those notifications are sent independently is browser closed or not. Notifications for chat is shown only if active window is not detected. Mobile notifications should be enabled.');?></p>

    <button class="btn btn-sm btn-primary" id="subscribe-persistent"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('notifications/accounts','Subscribe');?></button>

    <script>
        (function(){
            new LHCOperatorNotifications({
                'public_key': <?php echo json_encode($notificationsSettings['public_key']); ?>
            });
        })();
    </script>

    <h5 class="mt-4"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('notifications/accounts','Your subscriptions');?></h5>

    <div id="subscriptions">

    </div>

    <?php endif; ?>

    <?php endif; ?>
</div>





