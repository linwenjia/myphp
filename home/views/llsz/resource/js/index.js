$(function() {
	var currentindex=0;
	var index = {
		//大屏轮播图
		
		swiper:function() {
			$('.carousel').carousel()
		},
		//合作伙伴轮播图
		swiper_parnter:function(){
			var mySwiper = new Swiper('#swiper_parnter',{
			slidesPerView:5,
			spaceBetween:15,
			// 如果需要前进后退按钮
    		nextButton: '#swiper-button-next',
    		prevButton: '#swiper-button-prev',
			})
		},
		//关于我们轮播图
		swiper_about:function(){
			var mySwiper = new Swiper('#swiper_about',{
				effect : 'fade',
				fade: {
  				crossFade: true,
				},
				// 如果需要前进后退按钮
	    		nextButton: '#about_next',
	    		prevButton: '#about_prev',
	    		loop: true,
	    		onSlideChangeEnd: function(swiper){
	    			$('.about_div_html').css("display","none");
	    			$('.about_div_html').eq(swiper.realIndex).css("display","block");
			      console.log(swiper.realIndex) //切换结束时，告诉我现在是第几个slide
			    }
			})
			},
		//关于我们内容切换
		switch_about:function(o){
			if(o=='#about_prev'){
				$(o).click(function(){
					currentindex--;
					currentindex=currentindex<0?3:currentindex;
//					console.log(currentindex);
				})
			}
			if(o=='#about_next'){
				$(o).click(function(){
					currentindex++;
					currentindex=currentindex>3?0:currentindex;
//					console.log(currentindex);
				})
			}
		},
		//新闻咨询选项卡
		mousetab:function(o,sel){
			$(o).mouseenter(function() {
				var index=$(this).index();
				$(o).siblings().removeClass('active');
				$(this).addClass('active');
				$(sel).eq(index).css("display", "block").siblings(sel).css("display", "none");
			})
		},
		//所有函数调用index
		event:function(){
			this.swiper(); //大屏轮播图调用
			this.swiper_parnter();//合作伙伴轮播图调用
			this.swiper_about();//关于我们轮播图调用
			this.mousetab('.num_sel','.news_sel');//新闻咨询选项卡调用
			this.switch_about('#about_next');//关于我们内容切换调用
			this.switch_about('#about_prev');//关于我们内容切换调用
		}
	}
	index.event();
})