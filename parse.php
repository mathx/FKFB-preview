<?php

// reputable news organizations for someone who randomly stumbled on this document
$urls = array(
		'https://www.reuters.com/',
		'https://apnews.com/',
		'https://www.bbc.com/',
		'https://www.cbc.ca/',
		'https://www.aljazeera.com/',
		'https://www.dw.com/',
		'https://www.afp.com/',
		'https://www3.nhk.or.jp/nhkworld/',
		'https://www.abc.net.au/',
		'https://www.npr.org/',
		'https://www.pbs.org/',
	      );

// honor GET requests but otherwise just choose (and return) a random news site URL
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_SERVER['QUERY_STRING']) {
	// requests are sometimes sent as urlencoded-strings with a stupid FB
	// client tracker ID tacked on as a query string, so decode as needed and
	// discard the tracker ID
	try {
	    $url = explode('&', urldecode($_SERVER['QUERY_STRING']))[0];
	} catch (Exception $e) {
	    echo "Failed to parse request: $e";
	}
	if ($url == "" or !preg_match('/^http/', $url)) {
	    $array_key = array_rand($urls, 1);
	    $url = $urls[$array_key];
	}
    } else {
	$array_key = array_rand($urls, 1);
	$url = $urls[$array_key];
    }
} else {
    $array_key = array_rand($urls, 1);
    $url = $urls[$array_key];
}

// this can be presumably any user-agent string
//$fake_user_agent = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11";
$fake_user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0";

// HTTP query options
$options = array('http' => array('user_agent'    => $fake_user_agent,
				 'ignore_errors' => true),
		 'ssl'  => array(
				 'verify_peer' => false, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
				 'cafile'      => '/etc/ssl/certs/ca-certificates.crt',
				 'ciphers'     => 'HIGH:!SSLv2:!SSLv3',
				 'disable_compression' => true)
		 );

// fetch desired URL from upstream
$context = stream_context_create($options);
$page = file_get_contents($url, false, $context);

// create a new DOM Document object
$dom = new DomDocument();

// enable libxml error handling
libxml_use_internal_errors(true);

// parse the retrieved document as HTML into the DOM Document object
if (!$dom->loadHTML($page)) {
    echo "failed to load page $page\n";
}

// enable user XML error handling
libxml_use_internal_errors(false);

// create a new DOMXpath object from the DOM Document object
$xpath = new DOMXpath($dom);

// parse document title info
$heading = parseToArray($xpath, 'title', 'h1');

// extract and save the document meta tags
$tags = get_meta_tags($url);

// extract document meta properties
$query = '//*/meta[starts-with(@property, \'og:\')]';
$metas = $xpath->query($query);
foreach ($metas as $meta) {
    $property = $meta->getAttribute('property');
    $content = $meta->getAttribute('content');
    $rmetas[$property] = $content;
}

// decompose URL into its constituent parts
$domain = explode("/", $url, 4);

// strip leading 'www' from the URL as necessary
$domain[2] = preg_replace("/^www\./", "", $domain[2]);

// prepend the site name to the title meta property and make that the title of the (cached) URL seen by FB
$rmetas["og:title"] = $domain[2] ." -- ". $rmetas["og:title"];
$heading[1] = $rmetas["og:title"];

// attempt to maintain the document language
$lang = preg_replace("/_/", "-", $rmetas['og:locale']);

// an array key with ':' in the name will cause the PHP heredoc parser to choke
$tags = replaceArrayKeys($tags);
$rmetas = replaceArrayKeys($rmetas);

// output the (cached) URL metadata
echo <<<"EOF"
<!DOCTYPE html>
<html lang="$lang">
<head>

<!-- $url -->

<title>$heading[1]</title>

<meta data-rh="true" name="description" content="$tags[description]">
<meta data-rh="true" name="twitter:image" content="$tags[twitter_image]">
<meta data-hr="true" property="og:title" content="$rmetas[og_title]">
<meta data-hr="true" property="og:description" content="$rmetas[og_description]">
<meta data-hr="true" property="og:image" content="$rmetas[og_image]">

EOF;

// all done

// helper function to collate arbitrary document elements
function parseToArray($xpath, $elem1, $elem2) {
    $xpathquery="//$elem1 | //$elem2";
    $elements = $xpath->query($xpathquery);

    if (!is_null($elements)) {
        $resultarray = array();
        foreach ($elements as $element) {
            $nodes = $element->childNodes;
            foreach ($nodes as $node) {
                $resultarray[] = $node->nodeValue;
            }
        }
        return $resultarray;
    }
}

// helper function to make array key names PHP-safe
function replaceArrayKeys($array) {
    $replacedKeys = str_replace(':', '_', array_keys($array));
    return array_combine($replacedKeys, $array);
}

?>

// this will transparently redirect to the requested document on page load
<script type="text/javascript" language="javascript">
    window.location.href = "<?php echo $url; ?>";
</script>
</body></html>
