<?php global $BASEURL; ?>
<!-- <div class="tcategories index"> -->
	<h2 id="page-title"><?php echo __('Tcategories');?></h2>
	<div class="actions minor-list list-seperator minor-nav">
	  <ul>
	    <li>
	      <form method="GET" action="//<?php echo $BASEURL ?>/cake/tcategories/search/">
	      <label>
		<input type="search" placeholder="<?php echo __('Keywords') ?>" name="term">
		<input type="submit" class="btn" value="<?php echo __('Search') ?>">
	      </form>
	    </li>
	    <li><?php echo $this->Html->link(__('New Tcategory'), array('action' => 'add')); ?></li>
	    <li><?php echo $this->Html->link(__('List Tcategories'), array('controller' => 'tcategories', 'action' => 'index')); ?> </li>
	    <li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li>
	    <li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li>
	  </ul>
	</div>

	<table cellpadding="5" class="no-vertical-line">
	  <thead>
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('hidden');?></th>
			<th><?php echo $this->Paginator->sort('locked');?></th>
			<th><?php echo $this->Paginator->sort('redirect_to_id');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	  </thead>
	  <tbody>
	<?php foreach ($tcategories as $tcategory): ?>
	<tr>
	  <td><?php echo $this->Html->link(h($tcategory['Tcategory']['name']), array('action' => 'view', $tcategory['Tcategory']['id'])); ?>&nbsp;</td>
		<td><?php echo h($tcategory['Tcategory']['hidden']); ?>&nbsp;</td>
		<td><?php echo h($tcategory['Tcategory']['locked']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($tcategory['RedirectTo']['name'], array('controller' => 'tcategories', 'action' => 'view', $tcategory['RedirectTo']['id'])); ?>
		</td>
		<td class="actions">
		  <?php if (!$tcategory['Tcategory']['locked'] || $canLock): ?>
		  <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $tcategory['Tcategory']['id'])); ?>
		  <?php if ($canDelete): ?>
		  <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $tcategory['Tcategory']['id']), null, __('Are you sure you want to delete # %s?', $tcategory['Tcategory']['id'])); ?>
		<?php endif; ?>
		<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
	  </tbody>
	</table>
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
<!-- </div> -->
