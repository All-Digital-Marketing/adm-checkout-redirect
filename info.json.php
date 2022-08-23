<?php
$update = array(
  "name"           => "adm Payment Complete Redirect",
  "slug"           => "adm-checkout-redirect",
  "author"         => [
    "danielV",
  ],
  "author_profile" => "https://dj3dw.com",
  "version"        => "3.2",
  "download_url"   => "https://demo.alldigitalmarketing.com.au/plugins/adm-checkout-redirect.zip",
  "requires"       => "3.0",
  "tested"         => "6.0.1",
  "requires_php"   => "7.4",
  "last_updated"   => "2022-08-23 02:10:00",
  "sections"       => [
    "description"  => "Redirect after successful payment complete. Global redirect or per category. Requires 'Advanced Custom Fields' plugin for product category functionality.",
    "installation" => "Click the activate button and that's it.",
    "changelog"    => "<h4>1.0 –  August 2022</h4><ul><li>Bug fixes.</li><li>Initial release.</li></ul>",
  ],
  "banners"        => [
    "low"  => "https://demo.alldigitalmarketing.com.au/plugins/adm-checkout-redirect/banner-772x250.jpg",
    "high" => "https://demo.alldigitalmarketing.com.au/plugins/adm-checkout-redirect/banner-1544x500.jpg",
  ],
  
);

header('Content-Type: application/json');
try {
  echo json_encode($update, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
}