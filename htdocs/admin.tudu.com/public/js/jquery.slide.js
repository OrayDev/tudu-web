/**
 * 图片滑动（切换）js插件
 * 
 * @author Cute_CuBe
 * @version $Id: jquery.slide.js 1387 2011-12-14 02:07:40Z web_op $
 */

(function($){

/**
 * @class $.slide
 * @constructor
 * @param {Object} params
 */
$.slide = function(params) {
	this._settings = $.extend($.slide.defaults, params);
	
	this.init();
};

/**
 * 
 */
$.slide.defaults = {
	parent: document.body,		// 父元素
	interval: 3000,				// 自动轮换间隔(小于等于0为不自动轮换)
	speed: 500,					// 动画播放时间（速度）
	subject: false,				// 是否显示标题
	showNum: true,				// 是否显示索引数字
	arrow: true,
	arrowPrevCls: 'arrow prev',
	arrowNextCls: 'arrow next',
	anime: 'fade',				// 动画类型 {'fade', 'slide'}
	direction: 'vertical',		// 滑动方向，仅对动画效果 slide 生效 {'horizontal', 'vertical'}
	rotation: false,
	itemListCls: 'slide-list',			// 滚动列表css class
	containerCls: 'app-info-img',			// 滚动容器css class
	numListCls: 'pagination',			// 数字列表css class
	numActiveCls: 'current',			// 激活数字css class
	width: 200,					// 滚动内容宽度
	height: 100,				// 滚动内容高度
	items: []					// 滚动项目
};

/**
 * 
 * @class $.slide
 * @prototype
 */
$.slide.prototype = {
	
	/**
	 * 
	 * @var {Object}
	 */
	_settings: null,
	
	/**
	 * 
	 * @var {Array}
	 */
	_items: [],
	
	/**
	 * 
	 * @var {Object}
	 */
	_timer: null,
	
	/**
	 * 
	 * @var int
	 */
	_current: null,
	
	/**
	 * 
	 * @var {string}
	 */
	_anime: null,
	
	/**
	 * 
	 * @var int
	 */
	_anPos: 0,
	
	_arrow: null,
	
	/**
	 * 
	 * @function
	 * @returns void
	 */
	init: function() {
		if (!this._container) {
			this._initContainer();
			
		}
		
		for (k in this._settings) {
			switch (k) {
				case 'parent':
					if (typeof this._settings[k] == 'object') {
						this._settings[k].append(this._container);
						this._settings[k].append(this._numList);
					} else {
						$(this._settings[k]).append(this._container);
					}
					break ;
				case 'id':
					this._container.attr('id', this._settings[k]);
					break ;
				case 'width':
				case 'height':
					var val = this._settings[k];
					if (val % 1 === 0) {
						val = val + 'px';
					}
					this._container.css(k, val);
					break;
				case 'containerCls':
					this._container.addClass(this._settings[k]);
					break;
				case 'itemListCls':
					this._itemList.addClass(this._settings[k]);
					break;
				case 'numListCls':
					if (this._numList) {
						this._numList.addClass(this._settings[k]);
					}
					break;
				case 'items':
					this._initItems(this._settings[k]);
					break;
				case 'anime':
					this._anime = $.slide.anime.factory(this._settings[k], this);
					break;
				case 'arrow' :
					if (this._settings[k]) {
						if (!this._arrow) {
							this._initArrow();
						}
					}
					break;
			}
		}
		
		this._resetAnime();
	},
	
	_initArrow: function() {
		var _this = this;
		this._arrow = $('<div>');
		
		var prev = $('<a>').attr('href', 'javascript:void(0)').addClass(this._settings['arrowPrevCls']);
		var next = $('<a>').attr('href', 'javascript:void(0)').addClass(this._settings['arrowNextCls']);
		
		prev.bind('mouseover', function(){
			if (_this._timer) {
				clearTimeout(_this._timer);
				_this._timer = null;
			}
			_this._arrow.show();
		})
		.bind('mouseout', function(){
			if (null === _this._timer && _this._settings.interval > 0) {
				var next = _this._items.length > _this._current + 1 ? _this._current + 1 : 0;
				_this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
			}
			_this._arrow.hide();
		})
		.bind('click', function(){
			var prevIdx = (_this._items.length > _this._current - 1 && _this._current - 1 >= 0) ? _this._current - 1 : _this._items.length - 1;
			_this.switchTo(prevIdx, true);
		});
		
		next.bind('mouseover', function(){
			if (_this._timer) {
				clearTimeout(_this._timer);
				_this._timer = null;
			}
			_this._arrow.show();
		})
		.bind('mouseout', function(){
			if (null === _this._timer && _this._settings.interval > 0) {
				var next = _this._items.length > _this._current + 1 ? _this._current + 1 : 0;
				_this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
			}
			_this._arrow.hide();
		})
		.bind('click', function(){
			var nextIdx = _this._items.length > _this._current + 1 ? _this._current + 1 : 0;
			_this.switchTo(nextIdx, true);
		});
		
		this._arrow.append(prev).append(next);
		this._arrow.hide();
		this._container.prepend(this._arrow);
	},
	
	/**
	 * 初始化滚动容器
	 * 
	 * @private
	 * @function
	 * @returns void
	 */
	_initContainer: function() {
		this._container = $('<div></div>');
		
		this._itemList = $('<ul>').css({padding: 0, margin: 0, 'list-style-type': 'none'});
		this._numList  = $('<div></div>');
		
		var parentDiv = $('<div>').css({
			'overflow': 'hidden',
			'position': 'relative',
			'width' : this._settings['width'],
			'height' : this._settings['height']
		});
		parentDiv.append(this._itemList);
		this._container.append(parentDiv);
	},
	
	/**
	 * 初始化内容项目
	 * 
	 * @private
	 * @function
	 * @returns void
	 */
	_initItems: function(items) {
		this._items = [];
		this._itemList.empty();
		if (this._numList) {
			this._numList.empty();
		}
		for (var i = 0, c = items.length; i < c; i++) {
			this.appendItem(items[i]);
		}
	},
	
	/**
	 * 重置动画
	 * 
	 * @private
	 * @function
	 * @returns void
	 */
	_resetAnime: function() {
		this._anime.init();
		
		this.switchTo(0);
	},
	
	/**
	 * 数字切换效果
	 * 
	 */
	_switchNumber: function(index) {
		if (this._numList) {
			this._numList.find('a').removeClass(this._settings.numActiveCls);
			this._numList.find('a:eq('+index+')').addClass(this._settings.numActiveCls);
		}
	},
	
	/**
	 * 添加数字
	 * 
	 */
	_appendNum: function(index) {
		if (this._numList) {
			var item = this._items[index],
				text = item.num ? item.num : index + 1,
				_this = this;
			
			var el = $('<li>').text(text);
			
			el.bind('mouseover', function(){
				if (index == _this.current) {
					clearTimeout(_this.timer);
					return ;
				}
				_this.switchTo(index, true);
			})
			.bind('mouseout', function(){
				if (_this._settings.interval) {
					var next = _this._items.length > index + 1 ? index + 1 : 0;
					_this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
				}
			});
			
			this._numList.append(el);
		}
	},
	
	
	_appendPagination: function(index) {
		if (this._numList) {
			var _this = this,
				el = $('<a>');
			el.attr('href', 'javascript:void(0)');
			el.bind('mouseover', function(){
				if (index == _this.current) {
					clearTimeout(_this.timer);
					return ;
				}
				_this.switchTo(index, true);
			})
			.bind('mouseout', function(){
				if (_this._settings.interval) {
					var next = _this._items.length > index + 1 ? index + 1 : 0;
					_this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
				}
			});
			
			this._numList.append(el);
		}
	},
	/**
	 * 
	 * @public
	 * @function
	 * @param index
	 * @param pause
	 * @returns {$.slide}
	 */
	switchTo: function(index, pause) {
		var _this = this;
		if (index == this._current) {
			return ;
		}
		
		if (this._timer) {
			clearTimeout(this._timer);
		}

		this._switchNumber(index);
		this._anime.play(index, function(){
			if (this._settings.interval > 0 && !pause) {
				var next = this._items.length > index + 1 ? index + 1 : 0;
				var _this = this;
				this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
			}
		});
		
		this._current = index;
	},
	
	/**
	 * 
	 * @public 
	 * @function
	 * @param item
	 * @returns {$.slide}
	 */
	appendItem: function(item) {
		if (null === item || !item.src) {
			return this;
		}
		
		var len = this._items.push(item),
			_this = this;
		
		var idx = len - 1,
		    el = $('<li>', {'_index': idx, }),
		    img = $('<img>', {src: item.src});
		
		img.css({'width': _this._settings['width'], 'height': _this._settings['height']});
		
		el.append(img)
		.bind('mouseover', function(){
			if (_this._timer) {
				clearTimeout(_this._timer);
				_this._timer = null;
			}
			_this._arrow.show();
		})
		.bind('mouseout', function(){
			if (null === _this._timer && _this._settings.interval > 0) {
				var next = _this._items.length > idx + 1 ? idx + 1 : 0;
				_this._timer = setTimeout(function(){_this.switchTo(next);}, _this._settings.interval);
			}
			_this._arrow.hide();
		});
		
		if (this._anime == 'fade') {
			el.css('position', 'absolute');
		}
		
		this._itemList.append(el);
		
		this._appendPagination(idx);
	},
	
	/**
	 * 获取索引单位元素
	 * 
	 * @returns {Object}
	 */
	getItem: function(index) {
		if (index < this._items.length) {
			return this._itemList.find('li[_index="'+index+'"]');
		}
		
		return null;
	},
	
	/**
	 * 获取当前显示元素
	 * 
	 * @returns {Object}
	 */
	getCurrentItem: function() {
		return this.getItem(this._current);
	}
};

/**
 * @class $.slide.anime
 */
$.slide.anime = {
	
	/**
	 * 
	 */
	factory: function(type, slide) {
		var ret = null;
	
		switch (type) {
			case 'slide':
				ret = new $.slide.anime.slide(slide);
				break;
			case 'fade':
			default:
				ret = new $.slide.anime.fade(slide);
				break;
		}
		
		return ret;
	}
};

/**
 * @class $.slide.anime.fade
 * @param slide
 */
$.slide.anime.fade = function(slide) {
	this._slide = slide;
	this.init();
};
/**
 * @class $.slide.anime.fade
 * @prototype
 */
$.slide.anime.fade.prototype = {
	
	/**
	 * 
	 * @var {$.slide}
	 */
	_slide: null,
	
	/**
	 * 
	 */
	_timer: null,
	
	init: function() {
		this._slide._itemList.find('li').css({'opacity': 0, 'position': 'absolute', 'z-index': 0});
	},
	
	/**
	 * @function
	 * @param target
	 * @param callback
	 * @return void
	 */
	play: function(target, callback) {
		var _this = this,
		other = this._slide._itemList.find('li[_index!="'+target+'"]'),
		next  = this._slide.getItem(target),
		curr = this._slide.getCurrentItem(),
		opacity = 0,
		step  = 30 / this._slide._settings.speed;

		if (this._timer) {
			clearInterval(this._timer);
		}
		
		this._slide._itemList.find('li').css({'z-index': 0});
		this._slide.getCurrentItem().css({'z-index': 1});
		this._slide._itemList.find('li[_index="'+target+'"]').css({'z-index': 2});
		
		this._timer = setInterval(function(){
			
			opacity += step;
			
			if (this._timer) {
				clearInterval(this._timer);
			}
			
			curr.css({opacity: 1 - opacity});
			next.css({opacity: opacity});
			
			if (opacity >= 1) {
				clearInterval(_this._timer);
				_this._slide._itemList.find('li[_index!="'+target+'"]').css({'opacity': 0});
				
				if (typeof(callback) == 'function') {
					callback.call(_this._slide);
				}
			}
		}, 30);
	}
};

/**
 * @class $.slide.anime.fade
 * @param slide
 */
$.slide.anime.slide = function(slide) {
	this._slide = slide;
};
/**
 * @class $.slide.anime.fade
 * @prototype
 */
$.slide.anime.slide.prototype = {
	
	/**
	 * 
	 * @var {$.slide}
	 */
	_slide: null,
	
	/**
	 * 
	 */
	_timer: null,
	
	/**
	 * 
	 */
	_attr: null,
	
	/**
	 * 运动方式
	 */
	_direction: null,
	
	/**
	 * 当前位置
	 * 
	 * 
	 */
	_pos: null,
	
	/**
	 * 
	 */
	_mark: null,
	
	init: function() {
		this._direction = this._slide._settings.direction;
		this._attr = this._direction == 'horizontal' ? 'margin-left' : 'margin-top';
		
		var num = this._slide._itemList.find('li').size(),
			width =  this._slide._settings['width'] * num;
		this._slide._itemList.css({'width': width});
		this._pos = 0;
		this._slide._itemList.css(this._attr, 0);
	},
	
	/**
	 * 
	 * @param target
	 * @param callback
	 * @return
	 */
	play: function(target, callback) {
		var _this = this,
		disPos = this._direction == 'horizontal' ? -target * this._slide._container.width() : -target * this._slide._container.height(),
	    toMove = 0,
		itvl   = 100 / this._slide._settings.speed;	// 缓冲因数

		if (this._timer) {
			clearInterval(this._timer);
		}
		
		this._timer = setInterval(function(){
			
			toMove = (disPos - _this._pos) * itvl;
			_this._pos += toMove;
			
			_this._slide._itemList.css(_this._attr, _this._pos);
			
			if (Math.abs(toMove) < 1) {
				clearInterval(_this._timer);
				_this._pos = disPos;
				_this._slide._itemList.css(_this._attr, _this._pos);
				
				if (typeof(callback) == 'function') {
					callback.call(_this._slide);
				}
			}
		}, 30);
	}
};

})(jQuery);