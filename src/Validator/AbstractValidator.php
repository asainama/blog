<?php

namespace App\Validator;

abstract class AbstractValidator
{
    private $errors = [];

    protected function validate($src, $rules = [])
    {
        if ($src !== null) {
            foreach ($src as $item => $itemValue) {
                if (key_exists($item, $rules)) {
                    foreach ($rules[$item] as $rule => $ruleValue) {
                        if (is_int($rule)) {
                            $rule = $ruleValue;
                        }
                        switch ($rule) {
                            case 'required':
                                if (empty($itemValue) && $ruleValue) {
                                    $this->addError($item, ucwords($item) . ' est requis');
                                }
                                break;

                            case 'minLen':
                                if (strlen($itemValue) < $ruleValue) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait posséder une taille de '
                                    . $ruleValue
                                    . ' caractères au minimum');
                                }
                                break;
    
                            case 'maxLen':
                                if (strlen($itemValue) > $ruleValue) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait posséder une taille de'
                                    . $ruleValue
                                    . ' caractères au maximum');
                                }
                                break;
    
                            case 'numeric':
                                if (!ctype_digit($itemValue) && $ruleValue) {
                                    $this->addError($item, ucwords($item)
                                    . ' devrait être un numérique');
                                }
                                break;
                            case 'alpha':
                                if (!ctype_alpha($itemValue) && $ruleValue) {
                                    $this->addError($item, ucwords($item)
                                    . ' doit être des caractères alphabétiques');
                                }
                                break;
                            case 'email':
                                if (filter_var($itemValue, FILTER_VALIDATE_EMAIL) === false) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas un email valide');
                                }
                                break;
                            case 'date':
                                if (!$this->validateDate($itemValue)) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas une date valide');
                                }
                                break;
                            case 'bool':
                                $itemValue = boolval($itemValue);
                                if (!is_bool($itemValue)) {
                                    $this->addError($item, ucwords($item)
                                    . ' n\'est pas une valeur booléenne valide');
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    private function addError($item, $error)
    {
        $this->errors[$item][] = "Le champs " . $error;
    }
    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param [type] $date
     * @param string $format
     * @return void
     */
    private function validateDate(string $date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    public function error()
    {
        if (empty($this->errors)) {
            return false;
        }
        return $this->errors;
    }
}
