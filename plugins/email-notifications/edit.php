
<fieldset>
	<legend><?php echo $this->lang->line('create_task_title'); ?></legend>
	<p>
		<label><?php echo $this->lang->line('create_task_activated'); ?></label>
		<?php 
			echo form_checkbox('creating_task_activated', '1', $config->creating_task_activated);
		?>
	</p>
	<h3><?php echo $this->lang->line('create_task_groups'); ?></h3>
	<div>
		<?php echo render_groups($groups,'creating_task_group_'); ?>
	</div>
	<br/>
	<h3><?php echo $this->lang->line('create_task_who'); ?></h3>
	<div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('create_task_creator'); ?></label>
			<?php echo form_checkbox('creating_task_creator', '1', $config->creating_task_creator); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('create_task_assigned'); ?></label>
			<?php echo form_checkbox('creating_task_assigned', '1', $config->creating_task_assigned); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('create_task_project_member'); ?></label>
			<?php echo form_checkbox('creating_task_project_members', '1', $config->creating_task_project_members); ?>
		</div>
	</div>
	<br/>
	<h3><label><?php echo $this->lang->line('create_task_mail'); ?></label></h3>
	<div> 
		<p>
                    <label><?php echo $this->lang->line('create_task_subject'); ?></label>
                    <?php echo form_input('creating_task_email_subject', $config->creating_task_email_subject); ?>
		</p>
		<p>
                    <label><?php echo $this->lang->line('create_task_body'); ?></label>
                    <?php echo form_textarea('creating_task_email_body', $config->creating_task_email_body); ?>
		</p>
	</div>
</fieldset>

<fieldset>
	<legend><?php echo $this->lang->line('edit_task_title'); ?></legend>
	<p>
		<label><?php echo $this->lang->line('edit_task_activated'); ?></label>
		<?php echo form_checkbox('editing_task_activated', '1', $config->editing_task_activated); ?>
	</p>
	<h3><?php echo $this->lang->line('edit_task_groups'); ?></h3>
	<div>
		<?php echo render_groups($groups,'editing_task_group_'); ?>
	</div>
	<br/>
	<h3><?php echo $this->lang->line('edit_task_who'); ?></h3>
	<div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('edit_task_editor'); ?></label>
			<?php echo form_checkbox('editing_task_editor', '1', $config->editing_task_editor); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('edit_task_assigned'); ?></label>
			<?php echo form_checkbox('editing_task_assigned', '1', $config->editing_task_assigned); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('edit_task_project_member'); ?></label>
			<?php echo form_checkbox('editing_task_project_members', '1', $config->editing_task_project_members); ?>
		</div>
	</div>
	<br/>
	<h3><?php echo $this->lang->line('edit_task_mail'); ?></h3>
	<div> 
		<p>
                    <label><label><?php echo $this->lang->line('edit_task_subject'); ?></label></label>
                    <?php echo form_input('editing_task_email_subject', $config->editing_task_email_subject); ?>
		</p>
		<p>
                    <label><label><?php echo $this->lang->line('edit_task_body'); ?></label></label>
                    <?php echo form_textarea('editing_task_email_body', $config->editing_task_email_body); ?>
		</p>
	</div>
</fieldset>
<fieldset>
	<legend><?php echo $this->lang->line('comment_title'); ?></legend>
	<p>
		<label><?php echo $this->lang->line('comment_activated'); ?></label>
		<?php echo form_checkbox('commenting_activated', '1', $config->commenting_activated); ?>
	</p>
	<h3><?php echo $this->lang->line('comment_actions'); ?></h3>
	<div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_new'); ?></label>
			<?php echo form_checkbox('commenting_new', '1', $config->commenting_new);	?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_edit'); ?></label>
			<?php echo form_checkbox('commenting_edit', '1', $config->commenting_edit); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_delete'); ?></label>
			<?php echo form_checkbox('commenting_delete', '1', $config->commenting_delete); ?>
		</div>
	</div>
	<br/>
	<h3><?php echo $this->lang->line('comment_project_member'); ?></h3>
	<div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_creator'); ?></label>
			<?php echo form_checkbox('commenting_creator', '1', $config->commenting_creator);	?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_assigned'); ?></label>
			<?php echo form_checkbox('commenting_assigned', '1', $config->commenting_assigned); ?>
		</div>
		<div class="chk_box">
			<label><?php echo $this->lang->line('comment_project_member'); ?></label>
			<?php echo form_checkbox('commenting_project_members', '1', $config->commenting_project_members); ?>
		</div>
	</div>
	<br/>
	<h3><?php echo $this->lang->line('comment_mail'); ?></h3>
	<div> 
		<p>
                    <label><?php echo $this->lang->line('comment_project_subject'); ?></label>
                    <?php echo form_input('commenting_email_subject', $config->commenting_email_subject); ?>
		</p>
		<p>
                    <label><?php echo $this->lang->line('comment_body'); ?></label>
                    <?php echo form_textarea('commenting_email_body', $config->commenting_email_body); ?>
		</p>
	</div>
</fieldset>
<fieldset>
	<legend>Project User</legend>
	<p>
		<?php 
			echo form_label('Activated');
			echo form_checkbox('project_user_activated');
		?>
	</p>
	<h3>Project User Actions</h3>
	<div>
		<div class="chk_box">
		<?php 
			echo form_label('Assing User');
			echo form_checkbox('project_user_new');
		?>
		</div>
		<div class="chk_box">
		<?php 
			echo form_label('Remove User');
			echo form_checkbox('project_user_edit');
		?>
		</div>
	</div>
	<br/>
	<h3>Who recieve mail</h3>
	<div>
		<div class="chk_box">
		<?php 
			echo form_label('Assigned User');
			echo form_checkbox('project_user_assigned');
		?>
		</div>
		<div class="chk_box">
		<?php 
			echo form_label('Project Members');
			echo form_checkbox('project_user_project_members');
		?>
		</div>
	</div>
	<br/>
	<h3>Mail</h3>
	<div> 
		<p>
                    <label>Subject</label>
                    <?php echo form_input('project_user_email_subject'); ?>
		</p>
		<p>
                    <label>Body</label>
                    <?php echo form_textarea('project_user_email_body'); ?>
		</p>
	</div>
</fieldset>
<fieldset>
	<legend>User</legend>
	<p>
		<?php 
			echo form_label('Activated');
			echo form_checkbox('user_activated');
		?>
	</p>
	<h3>User Actions</h3>
	<div>
		<div class="chk_box">
		<?php 
			echo form_label('New User');
			echo form_checkbox('user_new');
		?>
		</div>
		<div class="chk_box">
		<?php 
			echo form_label('Delete User');
			echo form_checkbox('user_delete');
		?>
		</div>
	</div>
	<br/>
	<h3>Who recieve mail</h3>
	<div>
		<div class="chk_box">
		<?php 
			echo form_label('User Affected');
			echo form_checkbox('user_assigned');
		?>
		</div>
	</div>
	<br/>
	<h3>Mail</h3>
	<div> 
		<p>
                    <label>Subject</label>
                    <?php echo form_input('user_email_subject'); ?>
		</p>
		<p>
                    <label>Body</label>
                    <?php echo form_textarea('user_email_body'); ?>
		</p>
	</div>
</fieldset>

<?php 
function render_groups($groups, $prefix) {
	$ret = '';
	foreach ($groups as $value) {
		$ret .= '<div class="chk_box">';
                $ret .= form_label($value['description']);
		$ret .= form_checkbox($prefix . $value['name'], $value['id'], FALSE);
		$ret .= '</div>';
	}
	
	return $ret;
} 
?>