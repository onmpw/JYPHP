(function () {
    var w = window, d = document;
    w.FUNC = {
        filetype:['jpg','jpeg','png','gif'],
        uploadLogo:function(){
            var fi ='';
            var pic = '';
            if($('.companylogo').get(0).files){
                fi = $('.companylogo').get(0).files[0];
                //判断文件格式是否是图片 如果不是图片则返回false
                var fname = fi.name.split('.');
                if(FUNC.filetype.indexOf(fname[1].toLowerCase()) == -1){
                    alert('文件格式不支持');
                    return ;
                }
                var fr = new FileReader();
                fr.readAsDataURL(fi);
                fr.onload = function(frev){
                    pic = frev.target.result;
                    $.post(
                        '/User/User/uploadlogo',
                        {
                            username:FUNC.username,
                            userid:FUNC.userid,
                            //companyid:FUNC.companyid,
                            message:pic,
                            filename:fname[0],
                            filetype:fname[1],
                            filesize:fi.size
                        },
                        function(data){
                            data = eval('('+data+')');
                            if(data.code == 1 || data.code == 2){
                                console.log('上传失败')
                            }else if(data.code == 0){
                                $('.companylogo_img').attr('src',data.con);
                            }
                        }
                    );
                }
            }else{
                fi = $('.companylogo').val();
            }
        },
        uploadHeadimg:function(obj){
            var fi ='';
            var pic = '';
            if($('.headimage').get(0).files){
                fi = $('.headimage').get(0).files[0];
                //判断文件格式是否是图片 如果不是图片则返回false
                var fname = fi.name.split('.');
                if(FUNC.filetype.indexOf(fname[1].toLowerCase()) == -1){
                    alert('文件格式不支持');
                    return ;
                }
                var fr = new FileReader();
                fr.readAsDataURL(fi);
                fr.onload = function(frev){
                    pic = frev.target.result;
                    $.post(
                        '/User/User/uploadheadimg',
                        {
                            username:FUNC.username,
                            userid:FUNC.userid,
                            //companyid:FUNC.companyid,
                            message:pic,
                            filename:fname[0],
                            filetype:fname[1],
                            filesize:fi.size
                        },
                        function(data){
                            data = eval('('+data+')');
                            if(data.code == 1 || data.code == 2){
                                console.log('上传失败')
                            }else if(data.code == 0){
                                $('.userhead_img').attr('src',data.con);
                            }
                        }
                    );
                }
            }else{
                fi = $('.userhead_img').val();
            }
        },
        uploadThumimg:function(obj){
            var fi ='';
            var pic = '';
            if($('.file_thumimg').get(0).files){
                fi = $('.file_thumimg').get(0).files[0];
                //判断文件格式是否是图片 如果不是图片则返回false
                var fname = fi.name.split('.');
                if(FUNC.filetype.indexOf(fname[1].toLowerCase()) == -1){
                    alert('文件格式不支持');
                    return ;
                }
                var fr = new FileReader();
                fr.readAsDataURL(fi);
                fr.onload = function(frev){
                    pic = frev.target.result;
                    $.post(
                        '/User/User/uploadthumimg',
                        {
                            message:pic,
                            filename:fname[0],
                            filetype:fname[1],
                            filesize:fi.size
                        },
                        function(data){
                            data = eval('('+data+')');
                            console.log(data);
                            if(data.code == 1 || data.code == 2){
                                console.log('上传失败')
                            }else if(data.code == 0){
                                $('.thumimg_name').val(data.con);
                            }
                        }
                    );
                }
            }else{
                fi = $('.thumimg_name').val();
            }
        },
        uploadimages:function(){
            var fi ='';
            var pic = '';
            if($('.upfiletest').get(0).files){
                fi = $('.upfiletest').get(0).files[0];
                //判断文件格式是否是图片 如果不是图片则返回false
                var fname = fi.name.split('.');
                if(FUNC.filetype.indexOf(fname[1].toLowerCase()) == -1){
                    alert('文件格式不支持');
                    return ;
                }
                var fr = new FileReader();
                fr.readAsDataURL(fi);
                fr.onload = function(frev){
                    pic = frev.target.result;
                    var num = Math.floor(pic.length/100);
                    var leng = 0;
                    var per= 0;
                    var icount = setInterval(function(){
                        per = Math.floor(leng/pic.length*100);
                        $("#countpersent").html(per+"%");
                        leng+=num;
                        if(leng >= pic.length){
                            clearInterval(icount);
                        }
                    },40);
                    $.post(
                        '/User/User/uploadimages',
                        {
                            message: pic,
                            filename: fname[0],
                            filetype: fname[1],
                            filesize: fi.size,
                            currlenth:num,
                        },
                        function (data) {
                            $("#countpersent").html(data);
                            clearInterval(icount);
                        }
                    );
                }
            }else{
                fi = $('.userhead_img').val();
            }
        },
        tosign:function(nid){
            $.post(
                '/Onlinebid/Bidinfo/signup',
                {
                    nid:nid,
                },
                function(data){
                    data = eval('('+data+')');
                    if(data.code==0 || (data.code==3&&data.type=='mbs')){
                        $('.iwillsign').removeAttr('onclick').html('已经报名');
                    }
                }
            )
        }
    };
})();