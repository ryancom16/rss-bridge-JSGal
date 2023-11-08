<?php

class JustifiedgalleryFormat extends FormatAbstract
{
    const MIME_TYPE = 'text/html';

    public function stringify()
    {
        $queryString = $_SERVER['QUERY_STRING'];

        $extraInfos = $this->getExtraInfos();
        $formatFactory = new FormatFactory();
        $buttons = [];
        $linkTags = [];
        foreach ($formatFactory->getFormatNames() as $format) {
            // Dynamically build buttons for all formats (except HTML)
            if ($format === 'Jgal') {
                continue;
            }
            $formatUrl = '?' . str_ireplace('format=Html', 'format=' . $format, htmlentities($queryString));
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

        if (Configuration::getConfig('admin', 'donations') && $extraInfos['donationUri'] !== '') {
            $buttons[] = [
                'href' => e($extraInfos['donationUri']),
                'value' => 'Donate to maintainer',
            ];
        }

        $items = [];
        foreach ($this->getItems() as $item) {
            $galleryItems = [];

            $content = $item->getContent();

            $doc = new DOMDocument();
            @$doc->loadHTML($content); // The @ suppresses warnings generated by invalid HTML in the content.

            $tags = $doc->getElementsByTagName('img');

            foreach ($tags as $tag) {
                $imageSrc = $tag->getAttribute('src');
                $parent = $tag->parentNode;
                $linkHref = null;

                // Check if the img tag is wrapped in an a tag
                if ($parent instanceof DOMElement && $parent->tagName === 'a') {
                    $linkHref = $parent->getAttribute('href');
                }

                // Only add to the array if there is an image source
                if ($imageSrc) {
                    $galleryItems[] = [
                        'thumbnail' => $imageSrc,
                        'url' => $linkHref ?: $imageSrc, // Use the image source as a fallback if no link is present
                    ];
                }
            }

            $items[] = [
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

        $html = render_template(__DIR__ . '/../templates/justified-gallery-format.html.php', [
            'charset'   => $this->getCharset(),
            'title'     => $extraInfos['name'],
            'linkTags'  => $linkTags,
            'uri'       => $extraInfos['uri'],
            'buttons'   => $buttons,
            'items'     => $items,
        ]);
        // Remove invalid characters
        ini_set('mbstring.substitute_character', 'none');
        $html = mb_convert_encoding($html, $this->getCharset(), 'UTF-8');
        return $html;
    }
}
