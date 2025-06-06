<?php

$tpl = erLhcoreClassTemplate::getInstance( 'lhpermission/editrole.tpl.php');

$Role = erLhcoreClassRole::getSession()->load( 'erLhcoreClassModelRole', (int)$Params['user_parameters']['role_id'] );

if (isset($_POST['Cancel_role']))
{
    erLhcoreClassModule::redirect('permission/roles' );
    exit ;
}

if (isset($_POST['Update_role']))
{
   $definition = array(
        'Name' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::REQUIRED, 'unsafe_raw'
        )
    );

   if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
   		erLhcoreClassModule::redirect();
   		exit;
   }

    $form = new ezcInputForm( INPUT_POST, $definition );
    $Errors = array();

    if ( !$form->hasValidData( 'Name' ) || $form->Name == '' )
    {
        $Errors[] = erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','Please enter role name');
    }

    if (count($Errors) == 0)
    {
        $Role->name = $form->Name;
        erLhcoreClassRole::getSession()->update($Role);

        erLhcoreClassLog::logObjectChange(array(
            'object' => $Role,
            'msg' => array(
                'action' => 'role_name_change',
                'user_id' => $currentUser->getUserID(),
                'new' => $Role
            )
        ));

        erLhcoreClassModule::redirect('permission/roles');
        exit ;

    }  else {
        $tpl->set('errors',$Errors);
    }
}

$tpl->set('role',$Role);

if (isset($_POST['New_policy']) || isset($_GET['newPolicy']))
{
    $tpl->setFile( 'lhpermission/newpolicy.tpl.php');
}


if (isset($_POST['Store_policy']))
{
    $definition = array(
        'Module' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::REQUIRED, 'string'
        ),
        'Limitation' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::REQUIRED, 'unsafe_raw'
        ),
        'type' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'boolean'
        ),
       'ModuleFunction' => new ezcInputFormDefinitionElement(
				ezcInputFormDefinitionElement::OPTIONAL, 'string',
				null,
				FILTER_REQUIRE_ARRAY
		)
    );

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
    	erLhcoreClassModule::redirect();
    	exit;
    }

    $form = new ezcInputForm( INPUT_POST, $definition );
    $Errors = array();

    if ( !$form->hasValidData( 'Module' ) || $form->Module == '' )
    {
        $Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','Please choose module');
    }

    if ( !$form->hasValidData( 'ModuleFunction' ) || empty($form->ModuleFunction) )
    {
        $Errors[] =  erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','Please choose module function');
    }

    if (count($Errors) == 0)
    {
    	foreach ($form->ModuleFunction as $moduleFunction) {
    		$RoleFunction = new erLhcoreClassModelRoleFunction();
    		$RoleFunction->role_id = $Role->id;
    		$RoleFunction->module = $form->Module;
    		$RoleFunction->function = $moduleFunction;

            if ( $form->hasValidData('Limitation'))
            {
                $RoleFunction->limitation = $form->Limitation;
            }

            $RoleFunction->type = $form->hasValidData( 'type' ) && $form->type === true ? 1 : 0;

            erLhcoreClassRole::getSession()->save($RoleFunction);

            erLhcoreClassLog::logObjectChange(array(
                'object' => $Role,
                'msg' => array(
                    'action' => 'assign_role_function',
                    'user_id' => $currentUser->getUserID(),
                    'new' => $RoleFunction
                )
            ));

            erLhcoreClassAdminChatValidatorHelper::clearUsersCache();

        }
            
    } else {
    	$tpl->set( 'errors', $Errors);
        $tpl->setFile( 'lhpermission/newpolicy.tpl.php');
    }
}

if (isset($_POST['Delete_policy']))
{
	if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
		erLhcoreClassModule::redirect();
		exit;
	}

    if (isset($_POST['PolicyID']) && count($_POST['PolicyID']) > 0)
    {
        foreach ($_POST['PolicyID'] as $PolicyID)
        {
            $RoleFunction = erLhcoreClassRole::getSession()->load( 'erLhcoreClassModelRoleFunction', $PolicyID);

            erLhcoreClassLog::logObjectChange(array(
                'object' => $Role,
                'msg' => array(
                    'action' => 'delete_role_function',
                    'user_id' => $currentUser->getUserID(),
                    'new' => $RoleFunction
                )
            ));

            erLhcoreClassRoleFunction::deleteRolePolicy($PolicyID);
        }
        erLhcoreClassAdminChatValidatorHelper::clearUsersCache();
    }
}

if (isset($_POST['Remove_group_from_role']) && isset($_POST['AssignedID']) && count($_POST['AssignedID']) > 0)
{
	if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
		erLhcoreClassModule::redirect();
		exit;
	}

    foreach ($_POST['AssignedID'] as $AssignedID)
    {
        $AssignedRole = erLhcoreClassRole::getSession()->load( 'erLhcoreClassModelGroupRole', $AssignedID);

        $group = erLhcoreClassModelGroup::fetch($AssignedRole->group_id);

        erLhcoreClassLog::logObjectChange(array(
            'object' => $group,
            'msg' => array(
                'action' => 'delete_role_from_group',
                'user_id' => $currentUser->getUserID(),
                'group_role' => $AssignedRole,
                'group' => $group,
                'role' => $Role
            )
        ));

        erLhcoreClassGroupRole::deleteGroupRole($AssignedID);
    }
    erLhcoreClassAdminChatValidatorHelper::clearUsersCache();
}

if (isset($_POST['AssignGroups']) && isset($_POST['GroupID']) && count($_POST['GroupID']) > 0)
{
	if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
		erLhcoreClassModule::redirect();
		exit;
	}

    foreach ($_POST['GroupID'] as $GroupID)
    {
        $GroupRole = new erLhcoreClassModelGroupRole();
        $GroupRole->group_id =$GroupID;
        $GroupRole->role_id = $Role->id;;
        erLhcoreClassRole::getSession()->save($GroupRole);

        $group = erLhcoreClassModelGroup::fetch($GroupID);

        erLhcoreClassLog::logObjectChange(array(
            'object' => $group,
            'msg' => array(
                'action' => 'assign_role_to_group',
                'user_id' => $currentUser->getUserID(),
                'group_role' => $GroupRole,
                'group' => $group,
                'role' => $Role
            )
        ));

    }
    erLhcoreClassAdminChatValidatorHelper::clearUsersCache();
}


$Result['content'] = $tpl->fetch();

$Result['path'] = array(array('url' => erLhcoreClassDesign::baseurl('system/configuration'),'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','System configuration')),
array('url'=>erLhcoreClassDesign::baseurl('permission/roles'),'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','List of roles')),
array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('permission/editrole','Role edit').' - '.$Role->name)
);

erLhcoreClassChatEventDispatcher::getInstance()->dispatch('permission.editrole_path', array('result' => & $Result));

?>