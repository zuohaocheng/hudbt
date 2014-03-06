<div class="schools index">
	<h2><?php echo __('Schools'); ?></h2>
	<div> PS: 不允许删除 </div>
	<table cellpadding="5" class="no-vertical-line">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($schools as $school): ?>
	<tr>
		<td><?php echo h($school['School']['id']); ?>&nbsp;</td>
		<td><?php echo h($school['School']['name']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $school['School']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $school['School']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
      </tbody>	 
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
		<li><?php echo $this->Html->link(__('New School'), array('action' => 'add')); ?></li>
	</ul>
</div>
