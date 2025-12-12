---
description: Repository Information Overview
alwaysApply: true
---

# Employee Reviews API - Laravel Assessment

## Summary

This is a Laravel assessment project for building a REST API service that manages employee reviews across an organizational hierarchy. The API handles reviews of projects, team members, and managers with role-based access control (Executives, Managers, Associates, and Internal Advisors). The project is built using Laravel and MySQL.

## Structure

The repository is currently in the initial planning phase with only documentation. Once implemented, the structure will follow Laravel conventions:
- `app/` - Application code (Models, Controllers, Services)
- `database/` - Migrations and seeders for MySQL
- `routes/` - API routes and endpoints
- `tests/` - Test files and test configuration
- `config/` - Application configuration
- `resources/` - Views and other resources

## Language & Runtime

**Language**: PHP (Laravel framework)
**Framework**: Laravel
**Database**: MySQL
**Architecture**: REST API

## Key Requirements

The API must support:
- Organization with multiple teams (manager + associates)
- Projects managed by multiple teams
- Role-based access: Executives, Managers, Associates, Internal Advisors
- Review management (create, read, update, delete)
- Access control based on user roles and team membership
- Anonymous reviewer names (except to Executives)

## Implementation Approach

- Use Laravel as the PHP framework
- MySQL as the database
- Database migrations for schema management
- Seeders for initial data population
- RESTful API endpoints returning JSON responses
- Role-based authorization and access control
- Comprehensive test coverage

## Expected Development Areas

1. **Database Design**: Teams, Users, Projects, Reviews, Roles, Permissions
2. **Authentication & Authorization**: Role-based access control
3. **API Endpoints**: CRUD operations for reviews with proper filtering
4. **Business Logic**: Complex access rules based on roles and relationships
5. **Testing**: Unit and feature tests for all endpoints
6. **Documentation**: API documentation and setup instructions

## Assessment Criteria

- Communication and problem-solving approach
- Database design
- Code readability and best practices
- Security implementation
- Testing coverage
- Adherence to KISS principles
