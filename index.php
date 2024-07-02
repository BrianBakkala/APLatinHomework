<TITLE>AP Latin HW</TITLE>
<?php

require_once 'SQLConnection.php';
require_once 'GenerateNotesandVocab.php';
require_once 'ForwardHTTPS.php';

$context = new Context;
// echo $_GET['line'];
// echo $_GET['author'];

$Book3 = "Catullus";
$NextLine3 = SQLQuarry('SELECT  min(id)  FROM `' . Context::BOOK_DB[$Book3] . '` WHERE `definitionId` = 0', true)[0];
$NextLine3 = SQLQuarry('SELECT `book`, `chapter`, `lineNumber` FROM `' . Context::BOOK_DB[$Book3] . '` WHERE `id` = ' . $NextLine3)[0];

$Book4 = "AUC";
$NextLine4 = SQLQuarry('SELECT  min(id)  FROM `' . Context::BOOK_DB[$Book4] . '` WHERE `definitionId` = 0', true)[0];
$NextLine4 = SQLQuarry('SELECT `book`, `chapter`, `lineNumber` FROM `' . Context::BOOK_DB[$Book4] . '` WHERE `id` = ' . $NextLine4)[0];

?>
<!-- <a href = 'TextParser.php'>Text Parser</a><BR> -->
<!-- <a href = 'DictionaryParser.php'>Dictionary Parser</a><BR> -->
<a href = 'Dictionary.php'>Dictionary</a><BR>
<a href = 'Definer.php?title=<?php echo $Book3; ?>&level=3&book=<?php echo $NextLine3['book']; ?>&line=<?php echo $NextLine3['lineNumber']; ?>'>Definer 3</a><BR>
<a href = 'Definer.php?title=<?php echo $Book4; ?>&level=4&book=<?php echo $NextLine4['book']; ?>&chapter=<?php echo $NextLine4['chapter']; ?>&line=<?php echo $NextLine4['lineNumber']; ?>'>Definer 4</a><BR>
<BR>
<a href = 'HomeworkViewer.php?hw=<?php echo SQLQ('SELECT MAX(`HW`) FROM `~Latin3HW`'); ?>&level=3'>Homework Viewer 3</a><BR>
<a href = 'HomeworkViewer.php?hw=<?php echo SQLQ('SELECT MAX(`HW`) FROM `^Latin4HW`'); ?>&level=4'>Homework Viewer 4</a><BR>
<a href = 'HomeworkViewer.php?hw=1'>Homework Viewer AP</a><BR>
<BR>
<a href = 'TextParser.php'>Add Text</a><BR>
<a href = 'AddNotes.php?hw=1'>Add Notes</a><BR>
<BR>
<a href = 'HomeworkSync.php'>Homework Sync</a><BR>
<BR>
<a href = 'https://s519.altervista.org/dashboard.pl?sid=9bacea2c88ce68da2d0ad9de7483317d'>PHP My Admin</a><BR>

<BR>
<a href = 'ControlPanel.php'>Control Panel</a><BR>


<?

// SELECT * FROM `#APDictionary` WHERE `id` NOT IN (SELECT  `definitionId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `definitionId` FROM `#APAeneidText`  ) and  `id` NOT IN (SELECT  `secondaryDefId` FROM `#APDBGText`  ) and `id` NOT IN (SELECT  `secondaryDefId` FROM `#APAeneidText`  )

?>