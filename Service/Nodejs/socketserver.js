var http = require('http').createServer(handle);
var io = require('socket.io')(http);
var url = require('url');
var crypto = require('crypto');
var files = require('fs');
var ms = require('mysql');
http.listen(9999,"192.168.5.102");
function handle(req,response){


}
var onlineUsers = {};
//当前在线人数
var onlineCount = 0;
var mscon = '';
var dbinfo = {host:"192.168.5.102",user:'admin',password:'q1w2e3r4',database:'moma_mobileapp'};
io.on('connection',function(socket){
   //console.log("a user connected");
    /*socket.on('login',function(obj){
        io.emit('login',{username:obj.username});
    });*/
    /*socket.on('join',function(obj){
        console.log(obj);
    }),*/
    socket.on('join',function(obj){
        //console.log(obj);
    }),
    socket.on('message',function(obj){
        if(mscon == ''){
            mscon = ms.createConnection(dbinfo);
            mscon.connect();
            var sql = "set names utf8";
            mscon.query(sql);
        }
        var fdate = new Date(Date.now());
        var t = fdate.getFullYear()+"-"+parseInt(fdate.getMonth()+1)+"-"+fdate.getDate()+" "+fdate.getHours()+":"+fdate.getMinutes()+":"+fdate.getSeconds();
        var time = (new Date(t)).getTime()/1000;
        var query = mscon.query("INSERT INTO message SET ?",

            {
                message:obj.message,
                topicid:obj.topicid,
                userid:obj.userid,
                addtime:time,
                companyid:obj.companyid,
                isimage:0
            },
            function(errs,result){
                if(errs) throw errs;
                io.emit('message',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:obj.message,headimage:obj.headimage});
                io.emit('topicmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:obj.message,headimage:obj.headimage,time:t});
                io.emit('contractmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:obj.message,headimage:obj.headimage,time:t,contractid:obj.contractid});
                mscon.query("UPDATE usertopictime SET lasttime=? WHERE userid=? and topicid=?",
                    [time,obj.userid,obj.topicid]
                );
            }
        );
    });
    socket.on('upfile',function(obj){

        var dir = "/www/mobileapp";
        var fname = obj.filename;
        fname = fname.split(".");  //将文件名和文件格式解析出来
        /**
         * 为文件名加密 产生随机的加密的文件名 以保证上传的文件名不相同
         */
        var sha1 = crypto.createHash('sha1');  //以更新的sha1算法创建hash
        var str = crypto.randomBytes(20).toString('hex');  //产生随机字符串
        var updir = "/Data/Upload/Images";
        sha1.update(fname[0]);
        sha1.update(str);
        fname[0]=sha1.digest('hex');  //将经过处理的文件名重新赋值到fname[0]
        fname = fname[0]+"."+fname[1];  //重新合成文件名
        var file = dir+updir+"/"+fname;
        files.open(file,'w',function(err,fd){
            if(!err){
                var option ={
                    flags: 'w',
                    encoding: null,
                    fd: fd,
                    mode: 0o666
                };
                //var bf = new Buffer(obj.message);
               // var con = bf.slice(0, 300).toString();
               // console.log(obj.message);
                var ws = files.createWriteStream(file,option);
                ws.write(obj.message);
                //将信息写入数据库
                if(mscon == ''){
                    mscon = ms.createConnection(dbinfo);
                    mscon.connect();
                    var sql = "set names utf8";
                    mscon.query(sql);
                }
                //var time = (Date.now()).toString();
                var fdate = new Date(Date.now());
                var t = fdate.getFullYear()+"-"+parseInt(fdate.getMonth()+1)+"-"+fdate.getDate()+" "+fdate.getHours()+":"+fdate.getMinutes()+":"+fdate.getSeconds();
                var time = (new Date(t)).getTime()/1000;
                mscon.query("INSERT INTO message SET ?",
                    {
                        message:updir+"/"+fname,
                        topicid:obj.topicid,
                        userid:obj.userid,
                        addtime:time,
                        companyid:obj.companyid,
                        isimage:1
                    },
                    function(errs,result){
                        if(errs) throw errs;
                        //io.emit('message',{username:obj.username,userid:obj.userid,message:obj.message,headimage:obj.headimage});
                        io.emit('upfile',{username:obj.username,topicid:obj.topicid,userid:obj.userid,headimage:obj.headimage,message:updir+"/"+fname});
                        io.emit('topicmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:'',headimage:obj.headimage,time:t});
                        io.emit('contractmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:'',headimage:obj.headimage,time:t,contractid:obj.contractid});
                        //将当前说话的用户的id和话题id的时间更新到usertopictime表中
                        mscon.query("UPDATE usertopictime SET lasttime=? WHERE userid=? and topicid=?",
                            [time,obj.userid,obj.topicid]
                        );
                    }
                );
            }
        });
    });
    socket.on('iosupfile',function(obj){

          if(mscon == ''){
              mscon = ms.createConnection(dbinfo);
              mscon.connect();
              var sql = "set names utf8";
              mscon.query(sql);
          }
        //var time = (Date.now()).toString();
        var fdate = new Date(Date.now());
        var t = fdate.getFullYear()+"-"+parseInt(fdate.getMonth()+1)+"-"+fdate.getDate()+" "+fdate.getHours()+":"+fdate.getMinutes()+":"+fdate.getSeconds();
        var time = (new Date(t)).getTime()/1000;
        mscon.query("INSERT INTO message SET ?",
            {
                message:obj.message,
                topicid:obj.topicid,
                userid:obj.userid,
                addtime:time,
                companyid:obj.companyid,
                isimage:1
            },
            function(errs,result){
                if(errs) throw errs;
                //io.emit('message',{username:obj.username,userid:obj.userid,message:obj.message,headimage:obj.headimage});
                io.emit('upfile',{username:obj.username,topicid:obj.topicid,userid:obj.userid,headimage:obj.headimage,message:obj.message});
                io.emit('topicmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:'',headimage:obj.headimage,time:t});
                io.emit('contractmes',{username:obj.username,topicid:obj.topicid,userid:obj.userid,message:'',headimage:obj.headimage,time:t,contractid:obj.contractid});
                //将当前说话的用户的id和话题id的时间更新到usertopictime表中
                mscon.query("UPDATE usertopictime SET lasttime=? WHERE userid=? and topicid=?",
                    [time,obj.userid,obj.topicid]
                );
            }
        );
    });


});