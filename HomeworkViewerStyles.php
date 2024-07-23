<?php

require_once 'autoload.php';

use app\Context;

$CSSColors =

    [
    "Aeneid" =>
    [
        'BackgroundColor' => "FFF1AC",
        'HeaderColor' => "FFE667",
        'WordHighlightColor' => "FFE24F",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ],

    "DBG" =>
    [
        'BackgroundColor' => "D3E5FF",
        'HeaderColor' => "abcdff",
        'WordHighlightColor' => "94beff",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ],

    "InCatilinam" =>
    // [
    //     'BackgroundColor' => "#EEEEEE",
    //     'HeaderColor' => "BB9813",
    //     'WordHighlightColor' => "black",
    //     'HeaderTextColor' => "gold",
    //     'TextColor' => "BBBB13"
    // ]
    [
        'BackgroundColor' => "c5ffbf",
        'HeaderColor' => "8cff80",
        'WordHighlightColor' => "1aff00",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ],
    "PlinyEpistulae" =>
    [
        'BackgroundColor' => "c5ffbf",
        'HeaderColor' => "8cff80",
        'WordHighlightColor' => "1aff00",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ],
    "AUC" =>
    [
        'BackgroundColor' => "ffe3fe",
        'HeaderColor' => "b4aee8",
        'WordHighlightColor' => "c28bc8",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ],

    "Catullus" =>

    [
        'BackgroundColor' => "#BEE7E8",
        'HeaderColor' => "#A0D2DB",
        'WordHighlightColor' => "#89f5d6",
        'HeaderTextColor' => "black",
        'TextColor' => "black",
    ], //Isabel/Vedant

    // [
    //     'BackgroundColor' => "black",
    //     'HeaderColor' => "black",
    //     'WordHighlightColor' => "black",
    //     'HeaderTextColor' => "black",
    //     'TextColor' => "black"
    // ] //Dalton

    // [
    //     'BackgroundColor' => "red",
    //     'HeaderColor' => "darkred",
    //     'WordHighlightColor' => "gold",
    //     'HeaderTextColor' => "yellow",
    //     'TextColor' => "yellow"
    // ] //McDonalds

    //         [
    //     'BackgroundColor' => "white",
    //     'HeaderColor' => "white",
    //     'WordHighlightColor' => "black",
    //     'HeaderTextColor' => "cornsilk",
    //     'TextColor' => "cornsilk"
    // ]

    // [
    //     'BackgroundColor' => "#572600",
    //     'HeaderColor' => "black",
    //     'WordHighlightColor' => "gray",
    //     'HeaderTextColor' => "white",
    //     'TextColor' => "white"
    // ] //Dalton 2.0

    // [
    //     'BackgroundColor' => "#D3E5FF",
    //     'HeaderColor' => "lightblue",
    //     'WordHighlightColor' => "#4ee6bb",
    //     'HeaderTextColor' => "white",
    //     'TextColor' => "black"
    // ]

    // [
    //     'BackgroundColor' => "#c3dde3",
    //     'HeaderColor' => "#167eaf",
    //     'WordHighlightColor' => "#e1b783",
    //     'HeaderTextColor' => "black",
    //     'TextColor' => "black"
    // ] //Willem
];

