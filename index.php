<TITLE>AP Latin HW</TITLE>
<?php

require_once('SQLConnection.php');
// echo $_GET['line'];
// echo $_GET['author'];

$NextLine = SQLQuarry('SELECT  min(id)  FROM `^Latin4InCatilinamText` WHERE `definitionId` = 0', true)[0];
$NextLine = SQLQuarry('SELECT `book`, `chapter`, `lineNumber` FROM `^Latin4InCatilinamText` WHERE `id` = '. $NextLine)[0];
 
?>
<!-- <a href = 'TextParser.php'>Text Parser</a><BR> -->
<!-- <a href = 'DictionaryParser.php'>Dictionary Parser</a><BR> -->
<a href = 'Dictionary.php'>Dictionary</a><BR>
<a href = 'Definer.php?title=InCatilinam&level=4&book=<?php echo $NextLine['book'];?>&chapter=<?php echo $NextLine['chapter'];?>&line=<?php echo $NextLine['lineNumber'];?>'>Definer</a><BR>
<a href = 'HomeworkViewer.php?hw=1'>Homework Viewer</a><BR>
<a href = 'AddNotes.php?hw=1'>Add Notes</a><BR>
<a href = 'HomeworkSync.php'>Homework Sync</a><BR>
<a href = 'https://s513.altervista.org/phpmyadmin/?sid=c9590099a532d3c364f6e3e8b3f25f77#PMAURL-0:index.php?db=&table=&server=1&target=&lang=en&collation_connection=utf8mb4_general_ci&token=e18d06d00ccd6305ec345a226dce05db'>PHP My Admin</a><BR>




<?

// SELECT * FROM `#APDictionary` WHERE `id` NOT IN (SELECT  `definitionId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `definitionId` FROM `#APAeneidText`  ) and  `id` NOT IN (SELECT  `secondaryDefId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `secondaryDefId` FROM `#APAeneidText`  )

?>