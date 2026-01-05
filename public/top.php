<?php
require './services/config.php';
require './env.php';
?>


<?php
function getTopBooksWithCache($apiKey) {
    $cacheFile = 'cache_top_books.json';
    $cacheTime = 86400;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        $jsonData = file_get_contents($cacheFile);
        return json_decode($jsonData, true);
    }

    $url = "https://api.nytimes.com/svc/books/v3/lists/overview.json?api-key=" . $apiKey;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Biblioteca Scolara Proiect'); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if (isset($data['results']['lists'][0]['books'])) {
            $topBooks = array_slice($data['results']['lists'][0]['books'], 0, 10);
            
            file_put_contents($cacheFile, json_encode($topBooks));
            
            return $topBooks;
        }
    }

    if (file_exists($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    return [];
}

$myApiKey = $nyt_api_key;
$topBooks = getTopBooksWithCache($myApiKey);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 10 Carti in Aceasta Saptamana</title>

    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #e74c3c;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
        }

        .section-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .grid-carti {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-carte {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .card-carte:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .img-container {
            height: 350px;
            overflow: hidden;
            background-color: #eee;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .details h3 {
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            color: var(--primary-color);
            height: 3rem;
            overflow: hidden;
        }

        .author {
            color: #7f8c8d;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .description {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-amazon {
            display: block;
            text-align: center;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 6px;
            margin-top: auto;
            transition: background 0.3s;
        }

        .btn-amazon:hover {
            background: var(--accent-color);
        }
    </style>

</head>

<body>
    <?php require './components/header.php'; ?>

    <h2 class="section-title">Top 10 Carti in Aceasta Saptamana</h2>

    <div class="grid-carti">
        <?php foreach ($topBooks as $book): ?>
            <div class="card-carte">
                <div class="img-container">
                    <img src="<?php echo $book['book_image']; ?>" alt="Coperta <?php echo $book['title']; ?>">
                </div>

                <div class="details">
                    <h3><?php echo $book['title']; ?></h3>
                    <p class="author">de <?php echo $book['author']; ?></p>
                    <p class="description"><?php echo $book['description']; ?></p>

                    <a href="<?php echo $book['amazon_product_url']; ?>" target="_blank" class="btn-amazon">
                        Vezi pe Amazon
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require './components/footer.php'; ?>
</body>

</html>