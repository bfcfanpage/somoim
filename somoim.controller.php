<?php
class somoimController extends somoim
{
    /**
     * 초기화
     */
    function init()
    {
    }
    
    /**
     * 소모임 생성
     */
    function procSomoimCreate()
    {
        // 로그인 체크
        if(!Context::get('is_logged'))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        $logged_info = Context::get('logged_info');
        
        // 입력값 받기
        $title = Context::get('title');
        $description = Context::get('description');
        $require_approval = Context::get('require_approval') ?: 'Y';
        
        // 유효성 검사
        if(!$title)
        {
            return new BaseObject(-1, '소모임 이름을 입력해주세요.');
        }
        
        // 엠블럼 파일 업로드 처리
        $emblem = '';
        if($_FILES['emblem']['tmp_name'])
        {
            $emblem = $this->uploadEmblem($_FILES['emblem']);
        }
        
        // 소모임 생성
        $args = new stdClass();
        $args->somoim_srl = getNextSequence();
        $args->title = $title;
        $args->description = $description;
        $args->emblem = $emblem;
        $args->leader_srl = $logged_info->member_srl;
        $args->require_approval = $require_approval;
        
        $output = executeQuery('somoim.insertSomoim', $args);
        if(!$output->toBool())
        {
            return $output;
        }
        
        // 소모임장을 회원으로 자동 추가
        $this->addSomoimMember($args->somoim_srl, $logged_info->member_srl, 'leader');
        
        // 게시판 자동 생성
        $this->createSomoimBoard($args->somoim_srl, $title);
        
        $this->setMessage('소모임이 생성되었습니다.');
        $this->setRedirectUrl(getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', 'dispSomoimView', 'somoim_srl', $args->somoim_srl));
    }
    
    /**
     * 엠블럼 업로드
     */
    function uploadEmblem($file)
    {
        // 파일 확장자 체크
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if(!in_array($ext, $allowed_extensions))
        {
            return '';
        }
        
        // 업로드 디렉토리 생성
        $path = './files/somoim/emblems/';
        if(!is_dir($path))
        {
            mkdir($path, 0755, true);
        }
        
        // 파일명 생성
        $filename = md5(uniqid()) . '.' . $ext;
        $filepath = $path . $filename;
        
        // 파일 이동
        if(move_uploaded_file($file['tmp_name'], $filepath))
        {
            return $filepath;
        }
        
        return '';
    }
    
    /**
     * 소모임 회원 추가
     */
    function addSomoimMember($somoim_srl, $user_srl, $role = 'member')
    {
        $args = new stdClass();
        $args->member_srl = getNextSequence();
        $args->somoim_srl = $somoim_srl;
        $args->user_srl = $user_srl;
        $args->role = $role;
        
        return executeQuery('somoim.insertSomoimMember', $args);
    }
    
    /**
     * 소모임 게시판 생성
     */
    function createSomoimBoard($somoim_srl, $title)
    {
        $oModuleController = getController('module');
        
        $args = new stdClass();
        $args->module = 'board';
        $args->module_srl = getNextSequence();
        $args->mid = 'somoim_' . $somoim_srl;
        $args->browser_title = $title . ' 게시판';
        $args->site_srl = 0;
        
        $output = $oModuleController->insertModule($args);
        
        if($output->toBool())
        {
            // 소모임 정보 업데이트 (board_srl 저장)
            $update_args = new stdClass();
            $update_args->somoim_srl = $somoim_srl;
            $update_args->board_srl = $args->module_srl;
            executeQuery('somoim.updateSomoim', $update_args);
        }
        
        return $output;
    }
    
    /**
     * 가입 신청
     */
    function procSomoimJoinRequest()
    {
        if(!Context::get('is_logged'))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        $logged_info = Context::get('logged_info');
        $somoim_srl = Context::get('somoim_srl');
        $message = Context::get('message');
        
        // 이미 회원인지 체크
        $oSomoimModel = getModel('somoim');
        if($oSomoimModel->isSomoimMember($somoim_srl, $logged_info->member_srl))
        {
            return new BaseObject(-1, '이미 가입된 소모임입니다.');
        }
        
        // 가입 신청 추가
        $args = new stdClass();
        $args->request_srl = getNextSequence();
        $args->somoim_srl = $somoim_srl;
        $args->user_srl = $logged_info->member_srl;
        $args->message = $message;
        
        $output = executeQuery('somoim.insertJoinRequest', $args);
        
        if($output->toBool())
        {
            $this->setMessage('가입 신청이 완료되었습니다.');
        }
        
        return $output;
    }
    
    /**
     * 가입 승인
     */
    function procSomoimApproveJoin()
    {
        if(!Context::get('is_logged'))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        $logged_info = Context::get('logged_info');
        $request_srl = Context::get('request_srl');
        
        // 가입 신청 정보 가져오기
        $args = new stdClass();
        $args->request_srl = $request_srl;
        $output = executeQuery('somoim.getJoinRequest', $args);
        
        if(!$output->toBool() || !$output->data)
        {
            return new BaseObject(-1, '가입 신청을 찾을 수 없습니다.');
        }
        
        $request = $output->data;
        
        // 권한 체크 (소모임장인지)
        $oSomoimModel = getModel('somoim');
        if(!$oSomoimModel->isSomoimLeader($request->somoim_srl, $logged_info->member_srl))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        // 회원 추가
        $this->addSomoimMember($request->somoim_srl, $request->user_srl);
        
        // 신청 상태 업데이트
        $update_args = new stdClass();
        $update_args->request_srl = $request_srl;
        $update_args->status = 'approved';
        $update_args->processed_by = $logged_info->member_srl;
        executeQuery('somoim.updateJoinRequest', $update_args);
        
        // 회원 수 증가
        executeQuery('somoim.increaseMemberCount', (object)['somoim_srl' => $request->somoim_srl]);
        
        $this->setMessage('가입을 승인했습니다.');
        
        return new BaseObject();
    }
    
    /**
     * 가입 거부
     */
    function procSomoimRejectJoin()
    {
        if(!Context::get('is_logged'))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        $logged_info = Context::get('logged_info');
        $request_srl = Context::get('request_srl');
        
        // 가입 신청 정보 가져오기
        $args = new stdClass();
        $args->request_srl = $request_srl;
        $output = executeQuery('somoim.getJoinRequest', $args);
        
        if(!$output->toBool() || !$output->data)
        {
            return new BaseObject(-1, '가입 신청을 찾을 수 없습니다.');
        }
        
        $request = $output->data;
        
        // 권한 체크
        $oSomoimModel = getModel('somoim');
        if(!$oSomoimModel->isSomoimLeader($request->somoim_srl, $logged_info->member_srl))
        {
            return new BaseObject(-1, 'msg_not_permitted');
        }
        
        // 신청 상태 업데이트
        $update_args = new stdClass();
        $update_args->request_srl = $request_srl;
        $update_args->status = 'rejected';
        $update_args->processed_by = $logged_info->member_srl;
        executeQuery('somoim.updateJoinRequest', $update_args);
        
        $this->setMessage('가입을 거부했습니다.');
        
        return new BaseObject();
    }
}