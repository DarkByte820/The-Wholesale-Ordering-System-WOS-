<?php
/**
 * API Response Handler
 */

class Response {
    
    public static function success($data = null, $message = "Success", $code = SUCCESS) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    public static function error($message = "Error", $code = SERVER_ERROR, $errors = null) {
        http_response_code($code);
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response);
        exit();
    }
    
    public static function paginated($data, $total, $page, $per_page, $message = "Success") {
        http_response_code(SUCCESS);
        $total_pages = ceil($total / $per_page);
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
}

?>