<div class="inner-meta">
	<label>Approval Level: </label>

	<?php $approvalLevel = $custom['approved'][0];?>
	
	<select name="approved">
		<option value="3" <?= $approvalLevel == 3 ? "selected" : ""?>>CAH Front page</option>
		<option value="2" <?= $approvalLevel == 2 ? "selected" : ""?>>CAH News Room and Child Pages</option>
		<option value="1" <?= $approvalLevel == 1 ? "selected" : ""?>>Child Pages</option>
	</select>
</div>