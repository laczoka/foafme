<?
/*
 * clearSession.php
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



session_name('phpMyID_Server');
session_start();
session_destroy();

if (!empty($_REQUEST['return_to']) ) {
	header('Location: ' . $_REQUEST['return_to']);
}

?>