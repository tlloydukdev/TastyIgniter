+ function ($) {
    "use strict";

    var lastOrderId = 0;

    $(document).render(function () {

        $("<audio controls class='soundalert_audio_alert' style='display:none;'></audio>").attr({
            'src': "/assets/media/uploads/alert.mp3",
        }).appendTo("body");


        $.get('/admin/igniter/soundalert/soundalert/getlastorderid', function (data) {
            if (typeof data !== undefined && Number.isInteger(parseInt(data))) {
                lastOrderId = parseInt(data);
                console.log("[!] %cSoundAlert initialised, last order " + lastOrderId, 'color:green')
            } else {
                console.log("[!] %cSoundAlert initialised, awaiting order", 'color:green')
            }            
        })

        setInterval(function () {
            $.get('/admin/igniter/soundalert/soundalert/getlastorderid', function (data) {
                if (typeof data !== undefined && Number.isInteger(parseInt(data))) {
                    if(parseInt(data) > lastOrderId) {
                        console.log('[!] %cNew order received', 'color:red')
                        lastOrderId = parseInt(data)
                        // Trigger the sound
                        $('.soundalert_audio_alert').trigger("play");
                        console.log("[!] %cSoundAlert last order is now " + lastOrderId, 'color:green')
                    }
                }
            })
        }, 10000)
    })
    
}(window.jQuery);