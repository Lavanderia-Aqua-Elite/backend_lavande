<?php
declare(strict_types=1);

namespace app\Hooks;

class ImageHook
{
    protected $uploadPath;
    protected $allowedTypes;
    protected $maxSize;

    public function __construct(string $uploadPath = 'uploads/', array $allowedTypes = ['image/jpeg', 'image/png'], int $maxSize = 2097152)
    {
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
        
        // Crear directorio si no existe
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function handleUpload(array $file, string $fieldName = 'image'): ?string
    {
        if (!isset($file[$fieldName]) || $file[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadedFile = $file[$fieldName];

        // Validar tipo de archivo
        if (!in_array($uploadedFile['type'], $this->allowedTypes)) {
            throw new \RuntimeException('Tipo de archivo no permitido');
        }

        // Validar tamaño
        if ($uploadedFile['size'] > $this->maxSize) {
            throw new \RuntimeException('El archivo excede el tamaño máximo permitido');
        }

        // Generar nombre único seguro
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $extension;
        $destination = $this->uploadPath . $filename;

        if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
            throw new \RuntimeException('Error al mover el archivo subido');
        }

        return $filename;
    }

    public function handleMultipleUploads(array $files, string $fieldName = 'images'): array
    {
        $uploadedFiles = [];
        
        if (!isset($files[$fieldName])) {
            return $uploadedFiles;
        }

        foreach ($files[$fieldName]['error'] as $key => $error) {
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }

            $file = [
                'name' => $files[$fieldName]['name'][$key],
                'type' => $files[$fieldName]['type'][$key],
                'tmp_name' => $files[$fieldName]['tmp_name'][$key],
                'error' => $files[$fieldName]['error'][$key],
                'size' => $files[$fieldName]['size'][$key]
            ];

            try {
                $uploadedFiles[] = $this->handleUpload([$fieldName => $file], $fieldName);
            } catch (\RuntimeException $e) {
                // Opcional: registrar el error
                continue;
            }
        }

        return array_filter($uploadedFiles);
    }
}