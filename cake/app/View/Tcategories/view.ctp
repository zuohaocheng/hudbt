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
		<dt><?php echo __('Hidden'); ?></dt>
		<dd>
			<?php echo h($tcategory['Tcategory']['hidden']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Locked'); ?></dt>
		<dd>
			<?php echo h($tcategory['Tcategory']['locked']); ?>
			&nbsp;
		</dd>
		<?php if ($tcategory['RedirectTo']['id']): ?>
		<dt><?php echo __('Redirect To'); ?></dt>
		<dd>
			<?php echo $this->Html->link($tcategory['RedirectTo']['name'], array('controller' => 'tcategories', 'action' => 'view', $tcategory['RedirectTo']['id'])); ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<?php if (!empty($tcategory['RedirectFrom'])): ?>
		<dt<?php if ($tcategory['RedirectTo']['id']) echo ' class="invalid" title="' . __('Double redirects') . '"'; ?>><?php echo __('Redirect From'); ?></dt>
		<dd class="minor-list">
		<ul>
		  <?php foreach ($tcategory['RedirectFrom'] as $rFrom): ?>
		  <li>
   <?php echo $this->Html->link($rFrom['name'], ['controller' => 'tcategories', 'action' => 'view', $rFrom['id'], 'noredirect' => 1]); ?>
		  </li>
		  <?php endforeach; ?>
		</ul>
	      </dd>
	      <?php endif; ?>
		<dt><?php echo __('Parents'); ?></dt>
		<dd class="minor-list">
		<ul>
		  <?php foreach ($tcategory['Parent'] as $parent): ?>
		  <li>
		    <?php echo $this->Html->link($parent['name'], ['controller' => 'tcategories', 'action' => 'view', $parent['id']]); ?>
		  </li>
		  <?php endforeach; ?>
		</ul>
	      </dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	  <?php if ($canEdit): ?>
	  <li><?php echo $this->Html->link(__('Edit Tcategory'), array('action' => 'edit', $tcategory['Tcategory']['id'])); ?> </li>
	  <?php endif; ?>
		<?php if ($canDelete): ?>
		<li><?php echo $this->Form->postLink(__('Delete Tcategory'), array('action' => 'delete', $tcategory['Tcategory']['id']), null, __('Are you sure you want to delete # %s?', $tcategory['Tcategory']['id'])); ?> </li>
		<?php endif; ?>
		<li><?php echo $this->Html->link(__('List Tcategories'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Tcategory'), array('controller' => 'tcategories', 'action' => 'add')); ?> </li>
		<!-- <li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li> -->
		<!-- <li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li> -->
	</ul>
</div>
<?php if (!empty($torrents)):?>
<div class="related">
	<h3><?php echo __('Related Torrents');?></h3>
	<?php torrentTableCake($torrents);?>
	<div class="center">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</div>

	<div class="paging center">
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
<?php endif; ?>
