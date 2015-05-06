/**
 * Unicorn Admin Template
 * Diablo9983 -> diablo9983@gmail.com
**/
$(document).ready(function(){
	
//	$('.data-table').dataTable({
//		"bJQueryUI": true,
//		"sPaginationType": "full_numbers",
//		"sDom": '<""l>t<"F"fp>',
//		"bProcessing": false,
//		"bLengthChange": false
//	});
//	
	$('input[type=checkbox],input[type=radio],input[type=file]').uniform();
	
	//$('select').select2();
	
	$("span.icon input:checkbox, th input:checkbox").click(function() {
		var checkedStatus = this.checked;
		var checkbox = $(this).parents('.widget-box').find('tr td:first-child input:checkbox');		
		checkbox.each(function() {
			this.checked = checkedStatus;
			if (checkedStatus == this.checked) {
				$(this).closest('.checker > span').removeClass('checked');
			}
			if (this.checked) {
				$(this).closest('.checker > span').addClass('checked');
			}
		});
	});	
});



function tableresize(width){
	var pwidth = $('.cont_max').width();
	if(pwidth<width){
		$('.cont_max').addClass('cont_max_bro');	
		$('#maxtable').css('width',width+'px').css('margin-right','-1px').css('margin-bottom','0px');
	}else{
		$('.cont_max').removeClass('cont_max_bro').css('border-left','1px solid #ddd');	
		$('#maxtable').css('width','100%');
	}	
}