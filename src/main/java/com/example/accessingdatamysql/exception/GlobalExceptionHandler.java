package com.example.accessingdatamysql.exception;

import jakarta.servlet.http.HttpServletRequest;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.MissingServletRequestParameterException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.method.annotation.MethodArgumentTypeMismatchException;

import java.time.LocalDateTime;
import java.util.stream.Collectors;


/**
 * Controlador global para interceptar y gestionar excepciones en toda la aplicación.
 */
@RestControllerAdvice
public class GlobalExceptionHandler {

    private static final Logger logger = LoggerFactory.getLogger(GlobalExceptionHandler.class);

    /**
     * 1. Manejo de recurso no encontrado (404 Not Found) - LOG WARN
     */
    @ExceptionHandler(ResourceNotFoundException.class)
    public ResponseEntity<ErrorResponse> handleResourceNotFoundException(
            ResourceNotFoundException ex,
            HttpServletRequest request) {

        logger.warn("[WARN] Recurso no encontrado (404) en URI '{}': {}", request.getRequestURI(), ex.getMessage());

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.NOT_FOUND.value(),
                HttpStatus.NOT_FOUND.getReasonPhrase(),
                ex.getMessage(),
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.NOT_FOUND);
    }

    /**
     * 2. Manejo de tipo de argumento inválido (400 Bad Request) - LOG WARN
     */
    @ExceptionHandler(MethodArgumentTypeMismatchException.class)
    public ResponseEntity<ErrorResponse> handleMethodArgumentTypeMismatchException(
            MethodArgumentTypeMismatchException ex,
            HttpServletRequest request) {

        String tipoEsperado = ex.getRequiredType() != null ? ex.getRequiredType().getSimpleName() : "válido";
        String mensaje = String.format("El parámetro '%s' con valor '%s' no es válido. Se esperaba un tipo %s.",
                ex.getName(), ex.getValue(), tipoEsperado);

        logger.warn("[WARN] Tipo de dato incompatible (400) en URI '{}': {}", request.getRequestURI(), mensaje);

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.BAD_REQUEST.value(),
                HttpStatus.BAD_REQUEST.getReasonPhrase(),
                mensaje,
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.BAD_REQUEST);
    }

    /**
     * 3. Manejo de fallos en validaciones de entidad (@Valid / @NotBlank / @Size / etc.) - LOG WARN
     */
    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ErrorResponse> handleMethodArgumentNotValidException(
            MethodArgumentNotValidException ex,
            HttpServletRequest request) {

        String detalles = ex.getBindingResult().getFieldErrors().stream()
                .map(err -> String.format("[%s: %s]", err.getField(), err.getDefaultMessage()))
                .collect(Collectors.joining(", "));

        logger.warn("[WARN] Validación de entidad fallida (400) en URI '{}': {}", request.getRequestURI(), detalles);

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.BAD_REQUEST.value(),
                HttpStatus.BAD_REQUEST.getReasonPhrase(),
                "Error de validación en los campos: " + detalles,
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.BAD_REQUEST);
    }

    /**
     * 4. Manejo de parámetros de consulta obligatorios faltantes (400 Bad Request) - LOG WARN
     */
    @ExceptionHandler(MissingServletRequestParameterException.class)
    public ResponseEntity<ErrorResponse> handleMissingServletRequestParameterException(
            MissingServletRequestParameterException ex,
            HttpServletRequest request) {

        String mensaje = String.format("El parámetro obligatorio '%s' (tipo %s) no fue proporcionado en la solicitud.",
                ex.getParameterName(), ex.getParameterType());

        logger.warn("[WARN] Parámetro obligatorio faltante (400) en URI '{}': {}", request.getRequestURI(), mensaje);

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.BAD_REQUEST.value(),
                HttpStatus.BAD_REQUEST.getReasonPhrase(),
                mensaje,
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.BAD_REQUEST);
    }

    /**
     * 4. Manejo de argumentos ilegales o validaciones de negocio (400 Bad Request) - LOG WARN
     */
    @ExceptionHandler(IllegalArgumentException.class)
    public ResponseEntity<ErrorResponse> handleIllegalArgumentException(
            IllegalArgumentException ex,
            HttpServletRequest request) {

        logger.warn("[WARN] Argumento ilegal o violación de regla de negocio (400) en URI '{}': {}",
                request.getRequestURI(), ex.getMessage());

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.BAD_REQUEST.value(),
                HttpStatus.BAD_REQUEST.getReasonPhrase(),
                ex.getMessage(),
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.BAD_REQUEST);
    }

    /**
     * 5. Manejo general de cualquier otra excepción no capturada (500 Internal Server Error) - LOG ERROR
     */
    @ExceptionHandler(Exception.class)
    public ResponseEntity<ErrorResponse> handleGlobalException(
            Exception ex,
            HttpServletRequest request) {

        logger.error("[ERROR] Excepción crítica no controlada en el servidor (500) en URI '{}': {}",
                request.getRequestURI(), ex.getMessage(), ex);

        ErrorResponse error = new ErrorResponse(
                LocalDateTime.now(),
                HttpStatus.INTERNAL_SERVER_ERROR.value(),
                HttpStatus.INTERNAL_SERVER_ERROR.getReasonPhrase(),
                "Error interno del servidor: " + ex.getMessage(),
                request.getRequestURI()
        );

        return new ResponseEntity<>(error, HttpStatus.INTERNAL_SERVER_ERROR);
    }
}
