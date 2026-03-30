package com.example.ORM1.controller;

import java.util.List;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import com.example.ORM1.entity.student;
import com.example.ORM1.repository.StudentRepository;
import org.springframework.data.domain.Sort;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
@RestController
@RequestMapping("/student")
public class StudentController {

    @Autowired
    private StudentRepository repo;

    // ADD STUDENT
    @PostMapping("/add")
    public student addStudent(@RequestBody student s) {
        return repo.save(s);
    }

    // GET ALL STUDENTS
    @GetMapping("/all")
    public List<student> getStudents() {
        return repo.findAll();
    }

    // UPDATE STUDENT
    @PutMapping("/update/{id}")
    public student updateStudent(@PathVariable int id, @RequestBody student s) {
        s.setId(id);   // important line
        return repo.save(s);
    }

    // DELETE STUDENT
    @DeleteMapping("/delete/{id}")
    public String deleteStudent(@PathVariable int id) {
        repo.deleteById(id);
        return "Student Deleted Successfully";
    }
 // GET STUDENTS BY DEPARTMENT
    @GetMapping("/department/{dept}")
    public List<student> getByDepartment(@PathVariable String dept) {
        return repo.findByDepartment(dept);
    }

    // GET STUDENTS BY AGE
    @GetMapping("/age/{age}")
    public List<student> getByAge(@PathVariable int age) {
        return repo.findByAge(age);
    }
    @GetMapping("/sort/{field}")
    public List<student> sortStudents(@PathVariable String field) {
        return repo.findAll(Sort.by(Sort.Direction.ASC, field));
    }
    @GetMapping("/page/{pageNo}/{pageSize}")
    public Page<student> getStudentsPage(@PathVariable int pageNo, @PathVariable int pageSize) {
        return repo.findAll(PageRequest.of(pageNo, pageSize));
    }
 
}