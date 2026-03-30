package com.example.productapi.controller;

import java.util.List;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import com.example.productapi.entity.product;
import com.example.productapi.repository.ProductRepository;
import org.springframework.http.ResponseEntity;

@RestController
@RequestMapping("/product")
public class ProductController {

    @Autowired
    ProductRepository repo;

    // ADD PRODUCT
    @PostMapping("/add")
    public product addProduct(@RequestBody product p) {
        return repo.save(p);
    }

    // GET ALL PRODUCTS
    @GetMapping("/all")
    public List<product> getAllProducts() {
        return repo.findAll();
    }

    // GET PRODUCT BY ID
    @GetMapping("/{id}")
    public product getProduct(@PathVariable int id) {
        return repo.findById(id).orElse(null);
    }

    // UPDATE PRODUCT
    @PutMapping("/update/{id}")
    public product updateProduct(@PathVariable int id, @RequestBody product p) {
        p.setId(id);
        return repo.save(p);
    }

    // DELETE PRODUCT
    @DeleteMapping("/delete/{id}")
    public String deleteProduct(@PathVariable int id) {
        repo.deleteById(id);
        return "Product Deleted";
    }
    @GetMapping("/search")
    public ResponseEntity<String> searchProduct(@RequestParam String name) {
        return ResponseEntity.ok("Searching product: " + name);
    }
    @GetMapping("/details/{id}")
    public ResponseEntity<product> getProductDetails(@PathVariable int id) {
        product p = repo.findById(id).orElse(null);

        if (p == null) {
            return ResponseEntity.status(404).body(null);
        }

        return ResponseEntity.ok(p);
    }
    @PostMapping("/create")
    public ResponseEntity<product> createProduct(@RequestBody product p) {
        product saved = repo.save(p);
        return ResponseEntity.status(201).body(saved);
    }
}