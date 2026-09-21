# 📚 Documentación Técnica del Proyecto: Gestión de Productos con Spring Boot y MySQL

## 📋 Resumen General
Este proyecto es una API REST empresarial desarrollada con **Spring Boot**, **Java 21**, **Spring Data JPA** y **MySQL**. Originalmente concebido como un prototipo básico de acceso a datos, fue transformado en una solución robusta con arquitectura multicapa, manejo centralizado de excepciones, validaciones de entidad, logging estructurado, búsquedas avanzadas multicampo, paginación en base de datos y una interfaz web moderna e interactiva.

---

## 🛠️ Stack Tecnológico
* **Lenguaje:** Java 21 (OpenJDK).
* **Framework:** Spring Boot 4.1.x / Spring Framework 7.x.
* **Persistencia:** Spring Data JPA (Hibernate 7.x).
* **Base de Datos:** MySQL (Driver `mysql-connector-j` 9.x).
* **Validación:** Hibernate Validator / Jakarta Bean Validation (`spring-boot-starter-validation`).
* **Logging:** SLF4J con implementación Logback.
* **Frontend:** HTML5 semántico, CSS3 moderno (Variables CSS, Glassmorphism, Responsive Design), Vanilla JavaScript Asíncrono (Fetch API).
* **Construcción y Dependencias:** Apache Maven.

---

## 🏛️ Estructura del Proyecto

```text
accessing-data-mysql/
├── pom.xml                                   # Configuración de dependencias y plugins Maven
├── logs/
│   ├── .gitkeep                              # Preserva la carpeta en Git
│   └── app.log                               # Archivo persistente con rotación de logs
└── src/
    └── main/
        ├── java/com/example/accessingdatamysql/
        │   ├── AccessingDataMysqlApplication.java # Clase principal (@SpringBootApplication)
        │   ├── Controller/
        │   │   ├── ProductoController.java        # Endpoints REST CRUD, búsquedas y paginación
        │   │   └── Maincontroller.java            # Endpoints base para la entidad User
        │   ├── Repository/
        │   │   ├── ProductoRepository.java        # Consultas derivadas (AND, OR, Pageable)
        │   │   └── UserRepository.java            # Repositorio CrudRepository para User
        │   ├── entity/
        │   │   ├── Producto.java                  # Entidad mapeada con 5 validaciones Jakarta
        │   │   └── User.java                      # Entidad secundaria de usuarios
        │   └── exception/
        │       ├── GlobalExceptionHandler.java    # Interceptor global @RestControllerAdvice
        │       ├── ResourceNotFoundException.java # Excepción personalizada para errores 404
        │       └── ErrorResponse.java             # DTO de respuesta estándar para errores
        └── resources/
            ├── application.properties             # Configuración de puerto, BD, Hibernate y logs
            └── static/
                ├── index.html                     # Vista web principal interactiva con paginación
                └── Producto.html                  # Réplica accesible directamente
```

---

## 🚀 Módulos y Funcionalidades Implementadas

### 1. Búsquedas Avanzadas con Operadores Lógicos (`AND` y `OR`)
En `ProductoRepository.java` se crearon métodos de consulta derivados (**Spring Data Derived Query Methods**) que traducen las firmas Java a consultas SQL optimizadas:

#### A. Búsqueda por 2 campos con operador `AND` (Y)
* **Parcial e insensible a mayúsculas:**
  `findByNombreContainingIgnoreCaseAndDescripcionContainingIgnoreCase(String nombre, String descripcion)`
  * *Endpoint:* `GET /productos/buscar/and?nombre=...&descripcion=...`
* **Exacta:**
  `findByNombreAndDescripcion(String nombre, String descripcion)`
  * *Endpoint:* `GET /productos/buscar/and-exacto?nombre=...&descripcion=...`

#### B. Búsqueda por 3 campos con operador `OR` (O)
* **Por nombre, descripción o precio:**
  `findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrPrecio(String nombre, String descripcion, Double precio)`
  * *Endpoint:* `GET /productos/buscar/or?nombre=...&descripcion=...&precio=...`
* **Por nombre, descripción o stock:**
  `findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCaseOrStock(String nombre, String descripcion, Integer stock)`
  * *Endpoint:* `GET /productos/buscar/or-stock?nombre=...&descripcion=...&stock=...`
* **Exacta:**
  `findByNombreOrDescripcionOrPrecio(String nombre, String descripcion, Double precio)`
  * *Endpoint:* `GET /productos/buscar/or-exacto?nombre=...&descripcion=...&precio=...`

---

### 2. Controlador Global de Excepciones (`@RestControllerAdvice`)
Se centralizó la captura de errores en `GlobalExceptionHandler.java` para evitar que la aplicación exponga trazas internas (*stack traces*) al cliente:

* **DTO de Error (`ErrorResponse.java`):**
  Estandariza los mensajes con los campos:
  ```json
  {
    "timestamp": "2026-09-14T16:15:30",
    "status": 404,
    "error": "Not Found",
    "message": "Producto no encontrado con el ID: 99",
    "path": "/productos/99"
  }
  ```

* **Excepciones Manejadas:**
  1. `ResourceNotFoundException` (**404 Not Found**): Cuando un ID solicitado no existe.
  2. `MethodArgumentNotValidException` (**400 Bad Request**): Cuando fallan las validaciones de los campos (`@Valid`).
  3. `MethodArgumentTypeMismatchException` (**400 Bad Request**): Cuando se ingresa texto en un parámetro numérico.
  4. `MissingServletRequestParameterException` (**400 Bad Request**): Cuando faltan parámetros obligatorios en la URL.
  5. `IllegalArgumentException` (**400 Bad Request**): Violación de reglas de negocio.
  6. `Exception.class` (**500 Internal Server Error**): Fallback general para cualquier error imprevisto del servidor.

