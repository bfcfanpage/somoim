<?php
class somoimAdminController extends somoim
{
    /**
     * 초기화
     */
    function init()
    {
    }
    
    /**
     * 소모임 모듈 생성
     */
    function procSomoimAdminInsertModule()
    {
        $oModuleController = getController('module');
        $oModuleModel = getModel('module');
        
        // 기본 설정값
        $args = new stdClass();
        $args->module = 'somoim';
        $args->mid = Context::get('mid');
        $args->browser_title = Context::get('browser_title');
        $args->skin = Context::get('skin') ?: 'default';
        $args->site_srl = Context::get('site_srl') ?: 0;
        
        // mid 중복 체크
        if(!$args->mid)
        {
            return new BaseObject(-1, 'msg_module_name_exists');
        }
        
        $module_info = $oModuleModel->getModuleInfoByMid($args->mid);
        if($module_info->mid)
        {
            return new BaseObject(-1, 'msg_module_name_exists');
        }
        
        // 모듈 생성
        $output = $oModuleController->insertModule($args);
        if(!$output->toBool())
        {
            return $output;
        }
        
        $this->setMessage('success_registed');
        
        if(!in_array(Context::getRequestMethod(), array('XMLRPC', 'JSON')))
        {
            $returnUrl = Context::get('success_return_url') ?: getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomoimAdminList');
            $this->setRedirectUrl($returnUrl);
        }
        
        return $output;
    }
}