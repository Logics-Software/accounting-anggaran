<?php
/**
 * Validator Helper Class
 * 
 * Centralized validation for form inputs and data
 * 
 * @package Core\Helpers
 */
class Validator {
    private $errors = [];
    private $data = [];
    
    /**
     * Create new validator instance
     * 
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate data against rules
     * 
     * @param array $rules Validation rules (e.g., ['field' => 'required|email|min:3'])
     * @return bool True if valid, false otherwise
     */
    public function validate($rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = $this->getValue($field);
            
            foreach ($rulesArray as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = isset($ruleParts[1]) ? $ruleParts[1] : null;
                
                if (!$this->validateRule($field, $value, $ruleName, $ruleValue)) {
                    break; // Stop validation for this field on first error
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate single rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $ruleName Rule name
     * @param mixed $ruleValue Rule parameter value
     * @return bool True if rule passes
     */
    private function validateRule($field, $value, $ruleName, $ruleValue) {
        switch ($ruleName) {
            case 'required':
                if (!$this->required($value)) {
                    $this->addError($field, "Field {$field} wajib diisi");
                    return false;
                }
                break;
                
            case 'email':
                if (!empty($value) && !$this->email($value)) {
                    $this->addError($field, "Field {$field} harus berupa email yang valid");
                    return false;
                }
                break;
                
            case 'min':
                if (!empty($value) && !$this->minLength($value, (int)$ruleValue)) {
                    $this->addError($field, "Field {$field} minimal {$ruleValue} karakter");
                    return false;
                }
                break;
                
            case 'max':
                if (!empty($value) && !$this->maxLength($value, (int)$ruleValue)) {
                    $this->addError($field, "Field {$field} maksimal {$ruleValue} karakter");
                    return false;
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !$this->numeric($value)) {
                    $this->addError($field, "Field {$field} harus berupa angka");
                    return false;
                }
                break;
                
            case 'integer':
                if (!empty($value) && !$this->integer($value)) {
                    $this->addError($field, "Field {$field} harus berupa bilangan bulat");
                    return false;
                }
                break;
                
            case 'in':
                if (!empty($value) && !$this->in($value, explode(',', $ruleValue))) {
                    $this->addError($field, "Field {$field} tidak valid");
                    return false;
                }
                break;
                
            case 'same':
                $otherValue = $this->getValue($ruleValue);
                if (!empty($value) && $value !== $otherValue) {
                    $this->addError($field, "Field {$field} harus sama dengan {$ruleValue}");
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Get value from data array
     * 
     * @param string $field Field name (supports dot notation: 'user.name')
     * @return mixed Field value or null
     */
    private function getValue($field) {
        if (strpos($field, '.') !== false) {
            $keys = explode('.', $field);
            $value = $this->data;
            foreach ($keys as $key) {
                if (!isset($value[$key])) {
                    return null;
                }
                $value = $value[$key];
            }
            return $value;
        }
        
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }
    
    /**
     * Add validation error
     * 
     * @param string $field Field name
     * @param string $message Error message
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array Errors array
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get first error message for field
     * 
     * @param string $field Field name
     * @return string|null Error message or null
     */
    public function first($field) {
        return isset($this->errors[$field][0]) ? $this->errors[$field][0] : null;
    }
    
    /**
     * Check if field has errors
     * 
     * @param string $field Field name
     * @return bool True if has errors
     */
    public function has($field) {
        return isset($this->errors[$field]);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool True if has errors
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool True if no errors
     */
    public function passes() {
        return empty($this->errors);
    }
    
    // Static validation methods
    
    /**
     * Validate required field
     * 
     * @param mixed $value Value to validate
     * @return bool True if not empty
     */
    public static function required($value) {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }
    
    /**
     * Validate email format
     * 
     * @param string $value Email to validate
     * @return bool True if valid email
     */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $value String to validate
     * @param int $min Minimum length
     * @return bool True if length >= min
     */
    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $value String to validate
     * @param int $max Maximum length
     * @return bool True if length <= max
     */
    public static function maxLength($value, $max) {
        return strlen($value) <= $max;
    }
    
    /**
     * Validate numeric value
     * 
     * @param mixed $value Value to validate
     * @return bool True if numeric
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate integer value
     * 
     * @param mixed $value Value to validate
     * @return bool True if integer
     */
    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate value is in array
     * 
     * @param mixed $value Value to validate
     * @param array $array Array of allowed values
     * @return bool True if value in array
     */
    public static function in($value, $array) {
        return in_array($value, $array);
    }
    
    /**
     * Validate URL format
     * 
     * @param string $value URL to validate
     * @return bool True if valid URL
     */
    public static function url($value) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate date format
     * 
     * @param string $value Date string
     * @param string $format Date format (default: Y-m-d)
     * @return bool True if valid date
     */
    public static function date($value, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }
}