?>


	<style>


	html {
		-webkit-tap-highlight-color:  rgba(255, 255, 255, 0);
		margin:-8px;
		background-color:<?php echo $CSSColors[Context::getBookTitle()]['BackgroundColor']; ?>;
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
		color:<?php echo $CSSColors[Context::getBookTitle()]['TextColor']; ?>;
		cursor: pointer;
		display: inline-block;
		padding: 6.5px;
		vertical-align: top;
		/* font-family: 'Comic Sans MS', cursive; */
	}

	word:hover {
		border-radius: 8px;
		background-color: <?php echo $CSSColors[Context::getBookTitle()]['WordHighlightColor']; ?>;
	}

	text, nomacrons {
		font-size: 22pt;
		display: inline-block;
		text-align: center;
		padding-bottom: 6px;
	}


	entry {
		display: block;
		text-align: center;
	}

	definition {
		padding-left: 3px;
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




	word[reveal] entry,
	word[preview="true"] entry
	{
		border-top: 1px solid lightgray;
	}


	word[reveal] baseword,
	word[reveal] clitic
	{
		border-right: 2px solid darkgray;
	}

	word[reveal] baseword
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


	word[reveal] entry,
	word[preview="true"] entry,
	word[reveal] definition,
	word[preview="true"] definition
	{
		font-size: 18pt;

	}

	word[reveal] baseword,
	word[preview="true"] baseword,
	word[reveal] clitic,
	word[preview="true"] clitic
	{
		padding: 5px;
	}

	word[reveal]:not([clitic=""]) clitic text::before,
	word[preview="true"]:not([clitic=""]) clitic text::before,
	word[reveal]:not([clitic=""]) clitic nomacrons::before,
	word[preview="true"]:not([clitic=""]) clitic nomacrons::before
	{
		content: "-";
	}

	note
	{
		color:<?php echo $CSSColors[Context::getBookTitle()]['TextColor']; ?>;
		font-size:14pt;
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
		background-color: <?php echo $CSSColors[Context::getBookTitle()]['BackgroundColor']; ?>;

	}


	word:hover freq {

		color: rgba(0, 0, 0, 1);
	}

	#rightarrow,
	#leftarrow
	{
		height: 1.3em;
		display:inline-block;

		padding-left:1em;
		padding-right:1em;
		margin-top: 1.25em;
		<?php
if ($CSSColors[Context::getBookTitle()]['HeaderTextColor'] == "white")
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

	wrapper[show-notes] notes
	{
		width: var(--notes-width);
	}

	assignment
	{
		text-align:center;
		width: 100%;
	}

	wrapper[show-notes] assignment
	{
		left:0;
		width: calc(99.5% - var(--notes-width));
	}

	wrapper  .toggle-notes-text::after,
	wrapper  .toggle-macrons-text::after
	{
		content:"ff";
	}

	wrapper[show-notes] .toggle-notes-text::after,
	wrapper[show-macrons] .toggle-macrons-text::after
	{
		content:"n";
	}


	wrapper[show-macrons] nomacrons, wrapper:not([show-macrons]) text
	{
		display:none;
	}

	wrapper:not([show-macrons]) nomacrons, wrapper[show-macrons] text
	{
		display:inherit;
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
		padding: 10px 10px;
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

	author
	{
		z-index:100;
		font-family:Cinzel;

	}


	note
	{
		display:block;
	}


	quote[expanded="true"]
	{
		display:block;
		margin-top:.5em;
		margin-bottom:.5em;
		border-top:1px solid gray;
		border-bottom:1px solid gray;
	}

	quotetitle
	{
		font-weight:bold;
		color:blue;
		cursor:pointer;
	}

	quoteline
	{

		font-size:0;

		transition: all .3s ease-in-out;
	}

	quote[expanded="true"] quoteline
	{
		display:block;
		font-size:inherit;

	}



	header, header table, header duedate
	{  margin-left: auto;
		margin-right: auto;
		color:<?php echo $CSSColors[Context::getBookTitle()]['HeaderTextColor']; ?>;
		text-align:center;
		background-color:<?php echo $CSSColors[Context::getBookTitle()]['HeaderColor']; ?>;
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
		top:-138px;
		z-index:1;
	}

	submenu:nth-of-type(1)
	{
		padding-top:15px;
	}

	submenu
	{
		display: block;
		padding-bottom:10px;
	}

	select,	.menu-bar-option, .submenu-item
	{
		font-family: "Mulish", serif;
		margin-left: 15px;
		margin-right: 15px;
	}


	.menu-bar-option a, .submenu-item a
	{
		color:<?php echo $CSSColors[Context::getBookTitle()]['HeaderTextColor']; ?>;
		text-decoration:none;

	}

</style>