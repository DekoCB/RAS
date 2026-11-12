<?php

namespace App\Support;

/**
 * Portada de la campana de admision.
 *
 * El colegio puede subir su propio arte a public/images/admision/banner.*;
 * mientras no exista, las vistas dibujan una portada con el escudo.
 */
class BannerAdmision
{
    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp'];

    public static function ruta(): ?string
    {
        foreach (self::EXTENSIONES as $extension) {
            $ruta = 'images/admision/banner.' . $extension;

            if (file_exists(public_path($ruta))) {
                return $ruta;
            }
        }

        return null;
    }
}
