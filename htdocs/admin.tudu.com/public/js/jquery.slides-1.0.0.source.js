/**
 * 图片滑动（切换）js插件
 * 
 * @author Oray-Yongfa
 * @version $Id: jquery.slides-1.0.0.source.js 1973 2012-07-06 05:33:16Z chenyongfa $
 */

var Slides = function(params) {
    this.init();
};

Slides.options = {
    container: 'slides_container', // string, Class name for slides container. Default is "slides_container"
    next: 'next', // string, Class name for next button
    prev: 'prev', // string, Class name for previous button
    pagination: true, // boolean, If you're not using pagination you can set to false, but don't have to
    paginationClass: 'pagination', // string, Class name for pagination
    currentClass: 'current', // string, Class name for current class
    fadeSpeed: 350, // number, Set the speed of the fading animation in milliseconds
    fadeEasing: '', // string, must load jQuery's easing plugin before http://gsgd.co.uk/sandbox/jquery/easing/
    slideSpeed: 350, // number, Set the speed of the sliding animation in milliseconds
    slideEasing: '', // string, must load jQuery's easing plugin before http://gsgd.co.uk/sandbox/jquery/easing/
    start: 0, // number, Set the speed of the sliding animation in milliseconds
    effect: 'slide', // string, '[next/prev], [pagination]', e.g. 'slide, fade' or simply 'fade' for both
    play: 0, // number, Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds
    pause: 0, // number, Pause slideshow on click of next/prev or pagination. A positive number will set to true and be the time of pause in milliseconds
    hoverPause: false, // boolean, Set to true and hovering over slideshow will pause it
    animationStart: function(){}, // Function called at the start of animation
    animationComplete: function(){}, // Function called at the completion of animation
    slidesLoaded: function() {}, // Function is called when slides is fully loaded
    isArrow: false,
    arrowId: '#arrow'
};

/**
 * 
 */
