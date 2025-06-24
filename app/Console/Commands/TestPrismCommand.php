<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Prism;

class TestPrismCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prism:test {--with-api-call : Test with actual OpenAI API call}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Prism installation and OpenAI configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Prism installation...');

        try {
            // Test if Prism can be instantiated
            $prism = new Prism();
            $this->info('✅ Prism class loaded successfully');

            // Test configuration
            $openaiKey = config('prism.providers.openai.api_key');
            if (empty($openaiKey)) {
                $this->warn('⚠️  OpenAI API key not configured');
                $this->line('   Please add your OpenAI API key to the .env file:');
                $this->line('   OPENAI_API_KEY=sk-your-api-key-here');
                $this->newLine();
            } else {
                $this->info('✅ OpenAI API key is configured');

                if ($this->option('with-api-call')) {
                    $this->testApiCall();
                }
            }

            // Show usage examples
            $this->showUsageExamples();

        } catch (\Exception $e) {
            $this->error('❌ Error testing Prism: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function testApiCall()
    {
        $this->info('🚀 Testing OpenAI API call...');

        try {
            $response = Prism::text()
                ->using('openai', 'gpt-3.5-turbo')
                ->withPrompt('Say "Hello from Prism!" in exactly those words.')
                ->generate();

            $this->info('✅ API call successful!');
            $this->line('Response: ' . $response->text);

        } catch (\Exception $e) {
            $this->error('❌ API call failed: ' . $e->getMessage());
            $this->line('This might be due to:');
            $this->line('- Invalid API key');
            $this->line('- Insufficient credits');
            $this->line('- Network issues');
        }
    }

    private function showUsageExamples()
    {
        $this->newLine();
        $this->info('📚 Usage Examples:');
        $this->newLine();

        $this->line('Basic text generation:');
        $this->line('use Prism\Prism\Prism;');
        $this->newLine();
        $this->line('$response = Prism::text()');
        $this->line('    ->using(\'openai\', \'gpt-4\')');
        $this->line('    ->withPrompt(\'Write a haiku about programming\')');
        $this->line('    ->generate();');
        $this->newLine();
        $this->line('echo $response->text;');
        $this->newLine();

        $this->line('With system message:');
        $this->line('$response = Prism::text()');
        $this->line('    ->using(\'openai\', \'gpt-4\')');
        $this->line('    ->withSystemPrompt(\'You are a helpful assistant\')');
        $this->line('    ->withPrompt(\'Explain Laravel in simple terms\')');
        $this->line('    ->generate();');
        $this->newLine();

        $this->info('💡 To test with actual API call, run:');
        $this->line('php artisan prism:test --with-api-call');
    }
}
