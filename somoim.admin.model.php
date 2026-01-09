<?php
class somoimAdminModel extends somoim
{
    /**
     * 초기화
     */
    function init()
    {
    }
    
    /**
     * 소모임 모듈 인스턴스 목록 가져오기
     */
    function getSomoimModuleList()
    {
        $oModuleModel = getModel('module');
        $args = new stdClass();
        $args->module = 'somoim';
        $output = executeQueryArray('module.getModuleList', $args);
        
        if(!$output->toBool()) return $output;
        
        return $output->data;
    }
}