jQuery(function ($) {
    var baiduAudio = {
        stopped: true,
        getToken: function () {
            $.ajax({
                url: "/wp-json/hylsaytextreading/hylsay_text_reading_get_baiduAudio_token/",
                type: 'GET',
                dataType: 'json',
            }).done(function (data) {
                baiduAudio.tokenData = data;
                baiduAudio.makePlayer();
            }).fail(function () {
                $('.hylsay-text-r-info').addClass('active');
                $(".hylsay-text-r-info").text("** Token换取失败，可能是RestAPI被关闭导致或后台参数配置错误！**")
            })
        },
        makePlayer: function () {
            var player = $('<div>').attr('id', 'baiduAudioPlayer'),
                playBtn = $('<i class="fa fa-play-circle" aria-hidden="true"></i>').addClass('playBtn active').on('click',
                    function (event) {
                        baiduAudio.play();
                    });
            pauseBtn = $('<i class="fa fa-pause-circle" aria-hidden="true"></i>').addClass('pauseBtn').on('click',
                function (event) {
                    baiduAudio.pause();
                });
            stopBtn = $('<i class="fa fa-stop-circle" aria-hidden="true"></i>').addClass('stopBtn').on('click',
                function (event) {
                    baiduAudio.stop();
                });
            text = $('<span>朗读本文</span>').addClass('decoration');
            player.append(text).append(playBtn).append(pauseBtn).append(stopBtn);
            $('.baiduAudioWrap').append(player);
        },
        tokenData: {},
        speak: function (textArray) {
            if (baiduAudio.tokenData.access_token) {
                if (textArray < 1) {
                    return false;
                }

                var tok = baiduAudio.tokenData.access_token,
                    cuid = baiduAudio.tokenData.session_key,
                    spd = baiduAudio.tokenData.spd,
                    pit = baiduAudio.tokenData.pit,
                    vol = baiduAudio.tokenData.vol,
                    per = baiduAudio.tokenData.per;
                baiduAudio.audioArray = [];
                for (var i = 0; i < textArray.length; i++) {
                    var address = 'https://tsn.baidu.com/text2audio?tex=' + encodeURIComponent(textArray[i]) + '&lan=zh&ctp=1&cuid=' + cuid + '&per=' + per + '&spd=' + spd + '&pit=' + pit + '&vol=' + vol + '&tok=' + tok;
                    baiduAudio.audioArray.unshift(address);
                }
                baiduAudio.audio.preload = true;
                baiduAudio.audio.controls = true;
                baiduAudio.audio.src = baiduAudio.audioArray.pop();
                baiduAudio.audio.addEventListener('ended', baiduAudio.playEndedHandler, false);
                baiduAudio.audio.loop = false;
                console.log('插件地址：https://aoaoao.info 语音合成中，请稍后...');
                return true;
            } else {
                return false;
            }
        },
        playEndedHandler: function () {
            if (baiduAudio.audioArray.length > 0) {
                baiduAudio.audio.src = baiduAudio.audioArray.pop();
                baiduAudio.audio.play();
            } else {
                baiduAudio.stop();
            }
        },
        audioArray: [],
        audio: new Audio(),
        play: function () {
            if (baiduAudio.stopped) {
                baiduAudio.speak(baiduAudio.getTextArray());
                baiduAudio.stopped = false;
            }
            $('#baiduAudioPlayer .playBtn').removeClass('active');
            $('#baiduAudioPlayer .pauseBtn').addClass('active');
            $('#baiduAudioPlayer .stopBtn').addClass('active');
            baiduAudio.audio.play();
        },
        pause: function () {
            $('#baiduAudioPlayer .playBtn').addClass('active');
            $('#baiduAudioPlayer .pauseBtn').removeClass('active');
            $('#baiduAudioPlayer .stopBtn').addClass('active');
            baiduAudio.audio.pause();
        },
        stop: function () {
            $('#baiduAudioPlayer .playBtn').addClass('active');
            $('#baiduAudioPlayer .pauseBtn').removeClass('active');
            $('#baiduAudioPlayer .stopBtn').removeClass('active');
            baiduAudio.audio.pause();
            baiduAudio.audio.removeEventListener('ended', baiduAudio.playEndedHandler, false);
            baiduAudio.stopped = true;
        },
        getTextArray: function () {
            if (baiduAudio.tokenData.access_token) {
                var result = [];
                // var newDom = $('article ' + baiduAudio.tokenData.yuedu_posttag).clone();
                var newDom = $(baiduAudio.tokenData.yuedu_posttag).clone();
                newDom.find('#baiduAudioPlayer,iframe,[anti],[copy],pre,img,table,.modal,.donation cf,' + baiduAudio.tokenData.yuedu_pingbitag).remove();
                var text = '';
                $(newDom).find('div').each(function () {
                    $(this).append('。')
                });
                $(newDom).find('*').each(function () {
                    var content = $(this).contents();
                    $(this).replaceWith(content);
                });

                if ($(newDom).html() == null) {
                    $('.hylsay-text-r-info').addClass('active');
                    $(".hylsay-text-r-info").text("** 阅读范围设置错误，未获取到文本信息！**")
                    return '';
                } else {

                    text = $(newDom).html().replace(/&nbsp;/g, "");
                    textArr = text.split(/。/g);
                    var aAudioText = '';
                    for (var i = 0; i < textArr.length; i++) {
                        if (aAudioText.length + textArr[i].length < 500) {
                            aAudioText += textArr[i] + '。';
                        } else {
                            result.push(aAudioText);
                            aAudioText = '';
                            aAudioText += textArr[i] + '。';
                        }
                    }
                    result.push(aAudioText);
                    return result;

                }

            }

        }
    }
    baiduAudio.getToken();
});