---

### 3. Sistema de Logging Estructurado (`INFO`, `WARN`, `ERROR`)
Se integró **SLF4J** en todos los controladores y manejadores:

* **`INFO`:** Registra las operaciones cotidianas (inicio de peticiones, registros creados, búsquedas ejecutadas, productos listados y cantidad de registros devueltos).
* **`WARN`:** Alerta sobre anomalías no fatales (intentos de consultar o eliminar productos inexistentes, parámetros faltantes o tipos de datos incompatibles).
* **`ERROR`:** Registra caídas del sistema o excepciones críticas no controladas con su correspondiente traza.

#### Configuración en `application.properties`:
```properties
logging.level.root=INFO
logging.level.com.example.accessingdatamysql=INFO
logging.pattern.console=%d{yyyy-MM-dd HH:mm:ss.SSS} [%thread] %-5level %logger{36} - %msg%n
```

---

### 4. Persistencia y Rotación de Logs
Se creó la carpeta física `logs/` con configuración de rotación en disco:
* **Archivo de salida:** `logs/app.log`.
* **Rotación por tamaño:** `10 MB` por archivo (`max-file-size=10MB`).
* **Historial conservado:** `30 días` (`max-history=30`).
* **Git:** Se incluyó regla en `.gitignore` para ignorar `logs/*.log` preservando la carpeta mediante `!logs/.gitkeep`.

---

### 5. Validaciones con Anotaciones en la Entidad (`Producto.java`)
Se añadió la librería `spring-boot-starter-validation` y se aplicaron **5 validaciones estrictas**:

1. **`@NotBlank`** (en `nombre`): No permite valores nulos, vacíos ni compuestos solo por espacios.
2. **`@Size(min = 3, max = 100)`** (en `nombre`): Obliga a que el nombre tenga entre 3 y 100 caracteres.
3. **`@Size(max = 255)`** (en `descripcion`): Limita el texto a 255 caracteres para proteger la columna en base de datos.
4. **`@NotNull` + `@Positive`** (en `precio`): Campo obligatorio y estrictamente mayor que cero.
5. **`@NotNull` + `@Min(0)`** (en `stock`): Campo obligatorio que prohíbe inventarios negativos (permite 0 o más).

*En `ProductoController.java`, los métodos `crearProducto` y `actualizarProducto` cuentan con `@Valid` para validar automáticamente el cuerpo de la petición.*

---

### 6. Paginación con `JpaRepository` y Vista Web

#### A. Backend (Spring Data)
* En `ProductoRepository.java` se habilitó el método paginado con filtro:
  `Page<Producto> findByNombreContainingIgnoreCaseOrDescripcionContainingIgnoreCase(String n, String d, Pageable pageable)`
* En `ProductoController.java` se expuso:
  `GET /productos/paginado?page=0&size=5&sortBy=id&direction=asc&buscar=...`
  Devuelve un objeto `Page<Producto>` con metadatos de paginación (`totalPages`, `totalElements`, `number`, `size`, `first`, `last`).

#### B. Frontend Web (`index.html` y `Producto.html`)
Se implementó una interfaz visual moderna que ofrece:
* **Selector de tamaño de página:** 5, 10 o 20 productos por vista.
* **Buscador asíncrono en tiempo real:** Búsqueda paginada con debounce.
* **Selector de ordenamiento dinámico:** Ordenar por ID, Nombre, Precio o Stock (Ascendente / Descendente).
* **Barra de navegación de páginas:** Botones `« Primera`, `‹ Anterior`, botones numéricos dinámicos `[1] [2] [3]...`, `Siguiente ›` y `Última »`.
* **Badges de Stock:** Indicadores visuales de estado (Verde = Disponible, Amarillo = Bajo stock, Rojo = Agotado).
* **Feedback Visual:** Notificaciones flotantes tipo *Toast* para confirmar operaciones o alertar sobre errores.

---

### 7. Configuración de Base de Datos y Entorno
En `application.properties` se ajustaron los parámetros comprobados en el entorno local:
```properties
server.port=8086

spring.datasource.url=jdbc:mysql://localhost:3307/mysqljpa
spring.datasource.username=root
spring.datasource.password=1234
spring.datasource.driver-class-name=com.mysql.cj.jdbc.Driver

spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true
```

---

## 📖 Guía de Ejecución y Pruebas

1. **Asegurarse de que MySQL esté activo:**
   * Puerto: `3307`
   * Base de datos: `mysqljpa`
   * Usuario: `root` | Contraseña: `1234`

2. **Ejecutar la aplicación:**
   * En IntelliJ IDEA: Ejecutar la clase `AccessingDataMysqlApplication`.
   * En terminal: `./mvnw spring-boot:run`

3. **Abrir la aplicación en el navegador:**
   * URL Principal: `http://localhost:8086/`
   * URL de Productos: `http://localhost:8086/Producto.html`

4. **Endpoints de Búsqueda y Paginación (REST):**
   * Paginado: `GET http://localhost:8086/productos/paginado?page=0&size=5`
   * Búsqueda AND: `GET http://localhost:8086/productos/buscar/and?nombre=pollo&descripcion=especial`
   * Búsqueda OR: `GET http://localhost:8086/productos/buscar/or?nombre=chuzo&descripcion=carne&precio=15000`
