<?php

use app\Context;

require_once 'autoload.php';
require_once 'utility/debug.php';

$DocumentID = "11j0cC45e8RBiHbt0FKzJ-gHUZ_fEpDQzVo-cEU5eYAU";
$DocumentURL = "https://sheets.googleapis.com/v4/spreadsheets/" . $DocumentID . "/values/Export?alt=json&key=AIzaSyCN9ZxUhMb9zQW7rK4ZSaP1S4NJ7EKc_es";
$ExportPageNumber = 7;

?>
<script name = 'Context Globals'>

    const CONTEXT =
    {
        level: JSON.parse(`<?php echo json_encode(Context::getLevel()) ?>`),
        title: JSON.parse(`<?php echo json_encode(Context::getBookTitle()) ?>`),
        test_status: JSON.parse(`<?php echo json_encode(Context::getTestStatus()) ?>`),
    }

    const DOCUMENT =
    {
        id: JSON.parse(`<?php echo json_encode($DocumentID) ?>`),
        export_page_number: JSON.parse(`<?php echo json_encode($ExportPageNumber) ?>`),
        url: JSON.parse(`<?php echo json_encode($DocumentURL) ?>`),
    }

</script>

<style>
*[ap-only] {
    font-size: inherit;

    <?php if (!(Context::getLevel() == "AP"))
{
    echo "display:none;";
}

?>
}



*[no-latin-3]
	{
        font-size: inherit;
		<?php

if (Context::getLevel() == "3")
{
    echo "display:none;";
}

?>
	}



</style>
