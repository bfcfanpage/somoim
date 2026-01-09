<?php
class somoimAdminView extends somoim
{
    /**
     * 초기화
     */
    function init()
    {
        $this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile('index');
    }
    
    /**
     * 소모임 모듈 목록
     */
    function dispSomoimAdminList()
    {
        $oSomoimAdminModel = getAdminModel('somoim');
        $module_list = $oSomoimAdminModel->getSomoimModuleList();
        
        Context::set('module_list', $module_list);
        
        $this->setTemplateFile('somoim_list');
    }
    
    /**
     * 소모임 모듈 생성 화면
     */
    function dispSomoimAdminInsert()
    {
        // 스킨 목록
        $oModuleModel = getModel('module');
        $skin_list = $oModuleModel->getSkins($this->module_path);
        Context::set('skin_list', $skin_list);
        
        $this->setTemplateFile('somoim_insert');
    }
}