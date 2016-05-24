var Utils = {};

Utils.option = {
    type: 'post',
    dataType: 'json',
    success: function(data) {
        layer.closeAll();        

        if (data.errno > 0)
        {
            alert(data.errmsg);
        }
        else
        {
            Utils.sendMsg(data.imgurl, 2);
        }
    },
    beforeSubmit: function() {
        layer.open({type: 2});
    },
    timeout: 7000
};

Utils.sendMsg = function(content, type) {
    if (typeof (type) == 'undefined') {
        type = 1;
    }
    var c = {};
    c.info = {};
    content = this.trim(content);    

    if (content.length <= 0) {
        $(".stdin textarea").addClass('stdinerr');
        return false;
    } else {
        $(".stdin textarea").removeClass('stdinerr');
    }

    c.info.name = name;
    var opval = $('.stdin select option:selected');
    var biaoji;
    if (opval.val() == -1) {
        c.command = 'sayall';
        c.info.msg = content;
        c.info.type = type;
        biaoji = '';
    } else if (opval.val() > -1) {
        c.command = 'sayto';
        c.info.to = opval.val();
        c.info.msg = content;
        c.info.type = type;
        biaoji = '&nbsp;&nbsp;@' + opval.text();
    }
    pextIndex = $(".stdout ul li.self").length;
    console.log(pextIndex);
    c.msgid = pextIndex;
    ws.send($.toJSON(c) + "\r\n");

    content = this.replaceEm(content);

    if (type == 1) {
        var c = $(".stdin textarea").val('');
        var content = '<li class="self"><p><img datatype="load" src="/static/images/loader.gif" />' + content + biaoji + '</p></li>'
    } else {
        content = '<img src="' + content + '" />';
        var content = '<li class="self"><p><img datatype="load" src="/static/images/loader.gif" />' + content + biaoji + '</p></li>'
    }
    
    $(".stdout ul").append(content);
    $('.stdout')[0].scrollTop = 1000000;
};

Utils.trim = function(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
};

Utils.showChatList = function(scTop) {
    $.get(config.apiServer + rqId, function(data) {

        var dataObj = $.evalJSON(data);
        if (dataObj.length < 1) {
            return false;
        }

        $.each(dataObj, function(idx, val) {
            val.content = Utils.replaceEm(val.content);
            
            var classname = '';
            if (idx == 0) {
                classname = 'border_dotted';
            }
            if (val.type == 2) {
                val.content = '<img src="' + val.content + '" />';
            }
            if (val.to == '-8000') {
                var content = '<li><p class="' + classname + '"><span>' + val.from + ' 对大家说： [ ' + val.addtime + ' ] </span>' + val.content + '</p></li>'
            } else {
                var content = '<li><p class="' + classname + '"><span class="sayto">' + val.from + ' 对 ' + val.to + ' 说： [ ' + val.addtime + ' ] </span>' + val.content + '</p></li>'
            }
            $(".stdout ul").prepend(content);
        });
        rqId = dataObj[(dataObj.length - 1)].id;
        if (scTop > 0) {
            $('.stdout')[0].scrollTop = scTop;
        }
    });
};

Utils.replaceEm = function(str) {
    var path = config.facePath;
    str = str.replace(/\</g, '&lt;');
    str = str.replace(/\>/g, '&gt;');
    str = str.replace(/\n/g, '<br/>');
    str = str.replace(/\[em_([0-9]*)\]/g, '<img src="'+path+'$1.gif" border="0" />');
    return str;
};

Utils.heros = ['战争女神 希维尔', '祖安狂人 蒙多医生', '扭曲树精 茂凯', '战争之王 潘森', 
    '钢铁大使 波比', '光辉女郎 拉克丝', '众星之子 索拉卡', '琴瑟仙女 娑娜', '探险家 伊泽瑞尔', 
    '末日使者 费德提克', '荒漠屠夫 雷克顿', '酒桶 古拉加斯', '虚空行者 卡萨丁', '风暴之怒 迦娜', 
    '迅捷斥候 提莫', '发条魔灵 奥莉安娜', '德玛西亚皇子 嘉文四世', '金属大师 莫德凯撒', '雪人骑士 努努', 
    '海洋之灾 普朗克', '麦林炮手 崔丝塔娜', '刀锋意志 艾瑞莉娅', '嗜血猎手 沃里克', '赏金猎人 厄运晓姐', 
    '英勇投弹手 库奇', '复仇焰魂 布兰德', '天启者 卡尔玛', '盲僧 李青', '狂暴之心 凯南','玛西亚之力 盖伦',
    '寒冰射手 艾希','蛮族之王 泰达米尔','宝石骑士 塔里克','邪恶小法师 维迦','武器大师 贾克斯','暗夜猎手 薇恩',
    '堕落天使 莫甘娜','虚空先知 玛尔扎哈','时光守护者 基兰','机械公敌 兰博','诅咒巨魔 特朗德尔','炼金术士 辛吉德',
    '策士统领 斯维因','魔蛇之拥 卡西奥佩娅','死亡颂唱者 卡尔萨斯','黑暗之女 安妮','皮城女警 凯特琳',
    '寡妇制造者 伊芙琳'
];

Utils.getHero = function() {
    var randIdx = Math.floor( (Math.random() * this.heros.length) );

    return this.heros[randIdx];
}