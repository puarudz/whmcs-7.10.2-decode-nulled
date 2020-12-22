<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$this->layout("layouts/learn", $serviceOffering);
$this->start("nav-tabs");
echo "<li class=\"active\" role=\"presentation\">\n    <a aria-controls=\"about\" data-toggle=\"tab\" href=\"#about\" role=\"tab\">About</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"learn\" data-toggle=\"tab\" href=\"#learn\" role=\"tab\">Learn More</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"features\" data-toggle=\"tab\" href=\"#features\" role=\"tab\">Features</a>\n</li>\n<li role=\"presentation\">\n    <a aria-controls=\"pricing\" data-toggle=\"tab\" href=\"#pricing\" role=\"tab\">Pricing</a>\n</li>\n";
$this->end();
$this->start("content-tabs");
echo "<div class=\"tab-pane active\" id=\"about\" role=\"tabpanel\">\n    <div class=\"content-padded sitelockvpn\">\n        <h3>Offer High Speed, Secure, and Easy to Use VPN</h3>\n        <h4>Anonymous, secure internet browsing free of privacy concerns and content restrictions.</h4>\n\n        <br>\n\n        <p style=\"font-size:1.3em;\">What is VPN?</p>\n        <p>A VPN, or virtual private network, is a secure tunnel between your device and the internet. VPNs are used to protect your online traffic from snooping, interference, and censorship.</p>\n\n        <p style=\"font-size:1.3em;\">Why should I offer VPN to my customers?</p>\n        <p>Even if someone has nothing to hide, often people don’t like the idea of being watched and tracked. In addition, a VPN allows you to connect to servers in many different locations, bypassing censorship and geo-restrictions.</p>\n        <p>More and more people are choosing to use a VPN for greater online privacy and general security and a web hosting provider is a natural fit for consumers to obtain their VPN service from.</p>\n\n        <p style=\"font-size:1.3em;\">Who is SiteLock?</p>\n        <p>SiteLock is a global leader in website security. Founded in 2008, SiteLock makes cybersecurity and website security services affordable and accessible to small businesses and home users. Today, SiteLock protects over 12 million websites.</p>\n    </div>\n</div>\n\n<div class=\"tab-pane\" id=\"learn\" role=\"tabpanel\">\n    <div class=\"content-padded sitelockvpn\">\n        <p style=\"font-size:1.3em;\">What does a VPN do?</p>\n        <p>Typically, all of your internet traffic passes through your ISP's servers, which means they can see and log everything you do online. They may even hand your browsing history over to advertisers, government agencies, and other third parties.</p>\n        <p>However, if you use a VPN, your traffic is sent via a dedicated remote server. In doing this, the VPN hides your IP address and encrypts all the data you send or receive. The encrypted data is unreadable to anyone who intercepts it.</p>\n\n        <br>\n\n        <p style=\"font-size:1.3em;\">The Benefits of Using a VPN</p>\n\n        <div class=\"row\">\n            <div class=\"col-sm-6\">\n                <ul>\n                    <li><strong>Hide your IP and location</strong> - Prevent websites and your ISP from tracking where you are and what you are doing</li>\n                    <li><strong>Encrypt your connection</strong> - Browse from Wi-Fi hotspots like airports and cafes knowing your passwords, emails, photos, bank data and other sensitive information can’t be intercepted.</li>\n                    <li><strong>Watch content from anywhere</strong> - Stream all your shows and movies via a super fast network</li>\n                </ul>\n            </div>\n            <div class=\"col-sm-6\">\n                <ul>\n                    <li><strong>Unblock censored websites</strong> - Easily unblock sites and services blocked by governments or oganisations</li>\n                    <li><strong>Avoid spying and throttling</strong> - Stop snooping by governments, network administrators, and your ISP</li>\n                    <li><strong>Avoid website tracking</strong> - By hiding your IP address and location, its harder for sites and services to track your visits or display targeted advertising based on location</li>\n                </ul>\n            </div>\n        </div>\n\n    </div>\n</div>\n\n<div class=\"tab-pane\" id=\"features\" role=\"tabpanel\">\n    <div class=\"content-padded sitelockvpn features\">\n\n        <p style=\"font-size:1.3em;\">Features</p>\n\n        <br>\n\n        <div class=\"row\">\n            <div class=\"col-sm-6 col-md-4\">\n                <span>No Restrictions</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>High Speed Network</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>Unlimited bandwidth and traffic</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>256-bit AES Encryption</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n            </div>\n            <div class=\"col-sm-6 col-md-4\">\n                <span>OpenVPN, L2TP-IPsec<br>and PPTP protocols</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>Simultaneous connections on<br>up to 5 devices</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>Apps for Windows, Mac, iOS,<br>Android and Linux</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n            </div>\n            <div class=\"col-sm-6 col-md-4\">\n                <span>Unlimited Server Switching</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>40+ Countries</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n            </div>\n            <div class=\"col-sm-6 col-md-4\">\n                <span>1000+ Servers</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n                <span>24/7 US Based Support</span>\n                <img src=\"../assets/img/marketconnect/sitelockvpn/icon-ok.png\">\n            </div>\n        </div>\n\n    </div>\n</div>\n\n<div class=\"tab-pane\" id=\"pricing\" role=\"tabpanel\">\n    <div class=\"content-padded sitelockvpn\">\n        ";
if ($feed->isNotAvailable()) {
    echo "            <div class=\"pricing-login-overlay\">\n                <p>To view pricing, you must first register or login to your MarketConnect account.</p>\n                <button type=\"button\" class=\"btn btn-default btn-sm btn-login\">Login</button> <button type=\"button\" class=\"btn btn-default btn-sm btn-register\">Create Account</button>\n            </div>\n        ";
}
echo "\n        <table class=\"table table-pricing\">\n            ";
$productInfo = $feed->getServicesByGroupId("sitelockvpn");
$planNames = array();
foreach ($productInfo as $plan) {
    $planNames[] = $plan["display_name"];
}
echo "<tr><th></th><th>" . implode("</th><th>", $planNames) . "</th></tr>";
foreach ($productInfo[0]["terms"] as $term) {
    $currentTerm = $term["term"];
    echo "<tr><td>" . (new WHMCS\Billing\Cycles())->getNameByMonths($currentTerm) . "</td>";
    foreach ($productInfo as $plan) {
        foreach ($plan["terms"] as $term) {
            if ($term["term"] != $currentTerm) {
                continue;
            }
            echo "<td>Your Cost: " . formatCurrency($term["price"]) . "<br><small>" . formatCurrency($term["recommendedRrp"]) . " RRP</small></td>";
        }
    }
    echo "</tr>";
}
echo "        </table>\n    </div>\n</div>\n\n<div class=\"tab-pane\" id=\"activate\" role=\"tabpanel\">\n    ";
$this->insert("shared/configuration-activate", array("currency" => $currency, "service" => $service, "firstBulletPoint" => "Offer all SiteLock VPN Services", "availableForAllHosting" => false, "landingPageRoutePath" => "store-sitelockvpn-index", "serviceOffering" => $serviceOffering, "billingCycles" => $billingCycles, "products" => $products, "serviceTerms" => $serviceTerms));
echo "</div>\n";
$this->end();

?>