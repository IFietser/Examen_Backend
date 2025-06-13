<?php
require_once __DIR__ . '/../Models/Cliente.php';

class ClienteController {
    public function index() {
        echo json_encode(Cliente::all());
    }

    public function show($id) {
        $cliente = Cliente::find($id);
        if ($cliente) {
            echo json_encode($cliente);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Registro no existe']);
        }
    }

    public function store() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$this->validar($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos o inválidos']);
        return;
    }

    $nombreComercial = strtolower(trim($data['nombre_comercial']));

    if ($nombreComercial === '90minutos') {
        $data['categoria'] = 'preferencial';
        $data['porcentaje_oferta'] = 15.00;
    } elseif ($nombreComercial === 'tdeportes') {
        $data['categoria'] = 'regular';
        $data['porcentaje_oferta'] = 0.00;
    } else {
        $data['porcentaje_oferta'] = $data['porcentaje_oferta'] ?? 0.00;
    }

    Cliente::create($data);
    echo json_encode(['success' => true]);
}

    public function update($id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$this->validar($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos o inválidos']);
        return;
    }

    // Verificar si el cliente existe
    $cliente = Cliente::find($id);
    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no existe']);
        return;
    }

    Cliente::update($id, $data);
    echo json_encode(['success' => true]);
}

public function patch($id) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Verificar si cliente existe
    $cliente = Cliente::find($id);
    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no existe']);
        return;
    }

    // Validar que venga al menos un campo válido para actualizar
    $allowedFields = ['nombre_comercial', 'rut', 'direccion', 'categoria', 'contacto_nombre', 'contacto_email', 'porcentaje_oferta'];
    $updateData = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateData[$field] = $data[$field];
        }
    }

    if (empty($updateData)) {
        http_response_code(400);
        echo json_encode(['error' => 'No se enviaron datos válidos para actualizar']);
        return;
    }

    // Fusionar con los datos actuales para no perder valores no actualizados
    $newData = array_merge($cliente, $updateData);

    // Actualizar usando el método update (que espera todos los campos)
    Cliente::update($id, $newData);
    echo json_encode(['success' => true]);
}

    public function destroy($id) {
    // Verificar si el cliente existe
    $cliente = Cliente::find($id);
    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Registro no existe']);
        return;
    }

    if (!Cliente::delete($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede eliminar un cliente con camisetas asociadas']);
        return;
    }
    echo json_encode(['success' => true]);
}

    private function validar($data) {
        return isset($data['nombre_comercial'], $data['rut'], $data['direccion'], $data['contacto_nombre'], $data['contacto_email']);
    }
}