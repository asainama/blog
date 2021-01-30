<?php

namespace App\Validator;

use App\Validator\AbstractValidator;

class PostValidator extends AbstractValidator
{
    private $data;

    private $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'title' => ['required', 'minLen' => 6, 'maxLen' => 150],
            'chapo' => ['required', 'minLen' => 6],
            'content' => ['required', 'minLen' => 6],
            'slug' => ['required', 'minLen' => 6,'maxLen' => 100],
            'created_at' => ['required', 'date'],
            'draft' => ['bool'],
        ];
    }

    public function isValid()
    {
        $this->validate($this->data, $this->rules);
    }
}
