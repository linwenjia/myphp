$(function(){
	var controller = {
		onoff:false,//判断是否提交过数据
		obj_index:0,//当前选中对象的索引值
		arrobj:[],//所有已经加入到展示区的对象id
		//选中对象时
		SelectedObj:function(){
			var self = this;
			$(document).on('click','#clsUl>li',function(){
				var Img = $(this).find('img').attr('src');
				self.arrobj.push($(this).attr('data_model_id'));
				$('#body_txt').append('<img src="'+ Img +'" class="img0" />');
			})
		},
		//给所有添加到展示区的对象都加上点击事件
		ShowObjClick:function(){
			var self = this;
			$(document).on('click','#body_txt>img',function(){
				self.obj_index = $(this).index();
				$(this).css({'border':'1px solid #1ab394','padding':'5px'});
				$(this).siblings().css({'border':'none','padding':'0'});
			})
		},
		//生成视图按钮
		GenerBtn:function(){
			var self = this;
			if(self.onoff) return;
			$('#generate').click(function(){
				console.log(self.arrobj);
				self.onoff = true;
				$.ajax({
					url:'/admin.php/model/view_add',
					type:'post',
					data:{arrobj:self.arrobj,sub:'ok',view_id:view_id},//传给后台参数
					dataType:'json',
					success:function(data){
						console.log(data);
						self.onoff = false;
						$('#bomb').find('p').html(data.msg);
						$('#bomb').removeClass('txt_none');
						if(data.code == 200){
							window.location.href = data.url;
						}
					}
				})
			})
		},
		//关闭提示框
		Hidebomb:function(){
			$('#bombhide').click(function(){
				$('#bomb').find('p').html('');
				$('#bomb').addClass('txt_none');
			})
		},

		//切换分类下拉框
		ClsSelect:function(){
			var self = this;
			$('#model_data').change(function(){

				var oindex = $(this).find('option:selected').index();
				console.log(oindex)
				$('.clsUl').addClass('txt_none');
				$('.clsUl').eq(oindex).removeClass('txt_none');
			})
		},
		
		//删除对象
		DeleObj:function(){
			var self = this;
			$('#dele_obj').click(function(){
				$('#body_txt').children().eq(self.obj_index).remove();
				self.arrobj.splice(self.obj_index,1);
			})
		},
		
		//请求原有的数据
		ModData:function(){
			if(view_id == '')return;
			var self = this;
			$.ajax({
				url:'/admin.php/model/getViewInfo',
				type:'post',
				data:{'view_id':view_id},
				dataType:'json',
				success:function(data){
					var len = data.post;
					for(var i=0;i<len.length;i++){
						self.arrobj.push(len[i].model_id);
						$('#body_txt').append('<img src='+ Res +'/'+ len[i].model_pic +' class="img0" />');
					}
					console.log(self.arrobj);
				}
			})
		},

		//所有函数调用
		Event:function(){
			this.SelectedObj();//选中对象时
			this.ShowObjClick();//给所有添加到展示区的对象都加上点击事件
			this.GenerBtn()//生成视图
			this.ClsSelect();//切换分类下拉框
			this.DeleObj();//删除对象
			this.Hidebomb();//关闭提示框
			this.ModData();//请求原有的数据
		}
	};
	controller.Event();
})