<?php
class DB {
    public static function connect() {
        try {
            $conn = new PDO("mysql:host=localhost;dbname=todocamisetas_db", "user_todocamisetas", "12345678");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $exception) {
            // Error de autenticación: usuario o contraseña incorrectos (código 1045)
            if ($exception->getCode() == 1045) {
                http_response_code(401);
                $errorMsg = 'Error de autenticación: usuario o contraseña incorrectos.';
            } else {
                http_response_code(500);
                $errorMsg = 'No se pudo conectar a la base de datos. Por favor, intenta más tarde.';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => $errorMsg,
                'detalle' => $exception->getMessage()
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            exit;
        }
    }
}
?>
