<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CMS4Blog' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
        }
        header h1 {
            font-size: 1.5rem;
        }
        main {
            padding: 2rem 0;
            min-height: calc(100vh - 140px);
        }
        footer {
            background: #34495e;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }
    </style>
    <?= $this->section('styles') ?>
</head>
<body>
    <header>
        <div class="container">
            <h1><?= $siteName ?? 'CMS4Blog' ?></h1>
        </div>
    </header>

    <main>
        <div class="container">
            <?= $content ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= $siteName ?? 'CMS4Blog' ?>. All rights reserved.</p>
        </div>
    </footer>

    <?= $this->section('scripts') ?>
</body>
</html>
