# Prism PHP Setup Guide

## Overview
Prism PHP has been successfully installed and configured in your Laravel project. Prism is a powerful PHP library for integrating with various AI providers, including OpenAI.

## Installation Status
✅ **Prism PHP package installed** (v0.77.1)  
✅ **Configuration files published**  
✅ **Environment variables configured**  
✅ **Test command created**  

## Quick Start

### 1. Add Your OpenAI API Key
Edit your `.env` file and add your OpenAI API key:

```env
OPENAI_API_KEY=sk-your-actual-api-key-here
```

### 2. Test the Installation
Run the test command to verify everything is working:

```bash
php artisan prism:test
```

To test with an actual API call (requires valid API key):

```bash
php artisan prism:test --with-api-call
```

### 3. Basic Usage Examples

#### Simple Text Generation
```php
use Prism\Prism\Prism;

$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withPrompt('Write a haiku about programming')
    ->generate();

echo $response->text;
```

#### With System Message
```php
$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withSystemPrompt('You are a helpful assistant')
    ->withPrompt('Explain Laravel in simple terms')
    ->generate();

echo $response->text;
```

#### Using Different Models
```php
// GPT-3.5 Turbo (faster, cheaper)
$response = Prism::text()
    ->using('openai', 'gpt-3.5-turbo')
    ->withPrompt('Your prompt here')
    ->generate();

// GPT-4 (more capable, slower)
$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withPrompt('Your prompt here')
    ->generate();
```

## Configuration

### Environment Variables
The following environment variables are available in your `.env` file:

```env
# Required
OPENAI_API_KEY=sk-your-api-key-here

# Optional
OPENAI_ORGANIZATION=your-org-id
OPENAI_PROJECT=your-project-id
```

### Configuration File
Prism configuration is located at `config/prism.php`. You can modify provider settings, add new providers, or adjust server settings there.

## Available Providers
Prism supports multiple AI providers out of the box:

- **OpenAI** (GPT-4, GPT-3.5-turbo, etc.)
- **Anthropic** (Claude)
- **Google Gemini**
- **Mistral AI**
- **Groq**
- **DeepSeek**
- **Ollama** (local models)
- **VoyageAI** (embeddings)

## Advanced Features

### Tools and Function Calling
```php
use Prism\Prism\Tool;

$weatherTool = Tool::as('get_weather')
    ->for('Get current weather for a location')
    ->withStringParameter('location', 'The city name')
    ->using(function (string $location) {
        // Your weather API logic here
        return "Weather in {$location}: Sunny, 72°F";
    });

$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withTools([$weatherTool])
    ->withPrompt('What\'s the weather like in New York?')
    ->generate();
```

### Structured Output
```php
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;

$schema = ObjectSchema::as('user_profile')
    ->withProperty('name', StringSchema::as('Full name'))
    ->withProperty('age', NumberSchema::as('Age in years'))
    ->withProperty('email', StringSchema::as('Email address'));

$response = Prism::text()
    ->using('openai', 'gpt-4')
    ->withPrompt('Extract user info: John Doe, 30 years old, john@example.com')
    ->withSchema($schema)
    ->generate();

$userData = $response->object();
```

## Troubleshooting

### Common Issues

1. **API Key Not Working**
   - Verify your API key is correct
   - Check if you have sufficient credits
   - Ensure the key has the necessary permissions

2. **Rate Limiting**
   - Implement delays between requests
   - Use exponential backoff for retries

3. **Model Not Available**
   - Check if the model exists and you have access
   - Some models require special access or higher tier plans

### Getting Help

- **Prism Documentation**: Check the vendor directory for more examples
- **OpenAI Documentation**: https://platform.openai.com/docs
- **Test Command**: Use `php artisan prism:test` to diagnose issues

## Next Steps

1. Add your OpenAI API key to the `.env` file
2. Run the test command to verify everything works
3. Start integrating Prism into your Laravel application
4. Explore advanced features like tools and structured output

Happy coding with Prism! 🚀
