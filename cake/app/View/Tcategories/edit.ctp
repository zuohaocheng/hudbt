<div class="tcategories form">
<?php echo $this->Form->create('Tcategory');?>
	<fieldset>
		<legend><?php echo __('Edit Tcategory'); ?></legend>
	<?php
		echo $this->Form->input('id');
		?>
<?php include('edits.ctp'); ?>
<script type="text/javascript">hb.tcategory=(<?php echo json_encode(['id' => $tcategory['Tcategory']['id']]); ?>);</script>
