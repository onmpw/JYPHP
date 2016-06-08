$(function(){
    $(".chat_uploadimage").live('click',function(){
        var img = $(this).attr("src");
        $(".original_img").attr("src",img).show();
        $(".background_img").show();
    });
    $(".original_img").on("click",function(){
        $(this).hide();
        $(".background_img").hide();
    });
    $(".webmanager_add").click(function(){
        var uname = $(".username").val();
        var mobile = $('.mobile').val();
        if(uname == ''){
            $('.info').html("用户名不能为空!").show();
            return false;
        }else if(mobile == ''){
            $('.info').html('手机号不能为空').show();
            return false;
        }
        $.post(
            '/Admin/User/addeuser_do',
            {
                uname:uname,
                mobile:mobile
            },
            function(data){
                alert(data);
            }
        )
    });
})