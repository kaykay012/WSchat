<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="static/style.css">
        <!--<link rel="stylesheet" type="text/css" href="static/css/bootstrap.min.css" media="screen">-->
        <script src="static/js/jq/jquery.js"></script>
        <script src="static/js/jq/jquery.form.js"></script>
        <script src="static/js/jq/jquery.json.js"></script>
        <script src="static/js/jq/jquery.qqFace.js"></script>
        <script src="static/js/jq/jquery-browser.js"></script>
        <script src="static/layer/layer.js"></script>

        <script src="static/js/config.js"></script>
        <script src="static/js/utils.js"></script>        
        <script src="static/js/websocket.js"></script> 
        <script type="text/javascript">
            var name = '';
            var rqId = 0;            

            $(function() {                
                name = prompt("行不更名 坐不改姓, 我就是:", Utils.getHero());                

                Utils.showChatList(100000);
                $("#MORE").click(function() {
                    Utils.showChatList(280);
                })

                $(".stdin textarea").focus();
                $("#SEND").click(function() {
                    var c = $(".stdin textarea").val();
                    Utils.sendMsg(c);
                });

                document.onkeydown = function(e) {
                    var ev = document.all ? window.event : e;
                    if (ev.keyCode == 13) {
                        var c = $(".stdin textarea").val();
                        Utils.sendMsg(c);
                        return false;
                    } else {
                        return true;
                    }
                };

                // 实例WebSocket
                wserv.run();

                $('.face').qqFace({
                    id: 'facebox',
                    assign: 'content',
                    path: 'static/arclist/'	//表情存放的路径
                });
            })
        </script>        
    </head>
    <body>
        <h3 align="center">崔小凯的聊天室</h3>
        <div class="stdout">
            <a id="MORE"> 点击查看更多 </a>
            <ul></ul>
        </div>
        <br/>

        <div class="stdin">
            <div class="picAndface">
                <div class="face">表情</div>
                <div class="pic">
                    <form id="theForm" name="theForm" action="/index.php/api/uploadpic"  method="post" enctype="multipart/form-data">
                        <input name="uploadpic" type="file" style="display: none" onchange="$('form').ajaxSubmit(Utils.option)" />
                        <input name="sendpic" type="button" value=" 发送图片 " onclick="document.theForm.uploadpic.click()" />                    
                    </form>
                </div>                
            </div>
            <textarea id="content" name="content" placeholder="向小伙伴们打声招呼吧"></textarea><br />
            发送给：<select name="fdlist">
                <option value="-1"> 所有人 </option>
            </select>
            <button id="SEND"> 发送 </button>
            <br/>
            <div id="status" style="color:red;"></div>
        </div>

        <audio id="player_reg" src="static/music/reg.mp3">
            Your browser does not support the audio tag.
        </audio>
        <audio id="player_online" src="static/music/online.mp3">
            Your browser does not support the audio tag.
        </audio>
        <audio id="player_sendto" src="static/music/sendto.mp3">
            Your browser does not support the audio tag.
        </audio>
        <audio id="player_sendall" src="static/music/sendall.mp3">
            Your browser does not support the audio tag.
        </audio>        
    </body>
</html>