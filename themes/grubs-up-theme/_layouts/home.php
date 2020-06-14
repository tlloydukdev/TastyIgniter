---
description: Default layout

'[session]':
    security: all

'[staticMenu mainMenu]':
    code: main-menu

'[staticMenu footerMenu]':
    code: footer-menu

'[newsletter]': {  }
---
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= App::getLocale(); ?>">
<head>
    <?= partial('head'); ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body class="<?= $this->page->bodyClass; ?>" style="background-color: #ed561a;">

    <header class="header">
        <?= partial('header'); ?>
    </header>

    <main role="main">
        <div id="page-wrapper">
            <?= page(); ?>
        </div>
    </main>

    <footer class="pt-5">
        <?= partial('footer'); ?>
    </footer>
    <div id="notification">
        <?= partial('flash'); ?>
    </div>
    <?= partial('eucookiebanner'); ?>
    <?= partial('scripts'); ?>
</body>
</html>