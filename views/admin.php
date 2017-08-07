<div class="inner-meta">
	<label>Display in CAH news: </label>

	<?php
		$checked = $custom['approved'][0];
		
		if($checked!="off")
		echo "Yes <input type=\"radio\" name=\"approved\" checked=\"checked\" value=\"on\"/>";
		else
		echo "Yes <input type=\"radio\" name=\"approved\" value=\"on\"/>";
		
		if($checked=="off")
		echo "| No <input type=\"radio\" name=\"approved\" checked=\"checked\" value=\"off\"/>";
		else
		echo "| No <input type=\"radio\" name=\"approved\" value=\"off\"/>";

	?>
</div>