<?php

$host = "localhost";
$username="jasonbao";
$password="4Sdyy0zw";
$db_name="Hackathon";
mysql_connect("$host", "$username", "$password")or die("cannot connect to database");
mysql_select_db("$db_name") or die("cannot select DB");

$ticker = "AAPL";
for($i=0; $i<=30; $i++){
$randomass = 0;
$randcount = 0;
$startDate = strtotime("-$i day", time());
$year = date("Y", $startDate);
$month = date("m", $startDate);
$day = date("d", $startDate);
$startDate = $year."-".$month."-".$day;
$ch = curl_init("http://finance.yahoo.com/q/h?s=".$ticker."&t=".$startDate);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
$output .= curl_exec($ch); 
curl_close($ch);
file_put_contents("start.txt", $startDate);

//echo $output;
$document = new DOMDocument;
libxml_use_internal_errors(true);
$document->loadHTML($output);
$xpath = new DOMXPath($document);
$hrefs = $xpath->query("//div[@class='mod yfi_quote_headline withsky']/ul/li/a/@href");

foreach($hrefs as $href){
echo "<h1>".$href->nodeValue."</h1>";
$newch = curl_init($href->nodeValue);
curl_setopt($newch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($newch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects, need this if the url changes
$articleOut= curl_exec($newch);
curl_close($newch);
//$temp = '<script(.*)</script>';
//$articleOut = preg_replace($temp, "", $articleOut);

$artDoc= new DOMDocument;
libxml_use_internal_errors(true);
$artDoc->loadHTML($articleOut);
$Artxpath = new DOMXPath($artDoc);
$encodeArticle;
if(stripos($href->nodeValue, 'fool.com')){
$articles = $Artxpath->query("//div[@class='entry-content']");
foreach($articles as $article){
//echo "<p style='color:red'>".$article->nodeValue."</p>";
$encodeArticle = urlencode($article->nodeValue);
}
}

if(stripos($href->nodeValue, 'news.investors.com')){
$articles = $Artxpath->query("//div[@itemprop='articleBody']");
foreach($articles as $article){
//echo "<p style='color:green'>".$article->nodeValue."</p>";
$encodeArticle = urlencode($article->nodeValue);
}
}

if(stripos($href->nodeValue, 'finance.yahoo.com')){
$articles = $Artxpath->query("//div[@class='body yom-art-content clearfix']/p");
$temp = "";
foreach($articles as $article){
//echo "<p style='color:yellow'>".$article->nodeValue."</p>";
$temp.=$article->nodeValue;
}
$encodeArticle = urlencode($temp);
}

if(stripos($href->nodeValue, 'thestreet.com')){
$articles = $Artxpath->query("//div[@id='storyBody']/p");
$temp = "";
foreach($articles as $article){
//echo "<p style='color:yellow'>".$article->nodeValue."</p>";
$temp .=$article->nodeValue;
}
$encodeArticle = urlencode($temp);
}

if(stripos($href->nodeValue, 'blogs.barrons.com')){
$articles = $Artxpath->query("//div[@class='articlePage']/p");
$temp = "";
foreach($articles as $article){
//echo "<p style='color:red'>".$article->nodeValue."</p>";
$temp .=$article->nodeValue;
}
$encodeArticle = urlencode($temp);
}

if(stripos($href->nodeValue, 'latimes.com')){
$articles = $Artxpath->query("//div[@class='trb_article_page']/p");
$temp = "";
foreach($articles as $article){
//echo "<p style='color:green'>".$article->nodeValue."</p>";
$temp .=$article->nodeValue;
}
$encodeArticle = urlencode($temp);
}

if(stripos($href->nodeValue, 'news.morningstar.com')){
$articles = $Artxpath->query("//div[@id='mstarContent']/div");
$temp = "";
foreach($articles as $article){
//echo "<p style='color:black'>".$article->nodeValue."</p>";
$temp .=$article->nodeValue;
}
$encodeArticle = urlencode($temp);
}

$file = file_get_contents("https://api.idolondemand.com/1/api/sync/analyzesentiment/v1?apikey=da49bead-9c00-488a-abdc-03e27885b1b2&text=".$encodeArticle);
//echo $file;
$obj = json_decode($file);
$totalCount =0;
$totalScore = 0;
$average;
$obj = objectToArray($obj);
foreach($obj[positive] as $score){
    $totalScore += $score['score'];
    $totalCount++;
}
foreach($obj[negative] as $score){
     $totalScore -= abs($score['score']);
     $totalCount++;
}
echo "<h1>".$totalScore."</h1>";
echo "<h1>".$totalCount."</h1>";
$average = $totalScore / $totalCount;
echo "<h1>".$average."</h1>";
$randomass += (float) $average;
echo "<h1>".$randomass."</h1>";
$randcount++;
echo "<h1>".$randcount."</h1>";
}
$boobs = $randomass/$randcount;
echo "<h1>".$boobs."</h1>";
//$sql = "INSERT INTO `Hackathon`.`Sentiment` (`ID`, `Ticker`, `Sentiment`, `Date`) VALUES (NULL, '$ticker, '$boobs', '$startDate');";
mysql_query("INSERT INTO Sentiment(Ticker, Sentiment, Date) VALUES ('$ticker', '$boobs', '$startDate')");
//mysql_query($sql);
}
function objectToArray($d) 
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

?>