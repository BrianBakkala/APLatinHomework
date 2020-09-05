<TITLE>AP Latin HW</TITLE>
<?php

require_once('SQLConnection.php');
// echo $_GET['line'];
// echo $_GET['author'];

$NextLine = SQLQuarry('SELECT  min(id)  FROM `#APDBGText` WHERE `definitionId` = 0', true)[0];
$NextLine = SQLQuarry('SELECT `book`, `chapter`, `lineNumber` FROM `#APDBGText` WHERE `id` = '. $NextLine)[0];
 
?>
<!-- <a href = 'TextParser.php'>Text Parser</a><BR> -->
<!-- <a href = 'DictionaryParser.php'>Dictionary Parser</a><BR> -->
<a href = 'Dictionary.php'>Dictionary</a><BR>
<a href = 'Definer.php?author=C&book=<?php echo $NextLine['book'];?>&chapter=<?php echo $NextLine['chapter'];?>&line=<?php echo $NextLine['lineNumber'];?>'>Definer</a><BR>
<a href = 'HomeworkViewer.php?hw=1'>Homework Viewer</a><BR>
<a href = 'AddNotes.php?hw=1'>Homework Viewer</a><BR>
<a href = 'HomeworkSync.php'>Homework Sync</a><BR>
<a href = 'https://s462.altervista.org/phpmyadmin/?sid=79a8662e7b1f393725c7d9f61189d517#PMAURL-0:index.php?db=&table=&server=1&target=&token=f19f4338630c1be4ab9c697003e64e06'>PHP My Admin</a><BR>




<?

// DELETE FROM `#APDictionary` WHERE `id` NOT IN (SELECT  `definitionId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `definitionId` FROM `#APAeneidText`  ) and  `id` NOT IN (SELECT  `secondaryDefId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `secondaryDefId` FROM `#APAeneidText`  )

?>