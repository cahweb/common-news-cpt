<div class="inner-meta">
	<label>Display in CAH news: </label>

	<?php
		$checked = $custom['approved'][0];
		if(!empty($checked)){
			echo "<input type=\"checkbox\" name=\"approved\" checked=\"checked\"/>";
		}
		else {
			echo "<input type=\"checkbox\" name=\"approved\"/>";
		}

	?>
</div>