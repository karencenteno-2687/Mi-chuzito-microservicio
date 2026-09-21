package com.example.accessingdatamysql.Controller;

import com.example.accessingdatamysql.Repository.UserRepository;
import com.example.accessingdatamysql.entity.User;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@RequestMapping(path="/demo")
public class Maincontroller {

    private static final Logger logger = LoggerFactory.getLogger(Maincontroller.class);

    @Autowired
    private UserRepository userRepository;

    @PostMapping(path="/add")
    public @ResponseBody String addNewUser(
            @RequestParam String name,
            @RequestParam String email) {

        logger.info("[INFO] Registrando nuevo usuario: name='{}', email='{}'", name, email);

        User n = new User();
        n.setName(name);
        n.setEmail(email);

        userRepository.save(n);
        logger.info("[INFO] Usuario guardado exitosamente.");

        return "Saved";
    }

    @GetMapping(path="/all")
    public @ResponseBody Iterable<User> getAllUsers() {
        logger.info("[INFO] Consultando todos los usuarios registrados.");
        return userRepository.findAll();
    }

    @GetMapping(path="/buscar")
    public @ResponseBody Iterable<User> buscarUser(
            @RequestParam String name,
            @RequestParam String email) {
        logger.info("[INFO] Buscando usuarios por name='{}' y email='{}'", name, email);
        return userRepository.findByNameContainingIgnoreCaseAndEmailContainingIgnoreCase(name, email);
    }
}
