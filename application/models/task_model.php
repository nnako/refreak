<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Tasks Model
 *
 * @package	Refreak
 * @subpackage	tasks
 * @category	model
 * @author	Víctor <victor@ebavs.net> fromcouch
 * @link	https://github.com/fromcouch/refreak
 */
class Task_model extends CI_Model {
    
    /**
     * Constructor
     * 
     */
    public function __construct() 
    {
        parent::__construct();
        $this->plugin_handler->trigger('tasks_model_init');
    }
    
    /**
     * Get list of tasks
     * 
     * @param int $actual_user_id Actual user
     * @param int $user_id user to see tasks
     * @param int $project_id project id
     * @param int $time_concept 0 = future tasks , 1 = past tasks , 2 all tasks
     * @param int $projects projects array
     * @param int $context_id context identificator for tasks
     * @param bool $subtasks true if you need order by subtasks.
     * @return array of objects with tasks
     * @access public
     */
    public function get_tasks($actual_user_id, $user_id = null, $project_id = null, $time_concept = 0, $projects = array(), $context_id = null, $subtasks = false) {
        
        $this->db
                ->select('tasks.*, COUNT(DISTINCT rfk_task_comment.post_date) comment_count,
                        SUBSTRING(MAX(CONCAT(rfk_task_status.status_date,rfk_task_status.status)),1,19) AS status_date, 
                        SUBSTRING(MAX(CONCAT(rfk_task_status.status_date,rfk_task_status.status)),20) AS status_key,
                        projects.name as project_name, users.first_name as first_name, 
                        users.last_name as last_name, users.username as username, 
                        user_project.position', false)
                ->join('task_status', 'task_status.task_id = tasks.task_id', 'inner' )
                ->join('projects', 'tasks.project_id = projects.project_id', 'left' )
                ->join('user_project', 'user_project.project_id = tasks.project_id', 'left')
                ->join('users', 'users.id = tasks.user_id', 'left')
                ->join('task_comment', 'tasks.task_id = task_comment.task_id', 'left');
        
        $private = '(rfk_tasks.private = 0 OR';
        if ($this->ion_auth->in_group(array(1,2,3))) {
                $private .= ' rfk_tasks.private = 1 OR';
        }
        
        $this->db                
                ->where($private . ' (rfk_tasks.private = 2 AND (rfk_tasks.user_id=' . $actual_user_id . ' OR rfk_tasks.author_id = ' . $actual_user_id . ')))')
                ->where('user_project.user_id', $actual_user_id)
                ->group_by('tasks.task_id')                
                ->order_by('tasks.deadline_date','asc')
                ->order_by('tasks.priority','asc');
        
		if ($this->config->item('rfk_subtasks') ) {
			if (!$subtasks)
				$this->db->where('tasks.task_parent_id', 0);
			else
				$this->db->where('tasks.task_parent_id != ', 0);
		}
		
        $max_status = $this->config->item('rfk_status_levels');
                
        
        //if $time_concept == 2 do nothing to show all tasks
        switch ($time_concept) {
            
            case 0:
                $this->db->having('(status_key = ' . $max_status . ' AND DATE(status_date) > CURDATE()) OR status_key < ' . $max_status . '');
                break;

            case 1:
                $this->db->having('(DATE(rfk_tasks.deadline_date) <= CURDATE() OR status_key = ' . $max_status . ')');
                break;
            
        }
        
        if (!is_null($user_id) ) {
            
            $this->db->where('tasks.user_id', $user_id);
            
            if (is_array($projects) && count($projects)>0 ) {
                
                $this->db->where_in('tasks.project_id', $projects);
                
            }
        }
        
        if (!is_null($project_id)) {
            
            $this->db->where('tasks.project_id', $project_id);
            
        }
        
        if (!is_null($context_id)) {
            
            $this->db->where('tasks.context', $context_id);
            
        }
        
        $params                     = array(
                                       $actual_user_id, $user_id, $project_id, $time_concept, $projects, $context_id
                                    );
        
        $data                       = $this->plugin_handler->trigger( 'tasks_model_get_tasks', array( $this->db, $params ));
        $this->db                   = $data[0];
        
        return $this->db->get('tasks')->result_object();
        
    }
 
    /**
     * Return users assigned to a specific project. If no project is set return all users
     * 
     * @param int $project_id Project ID
     * @return array Users List
     * @access public 
     */
    public function get_users_project($project_id = 0) {
        
        $db         = $this->db;
        
        if ($project_id == 0) {
            $db->select('users.id, users.first_name, users.last_name, users.email');
            
            $db   = $this->plugin_handler->trigger( 'tasks_model_get_users', $db);
            
            return $db
                        ->get('users')
                        ->result_array();
        }
        else {
            
            $db
                ->select('users.id, users.first_name, users.last_name, users.email')
                ->join('users', 'users.id = user_project.user_id', 'inner')
                ->where('user_project.project_id', $project_id);
            
            $data       = $this->plugin_handler->trigger( 'tasks_model_get_project_users', array( $db, $project_id ) );
            $db         = $data[0];
            
            return $db
                        ->get('user_project')
                        ->result_array();
            
        }
        
        
    }
    
    /**
     * Insert or Update a task
     * 
     * @param string $title Task title
     * @param int $priority Task priority
     * @param int $context Task Context
     * @param date $deadline Task deadline
     * @param int $project_id Project Task 
     * @param string $project_name Name of Project for new project
     * @param string $description Task Description
     * @param int $user_id User ID assigned to task
     * @param int $scope Task Scope 0 = public, 1 = internal, 2 = private
     * @param int $status Task Status
     * @param int $author_id User ID created task
     * @param int $task_id 0 for new Task or Task ID for update
     * @param int $task_parent_id Parent Tasks if this is a subtask
     * @return int Return Task ID
     * @access public
     */
    public function save_task($title, $priority, $context, $deadline, $project_id, $project_name, $description, $user_id, $scope, $status, $author_id, $task_id = 0, $task_parent_id = 0) {
        
        if (!empty($project_name)) {
            // Create new project
            $this->load->model('project_model');
            $project_id         = $this->project_model->save($project_name, $author_id);
        }
        
        /**
         * @todo some patch for the moment.later do with config var
         */
        $deadline               = str_replace('/', '-', $deadline);
        $deadline               = date('Y-m-d', strtotime($deadline));
        
        $task_data              = array(
                                    'title'             => $title,
                                    'priority'          => $priority,
                                    'context'           => $context,
                                    'deadline_date'     => $deadline,
                                    'project_id'        => $project_id,
                                    'description'       => $description,
                                    'user_id'           => $user_id,
                                    'private'           => $scope,                                    
                                    'author_id'         => $author_id,
                                    'task_parent_id'    => $task_parent_id
        );
        
        // task id 0 is for insert, if task_id have non zero value is an update
        if ($task_id === 0) {
            
            $task_data         = $this->plugin_handler->trigger( 'tasks_model_insert_task', $task_data );
            
            $this->db->insert('tasks', $task_data); 
            $task_id           = $this->db->insert_id();   //get id from project
        }
        else {
            
            $tdata             = $this->plugin_handler->trigger( 'tasks_model_update_task', array($task_id, $task_data) );
            
            $this->db->where('task_id', $tdata[0]);
            $this->db->update('tasks', $tdata[1]); 
        }                
        
        $this->set_status($task_id, $status, $author_id);
        
        return $task_id;
    }
    
    /**
     * Get single Task
     * 
     * @param int $task_id Task Id 
     * @return array Task
     * @access public
     */
    public function get_task($task_id, $user_id) {
        
        
        
        $this->db
                    ->select('tasks.task_id, tasks.task_parent_id, tasks.project_id, tasks.priority, tasks.context, 
                              tasks.title, tasks.description, tasks.deadline_date, tasks.private,
                              tasks.user_id, tasks.author_id, tasks.modified_date, user_project.position') 
                    ->select('SUBSTRING(MAX(CONCAT(rfk_task_status.status_date,rfk_task_status.status)),20) AS status', false)
                    ->join('task_status', 'task_status.task_id = tasks.task_id', 'inner' )
                    ->join('user_project', 'user_project.project_id = tasks.project_id AND rfk_user_project.user_id = ' . $user_id, 'left')
                    ->where('tasks.task_id', $task_id);
                        
        $data               = $this->plugin_handler->trigger( 'tasks_model_get_task', array( $this->db, $task_id ) );
        $this->db           = $data[0];
                
        return $this->db->get('tasks')
                        ->result_array();
        
    }
    
    /**
     * Get data description for task
     * 
     * @param int $task_id Task Id
     * @return object Row with task description
     * @access public
     */
    public function get_task_description($task_id) {
        
        
        $db             = $this->db
                            ->select('tasks.description')                
                            ->where('tasks.task_id', $task_id);
        
        $data           = $this->plugin_handler->trigger( 'tasks_model_get_task_description', array( $this->db, $task_id ) );
        $db             = $data[0];
        
        $task           = $db->get('tasks')
                            ->result_object();
        
        if (count($task)>0) {
                return $task[0]->description;
        }
        else {
                return;
        }
        
    }
    
    /**
     * Get comment list for task
     * 
     * @param int $task_id Task Id
     * @return array Comment list for a task
     * @access public
     */
    public function get_task_comments($task_id) {
        
        $db         = $this->db
                            ->select('task_comment.task_comment_id, users.first_name, users.last_name, task_comment.comment, task_comment.last_change_date')
                            ->select("DATE_FORMAT(rfk_task_comment.post_date,'%d %b %Y %T') AS post_date",FALSE)
                            ->join('users', 'task_comment.user_id = users.id', 'inner' )
                            ->where('task_comment.task_id', $task_id);
        
        $data           = $this->plugin_handler->trigger( 'tasks_model_get_task_comment', array( $db, $task_id ) );
        $db             = $data[0];
        
        $comments       = $db->get('task_comment')
                            ->result_array();
        
        return $comments;
        
    }
    
    /**
     * Retrieve the status history for a task
     * 
     * @param int $task_id Task ID
     * @return array status history array
     * @access public
     */
    public function get_status_history($task_id) {
        
        $db             = $this->db
                            ->select('task_status.status, users.first_name, users.last_name')
                            ->select("DATE_FORMAT(rfk_task_status.status_date,'%d %b %Y %T') AS status_date",FALSE)
                            ->join('users', 'task_status.user_id = users.id', 'inner' )
                            ->where('task_status.task_id', $task_id);
        
        $data           = $this->plugin_handler->trigger( 'tasks_model_get_task_history', array( $db, $task_id ) );
        $db             = $data[0];
                            
        $history        = $db->get('task_status')
                            ->result_array();               
        
        return $history;
    }
    
    /**
     * Insert or Update task comment
     * 
     * @param string $comment comment to save
     * @param int $user_id User id that save comment
     * @param int $task_id Task ID for comment
     * @param int $task_comment_id if update this to be set to task_comment_id, 0 for insert
     * @return int task_comment_id 
     * @access public
     */
    public function save_comment($comment, $user_id, $task_id, $task_comment_id = 0) {
        
        $comment                = array(
                                        'comment'   => $comment,
                                        'user_id'   => $user_id
        );
        
        if ($task_comment_id === 0) {
            
            $comment['task_id']     = $task_id;
            
            $comment                = $this->plugin_handler->trigger( 'tasks_model_insert_comment_data', $comment );
            
            $this->db->set('post_date', 'NOW()', FALSE);
            $this->db->insert('task_comment', $comment);
            $task_comment_id        = $this->db->insert_id();
            
        }
        else {
            
            $cdata                  = $this->plugin_handler->trigger( 'tasks_model_update_comment_data', array( $task_comment_id, $comment ) );
            
            $task_comment_id        = $cdata[0];
            $comment                = $cdata[1];
            
            $this->db->set('last_change_date', 'NOW()', FALSE);
            $this->db->where('task_comment_id', $task_comment_id);
            $this->db->update('task_comment', $comment);
            
        }
        
        return $task_comment_id;        
    }
    
    /**
     * Delete a task comment
     * 
     * @param int $task_comment_id task_comment_id to delete
     * @return void 
     * @access public
     */
    public function delete_comment($task_comment_id) {
        
        $task_comment_id            = $this->plugin_handler->trigger( 'tasks_model_delete_comment', $task_comment_id );
        
        $this->db->where('task_comment_id', $task_comment_id);
        $this->db->delete('task_comment');
        
    }
    
    /**
     * Set new status for a task
     * 
     * @param int $task_id Task ID
     * @param int $status status level
     * @param int $user_id user id related
     * @return void
     * @access public
     */
    public function set_status($task_id, $status, $user_id) {
        
        $status_data            = array(
                                    'task_id'           => $task_id,
                                    'status'            => $status,
                                    'user_id'           => $user_id
        );
        
        $status_data            = $this->plugin_handler->trigger( 'tasks_model_set_status', $status_data );
        
        $this->db->set('status_date', 'NOW()', FALSE);
        $this->db->insert('task_status', $status_data);
        
    }
    
    /**
     * Close Task
     * 
     * @param int $task_id Task to close
     * @return void
     * @access public
     */
    public function close_task($task_id) {
        
        $this->db->set('deadline_date', 'CURDATE()', FALSE);        
        $this->db->where('task_id', $task_id);
        
        $data                   = $this->plugin_handler->trigger( 'tasks_model_close_task', array($task_id, $this->db ) );
        $this->db               = $data[1];
        $this->db->update('tasks');
        
    }
    
    /**
     * Delete Task
     * 
     * @param int $task_id Task to delete
     * @return void
     * @access public
     */
    public function delete_task($task_id) {
        
        $this->delete_task_comments($task_id);
        $this->delete_task_status($task_id);
        
        $this->db->where('task_id', $task_id);
        
        $data                   = $this->plugin_handler->trigger( 'tasks_model_delete_task', array($task_id, $this->db) );
        $this->db               = $data[1];
        
        $this->db->delete('tasks');
    }
    
    /**
     * Delete Task Status
     * 
     * @param int $task_id Task ID to delete status
     * @return void
     * @access public
     */
    public function delete_task_status($task_id) {
        
        $this->db->where('task_id', $task_id);
        
        $data                   = $this->plugin_handler->trigger( 'tasks_model_delete_task_status', array($task_id, $this->db) );
        $this->db               = $data[1];
        $this->db->delete('task_status');
        
    }
    
    /**
     * Delete Task Comments
     * 
     * @param int $task_id Task ID to delete comments
     * @return void
     * @access public
     */
    public function delete_task_comments($task_id) {
        
        $this->db->where('task_id', $task_id);
        
        $data                   = $this->plugin_handler->trigger( 'tasks_model_delete_task_comments', array($task_id, $this->db) );
        $this->db               = $data[1];
        $this->db->delete('task_comment');
        
    }
    
    /**
     * Get User Position in Project.
     * Calls Project model.
     * 
     * @param int $project_id project id
     * @param int $user_id user_id
     * @return int postion
     * @access public
     */
    public function get_user_position($task_id, $user_id) {
        
        $project                = $this->db
                                            ->select('project_id')
                                            ->where('task_id', $task_id)
                                            ->get('tasks')
                                            ->row();
        
        $project_id             = $project->project_id;
        
        $this->plugin_handler->trigger('tasks_model_get_user_project_position', array($task_id, $user_id, $project_id));
        
        $this->load->model('project_model');
        $up                     = $this->project_model->get_user_position($project_id, $user_id);
        
        if (count($up) > 0)
            return $up[0]->position;
        else
            return 0;
        
    }
    
    /**
     * Get Project from task.
     * 
     * @param int $task_id task id
     * @return int project id
     * @access public
     */
    public function get_task_project($task_id) {
        
        $project                = $this->db
                                            ->select('project_id')
                                            ->where('task_id', $task_id)
                                            ->get('tasks')
                                            ->row();
        
        $project_id             = $project->project_id;
        
        $data                   = $this->plugin_handler->trigger('tasks_model_get_project_task', array($task_id, $project_id));
        
        return $project_id;
        
    }
    
	/**
	 * Convert to readable array subtasks
	 * 
	 * @param array $subtasks Subtasks array
	 * @return array Array with parent id as key
	 * @access public
	 */
	public function process_subtasks($subtasks) {
		
		$ret = array();
		
		foreach ($subtasks as $value) {
			
				$ret[$value->task_parent_id][] = $value;
			
		}

		return $ret;
		
	}
	
	/**
	 * Count subtasks of parent tasks
	 * 
	 * @param int $task_parent_id parent tasks id
	 * @return int total subtasks
	 * @access public
	 */
	public function get_subtasks_number($task_parent_id) {
		
		$total_subtasks				= $this->db
                                            ->where('task_parent_id', $task_parent_id)
                                            ->count_all_results('tasks');
		
		return $total_subtasks;
		
	}
	
    /**
     * Check if user is task owner
     * 
     * @param int $task_id task id
     * @param int $user_id user id for check
     * @return boolean
     * @access public
     */
    public function is_owner($task_id, $user_id) {
        
        $task                   = $this->get_task($task_id, $user_id);
        
        $this->plugin_handler->trigger('tasks_model_is_owner', array($task_id, $user_id));
        
        if ((int)$task[0]['author_id'] === $user_id) {
            return true;
        }
        
        return false;
    }
	
	public function get_parent_task_title($task_id) {
		
		$parent_task			= $this->db
										->select('tasks.title')
										->where('tasks.task_id', $task_id)
										->get('tasks')
										->row();
		
		return $parent_task->title;
		
	}
}

/* End of file task_model.php */
/* Location: ./application/models/task_model.php */
