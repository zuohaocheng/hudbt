<div class="tcategories form">
<?php echo $this->Form->create('Tcategory');?>
	<fieldset>
		<legend><?php echo __('Add Tcategory'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('redirect_to_id');
		echo $this->Form->input('Parent');
#		echo $this->Form->input('Torrent');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Tcategories'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Tcategories'), array('controller' => 'tcategories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Redirect To'), array('controller' => 'tcategories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li>
	</ul>
</div>
