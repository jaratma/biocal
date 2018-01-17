"use strict";
var current = 0;

Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0,10);
});


jQuery(function ($) {
    var processFile = "process.php",
        fx = {
            "deserialize": function (str) {
                var data = str.split("&"),
                    pairs = [],
                    entry = {},
                    key, val;
                for (var x in data) {
                    pairs = data[x].split("=");
                    key = pairs[0];
                    val = pairs[1];
                    entry[key] = fx.urldecode(val);
                }
                return entry;
            },
            "urldecode": function (str) {
                var converted = str.replace(/\+/, ' ');
                return decodeURI(converted);
            }
        };

    $(document).ready(function () {
        $('audio').on('contextmenu', function (e) {
            return false;
        });
		$('#calcdate').val(new Date().toDateInputValue());
		$(function(){ 
			$('input[type="time"][value="now"]').each(function(){
				var d = new Date(),
					h = d.getHours(),
					m = d.getMinutes();
				if(h < 10) h = '0' + h;
				if(m < 10) m = '0' + m;
				$(this).attr({
					'value': h + ':' + m
				});
			});
		});
      });


    $("body").on("click", "input[type=submit]", function (event) {
        event.preventDefault();

        if ($("#search-box").val() == "") return;
        var formData = $(this).parents("form").serialize();

        $.ajax({
            type: "POST",
            url: processFile,
            data: formData,
            success: function (data) {
                $("#playcontainer").show();
                $("#playcontainer").html(data);
                console.log(data);
            },
            error: function (msg) {
                alert(msg);
            }
        });
    });

    $("#search-box").keyup(function () {
        $.ajax({
            type: "POST",
            url: "readcity.php",
            data: 'keyword=' + $(this).val() + '&country=' + $(this).parents("form")
                .children('fieldset').children('#country').val(),
            success: function (data) {
                console.log(data);
                $("#suggesstion-box").show();
                $("#suggesstion-box").html(data);
                $("#search-box").css("background", "#FFF");
            }
        });
    });
});

function selectCity(val) {
    $("#search-box").val(val);
    $("#suggesstion-box").hide();
    var input = $("#country");
    $.ajax({
        type: "POST",
        url: "loadcity.php",
        data: 'keyword=' + val + '&country=' + input.val(),
        success: function (data) {
            console.log(data);
            //$("<p>", {
            //    "text": JSON.stringify(JSON.parse(data), null, 2)
            //}).appendTo("div");
        }
    });
}

function collectaudio() {
	let audio = new Merger();
	let refs = [];
	var tracks = $("#playlist").find('li a');
	tracks.each(function(ix,val){
		refs.push(val.href);
	});
	let fname = $("#calcname").val();
	if (fname != "") fname += "-";

	$("#spinner").html("Creando archivo...");
	audio.fetchAudio(...refs)
		.then(buffers => {
			audio.concatAndExport(buffers, 'audio/wav', fname);
		}).then( dummy => $("#spinner").html(""));
}

function _totalDuration(buffers) {
	return buffers.map(buffer => buffer.duration).reduce((a, b) => a + b, 0);
}


function playalong() {
    var tracks = $("#playlist").find('li a');
    var len = tracks.length;
    var player = $('audio')[0];
    var link = $("#playlist").find('a')[current];
    current++;
    if (current < len) {
        console.log("Now playing " + $("#labels").find('span')[current].textContent)
        link = $("#playlist").find('a')[current];
        player.src = link.href;
        $("#labels").find('span')[current].classList.add('active');
        $("#labels").find('span')[current].previousElementSibling.classList.remove('active');
        player.load();
        player.play();
    } else {
        current = 0;
        link = $("#playlist").find('a')[0];
        player.src = link.href;
        $("#labels").find('span')[0].classList.add('active');
        $("#labels").find('span')[len-1].classList.remove('active');
    }
}
