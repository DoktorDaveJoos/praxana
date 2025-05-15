<?php

namespace App;

enum SurveyRunStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Aborted = 'aborted';

}
