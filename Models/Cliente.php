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
        $stmt = $db->prepare("UPDATE clientes SET nombre_comercial=?, rut=?, direccion=?, categoria=?, contacto_nombre=?, contacto_email=?, porcentaje_oferta=? WHERE id=?");
        return $stmt->execute([
            $data['nombre_comercial'], $data['rut'], $data['direccion'],
            $data['categoria'], $data['contacto_nombre'], $data['contacto_email'],
            $data['porcentaje_oferta'] ?? 0, $id
        ]);
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