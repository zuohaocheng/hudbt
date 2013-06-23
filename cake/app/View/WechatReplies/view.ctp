<div class="wechatReplies view">
<h2><?php  echo __('Wechat Reply'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($wechatReply['WechatReply']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Regexp'); ?></dt>
		<dd>
			<?php echo h($wechatReply['WechatReply']['regexp']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Content'); ?></dt>
		<dd>
			<?php echo h($wechatReply['WechatReply']['content']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Wechat Reply'), array('action' => 'edit', $wechatReply['WechatReply']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Wechat Reply'), array('action' => 'delete', $wechatReply['WechatReply']['id']), null, __('Are you sure you want to delete # %s?', $wechatReply['WechatReply']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Wechat Replies'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Wechat Reply'), array('action' => 'add')); ?> </li>
	</ul>
</div>
