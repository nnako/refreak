
(function( $ ){
    
    $.boxes = function (options) {                          

            var me = this;
            
            var defaults = {
                message: "Message",
                onInit: null,
                onShow: null,
		onHide: null
            };
            
            this.settings = {};
            
            this.init = function( options ) {

                options = ((typeof options) === "string" ? {message: options} : options);

                me.settings = $.extend({}, defaults, options);

                me._executeCallBack('onInit', null);
		
		$(".infoMessage").trigger("refreak.boxes.init");
		
                me._createBox();
                
            };
                       
            this._executeCallBack = function(callbackName, args){
			if($.isFunction(me.settings[callbackName]))
				me.settings[callbackName].apply(null, args);
            };
            
            this._createBox = function() {
		
		me._executeCallBack("onShow", null);
		
                $(".content").before(
                    $("<div/>")
                        .addClass("infoMessage")
                        .html(me.settings.message)
                        .show(100)
			.trigger("refreak.boxes.show")
                        .delay(5000)
			.hide(500, function() {
			    me._destroy($(this));
			})
			.on("click", function () {
			    me._destroy($(this));
			})
                );
		
            };
            
	    this._destroy = function (ele) {
		
		ele.trigger("refreak.boxes.destroy");
		me._executeCallBack("onHide", null);
		ele.remove();
		
	    };
	    
            this.init( options );
    };
    
    
    $.call_ajax = function (options) {                          

            var me = this;
            
            var defaults = {
                type: "POST",
                dataType: "json",
                url: null,
                async: false,
                data: {},
		onInit: null,
                onDone: null,
                onDoneKo: null,
                onFail: null                
            };
            
            this.settings = {};
            
            this.init = function( options ) {

                options = ((typeof options) === "string" ? {message: options} : options);

                me.settings = $.extend({}, defaults, options);

                me._executeCallBack('onInit', null);
		
                me._callAjax();
                
            };
                       
            this._executeCallBack = function(callbackName, args){
			if($.isFunction(me.settings[callbackName]))
				me.settings[callbackName].apply(null, args);
            };
            
            this._callAjax = function() {
                
                    $.ajax({
                        type:       me.settings.type,
                        url:        me.settings.url,
                        dataType:   me.settings.dataType,
                        data:       me.settings.data,
                        async:      me.settings.async,
                    }).done(function(res) {
                        if (res.response === "rfk_ok") {
                            
                            me._executeCallBack('onDone', [res]);
                            
                        }
                        else {
                            
                            me._executeCallBack('onDoneKo', [res]);
                            $.boxes(genmessage_ajax_error_security);
                            
                        }

                    }).fail(function(res) {
                        
                        me._executeCallBack('onFail', [res]),
                        $.boxes(genmessage_ajax_error_server);
                        
                    });
                
            };
            
            this.init( options );
    };
    
    $.clock = function( options ) {

            var me = this;
            
            var clk_date = new Date();            

            var defaults = {
                class_name: ".userdate"
            };
            
            this.settings = {};
            

            this.show_date = function ( opt ) {
                    clk_date = new Date();
                    /**
                     * @todo reload page every hour???
                     */
                    str = clk_date.toLocaleString();
                    $(opt.class_name).html(str);
                    
            };

            this.init = function ( options ) {
                
                    options = ((typeof options) === "string" ? {message: options} : options);

                    me.settings = $.extend({}, defaults, options);
                
                    clk_date = new Date();                    
                    me.show_date( me.settings );
            };

            this.init( options );       
    };
    
    $.alert = function ( options ) {
	
	    var me = this;
	
	    var defaults = {
		message: "Default alert message"
	    };

	    this.settings = {};

	    this.init = function ( options ) {
                
                    options = ((typeof options) === "string" ? {message: options} : options);
                    me.settings = $.extend({}, defaults, options);
                
		    me.show_alert();
            };

	    this.show_alert = function() {
		    
		    alert(me.settings.message);
		    
	    };

            this.init( options );     
    };
    
    $.confirm = function ( options ) {
	    var me = this;
	
	    var defaults = {
		message: "Default confirm message"
	    };

	    this.settings = {};

	    this.init = function ( options ) {
                
                    options = ((typeof options) === "string" ? {message: options} : options);
                    me.settings = $.extend({}, defaults, options);
                
		    return me.show_confirm();
            };

	    this.show_confirm = function() {
		    
		    return confirm(me.settings.message);
		    
	    };

            return this.init( options );     
    };
    
    window.setInterval("$.clock( { class_name: '.userdate' } )",1000);
    
})( jQuery );