  function g_upload_file(url, input_id, multi,filestype) {

	  	art.dialog.open(url, {

		width:500,

		height:500,

		title:'您可以上传或者选择' + (multi ? "多个" : "1个") + '文件',

        init: function() {

			art.dialog.data("multi", multi);

		},

		ok: function () {

			var origin = artDialog.open.origin;

			//返回图片ID

			var upfile = this.iframe.contentWindow.get_file_val();

			var filesurl = "";

            var cls = multi ? '<div class="closeimg"><a href="javascript:;" onclick="javascript:g_remove_file(this);" class="iclose"></a></div>' : '';

			for (var i = 0; i < upfile.length; i++) {

				if (upfile[i].fileurl) { 
					/*
					filesurl += '<div class="oneimg">' + cls + '<div class="imgdiv"><div class="outline"><img src="/html/upload/file_' + filestype + '.jpg"  height="60" /></div></div><input type="checkbox" name="'+input_id+'[id][]" value="'+ upfile[i].id +'" checked="checked"  style="display:none;" /><input type="checkbox" name="'+input_id+'[fileurl][]" value="'+ upfile[i].fileurl +'" checked="checked"  style="display:none;" /></div>';
					*/
					filesurl += '<table rules="none" border="0" cellpadding="0" cellspacing="0"><tr><td><input type="text" name="'+input_id+'[filetitle][]" value="" style="width:100px;"/></td><td><input type="text" name="'+input_id+'[fileurl][]" value="'+ upfile[i].fileurl +'"  readonly style="width:200px;"/></td><td><a href="javascript:;" onclick="javascript:g_remove_file(this);" class="iclose"></a></td></tr></table>';
					
					if (!multi) break;

				};

			}

			if (multi) {

				$('#' + input_id + '_show').append(filesurl);

			} else {

				$('#' + input_id + '_show').html(filesurl);

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

  

  function g_remove_file(a){

	$(a).parent().parent().parent().parent().empty().remove()

  }