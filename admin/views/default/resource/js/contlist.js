$(function(){
	var con_list = {
		//弹框显示
		BulletShow:function(){
			$('#last_li').click(function(){
				$('#Bullet_box').fadeIn(300);
			})
		},
		//弹框关闭
		BulletHide:function(o){
			$(o).click(function(){

				if($(this)[0].id == 'Bullet_hide'){
					$('#Bullet_box').fadeOut(300);
				}else{
					console.log(000)
					var Name = $('#Name').val();
					var Explain = $('#Explain').val();
					$.ajax({
						url:'/admin.php/controller/setController',
						type:'post',
						data:{'name':Name,'explain':Explain},//传参-->后台
						dataType:'json',
						success:function(data){
							console.log(data);
							$('#Bullet_box').css('display','none');
							if(data.code == 200)
								window.open(data.url);
							return false;
						}
					})
				}
			})
		},
		//删除控制器
		DeleController:function(){
			$('.li_dele').click(function(){
				var ID = $(this).attr('data_id');
				console.log(ID)
				$.ajax({
					url:'/admin.php/controller/del',
					type:'post',
					data:{'controller_id':ID},
					dataType:'json',
					success:function(data){
						console.log(data);
						window.location.reload();
						alert(data.msg);
					}
				})
				
				
			})
		},
		//所有函数调用
		Event:function(){
			this.BulletShow();//弹框显示
			this.BulletHide('#Bullet_hide');//弹框关闭
			this.BulletHide('#Bullet_btn');//确定按钮
			this.DeleController();//删除控制器
		}
	};
	con_list.Event();
})