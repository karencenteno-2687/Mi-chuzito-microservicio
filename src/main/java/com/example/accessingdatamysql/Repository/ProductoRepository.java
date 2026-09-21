package com.example.accessingdatamysql.Repository;

import com.example.accessingdatamysql.entity.Producto;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface ProductoRepository extends JpaRepository<Producto, Long> {

    // =========================================================================
    // 1. Búsqueda por 2 campos con operador AND (Y)
    // =========================================================================

    // Coincidencia parcial e insensible a mayúsculas/minúsculas (recomendado para buscadores)
    List<Producto> findByNombreContainingIgnoreCaseAndDescripcionContainingIgnoreCase(String nombre, String descripcion);

    // Coincidencia exacta
    List<Producto> findByNombreAndDescripcion(String nombre, String descripcion);

    // =========================================================================
    // 2. Búsqueda por 3 campos con operador OR (O)
    // =========================================================================

    // Búsqueda por 3 campos: nombre, descripcion o precio
    List<Producto> findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrPrecio(String nombre, String descripcion, Double precio);

    // Búsqueda exacta por 3 campos: nombre, descripcion o precio
    List<Producto> findByNombreOrDescripcionOrPrecio(String nombre, String descripcion, Double precio);

    // Búsqueda por 3 campos alternativos: nombre, descripcion o stock
    List<Producto> findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrStock(String nombre, String descripcion, Integer stock);

    // =========================================================================
    // 3. Paginación con JpaRepository
    // =========================================================================
    // Nota: JpaRepository ya provee de forma nativa: Page<Producto> findAll(Pageable pageable);

    // Búsqueda con filtro paginado por término en nombre o descripción
    Page<Producto> findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCase(String nombre, String descripcion, Pageable pageable);
}


