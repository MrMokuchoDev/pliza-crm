<?php

declare(strict_types=1);

namespace App\Domain\Site\Services;

/**
 * Servicio de dominio para validar URLs de política de privacidad.
 */
class PrivacyPolicyValidator
{
    /**
     * Valida que la URL de privacidad pertenezca al mismo dominio del sitio.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(string $privacyUrl, string $siteDomain): void
    {
        // Validar que sea una URL válida
        if (! filter_var($privacyUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('La URL de privacidad no es válida.');
        }

        // Extraer dominio de la URL de privacidad
        $privacyDomain = $this->extractDomain($privacyUrl);
        $normalizedSiteDomain = $this->extractDomain($siteDomain);

        // Comparar dominios
        if ($privacyDomain !== $normalizedSiteDomain) {
            throw new \InvalidArgumentException(
                sprintf(
                    'La URL de privacidad debe pertenecer al mismo dominio del sitio (%s). Dominio recibido: %s',
                    $normalizedSiteDomain,
                    $privacyDomain
                )
            );
        }
    }

    /**
     * Extrae el dominio base de una URL (sin subdominios www).
     */
    private function extractDomain(string $url): string
    {
        // Si no tiene esquema, agregar uno temporal
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            throw new \InvalidArgumentException('No se pudo extraer el dominio de la URL: ' . $url);
        }

        // Remover www. si existe para normalizar
        return preg_replace('/^www\./i', '', $host);
    }
}
