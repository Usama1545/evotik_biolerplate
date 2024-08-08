<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($sitemaps as $sitemap)
        <sitemap>
            <loc>{{ $sitemap }}</loc>
            <lastmod>{{ now()->subHour()->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endforeach
</sitemapindex>
