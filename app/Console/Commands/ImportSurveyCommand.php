<?php

namespace App\Console\Commands;

use App\Models\Step;
use App\Models\Survey;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class ImportSurveyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveys:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import survey data from a (YAML|Json) file';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: $filePath");

            return CommandAlias::FAILURE;
        }

        $content = file_get_contents($filePath);

        $data = null;
        try {
            if (str_ends_with($filePath, '.yaml') || str_ends_with($filePath, '.yml')) {
                $data = Yaml::parse($content);
            } elseif (str_ends_with($filePath, '.json')) {
                $data = json_decode($content, true);
            } else {
                $this->error('Unsupported file format. Use .yaml, .yml or .json');

                return CommandAlias::FAILURE;
            }
        } catch (Exception $e) {
            $this->error('Error parsing file: '.$e->getMessage());

            return CommandAlias::FAILURE;
        }

        if (! isset($data['survey'])) {
            $this->error('Invalid format: missing "survey" root element');

            return CommandAlias::FAILURE;
        }

        DB::beginTransaction();

        try {
            $surveyData = $data['survey'];

            $survey = Survey::create([
                'name' => $surveyData['name'],
                'description' => $surveyData['description'] ?? null,
                'version' => $surveyData['version'] ?? 1,
            ]);

            foreach ($surveyData['steps'] as $index => $stepData) {
                /** @var Step $step */
                $step = $survey->steps()->create([
                    'title' => $stepData['title'] ?? null,
                    'content' => $stepData['content'] ?? null,
                    'step_type' => $stepData['step_type'],
                    'answer_type' => $stepData['answer_type'] ?? null,
                ]);

                foreach ($stepData['choices'] ?? [] as $choiceData) {
                    $step->choices()->create([
                        'label' => $choiceData['label'],
                        'value' => $choiceData['value'],
                    ]);
                }
            }

            DB::commit();

            $this->info('Survey imported successfully!');

            return CommandAlias::SUCCESS;

        } catch (Exception $e) {
            DB::rollBack();
            $this->error('Error during import: '.$e->getMessage());

            return CommandAlias::FAILURE;
        }
    }
}
