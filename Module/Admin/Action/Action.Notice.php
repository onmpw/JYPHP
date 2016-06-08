<?php
namespace Admin\Action;
use Common\Action\CommonAction;
use Onlinebid\Model\BidnoticeModel;
class NoticeAction extends CommonAction{
    public function noticelist(){
        $currpage = \Common::get('p') === false ? 1 : \Common::get('p');
        $where = '';
        $this->assign('search','');
        if(\Common::get('search') !== false){
            $this->assign('search','/search/'.\Common::get('search'));
            $where = "where bidnoticename like '%".urldecode(\Common::get('search'))."%'";
        }
        $sql = "select count(*) from bidnotice ".$where;
        $mod = new BidnoticeModel();
        $res = $mod->select_sql($sql)[0];
        $totalnum = $res['count(*)'];
        $everypnum = 20;
        $totalpage = ceil($totalnum/$everypnum);
        if($currpage <= 0) $currpage = 1;
        elseif($currpage > $totalpage) $currpage = $totalpage;
        $this->assign('totalpage',$totalpage);
        $this->assign('currpage',$currpage);
        $sql = "select n.id,bidnoticename bnname,bidname,iseffective isef,addtime from bidnotice n left join bids b ";
        $sql .=" on n.bidid=b.id ".$where." order by n.id desc limit ".($currpage-1)*$everypnum.",".$everypnum;
        $res = $mod->select_sql($sql);
        $this->assign("notice",$res);
        $this->display();
    }
}