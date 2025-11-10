<?php

return [
    // AI provider selection (currently only 'openai' supported in code)
    'provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        // Your OpenAI API key, e.g., sk-... (set in .env as OPENAI_API_KEY)
        'api_key' => env('OPENAI_API_KEY'),

        // Default model to use
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

        // Optional: base URL override for self-hosted gateways
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

        // Optional: explicit project or org headers for project-scoped keys
        'project' => env('OPENAI_PROJECT'), // e.g., proj_ABC123
        'organization' => env('OPENAI_ORG'), // e.g., org_ABC123
    ],
];

