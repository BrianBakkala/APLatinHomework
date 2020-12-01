<?php

require_once ( 'GenerateNotesandVocab.php');
$context = new Context;

	$CSSColors = 
	
	[
		"Aeneid" =>
		[
			'BackgroundColor' => "FFF1AC",
			'HeaderColor' => "FFE667",
			'WordHighlightColor' => "FFE24F",
			'HeaderTextColor' => "black"
		],
	
	"DBG" =>
		[
			'BackgroundColor' => "D3E5FF",
			'HeaderColor' => "abcdff",
			'WordHighlightColor' => "94beff",
			'HeaderTextColor' => "black"
		],
	
	"InCatilinam" =>
		[
			'BackgroundColor' => "c5ffbf",
			'HeaderColor' => "8cff80",
			'WordHighlightColor' => "1aff00",
			'HeaderTextColor' => "black"
		],
	
	"Catullus" =>
		[
			'BackgroundColor' => "f8edeb",
			'HeaderColor' => "f9dcc4",
			'WordHighlightColor' => "fec89a",
			'HeaderTextColor' => "black"
		]
	]

	



	
?>

	
	<style>
	
	*[aponly]
	{
		<?php
		
			if(!($context->GetLevel() == "AP"))
			{
				echo "display:none;";
			}
		
		?>
	}

		
	*[nolatin3]
	{
		<?php
		
			if($context->GetLevel() == "3")
			{
				echo "display:none;";
			}
		
		?>
	}

	html {
		-webkit-tap-highlight-color:  rgba(255, 255, 255, 0); 
		margin:-8px;
		background-color:<?php echo $CSSColors[$context->GetBookTitle()]['BackgroundColor']; ?>;
		overflow-x:hidden;
		scroll-behavior: smooth;
	}

	h1 {
		font-size: 24pt;
		display:inline-block;
		margin-block-end: 0em;
	}

	line {

		display: block;
		padding-bottom: 1.5em;
	}

	line::before {
		content: attr(citation);
		top:17.6pt;
		position:relative;
		font-size: 12pt;
		vertical-align: middle;
		text-align: center;
		padding: 8px;
		color:gray;
		font-family:Cinzel;
		
	}

	word {
		cursor: pointer;
		display: inline-block;
		padding: 6.5px;
		vertical-align: top;
	}

	word:hover {
		border-radius: 8px;
		background-color: <?php echo $CSSColors[$context->GetBookTitle()]['WordHighlightColor'];?>;
	}

	text {
		font-size: 22pt;
		display: inline-block;
		text-align: center;
		padding-bottom: 6px;
	}


	entry {
		font-weight: bold;
		display: block;
		text-align: center;
	}

	definition {
		padding-left: 3px;
		font-style: italic;
		text-align: center;
		display: block;
	}

	entry,
	definition {
		-webkit-transition: .25s all ease-in-out; 
		transition: .25s all ease-in-out;
		/*transition-delay: .5s*/
		font-size: 0;
	}


	baseword,
	clitic {
		display: inline-block;
	}


	word[reveal="true"] entry,
	word[preview="true"] entry
	{
		border-top: 1px solid lightgray;
	}


	word[reveal="true"] baseword,
	word[reveal="true"] clitic
	{
		border-right: 2px solid darkgray;
	}

	word[reveal="true"] baseword
	{
		border-left: 2px solid darkgray;
	}

	
	word[preview="true"] baseword,
	word[preview="true"] clitic
	{
		border-right: 2px solid darkgray;
	}

	word[preview="true"] baseword
	{
		border-left: 2px solid darkgray;
	}


	word[reveal="true"] entry,
	word[preview="true"] entry,
	word[reveal="true"] definition,
	word[preview="true"] definition
	{
		font-size: 18pt;

	}

	word[reveal="true"] baseword,
	word[preview="true"] baseword,
	word[reveal="true"] clitic,
	word[preview="true"] clitic
	{
		padding: 5px;
	}

	word[reveal="true"]:not([clitic=""]) clitic text::before,
	word[preview="true"]:not([clitic=""]) clitic text::before
	{
		content: "-";
	}

	note
	{
		opacity:1;
		-webkit-transition: .7s all ease-in-out; 
		transition: .7s all ease-in-out;
	}

	note[highlighted="false"]
	{
		opacity:0.1;
	}
	
	freq {
		color: rgba(0, 0, 0, 0);
		display: block;
	}

	freq a {
		color: inherit;
    cursor: pointer;
	text-decoration: none;
	border-radius:25%;
	padding-right:4px;
	padding-left:4px;
	}

	freq a:hover {
		background-color: <?php echo $CSSColors[$context->GetBookTitle()]['BackgroundColor']; ?>;
 
	}


	word:hover freq {

		color: rgba(0, 0, 0, 1);
	}

	#rightarrow,
	#leftarrow
	{
		height: 2.5em;
		display:inline-block;

		padding-left:1em;
		padding-right:1em;
		margin-top: 1.25em;
		<?php
			if($CSSHeaderTextColor == "white")
			{
				echo "filter:invert(100);";
			}
		?>
		
	}

	#leftarrow {
		transform: scaleX(-1);
	}

	assignment, notes
	{
		display:inline-block;
		-webkit-transition: all 0.5s ease;
		-moz-transition: all 0.5s ease;
		-o-transition: all 0.5s ease;
		transition: all 0.5s ease;
	}

	notes
	{	
		line-height: 1.6;
		z-index: 1000;
		position: fixed;
		right: 250px;
		width: 0;
		height: 100%;
		margin-right: -250px;
		overflow-y: scroll;
		text-align:left;
	}

	wrapper
	{
		--notes-width: 28%;
	}

	wrapper[shownotes="true"] notes
	{		
		width: var(--notes-width);
	}

	assignment
	{
		text-align:center;
		width: 100%;
	}

	wrapper[shownotes="true"] assignment
	{
		left:0;
		width: calc(99.5% - var(--notes-width));
	}

	vocabword
	{
		display:block;
	}


	.literarydevice
	{
		font-variant:small-caps;
	}

	.tooltiptext
	{
		font-variant:none;
	}

	.literarydevice 
	{
		position: relative;
		display: inline-block;
		cursor: help;
		text-decoration-style: dotted;
		text-decoration-line: underline;
		text-decoration-color: gray;
	}

	.literarydevice .tooltiptext
	{
		visibility: hidden;
		width: 200px;
		background-color: black;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 10px 5px;
		
		/* Position the literarydevice */
		position: absolute;
		z-index: 1;
		top: 100%;
		left: 50%;
		margin-left: -60px;
	}

	.literarydevice:hover .tooltiptext
	{
		visibility: visible;
	}




	header, header table, header duedate
	{  margin-left: auto;
		margin-right: auto;
		color:<?php echo $CSSColors[$context->GetBookTitle()]['HeaderTextColor']; ?>;
		text-align:center;
		background-color:<?php echo $CSSColors[$context->GetBookTitle()]['HeaderColor']; ?>;
		position: relative; 
	}
	
	.menu-bar-option
	{
		font-weight:bold;
		text-transform: uppercase;
	}
	
	header
	{
		position: -webkit-sticky;
		position: sticky;
		top:-116px;
		z-index:1;
	}

	select,	.menu-bar-option, .submenu-item
	{
		font-family: "Mulish", serif; 
		margin-left: 15px;
		margin-right: 15px;
	}


	.menu-bar-option a, .submenu-item a
	{
		color:<?php echo $CSSColors[$context->GetBookTitle()]['HeaderTextColor']; ?>;
		text-decoration:none;
		
	}

</style>