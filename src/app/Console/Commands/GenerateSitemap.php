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
        $now = now()->toAtomString();

        $staticPaths = [
            '/products',
            '/privacy-policy',
            '/terms-and-conditions',
            '/returns-and-refunds',
            '/shipping-and-delivery',
            '/cookies',
        ];

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($staticPaths as $path) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', htmlspecialchars($baseUrl.$path));
            $urlElement->addChild('lastmod', $now);
        }

        Product::publiclyVisible()->each(function (Product $product) use ($baseUrl, $xml): void {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', htmlspecialchars($baseUrl.'/products/'.$product->slug));
            $urlElement->addChild('lastmod', $product->updated_at->toAtomString());
        });

        $count = count($xml->url);
        $path = public_path('sitemap.xml');
        $xml->asXML($path);

        $this->info('Sitemap written to '.$path.' with '.$count.' URLs.');

        return self::SUCCESS;
    }
}
