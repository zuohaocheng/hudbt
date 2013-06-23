<div class="wechatReplies index">
	<h2><?php echo __('Wechat Replies'); ?></h2>
	<table cellpadding="5" class="no-vertical-line">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('regexp'); ?></th>
			<th><?php echo $this->Paginator->sort('content'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($wechatReplies as $wechatReply): ?>
	<tr>
		<td><?php echo h($wechatReply['WechatReply']['id']); ?>&nbsp;</td>
		<td><?php echo h($wechatReply['WechatReply']['regexp']); ?>&nbsp;</td>
		<td><?php echo h($wechatReply['WechatReply']['content']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $wechatReply['WechatReply']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $wechatReply['WechatReply']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $wechatReply['WechatReply']['id']), null, __('Are you sure you want to delete # %s?', $wechatReply['WechatReply']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Wechat Reply'), array('action' => 'add')); ?></li>
		<li><a href="/wechat-test.php">测试效果</a></li>
	</ul>
</div>
