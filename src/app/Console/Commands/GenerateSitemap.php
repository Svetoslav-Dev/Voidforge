<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the public sitemap.xml file.';

    public function handle(): int
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $staticPaths = [
            '/products',
            '/privacy-policy',
            '/terms-and-conditions',
            '/returns-and-refunds',
            '/shipping-and-delivery',
            '/cookies',
            '/contact-and-trader-info',
        ];

        $urls = array_map(fn (string $path) => $baseUrl.$path, $staticPaths);

        Product::publiclyVisible()->each(function (Product $product) use ($baseUrl, &$urls) {
            $urls[] = $baseUrl.'/products/'.$product->slug;
        });

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', htmlspecialchars($url));
        }

        $path = public_path('sitemap.xml');
        $xml->asXML($path);

        $this->info('Sitemap written to '.$path.' with '.count($urls).' URLs.');

        return self::SUCCESS;
    }
}
