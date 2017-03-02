<?php
/**
 * index.php
 *
 * Description:
 * Checks for a shipping provider and reroutes to the providers website or shows a message.
 *
 * @author repat@repat.de
 * @date   March 2017
 * @version 1.0
 */

include "vendor/autoload.php";

use repat\ShippingServiceProvidersCheck\Check;

$trackingId = $_GET["tracking_id"];

if (empty($trackingId)) {
	$unprocessableEntity = 422;
	http_response_code($unprocessableEntity);
	echo "wrong input";
	return;
}

$shippingProviderURLs = [
	"dhl" => 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?idc=',
	"hermes" => 'https://www.myhermes.de/wps/portal/paket/Home/privatkunden/sendungsverfolgung/?auftragsNummer=',
	"gls" => 'https://gls-group.eu/DE/de/paketverfolgung?txtAction=71000&match=',
	"ups" => 'https://wwwapps.ups.com/WebTracking/processRequest?tracknum=',
	// no solution for this yet
	"amz_log" => null,
];

$shippingProviderURLsNoCheck = [
	"DPD" => 'https://tracking.dpd.de/parcelstatus?query=',
	"Deutsche Post" => 'https://www.deutschepost.de/sendung/simpleQuery.html',
	"TNT" => 'http://www.tnt.com/express/de_de/site/home/applications/tracking.html?source=public_menu',
	"Fedex" => 'https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=',
];

$check = new Check($trackingId);

$result = $check->checkAll();
// only uses first result, otherwise use array_keys()
$checkedProvider = array_search(true, $result);
$urlOfCheckedProvider = $shippingProviderURLs[$checkedProvider];

if ($checkedProvider !== false) {
	header('location: ' . $urlOfCheckedProvider . $trackingId);
}

include "skeleton.html";
echo "<p>";
switch ($_SERVER["HTTP_ACCEPT_LANGUAGE"]) {
case "de":
	echo "Ein passender Provider konnte leider nicht ermittelt werden. Möglicherweise ist es einer der folgenden:";
	break;
case "fr":
	echo "Un shipping provider n'a pu être déterminé. C'est peut-être l'un des suivants:";
	break;
default:
	echo "A provider could not be determined. It's possibly one of the following:";
	break;
}

echo "</ul><ul>";

foreach ($shippingProviderURLsNoCheck as $name => $url) {
	echo "<li><a href='" . $url . "'>" . $name . "</a></li>";
}

echo "</ul></div></body></html>";