<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api\V1;

use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use App\Infrastructure\Persistence\Eloquent\SalePhaseModel;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        if ($origin) {
            $originHost = parse_url($origin, PHP_URL_HOST);
            $siteHost = parse_url($site->domain, PHP_URL_HOST) ?? $site->domain;

            // Limpiar www. para comparación
            $originHost = preg_replace('/^www\./', '', $originHost ?? '');
            $siteHost = preg_replace('/^www\./', '', $siteHost);

            // Permitir localhost/127.0.0.1 en desarrollo
            $isLocalOrigin = in_array($originHost, ['localhost', '127.0.0.1']);
            $isLocalSite = in_array($siteHost, ['localhost', '127.0.0.1']);

            if (! $isLocalOrigin && ! $isLocalSite && $originHost !== $siteHost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dominio no autorizado',
                ], 403);
            }
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

        // Crear el lead
        $lead = LeadModel::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'source_type' => $sourceType->value,
            'source_site_id' => $site->id,
            'source_url' => $request->input('source_url') ?? $request->input('page_url'),
            'sale_phase_id' => $defaultPhase->id,
            'metadata' => [
                'user_agent' => $request->input('user_agent'),
                'ip_address' => $request->ip(),
                'page_url' => $request->input('page_url'),
                'captured_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead capturado exitosamente',
            'data' => [
                'id' => $lead->id,
            ],
        ], 201);
    }
}
