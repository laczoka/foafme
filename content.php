<?php
/** 
 * content.php - the social application, in this case FOAF wizard
 *
 * Copyright 2008-2009 foaf.me
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * "Everything should be made as simple as possible, but no simpler."
 * -- Albert Einstein
 *
 */
require_once('config.php');
require_once('db.class.php');
require_once('lib/libAuthentication.php');
$auth = getAuth();
if ($auth['isAuthenticated'] == 1) {
     $webid = $auth['agent']['webid'];
     $name = !empty($auth['agent']['name'])?$auth['agent']['name']:$webid;
     if ($webid == $_REQUEST['webid'] || empty($_REQUEST['webid']) ) {
         $loggedIn = true;
     }
}

if (!empty($_REQUEST['webid'])) {
    $webid = $_REQUEST['webid'];
}
?>

<form action="index.php" method="get">
<div style='text-align:center'>
    <b style='color:blue'>Enter a URI, or LOGIN to see your own</b>
    <br/>
    <br/>
    <input size="40" name="webid" />
    <br/>
    <br/>
    Example: <a href="http://sparul.org/index.php?webid=http%3A%2F%2Fbblfish.net%2Fpeople%2Fhenry%2Fcard">http://bblfish.net/people/henry/card</a> , <a href="http://sparul.org/index.php?webid=http%3A%2F%2Ftobyinkster.co.uk">http://tobyinkster.co.uk/</a>
</div>
</form>

<?php

if (!empty($webid)) {
    //$webid = $auth['agent']['webid'];
    $parser = ARC2::getRDFParser();
    $parser->parse($webid);
    $triples = $parser->getTriples();

    echo "<h1>Triples</h1>";
    echo "<table>";
    echo "<tr><td><b>Subject</b></td><td><b>Predicate</b></td><td><b>Object</b></td></tr>";
    foreach ($triples as $k => $v) {
        echo "<tr><td>$v[s]</td><td>$v[p]</td><td>" . wordwrap($v['o'], 40, '<br/>', true) . "</td></tr>";
    }
    echo "</table>";

    echo "<h1>RDFa Serializer (ARC2 plugin)</h1>";
    /* Serializer instantiation */
    $ser = ARC2::getSer('RDFa', '');

    /* Serialize a triples array */
    $doc = $ser->getSerializedTriples($triples);
    echo $doc;


    echo "<h1>Raw Triples</h1>";
    echo '<pre>';
    print_r($triples);
    echo '</pre>';



}

?>
<script type='text/javascript'>
$(document).ready(function() {
  $('table tbody tr:odd').addClass('odd');
  $('table tbody tr:even').addClass('even');
});
</script>



