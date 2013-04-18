/**
 * KindEditor 有/无序列表下拉菜单插件
 * 
 * Copyright (C) 2012-2012 oray.com
 * 
 * @author CuTe_CuBe
 */
KindEditor.plugin('listmenu', function(K){
	var self = this,
		name = 'listmenu';
	
	var listType = {
		orderedlist: '有序列表',
		unorderedlist: '无序列表'
	};
	
	self.plugin.listmenu = {
		isPop: false,
		
		menu: null,
			
		pop: function(e) {
			var menu = self.createMenu({
				width: 180,
				name: name
			});
			
			K.each(listType, function(k, val) {
				menu.addItem({
					title: '<span _alignType="'+k+'">' + val + '</span>',
					click: function() {
						method = 'insert' + k;
						self.cmd[method]();
						self.hideMenu();
					}
				});
			});
		},
	};
	
	self.clickToolbar(name, self.plugin.listmenu.pop);
});