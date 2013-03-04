<?php
/**
 * @todo change the way that ion_auth return data or write my own auth!
 *  
 */
class RF_BaseController extends CI_Controller {
    
    public $data = array();
    
    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in())
        {
                redirect('auth/login');
        }

        $this->lang->load('layout/header');
        $this->load->model('user_model');
        $this->load->helper('html');
        $this->load->helper('form');
        $this->load->helper('rfk_form');        
        
        $params                             = $this->_detect_user_project();
        $actual_user                        = $this->ion_auth->user()->row();
        $actual_user_id                     = $actual_user->id;
        $selected_user                      = 0;
        $selected_context                   = 0;
        $selected_project                   = 0;
        $selected_time                      = 0;
        
        if ($params != false) {
            $selected_user                  = $params['user'];
            $selected_context               = $params['context'];
            $selected_project               = $params['project'];
            $selected_time                  = $params['time'];
        }

        $this->data['js_vars'] =         
                    'var user_id    = ' . $selected_user . ";\n" .
                    'var context_id = ' . $selected_context . ";\n" .
                    'var project_id = ' . $selected_project . ";\n" .
                    'var time_concept = ' . $selected_time . ";\n" .
                    'var s_url      = "' . site_url() . '";'
                ;
        
        $this->data['users']                = $this->user_model->get_all_users_with_group();
        $this->data['actual_user']          = $actual_user;
        $this->data['groups']               = $this->ion_auth->groups()->result_array();
        $this->data['user_projects']        = $this->user_model->get_projects_user($actual_user_id);
        $this->data['menu_left']            = $this->_create_left_menu($this->data['user_projects'], $params);
        $this->data['menu_right']           = $this->_create_right_menu($actual_user_id, $params, $selected_user, $selected_context);
        
        $this->javascript->js->script(base_url() . 'js/panels.js');
        $this->css->add_style(base_url() . 'theme/default/css/refreak.css', 'core');
        
        unset($params, $actual_user);
    } 
    
    protected function to_dropdown_array($array, $id, $description) {
                
        $result = array();
        
        if (count($array) > 0) 
            foreach ($array as $value) 
                $result[$value[$id]] = $value[$description];
        
        return $result;
    }
    
    protected function _create_left_menu($user_projects, $params) {
        
        $user_id            = 0;
        $project_id         = 0;
        $context_id         = 0;
        
        if ($params !== false && is_array($params)) {
            $user_id        = $params['user'];
            $project_id     = $params['project'];
            $context_id     = $params['context'];
        }
        
        $view_menu          = array(
                                anchor('#', $this->lang->line('menu_view_all_projects'))    => array(
                                                            anchor('tasks/s/0/' . $user_id . '/2/' . $context_id . '/', $this->lang->line('menu_view_all_tasks')),
                                                            anchor('tasks/s/0/' . $user_id . '/0/' . $context_id . '/', $this->lang->line('menu_view_future_tasks')),
                                                            anchor('tasks/s/0/' . $user_id . '/1/' . $context_id . '/', $this->lang->line('menu_view_past_tasks')),
                                )
        );
        foreach ($user_projects as $up) {
            $view_menu[anchor('#', $up->name)]       = array(
                                anchor('tasks/s/' . $up->project_id . '/' . $user_id . '/2/' . $context_id . '/', $this->lang->line('menu_view_all_tasks')),
                                anchor('tasks/s/' . $up->project_id . '/' . $user_id . '/0/' . $context_id . '/', $this->lang->line('menu_view_future_tasks')),
                                anchor('tasks/s/' . $up->project_id . '/' . $user_id . '/1/' . $context_id . '/', $this->lang->line('menu_view_past_tasks')),
            ); 
        }
            
        
        $menu               = array(
                                anchor('#', $this->lang->line('menu_tasks'))    => array(
                                                            anchor('', $this->lang->line('menu_tasks_new'))
                                                                    ),
                                anchor('#', $this->lang->line('menu_view'))     => $view_menu,
                                
                                anchor('#', $this->lang->line('menu_manage'))   => array(
                                                            anchor('projects', $this->lang->line('menu_manage_projects')),
                                                            anchor('users', $this->lang->line('menu_manage_users')),
                                                            anchor('users/edit_user', $this->lang->line('menu_manage_profile'))
                                                                    ),
        );
        
        
        return $menu;
    }
    
    protected function _create_right_menu($user_id, $params, $selected_user, $selected_context) {
        
        $project_id         = 0;
        
        if ($params !== false && is_array($params)) {
            $project_id     = $params['project'];
        }
        
        $contexts           = $this->lang->line('task_context');
        array_unshift($contexts, $this->lang->line('combo_context_all_contexts')) ;
                
        $menu               = array(
                               anchor('/', $this->lang->line('header_alltasks')),
                               anchor('tasks/s/' . $project_id . '/' . $user_id . '/0/' . $selected_context , $this->lang->line('header_mytasks')),
                               form_dropdown_users('list_users', $this->lang->line('header_allusers'), $selected_user),
                               form_dropdown('header_context', $contexts, array($selected_context), 'class = "list_contexts"')
        );
        
        return $menu;
        
    }
    
    /**
     *  this method detects is passing user and project throught uri and fill array 
     *  with this parameters. Otherwise returns false
     * 
     * @return array|bool user and project for draw menus
     */
    private function _detect_user_project() {
        $class      = $this->router->fetch_class();
        $method     = $this->router->fetch_method();
        $params     = false;
        
        if ($class == 'tasks' && $method == 's') {
            $params         = array(
                                'project'       => $this->uri->segment(3),
                                'user'          => $this->uri->segment(4),
                                'time'          => $this->uri->segment(5),
                                'context'       => $this->uri->segment(6)
            );
        }
        
        return $params;
    }
        
}

?>