

<script>
	function ToggleQuote(clickedElement)
	{
		var Pele = clickedElement.parentElement; 
		if(Pele.getAttribute('expanded') != "true")
		{
			Pele.setAttribute('expanded', 'true')
		}
		else
		{
			Pele.setAttribute('expanded', 'false')
		}
	}
</script>