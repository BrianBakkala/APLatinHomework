<?php

use app\Context;

require_once 'autoload.php';
require_once 'utility/debug.php';

?>
<script name = 'Context Globals'>

    const CONTEXT =
    {
        level: JSON.parse(`<?php echo json_encode(Context::getLevel()) ?>`),
        title: JSON.parse(`<?php echo json_encode(Context::getBookTitle()) ?>`),
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
		<?php

if (Context::getLevel() == "3")
{
    echo "display:none;";
}

?>
	}



</style>
