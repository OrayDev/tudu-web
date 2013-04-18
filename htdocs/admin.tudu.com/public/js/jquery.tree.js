(function($) {
$.tree = function(config) {
	config = config || {};
	
	for (var k in config) {
		this[k] = config[k];
	}
	
	this.init();
};

$.tree.prototype = {
	
	ele: null,
	
	/**
	 * 
	 */
	id: null,
	
	/**
	 * 是否异步加载
	 */
	isAsync: false,
	
	/**
	 * 子级目录标识
	 */
	childrenKey: 'children',
	
	/**
	 * 唯一标识字段
	 */
	idKey: 'id',
	
	/**
	 * 唯一标识前缀
	 */
	idPrefix: 'tree-',
	
	/**
	 * css class前缀
	 */
	baseCls: 'tree',
	
	/**
	 * 第一层节点
	 */
	nodes: null,
	
	/**
	 * 
	 */
	init: function() {
		this.ele = $('<div>');
		this.ele.addClass(this.baseCls);
		this.nodeList = $('<ul>');
		this.nodeList.addClass(this.baseCls + '-root');
		
		this.ele.append(this.nodeList);
		
		if (this.id) {
			this.ele.attr('id', this.id);
		}
		
		if (this.cls) {
			this.ele.addClass(this.cls);
		}
		
		this.nodes = [];
	},
	
	/**
	 * 获取节点
	 * 
	 * @return
	 */
	getNode: function(id, isDeep, key) {
		if (!key) key = this.idKey;
		var ret = null;
		for (var i = 0, c = this.nodes.length; i < c; i++) {
			if (this.nodes[i].get(key) == id) {
				return this.nodes[i];
			}
			
			if (isDeep) {
				ret = this.nodes[i].find(id, true, key);
				
				if (ret != null) {
					return ret;
				}
			}
		}
		
		return null;
	},
	
	/**
	 * getNode 别名
	 * 
	 * @return
	 */
	find: function(id, isDeep, key) {
		return this.getNode(id, isDeep, key);
	},
	
	/**
	 * 搜索
	 */
	search: function(condition) {
		var ret = [];
		for (var i = 0, c = this.nodes.length; i < c; i++) {
			var matches = 0, total = 0;
			for (var k in condition) {
				total ++;
				if (condition[k] == this.nodes[i].get(k)) {
					matches ++;
				}
			}
			
			if (matches > 0 && matches == total) {
				ret.push(this.nodes[i]);
			}
			
			var cs = this.nodes[i].search(condition);
			for (var k = 0, l = cs.length; k < l; k++) {
				ret.push(cs[k]);
			}
		}
		
		return ret;
	},
	
	appendTo: function(ct) {
		this.ele.appendTo(ct);
		
		return this;
	},
	
	/**
	 * 添加节点
	 * 
	 * @param node
	 * @return
	 */
	appendNode: function(node) {
		this.nodes.push(node);
		node.setTree(this);
		this.nodeList.append(node.ele);
		
		return this;
	},
	
	/**
	 * 删除节点
	 */
	removeNode: function(id) {
		var node = this.find(id, true);
		
		if (node) {
			node.remove();
		}
		
		return this;
	},
	
	/**
	 * 清除节点
	 * @return
	 */
	clear: function() {
		for (var i in this.nodes) {
			this.nodes[i].remove();
			delete this.nodes[i];
		}
		
		this.nodes = [];
	}
};

$.treenode = function(config) {
	config = config || {};
	
	for (var k in config) {
		this[k] = config[k];
	}
	
	this._children = [];
	
	this.event = {};
	
	this.init();
};

$.treenode.prototype = {
	
	ele: null,
	
	nodeBody: null,
	
	/**
	 * 
	 */
	id: null,
	
	/**
	 * 子节点
	 */
	_childList: null,
	
	/**
	 * 树
	 */
	tree: null,
	
	/**
	 * 数据
	 */
	data: {},
	
	/**
	 * 父节点
	 */
	parent: null,
	
	/**
	 * 深度
	 */
	depth: 0,
	
	/**
	 * 是否叶节点
	 */
	isLeave: false,
	
	/**
	 * 
	 */
	event: null,
	
	autoEcIcon: true,
	
	/**
	 * 初始化
	 * 
	 * @return
	 */
	init: function() {
		var instance = this;
		
		if (!this.tree) return ;
		
        this.ele = $('<li>');
        this.ele.addClass(this.tree.baseCls + '-node');
        this.nodeBody = $('<div>');
        this.nodeBody.addClass(this.tree.baseCls + '-node-el');
        
        this.indent = $('<span>');
        this.indent.addClass(this.tree.baseCls + '-node-indent');
        
        for (var i = 0; i < this.depth; i++) {
        	this.indent.append('<span></span>');
        }
        this.nodeBody.append(this.indent);
        
        if (!this.isLeaf) {
        	this.nodeBody.append('<span class="'+this.tree.baseCls+'-ec-icon '+this.tree.baseCls+'-elbow-plus"></span>');
        	this.nodeBody.find('.' + this.tree.baseCls + '-ec-icon')
        	.click(function(e){
        		instance.toggle();
        		
        		e.cancelBubble = true;
        		if (e.stopPropagation) {
        			e.stopPropagation();
        		}
        	})
        	
        	if (this.autoEcIcon) {
        		this.nodeBody.find('.' + this.tree.baseCls + '-ec-icon').css({'visibility': 'hidden'});
        	}
        } else {
        	this.ele.addClass(this.tree.baseCls + '-node-leaf');
        	this.nodeBody.append('<span class="'+this.tree.baseCls+'-ec-icon"></span>');
        	//this.indent.append('<span></span>');
        }
        
        if (this.tree.template) {
        	this.content = this.tree.template;
        }
        
        content = this._formatEl(this.content);
        
        this._childList = $('<ul>');
        this._childList.hide();
        
        if (this.events) {
        	for (var key in this.events) {
        		this.ele.bind(key, this.events[key]);
        	}
        }
        
        this.nodeBody.append(content);
        this.ele.append(this.nodeBody);
        this.ele.append(this._childList);
        
        if (this.data[this.tree.idKey] != undefined) {
        	this.ele.attr('id', this.tree.idPrefix + this.data[this.tree.idKey]);
        }
        
        
	},
	
	/**
	 * 添加子节点
	 * 
	 * @param node
	 * @return
	 */
	appendChild: function(node) {
		this._children.push(node);
		node.parent = this;
		node.depth = this.depth + 1;
		node.setTree(this.tree);
		this._childList.append(node.ele);
		
		this.nodeBody.find('.' + this.tree.baseCls + '-ec-icon').css({'visibility': 'visible'});
	},
	
	/**
	 * 在指定节点前插入节点
	 * 
	 * @param id
	 * @param node
	 * @return
	 */
	insertBefore: function(id, node) {
		if (!node) return ;
		var next = this.find(id);
		
		if (!next) {
			this.appendChild(node);
		}
		
		this._childList.before(next.ele);
		node.parent = this;
		node.depth = this.depth + 1;
		this._children.push(node);
		node.setTree(this.tree);
	},
	
	/**
	 * 移除子节点
	 * 
	 * @param id
	 * @return
	 */
	removeChild: function(id) {
		var node = this.find(id);
		
		if (node != null) {
			node.remove();
		}
	},
	
	/**
	 * 查找子节点
	 * 
	 * @param id
	 * @return
	 */
	find: function(id, isDeep, key) {
		if (!this._children.length) return null;
		if (!key) key = this.tree.idKey;
		
		var ret = null;
		for (var i = 0, c = this._children.length; i < c; i++) {
			if (this._children[i].get(key) == id) {
				return this._children[i];
			}
			
			if (isDeep) {
				ret = this._children[i].find(id, true, key);
				
				if (ret != null) {
					return ret;
				}
			}
		}
		
		return null;
	},
	
	/**
	 * 搜索
	 */
	search: function(condition) {
		var ret = [];
		for (var i = 0, c = this._children.length; i < c; i++) {
			var matches = 0, total = 0;
			for (var k in condition) {
				total ++;
				if (this._children[i] && condition[k] == this._children[i].get(k)) {
					matches ++;
				}
			}
			
			if (matches > 0 && matches == total) {
				ret.push(this._children[i]);
			}
			
			var cs = this._children[i].search(condition);
			for (var k = 0, l = cs.length; k < l; k++) {
				ret.push(cs[k]);
			}
		}
		
		return ret;
	},
	
	/**
	 * 查找子节点
	 */
	getChildren: function(deep) {
		var children = [];
		
		for (var i = 0, c = this._children.length; i < c; i++) {
			if (!this._children[i]) {
				continue ;
			}
			
			children.push(this._children[i]);
			
			if (deep) {
				var cc = this._children[i].getChildren(true);
				for (var k = 0, l = cc.length; k < l; k++) {
					children.push(cc);
				}
			}
		}
		
		return children;
	},
	
	/**
	 * 移除本节点
	 * 
	 * @return
	 */
	remove: function() {
		this.ele.remove();
	},
	
	/**
	 * 
	 */
	hide: function() {
		this.ele.hide();
	},
	
	/**
	 * 
	 */
	show: function() {
		this.ele.show();
	},
	
	isShow: function() {
		return this.ele.is(':visible');
	},
	
	/**
	 * 获取节点数据节
	 * 
	 * @param key
	 * @return
	 */
	get: function(key) {
		return this.data[key] != undefined ? this.data[key] : null;
	},
	
	/**
	 * 获取数据
	 * 
	 * @return
	 */
	getData: function() {
		return this.data;
	},
	
	/**
	 * 
	 * @param tree
	 * @return
	 */
	setTree: function(tree) {
		var reInit = (this.tree != tree);
		this.tree = tree;
		this.baseCls = this.tree.baseCls;
		
		if (reInit) {
			this.init();
		}
	},
	
	/**
	 * 展开
	 * 
	 * @return
	 */
	expand: function() {
		this.isExpanded = true;
		this.ele.addClass(this.baseCls + '-node-expand');
		this.nodeBody.find('.' + this.baseCls + '-ec-icon')
		.removeClass(this.baseCls + '-elbow-plus')
		.addClass(this.baseCls + '-elbow-minus');
		
		if (this.parent) {
			this.parent.expand();
		}
		
		this._childList.show();
		
		this.triggerEvent('expand');
	},
	
	/**
	 * 收缩
	 * 
	 * @return
	 */
	collspan: function() {
		delete this.isExpanded;
		this.ele.removeClass(this.baseCls + '-node-expand');
		this.nodeBody.find('.' + this.baseCls + '-ec-icon')
		.removeClass(this.baseCls + '-elbow-minus')
		.addClass(this.baseCls + '-elbow-plus');
		this._childList.hide();
		
		this.triggerEvent('collspan');
	},
	
	/**
	 * 
	 * @return
	 */
	toggle: function() {
		if (this.isExpanded) {
			this.collspan();
		} else {
			this.expand();
		}
	},
	
	/**
	 * 
	 * @return
	 */
	select: function() {
		this.ele.addClass(this.baseCls + '-node-selected');
	},
	
	/**
	 * 
	 * @return
	 */
	unselect: function() {
		this.ele.removeClass(this.baseCls + '-node-selected');
	},
	
	/**
	 * 
	 */
	bind: function(evt, callback) {
		if (evt == 'expand' || evt == 'collspan') {
			if (typeof (callback) == 'function') {
				if (undefined == this.event[evt]) {
					this.event[evt] = [];
				}
				
				this.event[evt].push(callback);
			}
		}
	},
	
	/**
	 * 
	 */
	unbind: function(evt) {
		undefined === this.event[evt] || delete this.event[evt];
	},
	
	/**
	 * 
	 */
	triggerEvent: function(evt) {
		if (undefined === this.event[evt]) {
			return ;
		}
		
		for (var i = 0, c = this.event[evt].length; i < c; i++) {
			if (typeof (this.event[evt][i]) == 'function') {
				this.event[evt][i].call(this);
			}
		}
	},
	
	/**
	 * 
	 * @param str
	 * @return
	 */
	_formatEl: function(str) {
		var R = new RegExp('\{([^\}]+)\}', 'g');
		var m = null, v = null, s = [];
		
		while (m = R.exec(str)) {
			s.push(m);
		}
		
		for (var i = 0, c = s.length; i < c; i++) {
			v = this.get(s[i][1]);
			v = v == null ? '' : v;
			str = str.replace(s[i][0], v);
		}
		
		return str;
	}
};
})(jQuery);