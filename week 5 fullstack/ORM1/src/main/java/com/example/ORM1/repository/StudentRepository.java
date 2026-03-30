package com.example.ORM1.repository;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;

import com.example.ORM1.entity.student;

public interface StudentRepository extends JpaRepository<student,Integer>{

    List<student> findByDepartment(String department);

    List<student> findByAge(int age);

}