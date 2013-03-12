$(function() {
	$.selectSkill("#slt_indus_pid", "#slt_indus_id", "#skills", "#skills-selected", "#sdata", 5);
});

/**
 * selectSkill 快捷菜单
 * 
 * 单个
 * 
 * @param content 内容条件
 * 
 */
(function($) {

	$.selectSkill = function(sidIndustry, sidSubindustry, eidSkill, eidSelectdSkill, eidData, ca){
		var self = this;
		var $slcIndustry     = $(sidIndustry);
		var $slcSubindustry  = $(sidSubindustry);
		var $eleSkill        = $(eidSkill);
		var $eleSelectdSkill = $(eidSelectdSkill);
		var $data            = $(eidData);
		self.selectLength     = 0;
		self.selectList       = [];
		self.selectTextList   = [];
	
		this.industryOnchange = function(){
			var option = $slcIndustry.find("option:selected");
			var indus_pid = $(option).val();
			$.post("index.php?do=ajax_indus&code=r5tv", {
				indus_pid : indus_pid
			}, function(data) {
				var str_data = data;
				if (trim(str_data) == '') {
					$slcSubindustry.html(L.no_project);
				} else {
					var results = self.parse(str_data);
					self.refleshSkill(results);
				}
			});
		};
		
		this.parse = function(html){
			var patt = new RegExp(/(\d+)=>([^\|]+)/g);
			var arr;
			var results = [];
			while(arr = patt.exec(html)){
				results.push(arr);
			}
			
			return results;
		}
		
		
		this.refleshSkill = function(results){	
			var str = "";
			for(var i = 0; i < results.length; i ++){
				var value =  results[i][1];
				var text =  results[i][2];
				if($.inArray(value, self.selectList) == -1){
					str += '<a herf="###" value="'+value+'">'+text+'</a>';
				} else {
					str += '<a herf="###" class="selected" value="'+value+'">'+text+'</a>';
				}
			}
			
			$eleSkill.html(str);
			
			$eleSkill.find("a").click(self.eleSkillClick);
		}
		
		this.eleSkillClick = function(){
			$this = $(this);
			var value = $this.attr("value");
			var text = $this.text();
			if($.inArray(value, self.selectList) != -1){
				return;
			}
			
			if(self.updateList(value, text)){
				$this.addClass("selected")
			}
			
		
			return false;
		}
		
		this.skillClick = function(){
			$this = $(this);
			var value = $this.attr("value");
			var index = $.inArray(value, self.selectList);
			self.selectList[index] = null;
			self.selectTextList[index] = null;
			self.selectLength --;
			
			$eleSkill.find("a[value="+value+"]").removeClass("selected");
			
			self.render();
		}
		
		this.cleanlist = function(){
			self.selectList = [];
			self.selectTextList = [];
			self.selectLength = 0;
			
			$eleSkill.find("a").removeClass("selected");
			
			self.render();
			return false;
		}
		
		this.updateList = function(value, text){
			if(self.selectLength == ca){
				return false;
			}
			
			self.selectList.push(value);
			self.selectTextList.push(text);
			self.selectLength ++;
			
			self.render();
			return true;
		}
		
		this.render = function(){
			var cc = self.selectLength;
			var cl = ca - cc;
			var str = '	<div class="bstitle"><h4>'+L.you_have_selected+'<span>'+cc+'</span>'+L.a+L.ability_label+','+L.you_can_choose+'<span>'+cl+'</span>个</h4><a href="###" class="ctrl-clean">'+L.all_clear+'</a></div>';
			str += '    <ul class="list-selected-skill clearfix">';
			
			for(var i = 0; i < self.selectList.length; i ++){
				var v = self.selectList[i];
				var t = self.selectTextList[i]
				if(v != null){
					str += '<li><a href="###" value="'+v+'">'+t+'</a><input type="hidden" value="'+v+'" name="skill[]"></li>';
				}
			}
			
			str += '    </ul>';
			$eleSelectdSkill.html(str);
			$eleSelectdSkill.find("a.ctrl-clean").click(self.cleanlist);
			$eleSelectdSkill.find("li > a").click(self.skillClick);
		}
		
		// self.industryOnchange
		$slcIndustry.change(self.industryOnchange);
		
		/*
		 $slcSubindustry.change(self.subindustryOnchange); 
		 */
		
		this.init = function(){
			self.cleanlist();
			var data = $data.val();
			if(data != ""){
				var results = self.parse(data);
				for(var i = 0; i < results.length; i ++){
					var value =  results[i][1];
					var text =  results[i][2];
					
					self.selectList.push(value);
					self.selectTextList.push(text);
					self.selectLength ++;
				}
			}

			self.industryOnchange();
			self.render();
		}
		
		
		this.init();
	
	}
})(jQuery);
