<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\User;
use App\Models\Rol;
use App\Models\Propuesta;

echo "\n🧪 VALIDANDO TYPE HINTS...\n";
echo "================================\n\n";

// Test Cliente
echo "📦 Cliente:\n";
try {
    $cliente = Cliente::first();
    if ($cliente) {
        $nombre = $cliente->getNombreCompleto();
        echo "  ✅ getNombreCompleto(): string = '$nombre'\n";
        
        $contacto = $cliente->getContacto();
        echo "  ✅ getContacto(): string = '$contacto'\n";
        
        $tiene = $cliente->tienePropuestas();
        echo "  ✅ tienePropuestas(): bool = " . ($tiene ? 'true' : 'false') . "\n";
    } else {
        echo "  ⚠️  No hay clientes en BD\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test Empleado
echo "\n📦 Empleado:\n";
try {
    $empleado = Empleado::first();
    if ($empleado) {
        $nombre = $empleado->getNombreCompleto();
        echo "  ✅ getNombreCompleto(): string = '$nombre'\n";
        
        $contacto = $empleado->getContacto();
        echo "  ✅ getContacto(): string = '$contacto'\n";
        
        $puede = $empleado->puedeAcceder();
        echo "  ✅ puedeAcceder(): bool = " . ($puede ? 'true' : 'false') . "\n";
    } else {
        echo "  ⚠️  No hay empleados en BD\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test User
echo "\n📦 User:\n";
try {
    $user = User::first();
    if ($user) {
        $nombre = $user->getNombre();
        echo "  ✅ getNombre(): string = '$nombre'\n";
        
        $es_empleado = $user->esEmpleado();
        echo "  ✅ esEmpleado(): bool = " . ($es_empleado ? 'true' : 'false') . "\n";
    } else {
        echo "  ⚠️  No hay usuarios en BD\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test Rol
echo "\n📦 Rol:\n";
try {
    $rol = Rol::first();
    if ($rol) {
        $nombre = $rol->getNombre();
        echo "  ✅ getNombre(): string = '$nombre'\n";
        
        $tiene = $rol->tieneEmpleados();
        echo "  ✅ tieneEmpleados(): bool = " . ($tiene ? 'true' : 'false') . "\n";
    } else {
        echo "  ⚠️  No hay roles en BD\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test Propuesta
echo "\n📦 Propuesta:\n";
try {
    $propuesta = Propuesta::first();
    if ($propuesta) {
        $completa = $propuesta->estaCompleta();
        echo "  ✅ estaCompleta(): bool = " . ($completa ? 'true' : 'false') . "\n";
    } else {
        echo "  ⚠️  No hay propuestas en BD\n";
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "✅ VALIDACIÓN COMPLETADA\n\n";
