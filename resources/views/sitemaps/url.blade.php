<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($urls as $url)
        <url>
            <loc>{{ isset($url->url) ? $url->url : $url['url'] }}</loc>
            <lastmod>
                {{ isset($url->updated_at) ? \Carbon\Carbon::parse($url->updated_at)->format('Y-m-d\TH:i:sP') : now()->subHour()->format('Y-m-d\TH:i:sP') }}
            </lastmod>
        </url>
    @endforeach
</urlset>