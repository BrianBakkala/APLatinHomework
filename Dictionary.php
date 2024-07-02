<?php

require_once 'FontStyles.php';
require_once 'GenerateNotesandVocab.php';
require 'autoload.php'; // or 'vendor/autoload.php' if using Composer

use app\Context;

if (Context::getTestStatus())
{
    echo "<script>";
    echo "document.getElementsByTagName('html')[0].innerHTML = ('nope')";
    echo "</script>";
}
?>

<style>
*[ap-only] {
    font-size: inherit;

    <?php if (!(Context::getLevel() == "AP"))
{
    echo "display:none;";
}

?>
}
</style>

<!--

///////FIND UNUSED WORDS

SELECT * FROM `#APDictionary` LEFT JOIN `#APAeneidText` on (`#APAeneidText`.`definitionId` = `#APDictionary`.`id`) LEFT JOIN `#APDBGText` on (`#APDBGText`.`definitionId` = `#APDictionary`.`id`) where `#APAeneidText`.`definitionId` IS NULL AND `#APDBGText`.`definitionId` IS NULL and `entry` NOT LIKE "-%" AND `#APDictionary`.`id` > 0

-->

<p ap-only style = 'text-align:left;'><A href = 'UnitsViewer.php'>← Units</A></p>

<script src="js/global/utility.js<?php echo "?" . date("mds"); ?>" defer></script>
<script src="js/dictionary.js<?php echo "?" . date("mds"); ?>" defer></script>
<link rel="stylesheet" href="css/dictionary.css<?php echo "?" . date("mds"); ?>">


<CENTER><BR><BR>
<body  >
<form autocomplete="off">
<input placeholder = 'Search...' value = '<?php if (isset($_GET['word']))
{
    echo $_GET['word'];
}?>'   onkeyup = 'FilterDict(this.value);' type = "text" id = 'filterdict'><BR><BR>
</form>
<div style = 'display:none;' id = 'searchResult'></div>
<dictionary id = 'dictionary'>
</dictionary>


<BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR>