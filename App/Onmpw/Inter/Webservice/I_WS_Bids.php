<?php
namespace Inter\Webservice;
interface I_WS_Bids{
    /**
     * 添加招标项
     * @param string $bids
     */
    public function bids($bids = '');
    
    /**
     * 添加招标公告
     * @param string $notice
     */
    public function bidnotice($notice = '');
    
    /**
     * 更新招标公告
     * @param string $notice
     */
    public function bidnotice_update($notice = '');
    
    /**
     * 合同接口
     * @param string $contract
     */
    public function contracts($contract = '');
    
}