<?php

return [
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 2048),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 30),
        'max_tool_iterations' => (int) env('ANTHROPIC_MAX_TOOL_ITERATIONS', 5),
        'history_limit' => (int) env('ANTHROPIC_HISTORY_LIMIT', 20),
    ],
];
