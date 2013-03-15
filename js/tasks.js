(function($) {
        
        var task_new = function(element, options){
		this.element = $(element);			
		this.init( options );
        };
        
        var task_show = function(element, options){
		this.element = $(element);			
		this.init( options );
        };
          
        task_new.prototype = {
             
                options: null,
                element: null,

                init : function( options ) {

                        var me = this;

                        if (options !== undefined)
                            this.options = options;

                        this.element.show();
           
                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/edit/",
                            data:       { tid: this.options.task_id },
                            dataType:   "html"
                        }).done(function(res) {

                                me.element.html(res);
                                
                                me._(".task_dead").datepicker({
                                        showOn: "button",
                                        buttonImage: s_url + "/theme/default/images/cal.gif",
                                        buttonImageOnly: true,
                                        dateFormat:   "dd/mm/yy"
                                });

                                me._(".task_projects").on("change", function() { me.load_users(this); });

                                me._(".task_edit_cancel").on("click", function() { me.close(this); });

                                me._(".task_edit_new_project").on("click", function() { me.show_input_project(this); });
                                me._(".task_edit_list_project").on("click", function() { me.show_list_project(this); });

                                me._(".task_edit_save").on("click", function() { me.send_data(this); });
                       

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                                this.element.hide();
                        });

                },

                _: function(selector) {

                        return $(selector, this.element);

                }, 
                       
                load_users: function(obj) {
                        
                        var me = this;
            
                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/get_users_from_project/",
                            data:       {
                                            project_id: $(obj).val()
                                        }
                        }).done(function(res) {
                                if (res.response === "rfk_ok") {
                                    var $select_users = me._(".task_users").empty();

                                    $.each(res.data, function(i,item) {
                                        $select_users.append( '<option value="'
                                                             + item.id
                                                             + '">'
                                                             + item.first_name + " " + item.last_name
                                                             + '</option>' ); 
                                    });
                                }
                                else {
                                    alert(tasksmessage_ajax_error_security);
                                }

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                        });
                },
                
                show_input_project: function () {
                    
                    this._(".project_sel").hide();
                    this._(".project_txt").show();
                    
                },
                
                show_list_project: function () {
                    
                    this._(".project_txt").hide();
                    this._(".project_sel").show();
                    
                },
                
                send_data: function(obj) {
                        
                        var me = this;
                        var $title_value = this._(".task_edit_title");
                        
                        if ($title_value.val() != "") {

                            $.ajax({
                                type:       "POST",
                                url:        s_url + "/tasks/save_task/",
                                data:       this._(".task_edit_form").serialize()

                            }).done(function(res) {
                                    if (res.response === "rfk_ok") {
                                        
                                        if (res.tid > 0) {
                                            $.boxes(tasksmessage_updated);
                                        }
                                        else {
                                            $.boxes(tasksmessage_created);
                                        } 
                                            
                                        me.close();
                                    }
                                    else {
                                        alert(tasksmessage_ajax_error_security);
                                    }

                            }).fail(function(res) {
                                    alert(tasksmessage_ajax_error_server);
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
                                                          .attr("src", s_url + "theme/default/images/load.gif")
                                                          .addClass("loader")
                        
                        ).hide();
                        
                },
                
                destroy: function() {
                        this.close();
                }
         }
 
        task_show.prototype = {
             
                options:        null,
                element:        null,
                description:    true,
                comment:        false,
                history:        false,

                init : function( options ) {

                        var me = this;

                        if (options !== undefined)
                            this.options = options;
                        
                        this.element.show();

                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/show/",
                            data:       { tid: this.options.task_id },
                            dataType:   "html"
                        }).done(function(res) {

                                me.element.html(res);
                                
                                me._(".task_show_close").on("click", function() { me.close(this); });
                                me._(".task_show_edit").on("click", function() { me.edit(this); });
                                me._(".task_show_delete").on("click", function() { me.delete(this); });
                                
                                me._(".veditsubmit").on("click", function() { me.send_comment(this); });
                                me._(".veditcancel").on("click", function() { me.cancel_comment(this); });
                                
                                me._(".tab").on("click", function () { me.tabs(this); } );
                                
                                me.tabs( me._(".tab_desc") );

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                                me.element.hide();
                        });

                },

                _: function(selector) {

                        return $(selector, this.element);

                }, 

                edit: function() {
                        
                        this.close();
                        $(this.element).show();
                        new task_new(this.element, { task_id: this.options.task_id });
                        
                },

                tabs: function ( obj ) {
                        
                        var me = this;
                        
                        this._(".tab").removeClass("active");
                        
                        this._(".vmore").hide();
                        this._(".tabcontent_edit").hide();
                        
                        if (this._(obj).hasClass("tab_desc")) {
                            
                                if (!this.description) this._get_desc();                                
                                this._(".tab_description_content").show();
                                
                                
                        } else if (this._(obj).hasClass("tab_comm")) {
                            
                                if (!this.comment) this._get_comm();
                                this._(".tab_comments_content").show();
                                
                        } else if (this._(obj).hasClass("tab_hist")) {
                            
                                if (!this.history) this._get_hist();
                                this._(".tab_history_content").show();
                                
                        }
                        
                        this._(obj).addClass("active");
                },

                _get_desc: function() {
            
                        var me = this;

                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/get_description/",
                            data:       { tid: this.options.task_id },
                            async:      false
                        }).done(function(res) {

                                if (res.response === "rfk_ok") {

                                    me._(".tab_description_content").append( res.description );

                                }
                                else {
                                    alert(tasksmessage_ajax_error_security);
                                }

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                        });
           
                },
                 
                _get_comm: function() {
            
                        var me = this;

                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/get_comments/",
                            data:       { tid: this.options.task_id },
                            async:      false
                        }).done(function(res) {

                                if (res.response === "rfk_ok") {

                                    if (res.comments.length > 0) {
                                        
                                            var $comms = "";
                                            
                                            $.each(res.comments, function(key, value) {

                                                    $comm       = me._create_vaction( value );

                                                    if ($comms == "")
                                                        $comms = $().add($comm);
                                                    else
                                                        $comms.add($comm);

                                            });
                                        
                                            me._(".tab_comments_content").append( $comms );
                                            
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
                                else {
                                    alert(tasksmessage_ajax_error_security);
                                }

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                        });
           
                },
                                
                _get_hist: function() {
            
                        var me = this;

                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/get_history/",
                            data:       { tid: this.options.task_id },
                            async:      false
                        }).done(function(res) {

                                if (res.response === "rfk_ok") {

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
                                else {
                                    alert(tasksmessage_ajax_error_security);
                                }

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
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

                _edit_comment: function ( task_id, comment ) {
            
                        this._(".veditbody").val(comment);
            
                        this.show_edit_comment();
                },

                send_comment: function ( obj ) {
                    
                        var me = this;
                        var comment_id = this._(".veditid").val();
                        
                        $.ajax({
                            type:       "POST",
                            url:        s_url + "/tasks/save_comment",
                            data:       { 
                                            tid: this.options.task_id ,
                                            cid: comment_id
                                        },
                            async:      false
                        }).done(function(res) {

                                if (res.response === "rfk_ok") {

                                    me.tabs(".tab_comm");

                                }
                                else {
                                    alert(tasksmessage_ajax_error_security);
                                }

                        }).fail(function(res) {
                                alert(tasksmessage_ajax_error_server);
                        });
                        
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
                                                          .attr("src", s_url + "theme/default/images/load.gif")
                                                          .addClass("loader")
                        
                        ).hide();
                        
                },
                
                destroy: function() {
                        this.close();
                }
         }
 
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
        
})(jQuery);