<?php
namespace Inter\Webservice;
interface I_WS_Bids{
    /**
     * ����б���
     * @param string $bids
     */
    public function bids($bids = '');
    
    /**
     * ����б깫��
     * @param string $notice
     */
    public function bidnotice($notice = '');
    
    /**
     * �����б깫��
     * @param string $notice
     */
    public function bidnotice_update($notice = '');
    
    /**
     * ��ͬ�ӿ�
     * @param string $contract
     */
    public function contracts($contract = '');
    
}