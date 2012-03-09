	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('hidden');
		if ($canLock) {
		  echo $this->Form->input('locked');
		}
	?>
	<div class="input select" id="redirect">
	  <label><input type="checkbox"<?php echo $tcategory['RedirectTo']['id'] ? ' checked="checked"' : '';?> /><?php echo __('Redirect'); ?></label>
	  <span class="tcategory"<?php echo $tcategory['RedirectTo']['id'] ? '' : ' style="display:none;"';?>><input type="text" placeholder="<?php echo __('Redirect to'); ?>" value="<?php echo $tcategory['RedirectTo']['name']; ?>" /><input type="hidden" name="data[Tcategory][redirect_to_id]" value="<?php echo $tcategory['RedirectTo']['id']; ?>" /></span>
	  </div>
	  <div class="input select minor-list" id="parents"<?php echo $tcategory['RedirectTo']['id'] ? ' style="display:none;"' : '';?>>
	  <?php echo __('Parent'); ?>
	  <ul>

	   <?php foreach($tcategory['Parent'] as $parent): ?>
	  <li class="tcategory">
	    <input type="text" placeholder="<?php echo __('New parent'); ?>" value="<?php echo $parent['name']; ?>" />
	    <input type="hidden" name="data[Parent][Parent][]" value="<?php echo $parent['id']; ?>" />
	    <a href="#" class="remove-parent">-</a>
	  </li>
	  <?php endforeach; ?>
	  <li class="tcategory">
	    <input type="text" placeholder="<?php echo __('New parent'); ?>" value="" />
	    <input type="hidden" name="data[Parent][Parent][]" value="" />
	    <a href="#" class="remove-parent" style="display: none;">-</a>
	    </li>
	</ul>
	</div>
	<?php
#		echo $this->Form->input('Parent');
#		echo $this->Form->input('Torrent');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<?php if ($this->Form->value('Tcategory.id')): ?>
		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Tcategory.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Tcategory.id'))); ?></li>
		<?php endif;?>
		<li><?php echo $this->Html->link(__('List Tcategories'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('New Tcategory'), array('controller' => 'tcategories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Torrents'), array('controller' => 'torrents', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Torrent'), array('controller' => 'torrents', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script type="text/javascript" src="/js/tcategories.js"></script>