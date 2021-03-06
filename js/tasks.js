(function($) {
        
        var task_new = function(element, options){
		this.element = $(element);			
		this.init( options );
        };
        
        var task_show = function(element, options){
		this.element = $(element);			
		this.init( options );
        };
          
        var task_list = function(element, options){
		this.element = $(element);			
		this.init( options );
        };
          
        task_new.prototype = {
             
                options: null,
                element: null,

                init : function( options ) {

                        var me = this;
			var data = {};
			
                        if (options !== undefined)
                            this.options = options;
			
			this.element.trigger("refreak.task_new.init", this.options );
			
			data.tid = this.options.task_id;
			
			if  (this.options.task_parent_id !== undefined )
			    data.tpid = this.options.task_parent_id;
			
                        this.element.show();
           
                        $.ajax({
                            type:       "POST",
                            url:        s_url + "tasks/edit/",
                            data:       data,
                            dataType:   "html"
                        }).done(function(res) {

                                me.element.html(res);
                                me.element.trigger("refreak.task_new.render", [me.options, res] );
				
                                me._(".task_dead").datepicker({
                                        showOn: "button",
                                        buttonImage: theme_url + "/images/cal.gif",
                                        buttonImageOnly: true,
                                        dateFormat:   "dd/mm/yy"
                                });

                                me._(".task_projects").on("change", function() { me.load_users(this); });

                                me._(".task_edit_cancel").on("click", function() { me.close(this); });

                                me._(".task_edit_new_project").on("click", function() { me.show_input_project(this); });
                                me._(".task_edit_list_project").on("click", function() { me.show_list_project(this); });

                                me._(".task_edit_save").on("click", function() { me.send_data(this); });
                       
				me.element.trigger("refreak_task_new.bind", [me.options, res] );

                        }).fail(function(res) {
                                $.boxes(genmessage_ajax_error_server);
                                this.element.hide();
                        });

                },

                _: function(selector) {

                        return $(selector, this.element);

                }, 
                       
                load_users: function(obj) {
                        
                        var me = this;
            
                        $.call_ajax({
                            type:       "POST",
                            url:        s_url + "tasks/get_users_from_project/",
                            async:      true,
                            data:       {
                                            project_id: $(obj).val()
                                        },
                            onDone:     function(res) {
					    
					    me.element.trigger("refreak.task_new.load_users", [me.options, res] );
                                            var $select_users = me._(".task_users").empty();

                                            $.each(res.data, function(i,item) {
                                                $select_users.append( '<option value="'
                                                                     + item.id
                                                                     + '">'
                                                                     + item.first_name + " " + item.last_name
                                                                     + '</option>' ); 
                                            });
                                        }
                                
                        });
                },
                
                show_input_project: function () {
                    
                    this._(".project_sel").hide();
                    this._(".project_txt").show();
		    this.element.trigger("refreak.task_new.render_input_project", this.options );
                    
                },
                
                show_list_project: function () {
                    
                    this._(".project_txt").hide();
                    this._(".project_sel").show();
		    this.element.trigger("refreak.task_new.render_list_project", this.options );
                    
                },
                
                send_data: function(obj) {
                        
                        var me = this;
                        var $title_value = this._(".task_edit_title");
                        
                        if ($title_value.val() !== "") {
			    
			    me.element.trigger("refreak.task_new.pre_send_data", [me.options, this._(".task_edit_form").serialize()] );
			    
                            $.call_ajax({
                                type:       "POST",
                                url:        s_url + "tasks/save_task/",
                                data:       this._(".task_edit_form").serialize(),
                                onDone:     function(res) {
                                        
					me.element.trigger("refreak_task_new.send_data_done", [me.options, res] );
					
                                        if (res.tid > 0) {
                                            $.boxes(tasksmessage_updated);
                                            document.location.reload();
                                        }
                                        else {
                                            $.boxes(tasksmessage_created);
                                        } 
                                            
                                        me.close();
                                    }
                            });
                        }
                        else {
                            $title_value.css("border-color", "red");
                            $.boxes("title is required");
                        }
                    
                },
                
                close: function() {
                        
                        this._(".task_projects").off("change");
                       
                        this._(".task_edit_cancel").off("click");
                        
                        this._(".task_edit_new_project").off("click");
                        this._(".task_edit_list_project").off("click");
                       
                        this._(".task_edit_save").off('click');
                       
                        
                        $(this.element).html(
                                                $("<img>").attr("border","0")
                                                          .attr("src", theme_url + "/images/load.gif")
                                                          .addClass("loader")
                        
                        ).hide();
			this.element.trigger("refreak.task_new.close", this.options );
                        
                },
                
                destroy: function() {
                        this.close();
                }
         };
 
        task_show.prototype = {
             
                options:        {
                            tab: 0
                },
                element:        null,
                description:    true,
                comment:        false,
                history:        false,

                init : function( options ) {

                        var me = this;

                        if (options !== undefined)
                            this.options = $.extend({}, this.options, options);
                        
			this.element.trigger("refreak.task_show.init", this.options );
			
                        this.element.show();

                        $.ajax({
                            type:       "POST",
                            url:        s_url + "tasks/show/",
                            data:       { tid: this.options.task_id },
                            dataType:   "html"
                        }).done(function(res) {

                                me.element.html(res);
                                me.element.trigger("refreak.task_show.render", [me.options, res] );
				
                                me._(".task_show_close").on("click", function() { me.close(this); });
                                me._(".task_show_edit").on("click", function() { me.edit(this); });
                                me._(".task_show_delete").on("click", function() { me.del(this); });
                                me._(".task_show_new").on("click", function() { me.create(this); });
                                
                                me._(".veditsubmit").on("click", function() { me.send_comment(this); });
                                me._(".veditcancel").on("click", function() { me.cancel_comment(this); });
                                
                                me._(".tab").on("click", function () { me.tabs(this); } );
                                
                                switch (me.options.tab) {
                                    case 0:
                                        me.tabs( me._(".tab_desc") );
                                        break;
                                        
                                    case 1:
                                        me.tabs( me._(".tab_comm") );
                                        break;
                                        
                                    case 2:
                                        me.tabs( me._(".tab_hist") );
                                        break;
                                }
                                me.element.trigger("refreak.task_show.bind", [me.options, res] );

                        }).fail(function(res) {
                                $.boxes(tasksmessage_ajax_error_server);
                                me.element.hide();
                        });

                },

                _: function(selector) {

                        return $(selector, this.element);

                }, 

                edit: function() {
                        
			this.element.trigger("refreak.task_show.to_edit", this.options );
                        this.close();
                        $(this.element).show();
                        new task_new(this.element, { task_id: this.options.task_id });
                        
                },
		
                create: function() {
                        
			this.element.trigger("refreak.task_show.to_create", this.options );
                        this.close();
                        $(this.element).show();
                        
                        new task_new(this.element, { 
							task_id: 0,
							task_parent_id: this.options.task_id 
						    });
                },
                      
                del: function() {
                        
			var me = this; 
			
                        if ($.confirm(tasksmessage_delete)) {
                            
                            $row = $("tr[data-id='" + this.options.task_id + "']");
                            
			    me.element.trigger("refreak.task_show.pre_delete", me.options );
			    
                            $.call_ajax({
                                type:       "POST",
                                url:        s_url + "tasks/delete",
                                data:       { 
                                                tid: this.options.task_id
                                            },
                                onDone:     function(res) {
						    
						    me.element.trigger("refreak.task_show.deleted", [me.options, res] );
						    
                                                    $row.remove();
                                                    $.boxes(tasksmessage_deleted);
						    
                                            }
                                    
                            });
                                                    
                        }
                },

                tabs: function ( obj ) {
                        
                        var me = this;
                        
                        this._(".tab").removeClass("active");
                        
                        this._(".vmore").hide();
                        this._(".tabcontent_edit").hide();
                        
                        if (this._(obj).hasClass("tab_desc")) {
                            
				this.element.trigger("refreak.task_show.show_description", this.options );
                                if (!this.description) this._get_desc();                                
                                this._(".tab_description_content").show();
                                
                                
                        } else if (this._(obj).hasClass("tab_comm")) {
                            
				this.element.trigger("refreak.task_show.show_comments", this.options );
                                if (!this.comment) this._get_comm();
                                this._(".tab_comments_content").show();
                                
                        } else if (this._(obj).hasClass("tab_hist")) {
                            
				this.element.trigger("refreak.task_show.show_history", this.options );
                                if (!this.history) this._get_hist();
                                this._(".tab_history_content").show();
                                
                        }
                        
                        this._(obj).addClass("active");
                },

                _get_desc: function() {
            
                        var me = this;

                        $.call_ajax({
                            type:       "POST",
                            url:        s_url + "tasks/get_description/",
                            data:       { tid: this.options.task_id },
                            async:      false,
                            onDone:     function(res) {
					    
					    me.element.trigger("refreak.task_show.get_description", [ me.options, res ] );
                                            me._(".tab_description_content").append( res.description );

                                        }
                        });
           
                },
                 
                _get_comm: function() {
            
                        var me = this;
                        var comment_count = 0;
                        this._(".tab_comments_content").empty();

                        $.call_ajax({
                            type:       "POST",
                            url:        s_url + "tasks/get_comments/",
                            data:       { tid: this.options.task_id },
                            async:      false,
                            onDone:     function(res) {
                                            comment_count = res.comments.length;
                                            if (res.comments.length > 0) {

						    me.element.trigger("refreak.task_show.get_comments", [ me.options, res ] );
                                                    var $comms = me._(".tab_comments_content");

                                                    $.each(res.comments, function(key, value) {

                                                            $comm       = me._create_vaction( value );

                                                            $comms.append($comm);

                                                    });                                                                                    

                                            }
                                            else {

                                                    $no_comment     = $("<div>").addClass("vempty")
                                                                                .append("-no comment left yet-")
                                                                                .append(
                                                                                    $("<div>").addClass("vnewaction")
                                                                                              .append(
                                                                                                    $("<a>").addClass("vfirstcomment")
                                                                                                          .attr("href","#")
                                                                                                          .on("click", function () { me.show_edit_comment(this) })
                                                                                                          .html("post first comment")
                                                                                                )
                                                                                );
                                                    me._(".tab_comments_content").append( $no_comment );
                                            }

                                            me.comment = true;
                                        }
                                
                        });
                        
                        return comment_count;
                },
                                
                _get_hist: function() {
            
                        var me = this;

                        $.call_ajax({
                            type:       "POST",
                            url:        s_url + "tasks/get_history/",
                            data:       { tid: this.options.task_id },
                            async:      false,
                            onDone:     function(res) {

					    me.element.trigger("refreak.task_show.get_history", [ me.options, res ] );
                                            var $tr_header      = $("<tr>").append(
                                                                                    $("<th>").html("date")
                                                                           ).append(
                                                                                    $("<th>").html("user")
                                                                           ).append(
                                                                                    $("<th>").html("action")
                                                                           );

                                            var $table          = $("<table>").addClass("vhist")
                                                                              .append($tr_header);

                                            $.each(res.history, function(key, value) {

                                                    var $tr     = $("<tr>").append(
                                                                                    $("<td>").html( value.status_date )
                                                                           ).append(
                                                                                    $("<td>").html( value.first_name + ' ' + value.last_name )
                                                                           ).append(
                                                                                    $("<td>").html( value.status )
                                                                           );
                                                    $table.append($tr);
                                            });

                                            me._(".tab_history_content").append( $table );
                                            me.history = true;

                                    }
                        });
           
                },
                
                _create_vaction: function ( value ) {
                        var me = this;

                        var $vaction = $("<div>").addClass("vaction")
                                                 .append(
                                                        $("<a>").attr("href", "#")
                                                                .addClass("task_show_comm_new_comment")
                                                                .on("click", function () { me.show_edit_comment(); })
                                                                .html("new comment")
                                                 ).append(" | ").append(
                                                        $("<a>").attr("href", "#")
                                                                .addClass("task_show_comm_edit_comment")
                                                                .attr("data-id", value.task_comment_id)
                                                                .on("click", function () { me._edit_comment( value.task_comment_id, value.comment ); })
                                                                .html("edit")
                                                 ).append(" | ").append(
                                                        $("<a>").attr("href", "#")
                                                                .addClass("task_show_comm_delete")
                                                                .attr("data-id", value.task_comment_id)
                                                                .on("click", function () { me._delete_comment( value.task_comment_id ); })
                                                                .html("delete")
                                                 );                                          

                        var $header = $("<div>").addClass("vheader")
                                                .html(value.post_date + " by " + value.first_name + " " + value.last_name )
                                                .append($vaction);

                        var $body   = $("<div>").addClass("vbody")
                                                .html( value.comment );

                        var $comm   = $("<div>").addClass("vcomm")
                                                .append($header)
                                                .append($body);

                        return $comm;

                },
                        
                show_edit_comment: function() {
                        
                        this._(".vmore").hide();
                        this._(".tabcontent_edit").show();
                        
                }, 
                
                cancel_comment: function () {
            
                        this._(".tab_comments_content").show();
                        this._(".tabcontent_edit").hide();
                        
                },

                _edit_comment: function ( task_comment_id, comment ) {
            
                        this._(".veditid").val(task_comment_id);
                        this._(".veditbody").val(comment);
			
			this.element.trigger("refreak.task_show.edit_comment", [ this.options, task_comment_id, comment ] );
			
                        this.show_edit_comment();
                },

                _delete_comment: function ( task_comment_id ) {
            
                        var me = this;
                        
                        if ($.confirm(tasksmessage_delete_comment)) {
                        
			    me.element.trigger("refreak.task_show.pre_delete_comment", [ me.options, task_comment_id ] );
			    
                            $.call_ajax({
                                type:       "POST",
                                url:        s_url + "tasks/delete_comment",
                                data:       { 
                                                tcid: task_comment_id,
                                                tid: me.options.task_id
                                            },
                                async:      false,
                                onDone:     function(res) {

						me.element.trigger("refreak.task_show.deleted_comment", [ me.options, task_comment_id ] );
						
                                                me._update_comment_count( 
                                                            me._get_comm() 
                                                );
                                                me.tabs(".tab_comm");                                   

                                            }                               
                            });
                        }

                },

                send_comment: function ( obj ) {
                    
                        var me = this;
                        var comment_id = this._(".veditid").val();
                        var comment = this._(".veditbody").val();                                               
                        
                        $.call_ajax({
                                type:       "POST",
                                url:        s_url + "tasks/save_comment",
                                data:       { 
                                                tid: this.options.task_id ,
                                                tcid: comment_id,
                                                comment: comment
                                            },
                                async:      false,
                                onDone:     function (res) {
						    me.element.trigger("refreak.task_show.send_comment", [ me.options, res, comment_id, comment ] );
						    
                                                    me._(".veditbody").val("");
                                                    me._(".veditid").val("0");
                                                    me._update_comment_count( 
                                                            me._get_comm() 
                                                    );
                                                    me.tabs(".tab_comm");
                                            }
                        });

                        
                },

                _update_comment_count: function (comment_count) {
                        
                        $row = $("tr[data-id='" + this.options.task_id + "']");
                        $cc = $row.find(".comment_count");
                        $cc.html(comment_count);
                        
                },
                        
                close: function() {
                        
                        this._(".task_show_close").off('click');
                        this._(".task_show_edit").off('click');
                        this._(".task_show_delete").off('click');

                        this._(".veditsubmit").off("click");
                        this._(".veditcancel").off("click");
                    
                        this._(".tab").off("click");
                        this._(".vfirstcomment").off("click");
                        $(this.element).html(
                                                $("<img>").attr("border","0")
                                                          .attr("src", theme_url + "/images/load.gif")
                                                          .addClass("loader")
                        
                        ).hide();
		
			this.element.trigger("refreak.task_show.close", this.options );
                        
                },
                
                destroy: function() {
                        this.close();
                }
         };
 
         task_list.prototype = {
             
                options: null,
                element: null,

                init : function( options ) {

                        var me = this;

                        if (options !== undefined)
                            this.options = options;
                        
                        this.element.trigger("refreak.task_list.init", this.options );
			
                        this.element.tablesorter({
                                0: { 
                                    sorter: false 
                                }, 
                                1: { 
                                    sorter: false 
                                }, 
                                7: { 
                                    sorter: false 
                                }, 
                                8: { 
                                    sorter: false 
                                },
                                cssHeader: "table_headers"
                        });


                        this._(".btn_task_new").on("click", function( event ) {

                                //prevent call tr event
                                event.stopPropagation();
                                // 0 means new task
                                me.edittask(0);

                        });
			
                        this._(".btn_subtask_new").on("click", function( event ) {

                                //prevent call tr event
                                event.stopPropagation();
                                
				var task_id = $(this).parents("tr").attr("data-id");
                                me.createsubtask( task_id );

                        });

                        this._(".btn_task_edit").on("click", function( event ) {

                                //prevent call tr event
                                event.stopPropagation();

                                var task_id = $(this).parents("tr").attr("data-id");
                                me.edittask( task_id );

                        });

                        this._(".btn_task_delete").on("click", function( event ) {

                                //prevent call tr event
                                event.stopPropagation();

                                me.deletetask( this );

                        });

                        this._(".status0, .status1, .status2, .status3, .status4").on("click", function ( event ) {
                        
                                event.stopPropagation();
                                
                                me.changestatus( this );

                        });
                        
                        this._("tbody").on("click", "tr:not('.nothanks')", function (  ) {

                                me.showtask( this, {} );

                        });                                               
                        
                        this._(".comment_link").on("click", function ( event ) {
                                
                                event.stopPropagation();
                                
                                me.showtask( $(this).parents("tr"), {
                                    tab: 1
                                } );
                                
                        });
                },
                        
                _: function(selector) {

                        return $(selector, this.element);

                },
                        
                showtask: function ( obj, options ) {
            
                        var tasks = {
                            task_id: $(obj).attr("data-id")
                        }

                        var settings = $.extend({}, tasks, options);

			this.element.trigger("refreak.task_list.showtask", this.options );

                        $('.task_panel').showTask( settings );

                },
                        
                edittask: function (task_id) {
                
			this.element.trigger("refreak.task_list.edittask", this.options );
			
                        $(".task_panel").newTask({ task_id: task_id });

                },
		
                createsubtask: function (parent_task_id) {
                
			this.element.trigger("refreak.task_list.create_subtask", this.options );
			
                        $(".task_panel").newTask({ 
						    task_id: 0, 
						    task_parent_id: parent_task_id,
						 });

                },
                
                deletetask: function ( obj ) {
			
			var me = this; 
                        var task_id = this._(obj).parents("tr").attr("data-id");
                        var row = this._(obj).parents("tr");
			
			this.element.trigger("refreak.task_list.pre_delete", [ this.options, task_id ] );
			
                        if ($.confirm(tasksmessage_delete)) {
                            
                            $.call_ajax({
                                type:       "POST",
                                url:        s_url + "tasks/delete",
                                data:       { 
                                                tid: task_id
                                            },
                                onDone:     function(res) {

						    me.element.trigger("refreak.task_list.deleted", [ this.options, res, task_id ] );
                                                    row.remove();
                                                    $.boxes(tasksmessage_deleted);
                                            }
                                    
                            });
                                                    
                        }

                },
                
                changestatus: function ( obj ) {
                    
			    var me= this;
                            var task_id = this._(obj).parents("tr").attr("data-id");
                            var row = this._(obj).parents("tr");
                            var status  = 1;

                            if ( this._(obj).hasClass("status1") ) {
                                status = 2;                            
                            } else if( this._(obj).hasClass("status2") ) {
                                status = 3;                            
                            } else if( this._(obj).hasClass("status3") ) {
                                status = 4;                                                        
                            } else if( this._(obj).hasClass("status4") ) {
                                status = 5;                            
                            } 

			    this.element.trigger("refreak.task_list.status_changing", [ this.options, task_id, status ] );

                            if (status < maximum_status || $.confirm(task_list_close_task)) {
    
                                for (var st = 0; st<maximum_status; st++) {
                                    $(".status" + st, row).removeClass().addClass("status" + st);

                                    if (st<status)
                                        $(".status" + st, row).addClass("sts" + ((4-st) + 1));
                                    else
                                        $(".status" + st, row).addClass("sts0");

                                }

                                $.call_ajax({
                                    type:       "POST",
                                    url:        s_url + "tasks/change_status",
                                    data:       { 
                                                    tid: task_id ,
                                                    status: status
                                                },
                                    onDone:     function(res) {

						    me.element.trigger("refreak.task_list.status_changed", [ me.options, task_id, status ] );
                                                    if (status === maximum_status)
                                                        row.remove();

                                                }
                                });
                                
                            }
                                            
                },
                
                destroy: function() {
                        
                        this._('.btn_task_new').off('click');
                        this._('.btn_task_edit').off('click');
                        this._("tbody").off("click", "tr");
                        this._(".status0, .status1, .status2, .status3, .status4").off("click");
                        this._(".comment_link").off("click");
			this.element.trigger("refreak.task_list.close", this.options );
			
                }
         };
 
         $.fn.newTask = function( options ) {
              
              $(this).each(function(){ 
                  $(this).data('newTask', new task_new(this, options)); 
              });

              return this;
              
         };
        
         $.fn.showTask = function( options ) {
              
              $(this).each(function(){ 
                  $(this).data('showTask', new task_show(this, options)); 
              });

              return this;
              
         };
        
         $.fn.listTask = function( options ) {
              
              $(this).each(function(){ 
                  $(this).data('listTask', new task_list(this, options)); 
              });

              return this;
              
         };
        
})(jQuery);