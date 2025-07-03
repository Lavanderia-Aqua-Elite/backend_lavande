<?php
declare(strict_types=1);

namespace App\Controller\Products;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Models\Products;
use App\Validator\Validator;
use App\Hooks\ImageHook;
use App\Hooks\DeleteHook;

class ProductsController
{
    private Products $productsModel;
    private Validator $validator;
    private ImageHook $imageHook;
    private DeleteHook $deleteHook;

    public function __construct(
        Products $productsModel, 
        Validator $validator,
        ImageHook $imageHook,
        DeleteHook $deleteHook
    ) {
        $this->productsModel = $productsModel;
        $this->validator = $validator;
        $this->imageHook = $imageHook;
        $this->deleteHook = $deleteHook;
    }

    /**
     * Obtener todos los productos
     */
    public function getAllProducts(Request $request, Response $response): Response
    {
        try {
            $products = $this->productsModel->show();
            
            return $this->jsonResponse($response, [
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un producto por ID
     */
    public function getProductById(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            
            if ($id <= 0) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID inválido'
                ], 400);
            }

            $product = $this->productsModel->getById($id);
            
            if (!$product) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return $this->jsonResponse($response, [
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar nuevo producto
     */
    public function createProduct(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validación
            $validation = $this->validator->validate($data, [
                'nam_p' => 'required|max_len,100',
                'price_p' => 'required|numeric',
                'units_p' => 'numeric',
                'color_p' => 'max_len,50',
                'brand_p' => 'max_len,50'
            ]);

            // Procesar imagen si existe
            $files = $request->getUploadedFiles();
            if (!empty($files['image'])) {
                $imageName = $this->imageHook->handleUpload(['image' => $files['image']], 'image');
                if ($imageName) {
                    $data['image_p'] = $imageName;
                }
            }

            // Registrar producto
            $productId = $this->productsModel->register($data);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Producto registrado exitosamente',
                'product_id' => $productId
            ], 201);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar producto
     */
    public function updateProduct(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $data = $request->getParsedBody();
            
            if ($id <= 0) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID inválido'
                ], 400);
            }

            // Validación
            $validation = $this->validator->validate($data, [
                'nam_p' => 'max_len,100',
                'price_p' => 'numeric',
                'units_p' => 'numeric',
                'color_p' => 'max_len,50',
                'brand_p' => 'max_len,50'
            ]);

            // Procesar imagen si existe
            $files = $request->getUploadedFiles();
            if (!empty($files['image'])) {
                $imageName = $this->imageHook->handleUpload(['image' => $files['image']], 'image');
                if ($imageName) {
                    $data['image_p'] = $imageName;
                }
            }

            // Actualizar producto
            $success = $this->productsModel->update($id, $data);
            
            if (!$success) {
                throw new \Exception("No se pudo actualizar el producto");
            }

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function deleteProduct(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            
            if ($id <= 0) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'ID inválido'
                ], 400);
            }

            // Eliminar producto
            $success = $this->deleteHook->delete('products', 'id_product', $id);
            
            if (!$success) {
                throw new \Exception("No se pudo eliminar el producto");
            }

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper para respuestas JSON
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}