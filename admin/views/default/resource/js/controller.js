$(function(){
	var controller = {
		obj_a:{
			position:1,//显示位置
			cls_id:'',//分类ID
			type:'轮播',//该对象类型
			comment:'没有注释',//该对象注释
			level:{index:0,txt:'一级'},//该对象等级
			output:'top',//该对象输出变量
			output_type:{index:0,txt:'输出类型'},//输出类型
			onoff:0,//是否修改保存过
			model_id:'',//对象ID
			model_pic:'',//图片
			news_id:'',//文章ID
			textnum:1,//文张个数
			count_data:'',//统计数据
		},
		onoff:false,//判断是否有选中对象
		obj_index:0,//当前选中对象的索引值
		obj:{
			position:1,//显示位置
			cls_id:'',//分类ID
			type:'轮播',//该对象类型
			comment:'没有注释',//该对象注释
			level:{index:0,txt:'一级'},//该对象等级
			output:'top',//该对象输出变量
			output_type:{index:0,txt:'输出类型'},//输出类型
			onoff:0,//是否修改保存过
			model_id:'',//对象ID
			model_pic:'',//图片
			news_id:'',//文章ID
			textnum:1,//文章个数
			count_data:'',//统计数据
		},//当前选中对象的参数
		arrobj:[],//所有已经加入到展示区的对象
		//选中对象时
		SelectedObj:function(){
			var self = this;
			$(document).on('click','#clsUl>li',function(){
				var Img = $(this).find('img').attr('src');
				
				console.log($(this).attr('data_model_pic'))
				for(var key in self.obj){
					var arr0 = {
						position:1,//显示位置
						cls_id:'',//分类ID
						type:'',//该对象类型
						comment:'',//该对象注释
						level:0,//该对象等级
						output:'top',//该对象输出变量
						output_type:0,//输出类型
						onoff:0,//是否修改保存过
						model_pic:$(this).attr('data_model_pic'),//图片
						model_id:$(this).attr('data_model_id'),//对象ID
						news_id:'',//文章ID
						textnum:'',//文张个数
						// count_data:'',//统计数据
					};
					var data = $(this).attr('data_'+ key);
					arr0[key] = data;
				}
				self.arrobj.push(arr0);
				$('#body_txt').append('<img src="'+ Img +'" class="img0" />');
			})
		},
		//给所有添加到展示区的对象都加上点击事件
		ShowObjClick:function(){
			var self = this;
			$(document).on('click','#body_txt>img',function(){
				self.onoff = true;
				self.obj_index = $(this).index();
				$(this).css({'border':'1px solid #1ab394','padding':'5px'});
				$(this).siblings().css({'border':'none','padding':'0'});
				//设置默认数据
				//等级重新赋值
				$('#level_ul').find('li').find('span').removeClass('active');
				//输出类型重新赋值
				$('#level1_choose').find('p').find('.ra_sele').find('span').removeClass('span_active');
				//位置显示重新赋值
				$('#position').find('p').find('.ra_sele').find('span').removeClass('span_active');
				//文章数据选项重新赋值level2
				$('#level2').find('p').find('.ra_sele').find('span').removeClass('span_active');
				if(self.arrobj[self.obj_index].onoff == 0){
					console.log('未保存')

					self.obj = self.arrobj[self.obj_index];
					
					$('#comment').val('');
					$('#output_var').val('');
					$('#data_txt').val('');
					$('#ClsId').val('');
					$('#textnum').val('');
					// $('#count_data').val('');
					$('#data_cla_txt').addClass('txt_none');
					$('#level_ul_pren').addClass('txt_none');
					$('#level2').addClass('txt_none');
					$('#position').addClass('txt_none');
					$('#select_ce').val($('#select_ce').find('option').eq(0).html());
				}else{
					console.log('保存');
					var o = self.arrobj[self.obj_index];
					self.obj = self.arrobj[self.obj_index];
					//重新定义数据
					$('#comment').val(o.comment);
					$('#select_ce').val(o.type);
					$('#output_var').val(o.output);
					$('#ClsId').val(o.cls_id);
					$('#textnum').val(o.textnum);
					if($('#select_ce').val() == 'cls_data'){

						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').removeClass('txt_none');
						$('#level2').addClass('txt_none');
						$('#position').addClass('txt_none');

						//等级重新赋值
						$('#level_ul').find('li').find('span').removeClass('active');
						$('#level_ul').find('li').eq(o.level).find('span').addClass('active');
						//等级重新赋值end---------

						//数据等级下的文本框重新赋值
						$('#data_txt').val(o.txt);
						//数据等级下的文本框重新赋值end-------------
						//输出类型重新赋值
						$('#level1_choose').find('p').find('.ra_sele').find('span').removeClass('span_active');
						$('#level1_choose').find('p').eq(o.output_type).find('.ra_sele').find('span').addClass('span_active');
						//输出类型重新赋值end----
					}else if($('#select_ce').val() == 'banner' || $('#select_ce').val() == 'advert'){
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
						$('#level2').addClass('txt_none');
						$('#position').removeClass('txt_none');

						//位置显示重新赋值
						$('#position').find('p').find('.ra_sele').find('span').removeClass('span_active');
						$('#position').find('p').eq(o.position - 1).find('.ra_sele').find('span').addClass('span_active');
						//位置显示重新赋值end-------------
					}else if($('#select_ce').val() == 'news_data'){
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
						$('#position').addClass('txt_none');
						$('#level2').removeClass('txt_none');
						//文章数据选项重新赋值level2
						$('#level2').find('p').find('.ra_sele').find('span').removeClass('span_active');
						$('#level2').find('p').eq(o.output_type).find('.ra_sele').find('span').addClass('span_active');
						//文章数据选项重新赋值end-------------
					}else if($('#select_ce').val() == 'count_data'){
						$('#data_cla_txt').removeClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
						$('#position').addClass('txt_none');
						$('#level2').addClass('txt_none');
					}else{
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
						$('#position').addClass('txt_none');
					}
					//重新定义数据end----------------------
				}
				//设置默认数据end--------------
			})
		},
		//数据类型选择
		DateClsSele:function(){
			var self = this;
			$('#select_ce').change(function(){
				if(self.onoff){//判断是否已经选中对象
					if($(this).val() == 'cls_data'){
						console.log($(this).val())
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').removeClass('txt_none');
						$('#level2').addClass('txt_none');
						$('#position').addClass('txt_none');
						self.obj.level = 0;
					}else if($(this).val() == 'news_data'){
						self.obj.level = '';
						$('#level2').removeClass('txt_none');
						$('#position').addClass('txt_none');
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
					}else if($(this).val() == 'banner' || $(this).val() == 'advert'){
						self.obj.level = 0;
						self.obj.output_type = 0;
						$('#position').removeClass('txt_none');
						$('#level2').addClass('txt_none');
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
					}else if($(this).val() == 'count_data'){
						self.obj.level = 0;
						self.output_type = 0;
						$('#data_cla_txt').removeClass('txt_none');
						$('#level2').addClass('txt_none');
						$('#position').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
					}else{
						console.log($(this).val())
						$('#position').addClass('txt_none');
						$('#data_cla_txt').addClass('txt_none');
						$('#level_ul_pren').addClass('txt_none');
						$('#level2').addClass('txt_none');
					}
					self.obj.type = $('#select_ce').val();
				}else{
					return;
				}
			})
		},
		//选择显示位置
		ShowPosition:function(){
			var self = this;
			$('#position p').click(function(){
				$(this).find('.ra_sele').find('span').addClass('span_active');//添加选中状态
				$(this).siblings('p').find('.ra_sele').find('span').removeClass('span_active');//去除其他选中状态
				self.obj.position = $(this).index() + 1;
				console.log(self.obj);
			})
		},
		//单选按钮选择
		Single:function(){
			var self = this;
			$('.choose>p').click(function(){
				$(this).find('.ra_sele').find('span').addClass('span_active');//添加选中状态
				$(this).siblings('p').find('.ra_sele').find('span').removeClass('span_active');//去除其他选中状态
				self.obj.output_type = $(this).index();
				console.log(self.obj);
			})
		},
		//级别选择
		Level:function(){
			var self = this;
			$('#level_ul span').click(function(){
				$(this).parent('li').siblings('li').find('span').removeClass('active');
				$(this).addClass('active');
				self.obj.level = $(this).parent('li').index();
				console.log(self.obj)
			})
		},
		//保存按钮
		SaveBtn:function(){
			var self = this;
			$('#save_btn').click(function(){
				if(self.onoff){
					if($('#comment').val() == ''){
						alert('请填写注释');
						return false;
					}else if($('#ClsId').val() == ''){
						alert('请填写分类ID');
						return false;
					}else if($('#select_ce').val() == 'news_data' && $('#article_id').val() == ''){
						alert('请填写文章ID');
						return false;
					}else if($('#output_var').val() == ''){
						alert('请填写输出变量');
						return false;
					}else{
						self.obj.onoff = 1;//修改说明此对象已经被保存过

						//赋值给当前对象obj
						self.obj.comment = $('#comment').val();
						self.obj.output = $('#output_var').val();
						self.obj.cls_id = $('#ClsId').val();
						self.obj.news_id = $('#article_id').val();
						self.obj.textnum = $('#textnum').val();
						self.obj.count_data = $('#count_data').val();
						//赋值给当前对象obj--------------------
						//保存到总数组里
						self.arrobj[self.obj_index].type = self.obj.type;
						self.arrobj[self.obj_index].comment = self.obj.comment;
						self.arrobj[self.obj_index].level = self.obj.level;
						self.arrobj[self.obj_index].cls_id = self.obj.cls_id;
						self.arrobj[self.obj_index].output = self.obj.output;
						self.arrobj[self.obj_index].output_type = self.obj.output_type;
						self.arrobj[self.obj_index].news_id = self.obj.news_id;
						self.arrobj[self.obj_index].onoff = self.obj.onoff;
						self.arrobj[self.obj_index].textnum = self.obj.textnum;
						self.arrobj[self.obj_index].count_data = self.obj.count_data;
						
						//保存到总数组里-------------------
						alert('保存成功');
					}
				}else{
					alert('请选择对象');
				}
			})
		},
		//json转化
		JsonObj:function(){
			$('#btn-json-viewer').click(function() {
			    try {
			      var input = eval('(' + $('#json-input').val() + ')');
			    }
			    catch (error) {
			      return alert("Cannot eval JSON: " + error);
			    }
			    var options = {
			      collapsed: $('#collapsed').is(':checked'),
			      withQuotes: $('#with-quotes').is(':checked')
			    };
			    $('#json-renderer').jsonViewer(input, options);
			  });

			  // Display JSON sample on load
			  $('#btn-json-viewer').click();
		},
		//打开预览弹框
		Showbox:function(){
			var self = this;
			$('#Showbox').click(function(){
				if(self.onoff_data){return};//防止重复点击
				console.log(self.arrobj);
				self.onoff_data = true;
				var o = {};
				for(var i=0;i<self.arrobj.length;i++){
					if(self.arrobj[i].onoff == 1){
						o['obj'+ i] = self.arrobj[i];
					}else{
						$('#body_txt').children().css({'border':'none','padding':'0px'});
						$('#body_txt').children().eq(i).css({'border':'1px solid #1ab394','padding':'5px'});
						alert('第'+ (i+1) +'对象未填写数据,请填写数据后再预览数据');
						return false;
					}
				}
				if(self.arrobj.length == 0){
					console.log(0)
					alert('请添加模块');
					return false;
				};
				$.ajax({
					url:'/admin.php/controller/get_preview_data',
					type:'post',
					data:o,//传给后台参数
					dataType:'json',
					success:function(data){
						console.log(data);
						$('#bullet_box').fadeIn(300);
						$('#json-input').text(JSON.stringify(data));
						self.JsonObj();
					}
				})
			})
		},
		//关闭预览弹框
		Hidebox:function(){
			var self = this;
			$('#hide_box').click(function(){
				$('#bullet_box').fadeOut(300);
				self.onoff_data = false;
			})
		},
		//生成代码按钮
		GenerBtn:function(){
			var self = this;
			$('#generate').click(function(){
				console.log(self.arrobj)
				var o = {};
				for(var i=0;i<self.arrobj.length;i++){
					if(self.arrobj[i].onoff == 1){
						o['obj'+ i] = self.arrobj[i];
					}else{
						$('#body_txt').children().css({'border':'none','padding':'0px'});
						$('#body_txt').children().eq(i).css({'border':'1px solid #1ab394','padding':'5px'});
						alert('第'+ (i+1) +'对象未填写数据,请填写数据后再生成数据');
						return false;
					}
				}
				if(self.arrobj.length == 0){
					alert('请添加模块');
					return false;
				};

				$.ajax({
					url:'/admin.php/controller/create',
					type:'post',
					data:o,//传给后台参数
					dataType:'json',
					success:function(data){
						console.log(data);
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
		//关闭填写控制器名称弹框
		ClsObjBox:function(){
			$('#ClsBtn').click(function(){
				var val = $('#ClsobjBox').find('input').val()
				if(val == ''){
					alert('请填写控制器名称');
				}else{
					$('#ClsobjBox').fadeOut(300);
					$.ajax({
						url:'/admin.php/controller/setController',
						type:'post',
						data:{"name":val},//传给后台参数
						dataType:'json',
						sueccess:function(data){
							console.log(data);
						}
					})
				}
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
		//导入前端视图
		load:function(){
			var self = this;
			$('#load').click(function(){
				var view_id = $('#view_data').val();
				$.ajax({
					url:'/admin.php/model/load_view',
					type:'post',
					data:{'view_id':view_id},
					dataType:'json',
					success:function(data){
						if(data.code == '000000'){
							var len = data.list;
							self.arrobj = len;
							$('#body_txt').html('');
							for(var i=0;i<len.length;i++){
								self.arrobj[i].onoff = 0;
								$('#body_txt').append('<img src='+ self.arrobj[i].model_pic +' class="img0" />');
							}
						}
					}
				})
			});
		},
		//所有函数调用
		Event:function(){
			this.SelectedObj();//选中对象时
			this.ShowObjClick();//给所有添加到展示区的对象都加上点击事件
			this.SaveBtn();//保存按钮
			this.DateClsSele();//数据类型选择
			this.Level();//级别选择
			this.Single();//单选按钮选择
			this.Showbox();//关闭弹框
			this.JsonObj();//json转化
			this.Hidebox();//关闭弹框
			this.GenerBtn();//生成代码按钮
			this.ClsObjBox();//关闭填写控制器名称弹框
			this.ClsSelect();//切换分类下拉框
			this.ShowPosition();//选择显示位置
			this.DeleObj();//删除对象
			this.Hidebomb();//关闭提示框
			this.load();//导入视图
		}
	};
	controller.Event();
})