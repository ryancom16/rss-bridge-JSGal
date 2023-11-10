<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="<?= htmlspecialchars($charset, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="RSS-Bridge" />
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="static/style.css?2023-03-24" rel="stylesheet">
    <link rel="icon" type="image/png" href="static/favicon.png">
    <link rel="stylesheet" href="static/css/justifiedGallery.min.css" />
    <script src="static/js/jquery.min.js"></script>
    <script src="static/js/jquery.justifiedGallery.min.js"></script>

    <?php foreach ($linkTags as $link) : ?>
        <link href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8') ?>" rel="alternate" type="<?= htmlspecialchars($link['type'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>

    <meta name="robots" content="noindex, follow">
</head>

<body>

    <div class="container">

        <h1 class="pagetitle">
            <a href="<?= htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></a>
        </h1>

        <div class="buttons">
            <a href="./#bridge-<?= htmlspecialchars($_GET['bridge'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="backbutton">← back to rss-bridge</button>
            </a>

            <?php foreach ($buttons as $button) : ?>
                <a href="<?= htmlspecialchars($button['href'], ENT_QUOTES, 'UTF-8') ?>">
                    <button class="rss-feed"><?= htmlspecialchars($button['value'], ENT_QUOTES, 'UTF-8') ?></button>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modified Justified Gallery -->
    <?php foreach ($items as $index => $item) : ?>
        <div class="item-container">
            <?php if ($item['title']) : ?>
                <section class="feeditem">
                    <h2><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                </section>
            <?php endif; ?>
            <div id="justified-gallery-<?= $index ?>" class="justified-gallery">
                <?php foreach ($item['galleryItems'] as $galleryItem) : ?>
                    <a href="<?= htmlspecialchars($galleryItem['url'], ENT_QUOTES, 'UTF-8') ?>" class="gallery-item">
                        <img src="<?= htmlspecialchars($galleryItem['thumbnail'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    </div>
    <script>
        $(document).ready(function() {
            $('.justified-gallery').each(function() {
                $(this).justifiedGallery({
                    rowHeight: 300,
                    lastRow: 'nojustify',
                    margins: 3
                });
            });
        });
    </script>

</body>

</html>