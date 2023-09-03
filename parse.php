<?php

$dom = new DomDocument();

if ($argc) { $url=$argv[1]; } else { $url=$_GET["r"]; };

//$fake_user_agent = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11";
$fake_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0";

//ini_set('user_agent', $fake_user_agent);

$options = array('http' => array('user_agent'    => $fake_user_agent,
                                 'ignore_errors' => true),
                 'ssl'  => array(
                                 'verify_peer' => false, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
                                 'cafile'      => '/etc/ssl/certs/ca-certificates.crt',
                                 'ciphers'     => 'HIGH:!SSLv2:!SSLv3',
                                 'disable_compression' => true)
                 );

$context = stream_context_create($options);
$page    = file_get_contents($url, false, $context);

libxml_use_internal_errors(true);

if (! $dom->loadHTML($page) )  { echo "failed to load page $page\n"; };

libxml_use_internal_errors(false);

$xpath = new DOMXpath($dom);
$heading=parseToArray($xpath,'title','h1');

$tags = @get_meta_tags($url);

// libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings

$query = '//*/meta[starts-with(@property, \'og:\')]';
$metas = $xpath->query($query);
foreach ($metas as $meta) {
    $property = $meta->getAttribute('property');
    $content = $meta->getAttribute('content');
    $rmetas[$property] = $content;
}
//var_dump($rmetas);

$domain    = explode("/", $url,4);
//echo "::: domain: ". $domain[2] . "\n";

$domain[2] = strtoupper(preg_replace("/^www\./","", $domain[2]));

//echo "::: domain: ". $domain[2] . "\n";
//echo "\n\n";

$rmetas["og:title"] = $domain[2] ." // ". $rmetas["og:title"];

$heading[1] = $rmetas["og:title"];

echo "<!DOCTYPE html>\n <html lang='en'>\n  <head>\n\n";

echo "<!-- ". $url . "-->\n\n";

echo "   <title>". $heading[1] ."</title>\n";
echo '   <meta data-rh="true" name="description" content="'   . $tags["description"].   '">' ."\n";
echo '   <meta data-rh="true" name="twitter:image" content="' . $tags["twitter:image"]. '">' ."\n";

echo '   <meta data-hr="true" property="og:title"       content="' . $rmetas["og:title"]       . '">' ."\n";
echo '   <meta data-hr="true" property="og:description" content="' . $rmetas["og:description"] . '">' ."\n";
echo '   <meta data-hr="true" property="og:image"       content="' . $rmetas["og:image"]       . '">' ."\n";

if ($url == "" or !preg_match('/^http/',$url) ) {
  echo "</head><body>\nError - bad url: '<b>$url</b>'<br>\nuse <b>". $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0] . "?r=URL</b> please with http or https in url\n\n</body></html>\n";
  exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
function parseToArray($xpath,$elem1,$elem2)
{
    $xpathquery="//$elem1 | //$elem2";
    $elements = $xpath->query($xpathquery);

    if (!is_null($elements)) {
        $resultarray=array();
        foreach ($elements as $element) {
            $nodes = $element->childNodes;
            foreach ($nodes as $node) {
              $resultarray[] = $node->nodeValue;
            }
        }
        return $resultarray;
}   }

?>

  <script type="text/javascript" language="javascript">
    window.location.href = "<?php echo $url; ?>";
  </script>
  </body></html>
