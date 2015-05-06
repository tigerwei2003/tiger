  function g_upload_image(url, input_id, multi) {

	  	art.dialog.open(url, {

		width:500,

		height:500,

		title:'您可以上传或者选择' + (multi ? "多张" : "1张") + '图片',

        init: function() {

			art.dialog.data("multi", multi);

		},

		ok: function () {

			var origin = artDialog.open.origin;

			//返回图片ID

			var upfile = this.iframe.contentWindow.get_upfile_val();

			var thumb_html = "";

            var cls = multi ? '<div class="closeimg"><a href="javascript:;" onclick="javascript:g_remove_img(this);" class="iclose"></a></div>' : '';

			for (var i = 0; i < upfile.length; i++) {

				if (upfile[i].thumb) { 

					thumb_html += '<div class="oneimg">' + cls + '<div class="imgdiv"><div class="outline"><img src="'+ upfile[i].thumb +'"  height="60" /></div></div><input type="checkbox" name="'+input_id+'[id][]" value="'+ upfile[i].id +'" checked="checked"  style="display:none;" /><input type="checkbox" name="'+input_id+'[imgurl][]" value="'+ upfile[i].imgurl +'" checked="checked"  style="display:none;" /><input type="checkbox" name="'+input_id+'[thumb][]" value="'+ upfile[i].thumb +'" checked="checked"  style="display:none;" /></div>';

					if (!multi) break;

				};

			}

			if (multi) {

				$('#' + input_id + '_show').append(thumb_html);

			} else {

				$('#' + input_id + '_show').html(thumb_html);

			}

			

			art.dialog.removeData("multi");

		},

		cancel: function () {

			art.dialog.close();

			art.dialog.removeData("multi");

		},

		close: function () {

			art.dialog.removeData("multi");

		},

		lock:true //false

	});

	  

  }

  

  function g_remove_img(a){

	$(a).parent().parent().empty().remove()

  }