<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

/**
 * Smart Search Trait
 * 
 * Provides consistent search validation and behavior across the application.
 * Enforces minimum search length requirements and provides helper methods.
 */
trait SmartSearch
{
    /**
     * Minimum search length required (default: 1 character)
     */
    protected int $minSearchLength = 1;

    /**
     * Validate search input and return sanitized search term.
     * Returns null if search doesn't meet minimum requirements.
     *
     * @param Request $request
     * @param string $paramName The request parameter name (default: 'search')
     * @return string|null
     */
    protected function getValidatedSearchTerm(Request $request, string $paramName = 'search'): ?string
    {
        $search = $request->get($paramName, '');
        
        // Support both 'search' and 'q' parameters
        if (empty($search) && $paramName === 'search') {
            $search = $request->get('q', '');
        }
        
        $search = trim($search);
        
        // Return null if search term doesn't meet minimum length
        if (mb_strlen($search) < $this->minSearchLength) {
            return null;
        }
        
        return $search;
    }

    /**
     * Check if search term is valid (meets minimum length requirement).
     *
     * @param string|null $search
     * @return bool
     */
    protected function isValidSearchTerm(?string $search): bool
    {
        if ($search === null) {
            return false;
        }
        
        return mb_strlen(trim($search)) >= $this->minSearchLength;
    }

    /**
     * Get validation rules for search parameters.
     *
     * @param bool $required Whether search is required
     * @return array
     */
    protected function getSearchValidationRules(bool $required = false): array
    {
        $rule = $required ? 'required' : 'nullable';
        
        return [
            'search' => "{$rule}|string|min:{$this->minSearchLength}|max:255",
            'q' => "{$rule}|string|min:{$this->minSearchLength}|max:255", // Alternative parameter
        ];
    }

    /**
     * Apply search to query if valid search term is provided.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @param string $paramName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearchIfValid($query, Request $request, string $paramName = 'search')
    {
        $searchTerm = $this->getValidatedSearchTerm($request, $paramName);
        
        if ($searchTerm !== null && method_exists($query->getModel(), 'scopeSearch')) {
            return $query->search($searchTerm);
        }
        
        return $query;
    }

    /**
     * Get search response for AJAX requests with empty state handling.
     *
     * @param mixed $results
     * @param string|null $searchTerm
     * @param string $emptyMessage
     * @return array
     */
    protected function getSearchResponse($results, ?string $searchTerm, string $emptyMessage = 'Start typing to search...'): array
    {
        $count = is_countable($results) ? count($results) : 0;
        
        return [
            'success' => true,
            'data' => $results,
            'count' => $count,
            'search_term' => $searchTerm,
            'has_results' => $count > 0,
            'message' => $count === 0 && $searchTerm !== null 
                ? 'No results found for your search.' 
                : ($searchTerm === null ? $emptyMessage : null),
        ];
    }

    /**
     * Set custom minimum search length.
     *
     * @param int $length
     * @return $this
     */
    protected function setMinSearchLength(int $length): self
    {
        $this->minSearchLength = max(1, $length);
        return $this;
    }

    /**
     * Get current minimum search length.
     *
     * @return int
     */
    protected function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }
}

