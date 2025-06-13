<?php
require_once __DIR__ . '/../config/database.php';

class Camiseta {
    public static function all() {
        $db = DB::connect();
        $stmt = $db->query("SELECT * FROM camisetas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT * FROM camisetas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public static function create($data) {
    $db = DB::connect();

    // Obtener datos del cliente
    $stmtCliente = $db->prepare("SELECT categoria, porcentaje_oferta FROM clientes WHERE id = ?");
    $stmtCliente->execute([$data['cliente_id']]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        var_dump("No se encontró cliente con id: " . $data['cliente_id']);
        return false;
    }

    $precioOriginal = floatval($data['precio']);
    $precioOferta = $precioOriginal;

    $categoria = strtolower(trim($cliente['categoria']));
    $porcentaje = floatval($cliente['porcentaje_oferta']);

    if ($categoria === 'preferencial' && $porcentaje > 0) {
        $precioOferta = round($precioOriginal * (1 - $porcentaje / 100), 2);
        var_dump("Descuento aplicado: {$porcentaje}%, precio oferta: {$precioOferta}");
    } else {
        var_dump("No se aplicó descuento. Categoria: {$categoria}, porcentaje: {$porcentaje}");
    }

    var_dump("Precio original: {$precioOriginal}, Precio oferta que se guardará: {$precioOferta}");

    // Insertar
    $stmt = $db->prepare("
        INSERT INTO camisetas (
            titulo, club, pais, tipo, color,
            precio, precio_oferta, codigo_producto, detalles, cliente_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $data['titulo'], $data['club'], $data['pais'],
        $data['tipo'], $data['color'], $precioOriginal,
        $precioOferta, $data['codigo_producto'], $data['detalles'],
        $data['cliente_id']
    ]);

    if (!$result) {
        var_dump("Error al insertar: ", $stmt->errorInfo());
        return false;
    }

    var_dump("Insertado con ID: " . $db->lastInsertId());
    return $db->lastInsertId();
}


    public static function update($id, $data) {
    $db = DB::connect();

    // Obtener datos del cliente para calcular precio_oferta
    
    $stmtCliente = $db->prepare("SELECT categoria, porcentaje_oferta FROM clientes WHERE id = ?");
    $stmtCliente->execute([$data['cliente_id']]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    $precioOriginal = floatval($data['precio']);
    $precioOferta = $precioOriginal;

    if ($cliente) {
        $categoria = strtolower(trim($cliente['categoria']));
        $porcentaje = floatval($cliente['porcentaje_oferta']);
        if ($categoria === 'preferencial' && $porcentaje > 0) {
            $precioOferta = round($precioOriginal * (1 - $porcentaje / 100), 2);
        }
    }

    $stmt = $db->prepare("
        UPDATE camisetas SET
            titulo = ?, club = ?, pais = ?, tipo = ?, color = ?,
            precio = ?, precio_oferta = ?, codigo_producto = ?, detalles = ?, cliente_id = ?
        WHERE id = ?
    ");

    return $stmt->execute([
        $data['titulo'], $data['club'], $data['pais'],
        $data['tipo'], $data['color'], $precioOriginal,
        $precioOferta, $data['codigo_producto'], $data['detalles'],
        $data['cliente_id'], $id
    ]);
}


    public static function delete($id) {
        $db = DB::connect();
        $stmt = $db->prepare("DELETE FROM camisetas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getTallas($camiseta_id) {
        $db = DB::connect();
        $stmt = $db->prepare("
            SELECT t.talla FROM tallas t
            JOIN camiseta_talla ct ON t.id = ct.talla_id
            WHERE ct.camiseta_id = ?
        ");
        $stmt->execute([$camiseta_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function setTallas($camiseta_id, $tallas) {
        $db = DB::connect();
        // Eliminar tallas actuales
        $stmt = $db->prepare("DELETE FROM camiseta_talla WHERE camiseta_id = ?");
        $stmt->execute([$camiseta_id]);

        // Preparar consultas
        $stmtTalla = $db->prepare("SELECT id FROM tallas WHERE talla = ?");
        $stmtInsert = $db->prepare("INSERT INTO camiseta_talla (camiseta_id, talla_id) VALUES (?, ?)");

        foreach ($tallas as $nombreTalla) {
            $stmtTalla->execute([$nombreTalla]);
            $tallaId = $stmtTalla->fetchColumn();
            if ($tallaId) {
                $stmtInsert->execute([$camiseta_id, $tallaId]);
            }
        }
    }
}
?>