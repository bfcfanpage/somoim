<?php
class somoimModel extends somoim
{
    /**
     * 소모임 목록 가져오기
     */
    function getSomoimList($args = null)
    {
        $output = executeQueryArray('somoim.getSomoimList', $args);
        return $output;
    }
    
    /**
     * 소모임 정보 가져오기
     */
    function getSomoim($somoim_srl)
    {
        $args = new stdClass();
        $args->somoim_srl = $somoim_srl;
        $output = executeQuery('somoim.getSomoim', $args);
        
        if($output->toBool() && $output->data)
        {
            return $output->data;
        }
        return null;
    }
    
    /**
     * 소모임 회원 목록 가져오기
     */
    function getSomoimMembers($somoim_srl)
    {
        $args = new stdClass();
        $args->somoim_srl = $somoim_srl;
        $output = executeQueryArray('somoim.getSomoimMembers', $args);
        return $output;
    }
    
    /**
     * 사용자가 소모임 회원인지 확인
     */
    function isSomoimMember($somoim_srl, $user_srl)
    {
        $args = new stdClass();
        $args->somoim_srl = $somoim_srl;
        $args->user_srl = $user_srl;
        $output = executeQuery('somoim.getSomoimMember', $args);
        
        return $output->toBool() && $output->data;
    }
    
    /**
     * 가입 신청 목록 가져오기
     */
    function getJoinRequests($somoim_srl, $status = 'pending')
    {
        $args = new stdClass();
        $args->somoim_srl = $somoim_srl;
        $args->status = $status;
        $output = executeQueryArray('somoim.getJoinRequests', $args);
        return $output;
    }
    
    /**
     * 소모임장인지 확인
     */
    function isSomoimLeader($somoim_srl, $user_srl)
    {
        $somoim = $this->getSomoim($somoim_srl);
        if(!$somoim) return false;
        
        return $somoim->leader_srl == $user_srl;
    }
}