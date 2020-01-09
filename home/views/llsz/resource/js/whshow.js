$(function(){
	function tab(obj){
		$(obj).eq(0).children('.border').removeClass('txt_none');
		$(obj).click(function(){
			console.log(123);
			$(this).siblings().children('border').addClass('txt_none');
			$(this).children('.border').removeClass('txt_none');

		})
	}
	tab('.border_sel')
})
