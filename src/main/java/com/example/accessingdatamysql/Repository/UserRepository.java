package com.example.accessingdatamysql.Repository;

import com.example.accessingdatamysql.entity.User;
import org.springframework.data.repository.CrudRepository;



// This will be AUTO IMPLEMENTED by Spring into a Bean called userRepository
// CRUD refers Create, Read, Update, Delete

public interface UserRepository extends CrudRepository<User, Integer> {

    // Búsqueda por 2 campos con operador AND (Y)
    Iterable<User> findByNameAndEmail(String name, String email);

    // Búsqueda parcial ignorando mayúsculas/minúsculas
    Iterable<User> findByNameContainingIgnoreCaseAndEmailContainingIgnoreCase(String name, String email);
}

