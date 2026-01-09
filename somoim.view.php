<?php
class somoimView extends somoim
{
    /**
     * 초기화
     */
    function init()
    {
        // 템플릿 경로 설정
        $template_path = sprintf('%sskins/%s/', $this->module_path, 'default');
        if(!is_dir($template_path) || !$this->module_info->skin)
        {
            $this->module_info->skin = 'default';
            $template_path = sprintf('%sskins/%s/', $this->module_path, 'default');
        }
        $this->setTemplatePath($template_path);
    }
    
    /**
     * 소모임 목록 화면
     */
    function dispSomoimList()
    {
        // 소모임 목록 가져오기
        $oSomoimModel = getModel('somoim');
        $output = $oSomoimModel->getSomoimList();
        
        Context::set('somoim_list', $output->data);
        Context::set('total_count', $output->total_count);
        Context::set('page_navigation', $output->page_navigation);
        
        // 템플릿 설정
        $this->setTemplateFile('list');
    }
    
    /**
     * 소모임 상세 화면
     */
    function dispSomoimView()
    {
        $somoim_srl = Context::get('somoim_srl');
        
        if(!$somoim_srl)
        {
            return $this->dispSomoimList();
        }
        
        $oSomoimModel = getModel('somoim');
        
        // 소모임 정보
        $somoim = $oSomoimModel->getSomoim($somoim_srl);
        if(!$somoim)
        {
            return new BaseObject(-1, '존재하지 않는 소모임입니다.');
        }
        
        // 회원 목록
        $members_output = $oSomoimModel->getSomoimMembers($somoim_srl);
        
        // 현재 사용자가 회원인지 체크
        $logged_info = Context::get('logged_info');
        $is_member = false;
        $is_leader = false;
        
        if($logged_info)
        {
            $is_member = $oSomoimModel->isSomoimMember($somoim_srl, $logged_info->member_srl);
            $is_leader = $oSomoimModel->isSomoimLeader($somoim_srl, $logged_info->member_srl);
        }
        
        Context::set('somoim', $somoim);
        Context::set('members', $members_output->data);
        Context::set('is_member', $is_member);
        Context::set('is_leader', $is_leader);
        
        // 템플릿 설정
        $this->setTemplateFile('view');
    }
    
    /**
     * 소모임 생성 폼
     */
    function dispSomoimCreate()
    {
        // 로그인 체크
        if(!Context::get('is_logged'))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        // 템플릿 설정
        $this->setTemplateFile('create');
    }
    
    /**
     * 가입 신청 관리 화면
     */
    function dispSomoimManageRequests()
    {
        $somoim_srl = Context::get('somoim_srl');
        $logged_info = Context::get('logged_info');
        
        if(!$logged_info)
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        $oSomoimModel = getModel('somoim');
        
        // 권한 체크
        if(!$oSomoimModel->isSomoimLeader($somoim_srl, $logged_info->member_srl))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        // 대기 중인 가입 신청 목록
        $requests_output = $oSomoimModel->getJoinRequests($somoim_srl, 'pending');
        
        // 소모임 정보
        $somoim = $oSomoimModel->getSomoim($somoim_srl);
        
        Context::set('somoim', $somoim);
        Context::set('requests', $requests_output->data);
        
        // 템플릿 설정
        $this->setTemplateFile('manage_requests');
    }
}