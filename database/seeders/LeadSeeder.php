<?php

namespace Database\Seeders;

use App\Application\Lead\DTOs\LeadData;
use App\Application\Lead\Services\LeadService;
use App\Application\Note\DTOs\NoteData;
use App\Application\Note\Services\NoteService;
use App\Domain\Lead\ValueObjects\SourceType;
use App\Infrastructure\Persistence\Eloquent\LeadModel;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function __construct(
        private readonly LeadService $leadService,
        private readonly NoteService $noteService,
    ) {}

    public function run(): void
    {
        $leads = [
            ['name' => 'Carlos Mendoza', 'email' => 'carlos.mendoza@gmail.com', 'phone' => '+573001234567', 'message' => 'Interesado en el plan empresarial', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'María García', 'email' => 'maria.garcia@hotmail.com', 'phone' => '+573109876543', 'message' => 'Quiero más información sobre precios', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Juan Rodríguez', 'email' => 'juan.rodriguez@empresa.co', 'phone' => '+573205551234', 'message' => 'Necesito una demo del producto', 'source' => SourceType::PHONE_BUTTON],
            ['name' => 'Ana Martínez', 'email' => 'ana.martinez@outlook.com', 'phone' => '+573157778899', 'message' => 'Consulta sobre integración con mi sistema actual', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Pedro Sánchez', 'email' => 'pedro.sanchez@yahoo.com', 'phone' => '+573006667788', 'message' => 'Me contactaron por referencia de un amigo', 'source' => SourceType::MANUAL],
            ['name' => 'Laura Jiménez', 'email' => 'laura.jimenez@gmail.com', 'phone' => '+573118889900', 'message' => 'Interesada en el módulo de reportes', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Diego López', 'email' => 'diego.lopez@empresa.com', 'phone' => '+573223334455', 'message' => 'Buscamos solución para 50 usuarios', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Camila Torres', 'email' => 'camila.torres@hotmail.com', 'phone' => '+573014445566', 'message' => 'Necesito cotización urgente', 'source' => SourceType::PHONE_BUTTON],
            ['name' => 'Andrés Ramírez', 'email' => 'andres.ramirez@gmail.com', 'phone' => '+573125556677', 'message' => 'Vi su publicidad en Instagram', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Valentina Herrera', 'email' => 'valentina.herrera@outlook.com', 'phone' => '+573206667788', 'message' => 'Consulta sobre soporte técnico', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Santiago Morales', 'email' => 'santiago.morales@empresa.co', 'phone' => '+573017778899', 'message' => 'Interesado en plan anual', 'source' => SourceType::MANUAL],
            ['name' => 'Isabella Díaz', 'email' => 'isabella.diaz@gmail.com', 'phone' => '+573128889900', 'message' => 'Migración desde otro CRM', 'source' => SourceType::PHONE_BUTTON],
            ['name' => 'Mateo Vargas', 'email' => 'mateo.vargas@yahoo.com', 'phone' => '+573219990011', 'message' => 'Quiero probar antes de comprar', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Sofía Castro', 'email' => 'sofia.castro@hotmail.com', 'phone' => '+573000011122', 'message' => 'Recomendación de LinkedIn', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Daniel Ortiz', 'email' => 'daniel.ortiz@empresa.com', 'phone' => '+573111122233', 'message' => 'Necesito API para integración', 'source' => SourceType::MANUAL],
            ['name' => 'Paula Romero', 'email' => 'paula.romero@gmail.com', 'phone' => '+573222233344', 'message' => 'Interesada en automatización de marketing', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Nicolás Gutiérrez', 'email' => 'nicolas.gutierrez@outlook.com', 'phone' => '+573013344455', 'message' => 'Consulta sobre personalización', 'source' => SourceType::PHONE_BUTTON],
            ['name' => 'Mariana Peña', 'email' => 'mariana.pena@empresa.co', 'phone' => '+573124455566', 'message' => 'Empresa con 200 empleados', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Sebastián Ruiz', 'email' => 'sebastian.ruiz@yahoo.com', 'phone' => '+573215566677', 'message' => 'Buscando reemplazar Excel', 'source' => SourceType::MANUAL],
            ['name' => 'Luciana Mendez', 'email' => 'luciana.mendez@gmail.com', 'phone' => '+573006677788', 'message' => 'Me interesa el webinar de mañana', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Felipe Cardenas', 'email' => 'felipe.cardenas@hotmail.com', 'phone' => '+573117788899', 'message' => 'Startup en fase de crecimiento', 'source' => SourceType::CONTACT_FORM],
            ['name' => 'Gabriela Silva', 'email' => 'gabriela.silva@empresa.com', 'phone' => '+573228899900', 'message' => 'Necesito módulo de facturación', 'source' => SourceType::PHONE_BUTTON],
            ['name' => 'Alejandro Vega', 'email' => 'alejandro.vega@outlook.com', 'phone' => '+573019900011', 'message' => 'Evaluando diferentes opciones de CRM', 'source' => SourceType::MANUAL],
            ['name' => 'Carolina Ríos', 'email' => 'carolina.rios@gmail.com', 'phone' => '+573120011122', 'message' => 'Agencia de marketing digital', 'source' => SourceType::WHATSAPP_BUTTON],
            ['name' => 'Ricardo Navarro', 'email' => 'ricardo.navarro@yahoo.com', 'phone' => '+573211122233', 'message' => 'Pregunta sobre seguridad de datos', 'source' => SourceType::CONTACT_FORM],
        ];

        $notes = [
            'Llamada realizada, muy interesado. Seguimiento en 3 días.',
            'Envié propuesta comercial por email.',
            'Cliente potencial de alto valor.',
            'Requiere aprobación de gerencia.',
            'Agendada demostración para la próxima semana.',
            'Pidió descuento especial por volumen.',
            'Tiene dudas sobre la migración de datos.',
            'Competidor está ofreciendo precio menor.',
            'Excelente fit con nuestro producto.',
            'Necesita funcionalidad que no tenemos aún.',
        ];

        foreach ($leads as $leadInfo) {
            // Buscar si ya existe
            $lead = LeadModel::where('email', $leadInfo['email'])->first();

            if (! $lead) {
                // Crear lead usando el servicio
                $leadData = new LeadData(
                    name: $leadInfo['name'],
                    email: $leadInfo['email'],
                    phone: $leadInfo['phone'],
                    message: $leadInfo['message'],
                    sourceType: $leadInfo['source'],
                );
                $lead = $this->leadService->create($leadData);
            }

            // Agregar 1-3 notas aleatorias a algunos leads
            if (rand(0, 1) === 1) {
                $noteCount = rand(1, 3);
                $usedNotes = array_rand($notes, min($noteCount, count($notes)));
                $usedNotes = is_array($usedNotes) ? $usedNotes : [$usedNotes];

                foreach ($usedNotes as $noteIndex) {
                    // Verificar si ya existe la nota
                    $existingNote = $lead->notes()->where('content', $notes[$noteIndex])->first();

                    if (! $existingNote) {
                        $noteData = new NoteData(
                            leadId: $lead->id,
                            content: $notes[$noteIndex],
                        );
                        $this->noteService->create($noteData);
                    }
                }
            }
        }

        $this->command->info('25 leads creados con notas.');
    }
}
