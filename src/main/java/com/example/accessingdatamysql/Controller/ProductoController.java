package com.example.accessingdatamysql.Controller;

import com.example.accessingdatamysql.Repository.ProductoRepository;
import com.example.accessingdatamysql.entity.Producto;
import com.example.accessingdatamysql.exception.ResourceNotFoundException;
import jakarta.validation.Valid;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.web.bind.annotation.*;

import java.util.List;


@RestController
@RequestMapping("/productos")
public class ProductoController {

    private static final Logger logger = LoggerFactory.getLogger(ProductoController.class);

    @Autowired
    private ProductoRepository productoRepository;

    @GetMapping
    public List<Producto> listarProductos() {
        logger.info("[INFO] Solicitando listado completo de productos.");
        List<Producto> productos = productoRepository.findAll();
        logger.info("[INFO] Se obtuvieron {} productos.", productos.size());
        return productos;
    }

    @PostMapping
    public Producto crearProducto(@Valid @RequestBody Producto producto) {
        logger.info("[INFO] Registrando nuevo producto: nombre='{}', precio={}, stock={}",
                producto.getNombre(), producto.getPrecio(), producto.getStock());
        Producto nuevo = productoRepository.save(producto);
        logger.info("[INFO] Producto registrado exitosamente con ID: {}", nuevo.getId());
        return nuevo;
    }

    @GetMapping("/{id}")
    public Producto buscarProducto(@PathVariable Long id) {
        logger.info("[INFO] Buscando producto con ID: {}", id);
        return productoRepository.findById(id).orElseThrow(() -> {
            logger.warn("[WARN] Producto con ID {} no encontrado en la base de datos.", id);
            return new ResourceNotFoundException("Producto no encontrado con el ID: " + id);
        });
    }

    @PutMapping("/{id}")
    public Producto actualizarProducto(
            @PathVariable Long id,
            @Valid @RequestBody Producto datos) {

        logger.info("[INFO] Solicitud de actualización para producto con ID: {}", id);

        Producto producto = productoRepository.findById(id).orElseThrow(() -> {
            logger.warn("[WARN] No se puede actualizar: producto con ID {} no existe.", id);
            return new ResourceNotFoundException("No se puede actualizar. Producto no encontrado con el ID: " + id);
        });

        producto.setNombre(datos.getNombre());
        producto.setDescripcion(datos.getDescripcion());
        producto.setPrecio(datos.getPrecio());
        producto.setStock(datos.getStock());

        Producto actualizado = productoRepository.save(producto);
        logger.info("[INFO] Producto con ID {} actualizado correctamente.", id);
        return actualizado;
    }

    @DeleteMapping("/{id}")
    public String eliminarProducto(@PathVariable Long id) {
        logger.info("[INFO] Solicitud de eliminación para producto con ID: {}", id);

        if (!productoRepository.existsById(id)) {
            logger.warn("[WARN] No se puede eliminar: producto con ID {} no encontrado.", id);
            throw new ResourceNotFoundException("No se puede eliminar. Producto no encontrado con el ID: " + id);
        }

        productoRepository.deleteById(id);
        logger.info("[INFO] Producto con ID {} eliminado exitosamente.", id);
        return "Producto eliminado correctamente";
    }

    // =========================================================================
    // 1. BÚSQUEDA POR 2 CAMPOS CON OPERADOR (Y / AND)
    // =========================================================================

    /**
     * Búsqueda por 2 campos (nombre y descripción) con operador AND.
     * Insensible a mayúsculas/minúsculas y coincidencia parcial.
     * Ejemplo: GET /productos/buscar/and?nombre=lap&descripcion=gam
     */
    @GetMapping("/buscar/and")
    public List<Producto> buscarPorDosCamposAnd(
            @RequestParam String nombre,
            @RequestParam String descripcion) {
        logger.info("[INFO] Búsqueda AND (parcial): nombre='{}', descripcion='{}'", nombre, descripcion);
        List<Producto> resultados = productoRepository.findByNombreContainingIgnoreCaseAndDescripcionContainingIgnoreCase(nombre, descripcion);
        logger.info("[INFO] Búsqueda AND completada: {} resultados encontrados.", resultados.size());
        return resultados;
    }

    /**
     * Búsqueda por 2 campos (nombre y descripción) con operador AND (coincidencia exacta).
     * Ejemplo: GET /productos/buscar/and-exacto?nombre=Laptop&descripcion=Gamer
     */
    @GetMapping("/buscar/and-exacto")
    public List<Producto> buscarPorDosCamposAndExacto(
            @RequestParam String nombre,
            @RequestParam String descripcion) {
        logger.info("[INFO] Búsqueda AND (exacta): nombre='{}', descripcion='{}'", nombre, descripcion);
        List<Producto> resultados = productoRepository.findByNombreAndDescripcion(nombre, descripcion);
        logger.info("[INFO] Búsqueda AND exacta completada: {} resultados encontrados.", resultados.size());
        return resultados;
    }

