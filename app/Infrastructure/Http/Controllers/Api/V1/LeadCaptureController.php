<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api\V1;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\DealModel;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadCaptureController extends Controller
{
    public function capture(Request $request): JsonResponse
    {
        // Validar datos
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|uuid',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:5000',
            'source_type' => 'required|string|in:whatsapp_button,phone_button,contact_form',
            'source_url' => 'nullable|string|max:2000',
            'page_url' => 'nullable|string|max:2000',
            'user_agent' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verificar que el sitio existe y está activo
        $site = SiteModel::where('id', $request->input('site_id'))
            ->where('is_active', true)
            ->first();

        if (! $site) {
            return response()->json([
                'success' => false,
                'message' => 'Sitio no encontrado o inactivo',
            ], 404);
        }

        // Validar que la petición viene del dominio registrado
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if (! $origin) {
            return response()->json([
                'success' => false,
                'message' => 'Origen de la petición no identificado',
            ], 403);
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        $siteHost = parse_url($site->domain, PHP_URL_HOST) ?? $site->domain;

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

        // Obtener fase por defecto
        $defaultPhase = SalePhaseModel::where('is_default', true)->first();
        if (! $defaultPhase) {
            $defaultPhase = SalePhaseModel::orderBy('order')->first();
        }

        if (! $defaultPhase) {
            return response()->json([
                'success' => false,
                'message' => 'No hay fases de venta configuradas',
            ], 500);
        }

        // Determinar el source_type
        $sourceTypeValue = $request->input('source_type');
        $sourceType = match ($sourceTypeValue) {
            'whatsapp_button' => SourceType::WHATSAPP_BUTTON,
            'phone_button' => SourceType::PHONE_BUTTON,
            'contact_form' => SourceType::CONTACT_FORM,
            default => SourceType::CONTACT_FORM,
        };

        $email = $request->input('email');
        $phone = $request->input('phone');
        $message = $request->input('message');
        $name = $request->input('name');

        // Normalizar teléfono (remover espacios, guiones, paréntesis y +)
        $normalizedPhone = $phone ? $this->normalizePhone($phone) : null;

        // Toda la lógica de búsqueda y creación dentro de transacción para evitar race conditions
        return DB::transaction(function () use ($request, $site, $sourceType, $defaultPhase, $name, $email, $phone, $message, $normalizedPhone) {
            // Buscar contacto existente por email o teléfono normalizado (con lock para evitar duplicados)
            $existingLead = null;
            if ($email) {
                $existingLead = LeadModel::where('email', $email)->lockForUpdate()->first();
            }
            if (! $existingLead && $normalizedPhone && strlen($normalizedPhone) >= 7) {
                // Buscar por teléfono normalizado - usar los últimos 7 dígitos para filtro inicial
                $lastDigits = substr($normalizedPhone, -7);
                $existingLead = LeadModel::whereNotNull('phone')
                    ->where('phone', 'like', '%' . $lastDigits . '%')
                    ->lockForUpdate()
                    ->get()
                    ->first(fn ($lead) => $this->normalizePhone($lead->phone) === $normalizedPhone);
            }

            // Si el contacto existe
            if ($existingLead) {
                // Verificar si tiene negocio abierto
                if ($existingLead->hasOpenDeal()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Contacto ya registrado con negocio activo',
                        'data' => [
                            'id' => $existingLead->id,
                            'existing' => true,
                            'has_open_deal' => true,
                        ],
                    ], 200);
                }

                // No tiene negocio abierto, crear uno nuevo
                $deal = $this->createDeal(
                    $existingLead,
                    $defaultPhase,
                    $name,
                    $message,
                    $sourceType
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Nuevo negocio creado para contacto existente',
                    'data' => [
                        'id' => $existingLead->id,
                        'deal_id' => $deal->id,
                        'existing' => true,
                        'has_open_deal' => false,
                    ],
                ], 201);
            }

            // Contacto no existe, crear nuevo contacto y negocio
            $lead = LeadModel::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'source_type' => $sourceType->value,
                'source_site_id' => $site->id,
                'source_url' => $request->input('source_url') ?? $request->input('page_url'),
                'metadata' => [
                    'user_agent' => $request->input('user_agent'),
                    'ip_address' => $request->ip(),
                    'page_url' => $request->input('page_url'),
                    'captured_at' => now()->toIso8601String(),
                ],
            ]);

            // Crear el negocio asociado
            $deal = $this->createDeal(
                $lead,
                $defaultPhase,
                $name,
                $message,
                $sourceType
            );

            return response()->json([
                'success' => true,
                'message' => 'Contacto y negocio creados exitosamente',
                'data' => [
                    'id' => $lead->id,
                    'deal_id' => $deal->id,
                    'existing' => false,
                ],
            ], 201);
        });
    }

    /**
     * Crear un negocio para un contacto
     */
    private function createDeal(
        LeadModel $lead,
        SalePhaseModel $defaultPhase,
        ?string $name,
        ?string $message,
        SourceType $sourceType
    ): DealModel {
        // Generar nombre del negocio basado en el tipo de origen
        $dealName = match ($sourceType) {
            SourceType::WHATSAPP_BUTTON => 'WhatsApp - ' . ($name ?: 'Sin nombre'),
            SourceType::PHONE_BUTTON => 'Llamada - ' . ($name ?: 'Sin nombre'),
            SourceType::CONTACT_FORM => 'Formulario - ' . ($name ?: 'Sin nombre'),
            default => 'Nuevo negocio - ' . ($name ?: 'Sin nombre'),
        };

        return DealModel::create([
            'lead_id' => $lead->id,
            'sale_phase_id' => $defaultPhase->id,
            'name' => $dealName,
            'description' => $message,
            'estimated_close_date' => now()->addMonth()->format('Y-m-d'),
        ]);
    }

    /**
     * Normalizar teléfono removiendo espacios, guiones, paréntesis y +
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s\-\(\)\+]/', '', $phone);
    }
}
