/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var wserv = {};

wserv.run = function() {
    ws = new WebSocket(config.wsServer);

    ws.onopen = function(evt) {
        console.log("Connected to WebSocket server.");

        var c = {};
        c.info = {};
        c.command = 'reg';
        c.info.name = name == 'null' ? '' : name;

        ws.send($.toJSON(c) + "\r\n");
        $("#status").text('');
    };

    ws.onclose = function(evt) {
        console.log("Disconnected");

        $("#status").text('Disconnected');
    };

    ws.onmessage = function(evt) {
        console.log('Retrieved data from server: ' + evt.data);
        var data = $.evalJSON(evt.data);

        var ptxtIndex = data.msgid;
        $(".stdout ul li.self:eq("+ptxtIndex+") img:eq(0)").remove();

        switch (data.command) {
            case 'online':
                var content = '<li><p><span class="online">' + data.who.name + ' 上线了 [ ' + data.sendtime + ' ] </span></p>'

                var op = '';
                op += '<option value="' + data.who.fd + '">' + data.who.name + '</option>';
                $(".stdin select").append(op);

                //声音提醒
                player_online.play();
                break;
            case 'offline':
                var content = '<li><p><span class="offline">' + data.who.name + ' 下线了 [ ' + data.sendtime + ' ] </span></p>'

                $.each($(".stdin select option"), function(i, val) {
                    if (data.who.fd == val.value) {
                        $(this).remove();
                    }
                })
                break;
            case 'sayall':
                if (data.info.type == 2) {
                    data.info.msg = '<img src="' + data.info.msg + '" />';
                }else{
                    data.info.msg = Utils.replaceEm(data.info.msg);
                }
                var content = '<li><p><span>' + data.info.who.name + ' 对大家说： [ ' + data.sendtime + ' ] </span>' + data.info.msg + '</p></li>'

                //声音提醒
                player_sendall.play();
                break;
            case 'sayto':
                if (data.info.type == 2) {
                    data.info.msg = '<img src="' + data.info.msg + '" />';
                }else{
                    data.info.msg = Utils.replaceEm(data.info.msg);
                }
                var content = '<li><p><span class="sayto">' + data.info.who.name + ' 对你说： [ ' + data.sendtime + ' ] </span>' + data.info.msg + '</p></li>'

                //声音提醒
                player_sendto.play();
                break;
            case 'reg':
                console.log(data.msg);
                var content = '<li><p><span class="sayto">' + data.msg + '<a href="javascript:void(0);">' + data.sendtime + '</a></span></p></li>'

                //声音提醒
                player_reg.play();
                break;
            case 'clientlist':
                var op = '';
                $.each(data.fdlist, function(i, val) {
                    op += '<option value="' + val.fd + '">' + val.name + '</option>';
                })
                $(".stdin select").append(op);
                break;
            default:
                ;
        }
        
        $(".stdout ul").append(content);
        $('.stdout')[0].scrollTop = 1000000;
    };

    ws.onerror = function(evt, e) {
        console.log('Error occured: ' + evt.data);
    };
}