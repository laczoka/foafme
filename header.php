<?php
/** 
 * header.php - short header bar and login
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
} else {
    $webid = NULL;
}

?>

<body>
    <div id="wrap">
        <div id="header">
            <a href="index.php">Home</a> | <a href="http://groups.google.com/group/foafme">Mailing List</a>

            <?php

            // If logged in
            if (!empty($webid) ) {
                ?>
            <span id="user">
                <a id="logout" href="clearSession.php?return_to=<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]" ?>">
                    Logout <strong><?php echo $webid ?></strong>
                </a>
                <br/>
            </span>
            <?php } else { ?>
            <span id="user">
                <a id="account" href="https://foafssl.org/srv/idp?authreqissuer=<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]" ?>">
                    Login to your <strong>account</strong>
                </a>
                <br />
            </span>
            <?php } ?>

        </div>


        <div id="content">

            <h1>
                <img alt="sparul" src="images/rdf.jpg" />
                <strong>SPARUL.ORG</strong>
            </h1>

            <h2></h2>




