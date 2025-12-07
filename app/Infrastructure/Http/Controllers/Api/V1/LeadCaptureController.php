<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api\V1;

use App\Application\Lead\Services\LeadService;
use App\Application\Site\Services\SiteService;
use App\Domain\Lead\ValueObjects\SourceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class LeadCaptureController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly SiteService $siteService,
    ) {}

    public function capture(Request $request): JsonResponse
    {
        // Validar datos - teléfono validado en frontend con intl-tel-input
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|uuid',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'message' => 'nullable|string|max:5000',
            'source_type' => 'required|string|in:whatsapp_button,phone_button,contact_form',
            'source_url' => 'nullable|string|max:2000',
            'page_url' => 'nullable|string|max:2000',
            'user_agent' => 'nullable|string|max:500',
        ], [
            'email.email' => 'El email proporcionado no es válido.',
            'phone.min' => 'El teléfono debe tener al menos 7 dígitos.',
            'phone.regex' => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validar que al menos email o teléfono esté presente
        if (empty($request->input('email')) && empty($request->input('phone'))) {
            return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar al menos un email o teléfono.',
                'errors' => [
                    'email' => ['Debes proporcionar al menos un email o teléfono.'],
                    'phone' => ['Debes proporcionar al menos un email o teléfono.'],
                ],
            ], 422);
        }

        // Verificar que el sitio existe y está activo
        $site = $this->siteService->findActive($request->input('site_id'));

        if (! $site) {
            return response()->json([
                'success' => false,
                'message' => 'Sitio no encontrado o inactivo',
            ], 404);
        }

        // Validar que la petición viene del dominio registrado
        $originValidation = $this->validateOrigin($request, $site->domain);
        if ($originValidation !== true) {
            return $originValidation;
        }

        // Determinar el source_type
        $sourceType = $this->parseSourceType($request->input('source_type'));

        // Capturar lead usando el servicio
        $result = $this->leadService->capture(
            siteId: $site->id,
            sourceType: $sourceType,
            name: $request->input('name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            message: $request->input('message'),
            sourceUrl: $request->input('source_url') ?? $request->input('page_url'),
            userAgent: $request->input('user_agent'),
            ipAddress: $request->ip(),
            pageUrl: $request->input('page_url'),
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['status_code']);
    }

    /**
     * Validar que la petición viene del dominio registrado.
     */
    private function validateOrigin(Request $request, string $siteDomain): true|JsonResponse
    {
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if (! $origin) {
            return response()->json([
                'success' => false,
                'message' => 'Origen de la petición no identificado',
            ], 403);
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        $siteHost = parse_url($siteDomain, PHP_URL_HOST) ?? $siteDomain;

        // Limpiar www. para comparación
        $originHost = preg_replace('/^www\./', '', $originHost ?? '');
        $siteHost = preg_replace('/^www\./', '', $siteHost);

        // Solo permitir localhost/127.0.0.1 en entorno de desarrollo
        $isLocalOrigin = in_array($originHost, ['localhost', '127.0.0.1']);
        $isDevelopment = app()->environment('local', 'development', 'testing');

        if ($isLocalOrigin && ! $isDevelopment) {
            return response()->json([
                'success' => false,
                'message' => 'Dominio no autorizado',
            ], 403);
        }

        // Validar que el origen coincida con el dominio configurado del sitio
        if (! $isLocalOrigin && $originHost !== $siteHost) {
            return response()->json([
                'success' => false,
                'message' => 'Dominio no autorizado para este sitio',
            ], 403);
        }

        return true;
    }

    /**
     * Parsear el tipo de origen.
     */
    private function parseSourceType(string $sourceTypeValue): SourceType
    {
        return match ($sourceTypeValue) {
            'whatsapp_button' => SourceType::WHATSAPP_BUTTON,
            'phone_button' => SourceType::PHONE_BUTTON,
            'contact_form' => SourceType::CONTACT_FORM,
            default => SourceType::CONTACT_FORM,
        };
    }
}
