<?php
/*  Copyright 2002-2003 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script currently prevents WikiWords with a leading $ from being
    treated as WikiWord markup, primarily for PmWiki documentation.  
    A future version of this script will likely convert $VariableName 
    to be a link to the documentation for $VariableName if it exists.
*/

  $LinkPatterns[780]["\\$$WikiWordPattern"] = '$0';
?>
