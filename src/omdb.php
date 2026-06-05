<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Thin client for the OMDb API (https://www.omdbapi.com). The key lives in .env
// as OMDB_API_KEY. When no key is set every call returns an empty result, so the
// rest of the app keeps working without the movie search.

function omdb_enabled(): bool
{
    return (string) env('OMDB_API_KEY', '') !== '';
}

function omdb_request(array $params): array
{
    $key = (string) env('OMDB_API_KEY', '');
    if ($key === '') {
        return [];
    }

    $params['apikey'] = $key;
    $url = 'https://www.omdbapi.com/?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!is_string($response) || $response === '') {
        return [];
    }
    $data = json_decode($response, true);
    return is_array($data) ? $data : [];
}

// Search movies by title. Returns a list of ['title','year','poster','imdb_id'].
function omdb_search(string $query): array
{
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    $data = omdb_request(['s' => $query, 'type' => 'movie']);
    if (($data['Response'] ?? 'False') !== 'True' || empty($data['Search'])) {
        return [];
    }

    $results = [];
    foreach ($data['Search'] as $item) {
        $poster = $item['Poster'] ?? 'N/A';
        $results[] = [
            'title'   => $item['Title'] ?? '',
            'year'    => $item['Year'] ?? '',
            'poster'  => $poster !== 'N/A' ? $poster : null,
            'imdb_id' => $item['imdbID'] ?? '',
        ];
    }
    return $results;
}
