/**
 * KindEditor 对齐下拉菜单插件
 * 
 * Copyright (C) 2012-2012 oray.com
 * 
 * @author CuTe_CuBe
 */
KindEditor.plugin('alignmenu', function(K){
	var self = this,
		name = 'alignmenu';
	
	var alignType = {
		left: '左对齐',
		center: '居中',
		right: '右对齐',
		full: '两端对齐'
	};
	

	self.plugin.alignmenu = {
		isPop: false,
		
		menu: null,
			
		pop: function(e) {
			var menu = self.createMenu({
				width: 200,
				name: name
			});
			
			K.each(alignType, function(k, val) {
				menu.addItem({
					title: '<span _alignType="'+k+'">' + val + '</span>',
					click: function() {
						method = 'justify' + k;
						self.cmd[method]();
						self.hideMenu();
					}
				});
			});
		}
	};
	
	self.clickToolbar(name, self.plugin.alignmenu.pop);
});