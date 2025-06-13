<?php
require_once __DIR__ . '/../Models/Camiseta.php';

class CamisetaController {
    public function index() {
        $camisetas = Camiseta::all();
        $resultado = [];

        foreach ($camisetas as $camiseta) {
            $tallas = Camiseta::getTallas($camiseta['id']);

            $resultado[] = [
                'id' => $camiseta['id'],
                'titulo' => $camiseta['titulo'],
                'club' => $camiseta['club'],
                'pais' => $camiseta['pais'],
                'tipo' => $camiseta['tipo'],
                'color' => $camiseta['color'],
                'precio' => $camiseta['precio'],
                'precio_oferta' => $camiseta['precio_oferta'],
                'codigo_producto' => $camiseta['codigo_producto'],
                'detalles' => $camiseta['detalles'] ?? null,
                'cliente_id' => $camiseta['cliente_id'],
                'tallas_disponibles' => $tallas,
            ];
        }

        echo json_encode($resultado);
    }

    public function show($id) {
        $camiseta = Camiseta::find($id);
        if ($camiseta) {
            $tallas = Camiseta::getTallas($id);
            $resultado = [
                'id' => $camiseta['id'],
                'titulo' => $camiseta['titulo'],
                'club' => $camiseta['club'],
                'pais' => $camiseta['pais'],
                'tipo' => $camiseta['tipo'],
                'color' => $camiseta['color'],
                'precio' => $camiseta['precio'],
                'precio_oferta' => $camiseta['precio_oferta'],
                'codigo_producto' => $camiseta['codigo_producto'],
                'detalles' => $camiseta['detalles'] ?? null,
                'cliente_id' => $camiseta['cliente_id'],
                'tallas_disponibles' => $tallas,
            ];
            echo json_encode($resultado);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Registro no existe']);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validar($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        $id = Camiseta::create($data);
        if (!$id) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear camiseta']);
            return;
        }

        if (isset($data['tallas_disponibles']) && is_array($data['tallas_disponibles'])) {
            Camiseta::setTallas($id, $data['tallas_disponibles']);
        }

        echo json_encode(['success' => true, 'id' => $id]);
    }

    public function update($id) {
    $camiseta = Camiseta::find($id);
    if (!$camiseta) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no existe']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$this->validar($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos']);
        return;
    }

    $updated = Camiseta::update($id, $data);
    if (!$updated) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar camiseta']);
        return;
    }

    if (isset($data['tallas_disponibles']) && is_array($data['tallas_disponibles'])) {
        Camiseta::setTallas($id, $data['tallas_disponibles']);
    }

    echo json_encode(['success' => true]);
}

public function patch($id) {
    $camiseta = Camiseta::find($id);
    if (!$camiseta) {
        http_response_code(404);
        echo json_encode(['error' => 'Camiseta no encontrada']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos o vacíos']);
        return;
    }

    // Mezclar datos existentes con nuevos datos (actualización parcial)
    $updatedData = array_merge($camiseta, $data);

    // Verificamos si cambió el precio
    if (isset($data['precio'])) {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT categoria, porcentaje_oferta FROM clientes WHERE id = ?");
        $stmt->execute([$camiseta['cliente_id']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente && strtolower($cliente['categoria']) === 'preferencial') {
            $porcentaje = floatval($cliente['porcentaje_oferta']);
            $nuevoPrecio = floatval($data['precio']);
            $precioOferta = round($nuevoPrecio * (1 - $porcentaje / 100), 2);
            $updatedData['precio_oferta'] = $precioOferta;
        } else {
            // Si no tiene descuento o no es preferencial
            $updatedData['precio_oferta'] = floatval($data['precio']);
        }
    }

    // Ejecutar actualización
    $updated = Camiseta::update($id, $updatedData);
    if (!$updated) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar camiseta']);
        return;
    }

    // Actualizar tallas si vienen
    if (isset($data['tallas_disponibles']) && is_array($data['tallas_disponibles'])) {
        Camiseta::setTallas($id, $data['tallas_disponibles']);
    }

    echo json_encode(['success' => true]);
}

    public function destroy($id) {
    $camiseta = Camiseta::find($id);
    if (!$camiseta) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no existe']);
        return;
    }

    $deleted = Camiseta::delete($id);
    if ($deleted) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el registro']);
    }
}

    private function validar($data) {
        // Agregué 'detalles' como opcional
        return isset($data['titulo'], $data['club'], $data['pais'], $data['tipo'], $data['color'], $data['precio'], $data['codigo_producto']);
    }
}
?>