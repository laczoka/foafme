<?php
/** 
 * head.php - initialise the framework, add libraries
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
require_once 'FoafRequest.php';


// Start a session
session_start();
$loggedIn = false;


// Include libraries
// TODO: include libAuthentication, and set up FOAF:Agent from a REQUEST
require_once("lib/libImport.php");


// Check to see if we are importing a WebID, if so populate $import
$import = getImport();

$foafRequest = FoafRequest::get();

if ($foafRequest->isAuth) {
    $authAgent = $foafRequest->viewer;
    $webid = $foafRequest->displayedWebid;
    $agent = $foafRequest->foafToBeDisplayed;
}

$webidbase = preg_replace('/#.*/', '', $webid);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"
    "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<!--
 * FOAF Me : FOAF Me Home Page and FOAF creator wizard.
 * Copyright (c) http://foaf.me/
 * AGPL (AGPL-license.txt) license.
 *
-->
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:dcterms="http://purl.org/dc/terms/"
      xmlns:foaf="http://xmlns.com/foaf/0.1/"
      xmlns:vcard="http://www.w3.org/2006/vcard/ns#"
      xmlns:biografr="http://biografr.com/ontology#">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <title>FOAF Me</title>
        <link rel="stylesheet" href="css/jquery.tabs.css" type="text/css" media="print, projection, screen" />
        <!--
        <link rel="shortcut icon" href="/img/favicon.png" type="image/png" />	
        -->
        <link rel="stylesheet" type="text/css" title="default" href="css/main.css" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <script type="text/javascript" src="js/jquery/1.3.1/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery/jquery.tabs.pack.js"></script>
        <script type="text/javascript" src="js/jquery.rdfquery.core-1.0.js"></script>
        <script type="text/javascript" src="js/jquery.rdfquery.rdfa-1.0.js"></script>
        <script type="text/javascript" src="js/jquery.rdfquery.rules-1.0.js"></script>
        <script type="text/javascript" src="js/jquery.editinplace.js"></script>
        <script type="text/javascript" src="js/jquery.json-1.3.js"></script>


        <link rel="stylesheet" href="css/jquery.tabs-ie.css" type="text/css" media="projection, screen" />
        <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

        <script type="text/javascript">
            // <!--

            /* JQuery Script */
            /*****************/       

            // Initialise page
            $(function() {
                $('#container').tabs({ fxFade: true, fxSpeed: 'fast' });
                gGeneratorAgent = 'http://<?php echo $_SERVER['HTTP_HOST'] ?>';
                gErrorReportsTo = 'mailto:error@<?php echo $_SERVER['HTTP_HOST'] ?>';
                makeTags();
            });


            // TODO: make this more generic
            function adda() {
                $("#accounts tr:last").clone().appendTo("#accountstable");
            }

            // TODO: make this more generic
            function addi() {
                $("#interests tr:last").clone().appendTo("#intereststable");
            }

            // TODO: make this more generic
            function del(el) {
                str = "#" + el;
                frag = $(str).parent().parent().rdf().databank.triples()[0].subject.value;

                var re = new RegExp("[0-9A-Za-z]*$","ig");
                var resultArray = re.exec(frag);

                while (resultArray) {
                    frag = resultArray[0];
                    resultArray = re.exec(str);
                }

                frag = "<?php echo $agent ?>#" + frag;
                var sparul = 'DELETE { ';
                $(str).parent().parent().rdf().databank.triples().each(function () { sparul += this + ' '; } );
                sparul += ' } ';
                //alert('DELETE:  This funcionality is in Alpha ' + sparul);
                jQuery.post( '<?php echo $_REQUEST['webid'] ?>', sparul );

                $(str).parent().parent().remove();
                //location.reload();
            }


            // TODO: use RDFQuery for this
            function makeTags() {

                // set uri from nick
                if ( $("#nick").val() != "" ) {
                    $("#username").val($("#nick").val());
                } else {
                    return;
                }

                // set homepage from nick
                if ( $("#homepage").val() == "") {
                    <?php $root =  $_SERVER['HTTP_HOST'] . ((dirname($_SERVER['PHP_SELF'])=='/')?'':dirname($_SERVER['PHP_SELF'])); ?>
                    $("#homepage").val( "http://<?php echo $root ?>/" + $("#nick").val() );
                    $("#displayname").html( "http://<?php echo $root ?>/" + $("#nick").val() + "#me" );
                    $("#saving").css("display", "inline");;
                }

                rdf  = '<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n      ' +
                    'xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"\n      ' +
                    'xmlns:foaf=\"http://xmlns.com/foaf/0.1/\"\n      ' +
                    'xmlns:rsa=\"http://www.w3.org/ns/auth/rsa#\"\n      ' +
                    'xmlns:cert=\"http://www.w3.org/ns/auth/cert#\"\n      ' +
                    'xmlns:admin=\"http://webns.net/mvcb/\">\n';

                // preprocess

                // PersonalProfileDocument
                $("[typeof=foaf:PersonalProfileDocument]") .each(function (i) {
                    rdf += makeStartTag(this);
                    $(this).find("[property],[rel]").each( function(){ rdf += (makeTag(this)) ; }  );
                    rdf += makeEndTag(this);

                });

                // Person #me
                $("[typeof=foaf:Person]:first").each(function (i) {

                    // Profile
                    rdf += makeStartTag(this);
                    $("#me").find("[property],[rel]").each( function(){ rdf += (makeTag(this)) }  );
                    $("#accounts").find("[property],[rel]").each( function(){ rdf += (makeTag(this)) }  );
                    $("#interests").find("[property],[rel]").each( function(){ rdf += (makeTag(this)) }  );

                    // Friends tab
                    var friends = '';
                    $("#friends [typeof=foaf:Person]").each(function (i) {
                        var friend = ''
                        $(this).find("[property],[rel]").each( function(){ friend += '' + makeTag(this)? '        ' + makeTag(this):'' ; }  );
                        if (friend) {
                            friends = '        ' + makeStartTag(this);
                            friends += friend;
                            friends += '        ' + makeEndTag(this);
                            rdf += '    <foaf:knows>\n';
                            rdf += friends;
                            rdf += '    </foaf:knows>\n';
                        }
                    });

                    rdf += makeEndTag(this);
                });

                // Security tab
                var id = '';
                var cert = '';
                $("#security [typeof]").each(function (i) {
                    $(this).parent().find("[property],[rel]").each( function(){ cert += '' + makeTag(this)? '        ' + makeTag(this):'' ; }  );
                    if (cert) {
                        id += '    ' + makeStartTag(this);
                        id += cert;
                        id += '    ' + makeEndTag(this);
                    }
                });
                if (id) {
                    rdf += '<rdf:Description>\n<rdf:type rdf:resource="http://www.w3.org/ns/auth/rsa#RSAPublicKey"/>\n<cert:identity rdf:resource="#me"/>\n';
                    rdf += cert;
                    rdf += '</rdf:Description>\n';
                }

                rdf += '</rdf:RDF>\n'
                //alert(rdf);
                $("#rdf").val(rdf);
            }

            function makeStartTag(el) {
                var about = $(el).attr("about") ? $(el).attr("about") : $(el).find("[rel]").val() ;
                return '<' + $(el).attr("typeof")  + (about?(' rdf:ID="'+about+'"'):(' rdf:about=""'))  + '>\n';
            }

            function makeEndTag(el) {
                return '</' + $(el).attr("typeof") + '>\n';
            }


            function makeTag(el) {
                var prop   = $(el).attr("property");
                var type   = $(el).attr("typeof");
                var about  = $(el).attr("about");
                var rel    = $(el).attr("rel");
                var val    = $(el).val();
                var cl     = $(el).attr("class");

                var inner  = '';
                if (cl && cl.indexOf(':') != -1) {
                    inner = cl;
                }

                var href   = $(el).attr("href")?$(el).attr("href"):val;

                if (!val && !href) return '';
                if (rel)           return '    <' + rel  + ' rdf:resource="'+ href +'"' + '/>\n';
                if (prop)          return '    <' + prop + ((inner)?' rdf:parseType="Resource"':'') + '>' + (inner?'<'+inner+'>':'') + val + (inner?'</'+inner+'>':'') + '</' + prop + '>' + '\n';
                if (type)          return '    <' + type + ' rdf:ID="'+ about +'"' + '>' + '</' + type + '>' + '\n';

            }

            // -->
        </script>




    </head>
