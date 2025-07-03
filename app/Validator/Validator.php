<?php
declare(strict_types=1);

namespace App\Validator;

use GUMP;
use Exception;

class Validator
{
    private GUMP $gump;

    public function __construct() 
    {
        $this->gump = new GUMP();
    }

    /**
     * Valida los datos según las reglas especificadas
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @param array $customMessages Mensajes de error personalizados
     * @return array Datos validados y filtrados
     * @throws Exception Si la validación falla
     */
    public function validate(array $data, array $rules, array $customMessages = []): array
    {
        try {
            // Configurar reglas y mensajes
            $this->gump->validation_rules($rules);
            
            if (!empty($customMessages)) {
                $this->gump->set_fields_error_messages($customMessages);
            }

            // Configurar filtros basados en las reglas
            $filters = $this->generateFiltersFromRules($rules);
            $this->gump->filter_rules($filters);

            // Ejecutar validación
            $validatedData = $this->gump->run($data);

            if ($validatedData === false) {
                $errors = $this->gump->get_errors_array();
                throw new Exception(json_encode([
                    'validation_errors' => $errors,
                    'readable_errors' => $this->gump->get_readable_errors(true)
                ]));
            }

            return $validatedData;

        } catch (Exception $e) {
            throw new Exception("Validation failed: " . $e->getMessage());
        }
    }

    /**
     * Genera reglas de filtrado basadas en las reglas de validación
     */
    private function generateFiltersFromRules(array $rules): array
    {
        $filters = [];
        
        foreach ($rules as $field => $ruleStr) {
            $rulesArray = explode('|', $ruleStr);
            $filters[$field] = [];
            
            foreach ($rulesArray as $rule) {
                $ruleParts = explode(',', $rule);
                $ruleName = $ruleParts[0];
                
                switch ($ruleName) {
                    case 'valid_email':
                        $filters[$field][] = 'sanitize_email';
                        break;
                    case 'alpha':
                    case 'alpha_numeric':
                    case 'alpha_dash':
                        $filters[$field][] = 'sanitize_string';
                        break;
                    case 'numeric':
                        $filters[$field][] = 'sanitize_numbers';
                        break;
                }
            }
            
            // Siempre añadir trim
            array_unshift($filters[$field], 'trim');
            
            // Si no hay filtros específicos, usar sanitize_string por defecto
            if (count($filters[$field]) === 1) {
                $filters[$field][] = 'sanitize_string';
            }
        }
        
        return $filters;
    }

    /**
     * Valida datos para registro de usuario
     */
    public function validateUserRegistration(array $data): array
    {
        $rules = [
            'name' => 'required|alpha|max_len,100',
            'lastname' => 'required|alpha|max_len,100',
            'email' => 'required|valid_email',
            'password' => 'required|min_len,8|max_len,20'
        ];

        $messages = [
            'name' => ['required' => 'El nombre es requerido'],
            'lastname' => ['required' => 'El apellido es requerido'],
            'email' => [
                'required' => 'El email es requerido',
                'valid_email' => 'El email no es válido'
            ],
            'password' => [
                'required' => 'La contraseña es requerida',
                'min_len' => 'La contraseña debe tener al menos 8 caracteres'
            ]
        ];

        return $this->validate($data, $rules, $messages);
    }

    /**
     * Valida datos para actualización de usuario
     */
    public function validateUserUpdate(array $data): array
    {
        $rules = [
            'name' => 'alpha|max_len,100',
            'lastname' => 'alpha|max_len,100',
            'email' => 'valid_email'
        ];

        return $this->validate($data, $rules);
    }

    /**
     * Obtiene los errores de validación en formato legible
     */
    public function getReadableErrors(): array
    {
        return $this->gump->get_readable_errors(true);
    }

    /**
     * Obtiene los errores de validación en formato array
     */
    public function getErrorsArray(): array
    {
        return $this->gump->get_errors_array();
    }
}