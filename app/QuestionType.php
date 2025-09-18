<?php

namespace App;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case SingleChoice = 'single_choice';
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Scale = 'scale';
    //    case Boolean = 'boolean';
}
