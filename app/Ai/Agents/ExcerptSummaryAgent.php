<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('anthropic')]
class ExcerptSummaryAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are an expert copywriter specializing in concise project summaries.

        Your task: write a single-sentence excerpt that captures the essence of a portfolio project.

        Rules:
        - Maximum 200 characters (including spaces and punctuation)
        - Plain text only — no HTML, markdown, or special formatting
        - Write in the same language as the provided content
        - Focus on what was built, the problem solved, or the key technology used
        - Be specific and engaging, not generic
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'excerpt' => $schema->string()
                ->description('A concise project summary, maximum 200 characters.')
                ->required(),
        ];
    }
}
