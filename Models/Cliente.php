<?php
require_once __DIR__ . '/../config/database.php';

class Cliente {
    public static function all() {
        $db = DB::connect();
        $stmt = $db->query("SELECT * FROM clientes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
    $db = DB::connect();
    $stmt = $db->prepare("
        INSERT INTO clientes (
            nombre_comercial, rut, direccion, categoria,
            contacto_nombre, contacto_email, porcentaje_oferta
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['nombre_comercial'],
        $data['rut'],
        $data['direccion'],
        $data['categoria'],
        $data['contacto_nombre'],
        $data['contacto_email'],
        $data['porcentaje_oferta']
    ]);
}

public static function update($id, $data) {
    $db = DB::connect();

    // Paso 1: Determinar categoría y porcentaje según nombre comercial
    $nombre = strtolower($data['nombre_comercial']);
    $preferenciales = ['90deportes', '90minutos'];

    if (in_array($nombre, $preferenciales)) {
        $data['categoria'] = 'preferencial';
        $data['porcentaje_oferta'] = 15.00;
    } else {
        $data['categoria'] = 'regular';
        $data['porcentaje_oferta'] = 0.00;
    }

    // Paso 2: Actualizar cliente
    $stmt = $db->prepare("UPDATE clientes SET nombre_comercial=?, rut=?, direccion=?, categoria=?, contacto_nombre=?, contacto_email=?, porcentaje_oferta=? WHERE id=?");
    $ok = $stmt->execute([
        $data['nombre_comercial'], $data['rut'], $data['direccion'],
        $data['categoria'], $data['contacto_nombre'], $data['contacto_email'],
        $data['porcentaje_oferta'], $id
    ]);

    // Paso 3: Actualizar camisetas solo si el cliente fue actualizado
    if ($ok) {
        // Obtener camisetas del cliente
        $stmtCamisetas = $db->prepare("SELECT id, precio FROM camisetas WHERE cliente_id = ?");
        $stmtCamisetas->execute([$id]);
        $camisetas = $stmtCamisetas->fetchAll(PDO::FETCH_ASSOC);

foreach ($camisetas as $camiseta) {
    $precio = floatval($camiseta['precio']);
    $descuento = $data['porcentaje_oferta'] / 100;  // Convertir porcentaje a decimal
    $precio_oferta = $precio * (1 - $descuento);

    $stmtUpdate = $db->prepare("UPDATE camisetas SET precio_oferta = ? WHERE id = ?");
    $stmtUpdate->execute([$precio_oferta, $camiseta['id']]);
}

    }

    return $ok;
}

    public static function delete($id) {
        $db = DB::connect();
        // Verificar si hay camisetas asociadas antes de eliminar
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM camisetas WHERE cliente_id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetchColumn() > 0) {
            return false;
        }

        $stmt = $db->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>