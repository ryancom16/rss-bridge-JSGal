<?php

class GalleryFormat extends FormatAbstract
{
    const MIME_TYPE = 'text/html';

    public function stringify()
    {
        $queryString = $_SERVER['QUERY_STRING'];

        $extraInfos = $this->getExtraInfos();
        $formatFactory = new FormatFactory();
        $buttons = [];
        $linkTags = [];

        // Build buttons for all formats except Gallery
        foreach ($formatFactory->getFormatNames() as $format) {
            // Dynamically build buttons for all formats (except Justifiedgallery)
            if ($format === 'Gallery') {
                continue;
            }

            // Parse the query string into an associative array
            parse_str($queryString, $queryParams);

            // Update the 'format' parameter and remove 'galleryGrouping' and 'gallerySize'
            $queryParams['format'] = $format;
            unset($queryParams['galleryGroupImages']);
            unset($queryParams['gallerySize']);

            // Rebuild the query string with modifications
            $modifiedQueryString  = http_build_query($queryParams);

            // Create the format URL
            $formatUrl = '?' . $modifiedQueryString;

            $buttons[] = [
                'href' => $formatUrl,
                'value' => $format,
            ];
            $linkTags[] = [
                'href' => $formatUrl,
                'title' => $format,
                'type' => $formatFactory->create($format)->getMimeType(),
            ];
        }

        // Add donation button if applicable
        if (Configuration::getConfig('admin', 'donations') && $extraInfos['donationUri'] !== '') {
            $buttons[] = [
                'href' => e($extraInfos['donationUri']),
                'value' => 'Donate to maintainer',
            ];
        }

        // Set default values and check for URL parameters
        $galleryGroupImages = !isset($_GET['galleryGroupImages']) || $_GET['galleryGroupImages'] === 'true';
        $gallerySize = isset($_GET['gallerySize']) ? (int)$_GET['gallerySize'] : 300;

        $items = [];
        $allGalleryItems = [];

        foreach ($this->getItems() as $item) {

            $galleryItems = $this->extractGalleryItems($item);

            if ($galleryGroupImages) {
                $allGalleryItems = array_merge($allGalleryItems, $galleryItems);
            } else {
                $items[] = $this->formatItem($item, $extraInfos, $galleryItems);
            }
        }

        // Group all images if required
        if ($galleryGroupImages) {
            $items = [['galleryItems' => $allGalleryItems]];
        }

        $html = $this->renderTemplate($extraInfos, $linkTags, $buttons, $gallerySize, $items);
        return $this->cleanHtml($html);
    }

    private function extractGalleryItems($item)
    {
        // Extract gallery items from the item content
        $galleryItems = [];
    
        $content = $item->getContent();
        $doc = new DOMDocument();
        @$doc->loadHTML($content); // Suppress warnings for invalid HTML
    
        $tags = $doc->getElementsByTagName('img');
    
        foreach ($tags as $tag) {
            $imageSrc = $tag->getAttribute('src');
            $imageTitle = $tag->getAttribute('title');
            $imageAlt = $tag->getAttribute('alt');
            $parent = $tag->parentNode;
            $linkHref = null;
    
            if ($parent instanceof DOMElement && $parent->tagName === 'a') {
                $linkHref = $parent->getAttribute('href');
            }
    
            if ($imageSrc) {
                $galleryItems[] = [
                    'thumbnail' => $imageSrc,
                    'url' => $linkHref ?: $imageSrc,
                    'alt' => $imageTitle ?: ($imageAlt ?: ($item->getTitle() ?: '')),
                ];
            }
        }
    
        return $galleryItems;
    }
    
    private function formatItem($item, $extraInfos, $galleryItems)
    {
        // Format a single item
        return [
            'url'           => $item->getURI() ?: $extraInfos['uri'],
            'title'         => $item->getTitle() ?? '(no title)',
            'timestamp'     => $item->getTimestamp(),
            'author'        => $item->getAuthor(),
            'content'       => $item->getContent() ?? '',
            'enclosures'    => $item->getEnclosures(),
            'categories'    => $item->getCategories(),
            'galleryItems'  => $galleryItems,
        ];
    }

    private function renderTemplate($extraInfos, $linkTags, $buttons, $gallerySize, $items)
    {
        // Render the HTML template
        $html = render_template(__DIR__ . '/../templates/gallery-format.html.php', [
            'charset'   => $this->getCharset(),
            'title'     => $extraInfos['name'],
            'linkTags'  => $linkTags,
            'uri'       => $extraInfos['uri'],
            'buttons'   => $buttons,
            'gallerySize' => $gallerySize,
            'items'     => $items,
        ]);
        return $html;
    }

    private function cleanHtml($html)
    {
        // Clean and return HTML
        ini_set('mbstring.substitute_character', 'none');
        $html = mb_convert_encoding($html, $this->getCharset(), 'UTF-8');
        return $html;
    }
}
