<?php
/**
 * simpleLogin.php - simpleLogin
 *
 * Copyright 2008-2010 foaf.me
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
require_once('lib/Authentication.php');
$auth = new Authentication_FoafSSLARC($GLOBALS['config'], NULL, FALSE);
?>
<body>
    <h1>FOAF+SSL Simple Login Page</h1>

    <!--
    <form><input type=button value="Click Here For Login Diagnostics" onclick="javascript:document.getElementById('diagnostics').style.display = 'block'" /></form>
    -->


    <div id="diagnostics">
        <?
        if ($_SERVER['SSL_CLIENT_CERT']) {
            $certModulus = $auth->certModulus;
            $certExponent = $auth->certExponent;
            $subjectAltName = $auth->certSubjectAltName['URI'];
            $agentArc = new Authentication_AgentARC($GLOBALS['config'], $auth->webid);
            $agent = $agentArc->getAgent();
            if ( isset($agent['RSAKey']) ) {
                $foaf_rsakey = $agent['RSAKey'];
            }
        }

        if ($auth->isAuthenticated())
            print "<b>The login Suceeded! Authenticated as:  $subjectAltName</b><p>Technical Explanation:</p>";
        else
            print "<b>Not Logged In</b><br/><br/>";


        if ($_SERVER['SSL_CLIENT_CERT'])
            print 'SSL Client Certificate: <span style="color:green">detected!</span><BR><BR>';
        else
            print 'SSL Client Certificate: <span style="color:green">Not detected!</span><BR><BR>';


        if ($_SERVER[SSL_CLIENT_CERT]) {

            print "Client Certificate Public Key <span style='color:green'>detected! (HEX):<br>";

            print "<pre>";
            print "Modulus : <br /><span style='color:green'>".wordwrap($certModulus, 80, "<br />", true)."</span><br/>";
            print "Exponent : <span style='color:green'> ".wordwrap($certExponent, 80, "<br />", true)." </span><br/>";
            print "</pre></span>";
        }
        else
            print "Client Certificate Public Key: <span style='color:green'>Not detected!</span><BR><BR>";


        if ($subjectAltName) {
            print "Subject Alt Name (FOAF Profile): <span style='color:green'>detected!: $subjectAltName</span><BR><BR>";
        }
        else
            print "Subject Alt Name: <span style='color:green'>Not detected!</span><BR><BR>";


        if ( $foaf_rsakey ) {
            print "FOAF Remote Public Key found in $subjectAltName:<br><span style='color:green'>";

            foreach ($foaf_rsakey as $rsa_key) {
                print "<pre>";
                print "Modulus : <br /><span style='color:green'>".wordwrap($rsa_key['modulus'], 80, "<br />", true)."</span><br/>";
                print "Exponent : <span style='color:green'> ".wordwrap($rsa_key['exponent'], 80, "<br />", true)." </span><br/>";
                print "</pre></span>";
            }
        }
        else
            print "FOAF Remote Public Key: <span style='color:green'>Not detected!</span><BR><BR>";


        ?>
    </div>

    <br/>
    <SCRIPT>
        function show() {
            document.getElementById('rdf').style.display = 'block';
            document.getElementById('rdfa').style.display = 'block';
        }
    </SCRIPT>




    <a href="javascript:show()">more</a>

    <div id=rdfa style='display:none'>
        <h2>RDF Representation</h2>
        <textarea rows=20 cols=200>
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:cert="http://www.w3.org/ns/auth/cert#"
	xmlns:rsa="http://www.w3.org/ns/auth/rsa#">

<rsa:RSAPublicKey>
   <cert:identity rdf:resource="<? echo $subjectAltName ?>"/>
   <rsa:public_exponent cert:decimal="<?php echo $certExponent ?>"/>
   <rsa:modulus cert:hex="<?php echo $certModulus ?>"/>
</rsa:RSAPublicKey>

</rdf:RDF>
        </textarea>
    </div>

    <div id=rdf style='display:none'>
        <h2>RDFa Representation</h2>
        <textarea rows=10 cols=200>
<span typeof="rsa:RSAPublicKey">
<div about="#cert" typeof="rsa:RSAPublicKey">
  <div rel="cert:identity" href="<?php echo $subjectAltName?>"></div>
  <div rel="rsa:public_exponent">
    <div property="cert:decimal" content="<?php echo $certExponent ?>"></div>
  </div>
  <div rel="rsa:modulus">
    <div property="cert:hex" content="<?php echo $certModulus ?>"></div>
  </div>
</div>
</span>

        </textarea>
    </div>

</body>
</html>
