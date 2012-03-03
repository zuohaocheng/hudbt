<?php global $BASEURL; ?>
<div class="tcategories index">
	<h2><?php echo __('Tcategories');?></h2>
	<form method="GET" action="//<?php echo $BASEURL ?>/cake/tcategories/search/">
	<label>
	<input type="search" placeholder="<?php echo __('Keywords') ?>" name="term">
	<input type="submit" class="btn" value="<?php echo __('Search') ?>">
	</form>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('redirect_to_id');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($tcategories as $tcategory): ?>
	<tr>
		<td><?php echo h($tcategory['Tcategory']['id']); ?>&nbsp;</td>
		<td><?php echo h($tcategory['Tcategory']['name']); ?>&nbsp;</td>
		<td><?php echo h($tcategory['Tcategory']['created']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($tcategory['RedirectTo']['name'], array('controller' => 'tcategories', 'action' => 'view', $tcategory['RedirectTo']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $tcategory['Tcategory']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $tcategory['Tcategory']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $tcategory['Tcategory']['id']), null, __('Are you sure you want to delete # %s?', $tcategory['Tcategory']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Tcategory'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Tcategories'), array('controller' => 'tcategories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Redirect To'), array('controller' => 'tcategories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li>
	</ul>
</div>
