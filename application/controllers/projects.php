<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends RF_BaseController {
   
    
    public function __construct() {
        parent::__construct();        
        //$this->output->enable_profiler(TRUE);
        $this->lang->load('projects');
        $this->load->library('form_validation');
        
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
    }

    public function index()
    {
        $this->load->database();
        $this->load->model('project_model');
        
        $this->data['projects'] = $this->project_model->get_projects_list($this->data['actual_user']->id);

        $this->load->view('projects/projects', $this->data);        
    }

    public function create()
    {
        if ($this->ion_auth->in_group(array(1,2))) {
            //validate form input
            $this->form_validation->set_rules('name', 'Name', 'required|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'xss_clean');
            $this->form_validation->set_rules('status', 'Status', 'required|xss_clean');

            if ($this->form_validation->run() == true)
            {
                    $name           = $this->input->post('name');
                    $description    = $this->input->post('description');
                    $status         = $this->input->post('status');
                    
                    //check to see if we are creating the project
                    //redirect them back to the project page                    
                    $this->load->model('project_model');
                    
                    $this->project_model->save($name, $this->data['actual_user']->id, $description, $status);
                    $this->session->set_flashdata('message', $this->lang->line('projectsmessage_created'));
                    redirect("projects", 'refresh');
            }
            else
            { 
                    //display the create project form
                    
                    $this->data['name'] = array(
                            'name'  => 'name',
                            'id'    => 'name',
                            'type'  => 'text',
                            'value' => $this->form_validation->set_value('name'),
                    );
                    $this->data['description'] = array(
                            'name'  => 'description',
                            'id'    => 'description',
                            'type'  => 'text',
                            'value' => $this->form_validation->set_value('description'),
                    );
                    

                    $this->data['status'] = $this->lang->line('project_status');
                    
                    $this->load->view('projects/create', $this->data);
            }
        }
    }
    
    /**
     * Edit Project
     * @param int $id project id
     * @todo status history
     */
    public function edit($id)
    {
        
            //validate form input
            if ($this->ion_auth->is_admin()) {
                $this->form_validation->set_rules('name', 'Name', 'required|xss_clean');
                $this->form_validation->set_rules('description', 'Description', 'xss_clean');
            }
            $this->form_validation->set_rules('status', 'Status', 'required|xss_clean');            

            $this->load->model('project_model');
            
            //get project and users
            $project            = $this->project_model->get_project($id);
            $project_users      = $this->project_model->get_users_project($id);
            
            if ($this->ion_auth->in_group(array(1,2)) && $this->input->post('id'))
            {
                    // do we have a valid request?
                    if ($id != $this->input->post('id'))
                    {
                            show_error('This form post did not pass our security checks.');
                    }

                    if ($this->form_validation->run() == true)
                    {
                            $name           = $this->ion_auth->is_admin() ? $this->input->post('name') : $project->name;
                            $description    = $this->ion_auth->is_admin() ? $this->input->post('description') : $project->description;
                            $status         = $this->input->post('status');
                            
                            $this->project_model->update($id, $name, $this->data['actual_user']->id, $description, $status);
                            $this->session->set_flashdata('message', $this->lang->line('projectsmessage_saved'));
                            redirect("projects", 'refresh');
                    }
                    
            }
            
            //search permisions for user in project
            $user_position      = $this->project_model->get_user_position($id, $this->data['actual_user']->id);
            $this->data['actual_user']->project_position = $user_position[0]->position;                        
            
            //display the create project form
            $this->data['name'] = array(
                    'name'  => 'name',
                    'id'    => 'name',
                    'type'  => 'text',
                    'value' => $this->form_validation->set_value('name', $project->name),
            );
            $this->data['description'] = array(
                    'name'  => 'description',
                    'id'    => 'description',
                    'type'  => 'text',
                    'value' => $this->form_validation->set_value('description', $project->description),
            );
                       
            $this->data['status'] = $project->status_id;
            $this->data['project_users'] = $project_users;
            $this->data['pid'] = $id;
            
            //search for users in projects to create dropdown from array
            $users = $this->data['users'];
            $result_users = array( '0' => $this->lang->line('projectscrud_select_user'));
            $add = false;
            foreach ($users as $u_key => $u) {
                
                foreach ($project_users as $pu) {
                    if ($u->id == $pu->user_id)
                    {
                        $add = false;
                        break;
                    }
                    else 
                        $add = true;
                }
                
                if ($add) {
                    $result_users[$u->id] = $u->first_name . ' ' . $u->last_name;
                }
            }            
            $this->data['dropdown_users'] = $result_users;
            
            $this->javascript->output('
                    $.ajaxSetup({
                        data: {'. $this->security->get_csrf_token_name() . ': "' . $this->security->get_csrf_hash() . '"},
                        dataType: "json"
                    });
                ');
                    
            unset($users, $project_users, $result_users);            
            $this->load->view('projects/edit', $this->data);
            
        
    }
    
    public function delete($project_id) {
        
        if ($this->ion_auth->is_admin()) {
            
            $this->load->model('project_model');
            
            $this->project_model->delete($project_id, $this->data['actual_user']->id);
            $this->session->set_flashdata('message', $this->lang->line('projectsmessage_deleted'));
            redirect("projects", 'refresh');
            
        }
    }
    
    public function add_user_project() {
        
        if ($this->input->is_ajax_request() && $this->input->post('project_id') && $this->input->post('user_id') && $this->input->post('position'))
        {
            $this->load->model('project_model');
            $this->project_model->set_user_project(
                    $this->input->post('user_id'),
                    $this->input->post('project_id'),
                    $this->input->post('position')
            );
            
            echo json_encode(array('response' => 'rfk_ok'));
        }
        else
        {
            echo json_encode(array('response' => 'rfk_fuckyou'));
        }
        
    }
    
    public function remove_user_project() {
        
        if ($this->input->is_ajax_request() && $this->input->post('project_id') && $this->input->post('user_id'))
        {
            $this->load->model('project_model');
            $this->project_model->remove_user_project(
                    $this->input->post('user_id'),
                    $this->input->post('project_id')
            );
            
            echo json_encode(array('response' => 'rfk_ok'));
        }
        else
        {
            echo json_encode(array('response' => 'rfk_fuckyou'));
        }
        
    }

    public function change_user_position() {
        
        if ($this->input->is_ajax_request() && $this->input->post('project_id') && $this->input->post('user_id') && $this->input->post('position'))
        {
            $this->load->model('project_model');
            $this->project_model->update_user_position(
                    $this->input->post('user_id'),
                    $this->input->post('project_id'),
                    $this->input->post('position')
            );
            
            echo json_encode(array('response' => 'rfk_ok'));
        }
        else
        {
            echo json_encode(array('response' => 'rfk_fuckyou'));
        }
        
    }
}

/* End of file projects.php */
/* Location: ./application/controllers/projects.php */