<?php

if (!function_exists('has_permission')) {
    function has_permission(string $permiso): bool
    {
        $permisos = session('usuario_permisos');
        if (!is_array($permisos)) {
            $permisos = json_decode((string)$permisos, true) ?? [];
        }

        return in_array($permiso, $permisos, true);
    }
}
