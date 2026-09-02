<?php

namespace App\Services;

use App\Models\PageSeo;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;

class SeoService
{
    protected string $defaultOgImage;

    public function __construct()
    {
        $this->defaultOgImage = asset('images/content/cta2.jpg');
    }

    /**
     * Apply SEO for a static page. Uses admin-configured PageSeo when available,
     * otherwise falls back to provided defaults.
     */
    public function applyForPage(string $routeName, array $defaults = []): void
    {
        $pageSeo = PageSeo::forRoute($routeName);
        $url = url()->current();

        $metaTitle = $pageSeo?->meta_title ?? $defaults['meta_title'] ?? null;
        $metaDesc = $pageSeo?->meta_description ?? $defaults['meta_description'] ?? null;
        $ogTitle = $pageSeo?->og_title ?? $defaults['og_title'] ?? $metaTitle;
        $ogDesc = $pageSeo?->og_description ?? $defaults['og_description'] ?? $metaDesc;
        $ogImage = $pageSeo?->og_image_url ?? $defaults['og_image'] ?? $this->defaultOgImage;
        $twTitle = $pageSeo?->twitter_title ?? $defaults['twitter_title'] ?? $ogTitle;
        $twDesc = $pageSeo?->twitter_description ?? $defaults['twitter_description'] ?? $ogDesc;
        $twImage = $pageSeo?->twitter_image_url ?? $defaults['twitter_image'] ?? $ogImage;
        $canonical = $pageSeo?->canonical_url ?? $defaults['canonical'] ?? $url;

        if ($metaTitle) {
            SEOMeta::setTitle($metaTitle);
        }
        if ($metaDesc) {
            SEOMeta::setDescription($metaDesc);
        }
        SEOMeta::setCanonical($canonical);
        if ($pageSeo?->robots) {
            SEOMeta::setRobots($pageSeo->robots);
        }

        if ($ogTitle) {
            OpenGraph::setTitle($ogTitle);
        }
        if ($ogDesc) {
            OpenGraph::setDescription($ogDesc);
        }
        OpenGraph::setUrl($url);
        OpenGraph::addImage($ogImage);

        if ($twTitle) {
            TwitterCard::setTitle($twTitle);
        }
        if ($twDesc) {
            TwitterCard::setDescription($twDesc);
        }
        TwitterCard::setImage($twImage);
    }
}
