package com.example.accessingdatamysql.entity;

import jakarta.persistence.*;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import jakarta.validation.constraints.Size;

@Entity
@Table(name = "productos")
public class Producto {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    // Validación 1: @NotBlank (no vacío ni solo espacios)
    // Validación 2: @Size (longitud mínima y máxima)
    @NotBlank(message = "El nombre del producto es obligatorio y no puede estar vacío")
    @Size(min = 3, max = 100, message = "El nombre debe tener entre 3 y 100 caracteres")
    private String nombre;

    // Validación 3: @Size (límite máximo de caracteres en descripción)
    @Size(max = 255, message = "La descripción no puede exceder los 255 caracteres")
    private String descripcion;

    // Validación 4: @Positive (el valor numérico debe ser mayor a cero)
    @NotNull(message = "El precio es obligatorio")
    @Positive(message = "El precio debe ser un número positivo mayor que 0")
    private Double precio;

    // Validación 5: @Min (el stock no puede ser inferior a 0)
    @NotNull(message = "El stock es obligatorio")
    @Min(value = 0, message = "El stock debe ser igual o mayor a 0 (no negativo)")
    private Integer stock;


    public Producto() {
    }

    public Producto(String nombre, String descripcion, Double precio, Integer stock) {
        this.nombre = nombre;
        this.descripcion = descripcion;
        this.precio = precio;
        this.stock = stock;
    }

    public Long getId() {
        return id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public String getDescripcion() {
        return descripcion;
    }

    public void setDescripcion(String descripcion) {
        this.descripcion = descripcion;
    }

    public Double getPrecio() {
        return precio;
    }

    public void setPrecio(Double precio) {
        this.precio = precio;
    }

    public Integer getStock() {
        return stock;
    }

    public void setStock(Integer stock) {
        this.stock = stock;
    }
}