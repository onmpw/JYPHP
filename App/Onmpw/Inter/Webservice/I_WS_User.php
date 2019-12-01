<?php
namespace Inter\Webservice;
interface I_WS_User{
    /**
     * 开通手机账户函数
     * @param string $info
     */
    public function open_account($info = '');
}