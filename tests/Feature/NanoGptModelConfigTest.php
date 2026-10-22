<?php

namespace Tests\Feature;

use Tests\TestCase;

class NanoGptModelConfigTest extends TestCase
{
    public function test_all_nanogpt_calls_default_to_deepseek_v4(): void
    {
        $this->assertSame('deepseek/deepseek-v4-flash', config('services.nanogpt.model'));
        $this->assertSame('deepseek/deepseek-v4-flash', config('services.nanogpt.categorise_model'));
        $this->assertSame('deepseek/deepseek-v4-flash', config('services.nanogpt.search_model'));
        $this->assertGreaterThan(20, config('services.nanogpt.categorise_batch'));
    }

    public function test_no_command_hardcodes_a_disabled_model(): void
    {
        foreach (['CategoriseVideos', 'SearchVideosForCategories'] as $cmd) {
            $src = file_get_contents(app_path("Console/Commands/{$cmd}.php"));
            $this->assertStringNotContainsString('gemini-2.0-flash', $src);
            $this->assertStringNotContainsString("'qwen-plus'", $src);
        }
    }
}