Slides.prototype = {
    
    settings: null,
    
    container: null,
    
    playInterval: null,
    
    pauseTimeout: null,
    
    current: 0,
    
    active: false,
    
    /**
     * 初始化
     */
    init: function(){},
    
    /**
     * 初始化图片切换主控制窗体元素
     */
    _initContainer: function() {
        var o = this,
            elem = $(this.settings.target);
        
        $('.' + this.settings.container, elem).children().wrapAll('<div class="slides_control"/>');
        
        this.container = $('.slides_control', elem);
        
        var width = this.container.children().outerWidth(),
            height = this.container.children().outerHeight();
        
        $('.' + this.settings.container, elem).css({
            overflow: 'hidden',
            position: 'relative'
        });
        
        this.container.children().css({
            position: 'absolute',
            top: 0, 
            left: this.container.children().outerWidth(),
            zIndex: 0,
            display: 'none'
         });
        
        this.container.css({
            position: 'relative',
            width: (width * 3),
            height: height,
            left: -width
        });
        
        $('.' + this.settings.container, elem).css({
            display: 'block'
        });
        
        this.container.children(':eq(' + this.settings.start + ')').fadeIn(this.settings.fadeSpeed, this.settings.fadeEasing);
        
        this.container.bind('mouseover',function(){
            if (o.settings.hoverPause) {
                o.stop();
            }
            if (o.settings.isArrow) {
                $(o.settings.arrowId).show();
            }
        });
        this.container.bind('mouseleave',function(){
            if (o.settings.hoverPause) {
                o.pause();
            }
            if (o.settings.isArrow) {
                $(o.settings.arrowId).hide();
            }
        });
    },
    
    /**
     * 导航点控制图片切换
     */
    _initPagination: function() {
        var o = this;
        $(this.settings.target).parent().append('<div class='+ this.settings.paginationClass +'></div>');
        var num = 0;
        this.container.children().each(function(){
            $('.' + o.settings.paginationClass).append('<a href="javascript:void(0)" _index="'+num+'"></a>');
            num ++;
        });
        
        $('.' + this.settings.paginationClass + ' a:eq('+ this.settings.start +')').addClass(this.settings.currentClass);
        
        this._initPaginationEvent();
    },
    
    _initPaginationEvent: function() {
        var o = this, 
            clicked = 0;
        $('.' + this.settings.paginationClass + ' a').bind('mouseover',function(){
            if (o.settings.play) {
                 o.stop();
            }           
            
            clicked = $(this).attr('_index');
            
            if (o.current != clicked) {
                o.animate('pagination', o.settings.effect, clicked);
            }
            return false;
        })
        .bind('mouseleave',function(){
            o.pause();
        });
    },
    
    /**
     * 图片悬浮左右控制
     */
    _initArrow: function() {
        $(this.settings.arrowId).bind('mouseover', function(){
            $(this).show();
        }).bind('mouseout', function(){
            $(this).hide();
        });
        
        this._initArrowEvent();
    },
    
    /**
     * 左右按钮事件
     */
    _initArrowEvent: function() {
        var o = this;
        
        $('.' + o.settings.next, $(this.settings.target)).click(function(e){
            e.preventDefault();
            if (o.settings.play) {
                o.pause();
            }
            o.animate('next', o.settings.effect);
        });
        
        $('.' + o.settings.prev, $(this.settings.target)).click(function(e){
            e.preventDefault();
            if (o.settings.play) {
                o.pause();
            }
            o.animate('prev', o.settings.effect);
        });
    },
    
    _initSlide: function() {
        var o = this;
        this.playInterval = setInterval(function() {
            o.animate('next', 'slide');
        }, o.settings.play);
        
        $(this.settings.target).data('interval', this.playInterval);
    },
    
    /**
     * 图片切换操作
     */
    animate: function(direction, effect, clicked) {
        if (!this.active) {
            var o = this, 
                prev = 0, 
                next = 0, 
                position = 0, 
                total = this.container.children().size(), 
                width = this.container.children().outerWidth(), 
                height = this.container.children().outerHeight();
            this.active = true;
            
            if (this.settings.start > total) {
                this.settings.start = total - 1;
            }
            
            switch (direction) {
                case 'next':
                    prev = this.current;
                    next = this.current + 1;
                    next = (total === next) ? 0 : next;
                    position = width * 2;
                    direction = -width * 2;
                    this.current = next;
                    break;
                case 'prev':
                    prev = this.current;
                    next = this.current - 1;
                    next = (next === -1) ? total - 1 : next;
                    position = 0;
                    direction = 0;
                    this.current = next;
                    break;
                case 'pagination':
                    next = parseInt(clicked, 10);
                    prev = $('.' + this.settings.paginationClass + ' a.' + this.settings.currentClass).attr('_index');
                    if (next > prev) {
                        position = width * 2;
                        direction = -width * 2;
                    }
                    else {
                        position = 0;
                        direction = 0;
                    }
                    this.current = next;
                    break;
            }
            
            this.container.children(':eq(' + next + ')').css({
                left: position,
                display: 'block'
            });
            
            this.container.animate({
                left: direction
            }, this.container.slideSpeed, this.container.slideEasing, function(){
            
                o.container.css({
                    left: -width
                });
                
                o.container.children(':eq(' + next + ')').css({
                    left: width,
                    zIndex: 5
                });
                
                o.container.children(':eq(' + prev + ')').css({
                    left: width,
                    display: 'none',
                    zIndex: 0
                });
                
                o.active = false;
            });
            
            if (this.settings.pagination) {
                $('.' + this.settings.paginationClass + ' a.' + this.settings.currentClass).removeClass(this.settings.currentClass);
                $('.' + this.settings.paginationClass + ' a:eq(' + next + ')').addClass(this.settings.currentClass);
            }
        }
    },
    
    /**
     * run
     */
    play: function(params) {
        this.settings = $.extend(Slides.options, params);
        
        this._initContainer();
        if (this.settings.isArrow) {
            this._initArrow();
        }
        if (this.settings.pagination) {
            this._initPagination();
        }
        
        this.current = this.settings.start;
        if (this.settings.effect == 'slide') {
            this._initSlide();
        }
    },
    
    /**
     * 暂停
     */
    stop: function() {
        clearInterval($(this.settings.target).data('interval'));
    },
    
    /**
     * 终止暂停，继续图片切换
     */
    pause: function() {
        var o = this;
        
        clearTimeout($(o.settings.target).data('pause'));
        clearInterval($(o.settings.target).data('interval'));
        
        this.pauseTimeout = setTimeout(function() {
            
            clearTimeout($(o.settings.target).data('pause'));
            
            o.playInterval = setInterval(function(){
                o.animate("next", o.settings.effect);
            }, o.settings.play);
            
            $(o.settings.target).data('interval', o.playInterval);
        }, o.settings.pause);
        
        $(this.settings.target).data('pause', this.pauseTimeout);
    },
    
    /**
     * 销毁
     */
    destroy: function() {
        var o = this;
        
        clearTimeout($(o.settings.target).data('pause'));
        clearInterval($(o.settings.target).data('interval'));
    }
};