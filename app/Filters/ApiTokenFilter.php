<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiTokenFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $expected = env('CRON_API_TOKEN');
        $provided = $request->getHeaderLine('X-Api-Token');

        if (!$expected || !hash_equals($expected, (string) $provided)) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Token de API inválido o ausente.',
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
