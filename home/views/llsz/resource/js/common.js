$(function() {
	var common = {
		//字数控制
		txtnum:function(o,num){
			var len = $(o).length;
		for(var i = 0; i < len; i++) {
			var html = $(o).eq(i).html();
			if(html.length >= num) {
				$(o).eq(i).html(html.substring(0, num) + '......');
				}
			}
		},
       //所有函数调用common
		event:function(){
			this.txtnum('.about_txt',240);//关于我们调用
			this.txtnum('.centre_txt',85);//核心课程调用
			this.txtnum('.news_txt',100);//新闻咨询调用
			this.txtnum('.desc_txt',150);//文章详细调用
		}
	}
	common.event();
	//导航下拉列表
	$('.nav_ul>li').mouseenter(function() {
		$(this).find('.tab_item').stop().slideDown();
	})
	$('.nav_ul>li').mouseleave(function() {
		$(this).find('.tab_item').stop().slideUp();
	})

	//点击选项卡
	function tab(o, sel) {
		$(o).eq(0).addClass('active');
		$(o).click(function() {
			$(this).addClass('active').siblings().removeClass('active');
			var index = $(this).index();
			$(sel).eq(index).css("display", "block").siblings().css("display", "none");
		})
	}
	tab('.about_left_sel', '.about_center_sel');
	tab('.about_top_sel', '.about_right_sel');
	
	//移入选项卡
	function mousetab(o, sel) {
		$(o).mouseenter(function() {
			var index=$(this).context.dataset.index
			$(sel).eq(index).css("display", "block").siblings(sel).css("display", "none");
		})
	}
	// console.log($('.recommend_left_sel').length)
	mousetab('.recommend_left_sel','.recommend_right_sel');
	
	//搜索功能
	$('#btn').click(function(){
			var val = $('#input').val();
			console.log(key_url)
			if(val == ''){
				alert('请输入搜索词');
				return false;
			}
			window.location.href = key_url + val;
		})
})