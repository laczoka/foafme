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
require_once('FoafRequest.php');

$foafRequest = FoafRequest::get();
$agent = $foafRequest->viewingAgent;
$webid = $foafRequest->viewingWebid;

?>

<body>
    <div id="wrap">
        <div id="header">
            <a href="index.php">Home</a> | <a href="/index.php?webid=http://www.w3.org/People/Berners-Lee/card">Explore FOAFs</a> | <a href="about.php">About</a> | <a href="documentation.php">Documentation</a> | <a href="downloads.php">Downloads</a> | <a href="http://groups.google.com/group/foafme?lnk=srg&amp;hl=en&amp;ie=UTF-8&amp;oe=utf-8">Mailing List</a>

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
                <img alt="foaf" src="images/foaf.gif" />
                <strong>FOAF</strong>
            </h1>

            <h2></h2>