    // =========================================================================
    // 2. BÚSQUEDA POR 3 CAMPOS CON OPERADOR (O / OR)
    // =========================================================================

    /**
     * Búsqueda por 3 campos (nombre, descripción o precio) con operador OR.
     * Ejemplo: GET /productos/buscar/or?nombre=Mouse&descripcion=Optico&precio=50000
     */
    @GetMapping("/buscar/or")
    public List<Producto> buscarPorTresCamposOr(
            @RequestParam(required = false, defaultValue = "") String nombre,
            @RequestParam(required = false, defaultValue = "") String descripcion,
            @RequestParam(required = false) Double precio) {
        logger.info("[INFO] Búsqueda OR: nombre='{}', descripcion='{}', precio={}", nombre, descripcion, precio);
        List<Producto> resultados = productoRepository.findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrPrecio(
                nombre, descripcion, precio);
        logger.info("[INFO] Búsqueda OR completada: {} resultados encontrados.", resultados.size());
        return resultados;
    }

    /**
     * Búsqueda por 3 campos (nombre, descripción o precio) con coincidencia exacta (OR).
     * Ejemplo: GET /productos/buscar/or-exacto?nombre=Mouse&descripcion=Optico&precio=50000
     */
    @GetMapping("/buscar/or-exacto")
    public List<Producto> buscarPorTresCamposOrExacto(
            @RequestParam(required = false) String nombre,
            @RequestParam(required = false) String descripcion,
            @RequestParam(required = false) Double precio) {
        logger.info("[INFO] Búsqueda OR (exacta): nombre='{}', descripcion='{}', precio={}", nombre, descripcion, precio);
        List<Producto> resultados = productoRepository.findByNombreOrDescripcionOrPrecio(nombre, descripcion, precio);
        logger.info("[INFO] Búsqueda OR exacta completada: {} resultados encontrados.", resultados.size());
        return resultados;
    }

    /**
     * Búsqueda por 3 campos alternativos (nombre, descripción o stock) con operador OR.
     * Ejemplo: GET /productos/buscar/or-stock?nombre=Mouse&descripcion=Optico&stock=10
     */
    @GetMapping("/buscar/or-stock")
    public List<Producto> buscarPorTresCamposOrStock(
            @RequestParam(required = false, defaultValue = "") String nombre,
            @RequestParam(required = false, defaultValue = "") String descripcion,
            @RequestParam(required = false) Integer stock) {
        logger.info("[INFO] Búsqueda OR por stock: nombre='{}', descripcion='{}', stock={}", nombre, descripcion, stock);
        List<Producto> resultados = productoRepository.findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrStock(
                nombre, descripcion, stock);
        logger.info("[INFO] Búsqueda OR por stock completada: {} resultados encontrados.", resultados.size());
        return resultados;
    }

    // =========================================================================
    // 3. PAGINACIÓN CON JPAREPOSITORY
    // =========================================================================

    /**
     * Endpoint para listar productos de forma paginada usando JpaRepository.
     * Soporta paginación, tamaño, ordenamiento y filtro opcional de búsqueda.
     * Ejemplo: GET /productos/paginado?page=0&size=5&sortBy=id&direction=asc
     */
    @GetMapping("/paginado")
    public Page<Producto> listarProductosPaginados(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "5") int size,
            @RequestParam(defaultValue = "id") String sortBy,
            @RequestParam(defaultValue = "asc") String direction,
            @RequestParam(required = false) String buscar) {

        Sort sort = direction.equalsIgnoreCase("desc")
                ? Sort.by(sortBy).descending()
                : Sort.by(sortBy).ascending();

        Pageable pageable = PageRequest.of(page, size, sort);
        Page<Producto> paginaResultado;

        if (buscar != null && !buscar.trim().isEmpty()) {
            String termino = buscar.trim();
            logger.info("[INFO] Solicitando página {} con búsqueda '{}' (tamaño: {}, orden: {} {})",
                    page, termino, size, sortBy, direction);
            paginaResultado = productoRepository.findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCase(termino, termino, pageable);
        } else {
            logger.info("[INFO] Solicitando página {} de productos (tamaño: {}, orden: {} {})",
                    page, size, sortBy, direction);
            paginaResultado = productoRepository.findAll(pageable);
        }

        logger.info("[INFO] Página {} obtenida. Elementos en página: {}, Total elementos: {}, Total páginas: {}",
                paginaResultado.getNumber(), paginaResultado.getNumberOfElements(),
                paginaResultado.getTotalElements(), paginaResultado.getTotalPages());

        return paginaResultado;
    }
}

