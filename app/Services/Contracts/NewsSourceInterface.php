<?php

namespace App\Services\Contracts;

interface NewsSourceInterface
{
    /**
     * Get articles from the news source.
     *
     * @param array $params
     * @return array
     */
    public function getArticles(array $params = []): array;
    
    /**
     * Get sources from the news source.
     *
     * @param array $params
     * @return array
     */
    public function getSources(array $params = []): array;
    
    /**
     * Get the name of the news source.
     *
     * @return string
     */
    public function getSourceName(): string;
    
    /**
     * Search for articles from the news source.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function searchArticles(string $query, array $params = []): array;
}
