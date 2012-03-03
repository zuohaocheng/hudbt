<?php global $BASEURL; ?>
<div class="tcategories view">
<h2><?php  echo __('Tcategory');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($tcategory['Tcategory']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($tcategory['Tcategory']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($tcategory['Tcategory']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Redirect To'); ?></dt>
		<dd>
			<?php echo $this->Html->link($tcategory['RedirectTo']['name'], array('controller' => 'tcategories', 'action' => 'view', $tcategory['RedirectTo']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Parents'); ?></dt>
		<dd>
		<ul>
		  <?php foreach ($tcategory['Parent'] as $parent): ?>
		  <li>
		    <?php echo $parent['name']; ?>
		  </li>
		  <?php endforeach; ?>
		</ul>
	      </dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Tcategory'), array('action' => 'edit', $tcategory['Tcategory']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Tcategory'), array('action' => 'delete', $tcategory['Tcategory']['id']), null, __('Are you sure you want to delete # %s?', $tcategory['Tcategory']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Tcategories'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Redirect To'), array('controller' => 'tcategories', 'action' => 'add')); ?> </li>
		<!-- <li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li> -->
		<!-- <li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li> -->
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Tcategories');?></h3>
	<?php if (!empty($tcategory['Parent'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Redirect To Id'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($tcategory['Parent'] as $parent): ?>
		<tr>
			<td><?php echo $parent['id'];?></td>
			<td><?php echo $parent['name'];?></td>
			<td><?php echo $parent['created'];?></td>
			<td><?php echo $parent['redirect_to_id'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'tcategories', 'action' => 'view', $parent['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'tcategories', 'action' => 'edit', $parent['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'tcategories', 'action' => 'delete', $parent['id']), null, __('Are you sure you want to delete # %s?', $parent['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Parent'), array('controller' => 'tcategories', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Torrents');?></h3>
	<?php if (!empty($torrents)):?>
	<?php torrentTableCake($torrents);?>
	<?php endif; ?>
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

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
