package com.example.productapi.repository;

import org.springframework.data.jpa.repository.JpaRepository;

import com.example.productapi.entity.product;

public interface ProductRepository extends JpaRepository<product,Integer>{

}