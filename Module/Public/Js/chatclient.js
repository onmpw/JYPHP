(function () {
    var w = window, d = document,sockets='';
    w.CHAT = {
        msgObj:document.getElementById('container_mb_100'),
        username:null,
        userid: null,
        message:null,
        socket: null,
        topicid:null,
        companyid:null,
        contractid:null, // 添加了合同id
        filetype:['jpg','jpeg','png','gif'],
        init: function(){
           this.socket = io.connect('ws://118.144.133.92:9999');
            this.socket.on('join',function(obj){
                /*var con =  $('#chat_show .show_message').html()+"<br />"+obj.username+'---joined';
                $('#chat_show .show_message').html(con);*/
            });
            this.socket.on('message',function(obj){
                if(obj.topicid==CHAT.topicid) {
                    var info = '<div class="IM">';
                    if (CHAT.userid == obj.userid) {
                        info += "<div class='r_box'>";
                    } else {
                        info += "<div class='l_box'>";
                    }
                    info += "<div class='tx'>";
                    if (obj.headimage == "") {
                        info += "<img src='/Module/Public/Images/group_tx.png' width='100%' height='100%' />";
                    } else {
                        info += "<img src='" + obj.headimage + "' width='100%' height='100%' />";
                    }
                    info += "</div>";
                    info += "<div class='text'>";
                    info += "<h4>" + obj.username + "</h4>";
                    info += "<p><span class='icon_jt'></span>" + obj.message + "</p>";
                    info += "</div></div></div>";
                    $(".mb_110").append(info);
                    w.scrollTo(0, document.getElementById('container_mb_100').clientHeight);
                }
            });
            this.socket.on('topicmes',function(obj){
                $(".topicid").each(function(){
                    if(obj.topicid == parseInt($(this).val())) {
                        var qx = $(".topicid").index(this);
                        $(".lastmes").eq(qx).children("p").html(obj.username + ": " + (obj.message==''?'图片':obj.message) + " &nbsp;&nbsp;" + obj.time);
                        if ($(".shownc").eq(qx).children("em").length <= 0) {
                            $(".shownc").eq(qx).append('<em>1</em>');
                        } else {
                            var num = parseInt($(".shownc").eq(qx).children("em").html()) + 1;
                            $(".shownc").eq(qx).children("em").html(num);
                        }
                    }
                })
            }),
            this.socket.on('contractmes',function(obj){
                $(".contractid").each(function(){
                    if(obj.contractid == parseInt($(this).val())) {
                        var qx = $(".contractid").index(this);
                        //$(".contlastmes p").html(obj.username + ": " + (obj.message==''?'图片':obj.message) + " &nbsp;&nbsp;" + obj.time);
                        $(".contlastmes").eq(qx).children("p").html(obj.username + ": " + (obj.message==''?'图片':obj.message) + " &nbsp;&nbsp;" + obj.time);
                        if ($(".contshownc").eq(qx).children("em").length <= 0) {
                            $(".contshownc").eq(qx).append('<em>1</em>');
                        } else {
                            var num = parseInt($(".contshownc").eq(qx).children("em").html()) + 1;
                            $(".contshownc").eq(qx).children("em").html(num);
                        }
                    }
                })
            }),
            this.socket.on('upfile',function(obj){
                if(obj.topicid==CHAT.topicid) {
                    var info = '<div class="IM">';
                    if (CHAT.userid == obj.userid) {
                        info += "<div class='r_box'>";
                    } else {
                        info += "<div class='l_box'>";
                    }
                    info += "<div class='tx'>";
                    if (obj.headimage == "") {
                        info += "<img src='/Module/Public/Images/group_tx.png' width='100%' height='100%' />";
                    } else {
                        info += "<img src='" + obj.headimage + "' width='100%' height='100%' />";
                    }
                    info += "</div>";
                    info += "<div class='text'>";
                    info += "<h4>" + obj.username + "</h4>";
                    info += "<p><img src='" + obj.message + "' class='chat_uploadimage' /></p>";
                    info += "</div></div></div>";
                    $(".mb_110").append(info);
                    w.scrollTo(0, document.getElementById('container_mb_100').clientHeight);
                }
            });
            this.joinuser();
            //sockets = this.socket;
            //w.scrollTo(0,document.getElementById('container_mb_100').clientHeight);
        },

        scrollToBottom:function(){
            w.scrollTo(0, CHAT.msgObj.clientHeight);
        },
        joinuser: function(){
            //this.socket.emit('join',{username:this.username});
            this.socket.emit('join',{username:this.username});
        },
        joinchat: function(){
            this.username = $('#join_chat .username').val();
            this.userid = new Date().getTime()+""+Math.floor(Math.random()*899+100);
            $('#join_chat').hide();
            $('#chat_show').show();
            this.init();
        },
        sendmessage:function(){
            var mes = $("#textfield").val();
            if(mes == ''){
                $("#textfield").attr('placeholder',"发送内容不能为空!");
            }else {
                this.socket.emit('message', {
                    username: this.username,
                    userid: this.userid,
                    message: mes,
                    topicid: this.topicid,
                    companyid: this.companyid,
                    contractid:this.contractid,
                    headimage: this.headimage
                });
                var mes = $("#textfield").val('');
                $.post(
                    '/User/User/push',
                    {
                        cid: this.contractid,
                        userid:this.userid,
                        tid:this.topicid
                    },
                    function(data){
                        console.log(data);
                    }

                );
            }
        },
        uploadFile:function(obj){

            var fi ='';
            var pic = '';
            if($('.upimage').get(0).files){

                fi = $('.upimage').get(0).files[0];
                //判断文件格式是否是图片 如果不是图片则返回false
                var fname = fi.name.split('.');
                if(CHAT.filetype.indexOf(fname[1].toLowerCase()) == -1){
                    alert('文件格式不支持');
                    return ;
                }
                var u = navigator.userAgent;
                if(typeof(window.webkitURL) != 'undefined') {
                    lrz(obj.files[0]).then(function (result) {
                        var source = result.base64;
                        var form = document.getElementById('form');
                        form.reset();
                        $.post(
                            '/User/User/iosUploadimg',
                            {
                                message: source,
                                filename: fname[0],
                                filetype: fname[1],
                                filesize: fi.size
                            },
                            function (data) {
                                if (data != 0) {
                                    CHAT.socket.emit('iosupfile', {
                                        username: CHAT.username,
                                        userid: CHAT.userid,
                                        companyid: CHAT.companyid,
                                        topicid: CHAT.topicid,
                                        headimage: CHAT.headimage,
                                        contractid: CHAT.contractid,
                                        message: data,
                                    });
                                }
                            }
                        );
                    })
                }else if(typeof(window.FileReader) != 'undefined'){
                    var fr = new FileReader();
                    fr.readAsArrayBuffer(fi);
                    fr.onload = function (frev) {
                        pic = frev.target.result;
                        var form = document.getElementById('form');
                        form.reset();
                        CHAT.socket.emit('upfile', {
                            username: CHAT.username,
                            userid: CHAT.userid,
                            companyid: CHAT.companyid,
                            topicid: CHAT.topicid,
                            headimage: CHAT.headimage,
                            contractid: CHAT.contractid,
                            message: pic,
                            filename: fi.name,
                            filetype: fi.type,
                            filesize: fi.size
                        });
                    }
                }else{
                    alert('safari');
                    $.ajaxFileUpload({
                            url: '/User/User/chatUpload',
                            secureuri: true,
                            fileElementId: 'upimage',
                            dataType: 'json',
                            success: function (data, status) {
                                if (typeof(data.error) != 'undefined'){
                                    if(data.error == 0){
                                        var form = document.getElementById('form');
                                        form.reset();
                                        CHAT.socket.emit('iosupfile', {
                                            username: CHAT.username,
                                            userid: CHAT.userid,
                                            companyid: CHAT.companyid,
                                            topicid: CHAT.topicid,
                                            headimage: CHAT.headimage,
                                            contractid:CHAT.contractid,
                                            message: data.content,
                                        });
                                    }
                                }
                            },
                            error: function (data, status, e) {
                                console.log(status);
                            }
                        }
                    );
                }
                //var u = navigator.userAgent;
            }else{
                fi = $('.upimage').val();
            }
        },

    };
})();