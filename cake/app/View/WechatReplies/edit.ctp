<div class="wechatReplies form">
<?php echo $this->Form->create('WechatReply'); ?>
	<fieldset>
		<legend><?php echo __('Edit Wechat Reply'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('regexp', ['label' => '匹配模式', 'after'=>'使用<a href="http://www.php.net/manual/zh/reference.pcre.pattern.syntax.php">PCRE语法</a>, 如<code>/^hello/i</code>']);
		echo $this->Form->input('string', ['checked' =>true, 'label'=>'使用正则表达式']);
		echo $this->Form->input('content', ['label' => '回复']);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('WechatReply.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('WechatReply.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Wechat Replies'), array('action' => 'index')); ?></li>
	</ul>
</div>
