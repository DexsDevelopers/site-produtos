<?php
// includes/error_handler.php - Sistema de Tratamento de Erros

class ErrorHandler
{
    private static $log_file = 'logs/error.log';

    public static function init()
    {
        // Define o handler de erros
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        // Cria o diretório de logs se não existir
        $log_dir = dirname(self::$log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }

    public static function handleError($severity, $message, $file, $line)
    {
        // Não reporta erros que foram suprimidos com @
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $error_type = self::getErrorType($severity);
        $error_message = "[$error_type] $message in $file on line $line";

        self::logError($error_message);

        // Em produção, não mostra erros detalhados
        if (self::isProduction()) {
            self::showUserFriendlyError();
        } else {
            self::showDetailedError($severity, $message, $file, $line);
        }

        return true;
    }

    public static function handleException($exception)
    {
        $error_message = "[EXCEPTION] " . $exception->getMessage() .
            " in " . $exception->getFile() .
            " on line " . $exception->getLine() .
            "\nStack trace:\n" . $exception->getTraceAsString();

        self::logError($error_message);

        if (self::isProduction()) {
            self::showUserFriendlyError();
        } else {
            self::showDetailedException($exception);
        }
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $error_message = "[FATAL ERROR] " . $error['message'] .
                " in " . $error['file'] .
                " on line " . $error['line'];

            self::logError($error_message);

            if (self::isProduction()) {
                self::showUserFriendlyError();
            }
        }
    }

    private static function getErrorType($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'ERROR';
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 'WARNING';
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'NOTICE';
            case E_STRICT:
                return 'STRICT';
            case E_RECOVERABLE_ERROR:
                return 'RECOVERABLE_ERROR';
            default:
                return 'UNKNOWN';
        }
    }

    private static function logError($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message\n";
        file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    private static function isProduction()
    {
        return !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']);
    }

    private static function showUserFriendlyError()
    {
        http_response_code(500);

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Erro - Minha Loja</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: #fff; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; text-align: center; }
        .error-icon { font-size: 64px; color: #e53e3e; margin-bottom: 20px; }
        h1 { color: #e53e3e; margin-bottom: 20px; }
        p { color: #a0aec0; margin-bottom: 30px; }
        .btn { background: #e53e3e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; }
        .btn:hover { background: #c53030; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠</div>
        <h1>Ops! Algo deu errado</h1>
        <p>Estamos enfrentando uma dificuldade técnica. Nossa equipe foi notificada e está trabalhando para resolver o problema.</p>
        <p>Tente novamente em alguns minutos ou entre em contato conosco se o problema persistir.</p>
        <a href="/" class="btn">Voltar ao Início</a>
    </div>
</body>
</html>';
        exit();
    }

    private static function showDetailedError($severity, $message, $file, $line)
    {
        echo "<div style='background: #2d1b1b; color: #ff6b6b; padding: 20px; margin: 20px; border: 1px solid #ff6b6b; border-radius: 8px;'>";
        echo "<h3>Erro PHP</h3>";
        echo "<p><strong>Tipo:</strong> " . self::getErrorType($severity) . "</p>";
        echo "<p><strong>Mensagem:</strong> $message</p>";
        echo "<p><strong>Arquivo:</strong> $file</p>";
        echo "<p><strong>Linha:</strong> $line</p>";
        echo "</div>";
    }

    private static function showDetailedException($exception)
    {
        echo "<div style='background: #2d1b1b; color: #ff6b6b; padding: 20px; margin: 20px; border: 1px solid #ff6b6b; border-radius: 8px;'>";
        echo "<h3>Exceção PHP</h3>";
        echo "<p><strong>Mensagem:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre style='background: #1a1a1a; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars($exception->getTraceAsString());
        echo "</pre>";
        echo "</div>";
    }

    public static function logCustomError($message, $context = [])
    {
        $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $error_message = "[CUSTOM ERROR] $message$context_str";
        self::logError($error_message);
    }

    public static function getErrorLogs($lines = 50)
    {
        if (!file_exists(self::$log_file)) {
            return [];
        }

        $logs = file(self::$log_file, FILE_IGNORE_NEW_LINES);
        return array_slice(array_reverse($logs), 0, $lines);
    }
}

// Inicializa o sistema de tratamento de erros
ErrorHandler::init();
?>