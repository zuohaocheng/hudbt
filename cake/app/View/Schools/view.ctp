<div class="schools view">
<h2><?php  echo __('School'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($school['School']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($school['School']['name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit School'), array('action' => 'edit', $school['School']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete School'), array('action' => 'delete', $school['School']['id']), null, __('Are you sure you want to delete # %s?', $school['School']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Schools'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New School'), array('action' => 'add')); ?> </li>
	</ul>
</div>
