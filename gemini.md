SISTEM SPPD KPU KABUPATEN CIREBON
api key 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

## 🎯 PROJECT CONTEXT
You are working on a PRODUCTION-READY enterprise government system for KPU Kabupaten Cirebon (Regional Election Commission). This is a critical system that handles official business travel (SPPD) with the highest security and compliance requirements.

## 🏆 SYSTEM STATUS AWARENESS
- Laravel 12 Framework (Latest Enterprise Version)
- PostgreSQL Database (Enterprise Grade)
- OWASP Top 10 Compliance (100% Verified)
- Government Security Standards (APPROVED)
- Production Ready Status (CERTIFIED)

## 🔒 SECURITY FIRST PRINCIPLES
CRITICAL: This is a government system. Security is PARAMOUNT.

### Security Rules (NON-NEGOTIABLE):
- NEVER hardcode sensitive data (API keys, passwords, tokens)
- Always validate and sanitize ALL user inputs
- Use Laravel's built-in security features (CSRF, XSS protection, etc.)
- Implement proper authentication and authorization
- Follow OWASP Top 10 security guidelines
- Always use prepared statements for database queries
- Validate file uploads with strict type checking
- Implement rate limiting on all routes
- Use HTTPS everywhere
- Log all security-relevant events

### Authentication & Authorization:
- Use Laravel Sanctum for API authentication
- Implement role-based access control (RBAC)
- Never bypass middleware security checks
- Always verify user permissions before actions
- Implement session management properly
- Use secure password hashing (bcrypt/argon2)

## 🏗️ ARCHITECTURE & CODE STANDARDS

### Laravel Best Practices:
- Follow PSR-4 autoloading standards
- Use Laravel's service container for dependency injection
- Implement Repository Pattern for data access
- Use Form Request classes for validation
- Create dedicated Service classes for business logic
- Use Laravel's built-in Queue system for heavy operations
- Implement proper error handling with try-catch blocks
- Use Laravel's built-in caching mechanisms

### Database Standards (PostgreSQL):
- Use Laravel migrations for all schema changes
- Always use Eloquent ORM relationships
- Implement proper indexing for performance
- Use database transactions for critical operations
- Follow proper naming conventions (snake_case)
- Create seeders for initial data
- Use soft deletes where appropriate

### Code Quality Standards:
- Write self-documenting code with clear variable names
- Add comprehensive PHPDoc comments
- Follow SOLID principles
- Implement proper error handling
- Use type hints for all function parameters and return types
- Write unit tests for all business logic
- Use Laravel's built-in validation rules
- Implement proper logging throughout the application

## 📊 BUSINESS LOGIC UNDERSTANDING

### Core Business Flow:
1. User Authentication & Role Management
2. SPPD Creation & Management
3. Approval Workflow System
4. Document Upload & Management
5. Report Generation & Export
6. Audit Trail & Logging

### Key Business Rules:
- Only authorized users can create SPPD
- Multi-level approval system required
- All actions must be auditable
- Document integrity must be maintained
- User protection system (prevent admin deletion)
- Recovery system for critical failures

## 🎨 FRONTEND STANDARDS

### UI/UX Guidelines:
- Use responsive design (mobile-first approach)
- Implement professional government-grade UI
- Use consistent color scheme and typography
- Ensure accessibility compliance (WCAG 2.1)
- Implement proper form validation feedback
- Use loading states and progress indicators
- Create intuitive navigation structure

### Technical Implementation:
- Use Laravel Blade templates with components
- Implement Alpine.js for interactive elements
- Use Tailwind CSS for styling (if applicable)
- Ensure cross-browser compatibility
- Optimize images and assets
- Implement proper error pages (404, 500, etc.)

## 🧪 TESTING REQUIREMENTS

### Testing Standards:
- Write unit tests for all service classes
- Create feature tests for all API endpoints
- Implement integration tests for critical workflows
- Use Laravel's built-in testing tools
- Maintain minimum 80% code coverage
- Write browser tests for critical user journeys
- Test all security measures thoroughly

### Test Categories:
- Unit Tests (Models, Services, Helpers)
- Feature Tests (Controllers, API endpoints)
- Integration Tests (Database, External services)
- Security Tests (Authentication, Authorization)
- Browser Tests (User workflows)

## 📋 CODING CONVENTIONS

### File Organization:
- Controllers: Handle HTTP requests only
- Services: Contains business logic
- Models: Eloquent models with relationships
- Repositories: Data access layer
- Requests: Form validation classes
- Resources: API response formatting
- Middleware: Request filtering and processing

### Naming Conventions:
- Classes: PascalCase (UserController, SppdService)
- Methods: camelCase (getUserData, createSppd)
- Variables: camelCase ($userData, $sppdList)
- Constants: UPPER_SNAKE_CASE (MAX_UPLOAD_SIZE)
- Database: snake_case (user_profiles, sppd_documents)

## 🚀 PERFORMANCE OPTIMIZATION

### Performance Rules:
- Use eager loading to prevent N+1 queries
- Implement proper caching strategies
- Optimize database queries with proper indexing
- Use queue jobs for time-consuming operations
- Implement pagination for large datasets
- Optimize images and static assets
- Use CDN for static content delivery

## 📝 DOCUMENTATION STANDARDS

### Code Documentation:
- Write comprehensive PHPDoc comments
- Document all public methods and classes
- Include parameter types and return types
- Add usage examples for complex functions
- Document any business rule implementations
- Keep README files updated
- Document API endpoints with proper examples

## 🔍 ERROR HANDLING

### Error Management:
- Use try-catch blocks for all risky operations
- Implement proper exception handling
- Log all errors with appropriate context
- Return meaningful error messages to users
- Never expose sensitive information in errors
- Use Laravel's exception handling mechanism
- Implement graceful degradation

## 🌐 API DEVELOPMENT

### API Standards:
- Follow RESTful conventions
- Use proper HTTP status codes
- Implement consistent response format
- Add API versioning from the start
- Use resource classes for response formatting
- Implement proper error responses
- Add comprehensive API documentation

## 🔧 MAINTENANCE & MONITORING

### System Maintenance:
- Implement comprehensive logging
- Set up proper monitoring and alerting
- Create automated backup procedures
- Plan for regular security updates
- Monitor system performance metrics
- Implement health check endpoints

## 💬 COMMUNICATION STYLE

### When Providing Solutions:
- Always explain the security implications
- Provide enterprise-grade solutions
- Consider government compliance requirements
- Suggest best practices and alternatives
- Include proper error handling in all examples
- Provide complete, production-ready code
- Explain the business impact of technical decisions

### Code Review Focus:
- Security vulnerabilities
- Performance implications
- Code maintainability
- Compliance with standards
- Test coverage
- Documentation quality

## 🚨 CRITICAL REMINDERS

1. **SECURITY FIRST**: Every line of code must be secure
2. **GOVERNMENT STANDARDS**: Follow all compliance requirements
3. **PRODUCTION READY**: Code must be enterprise-grade
4. **USER PROTECTION**: Prevent data loss and system failures
5. **AUDIT TRAIL**: Log all critical operations
6. **PERFORMANCE**: Optimize for government-scale usage
7. **DOCUMENTATION**: Maintain comprehensive documentation
8. **TESTING**: Ensure 100% critical path coverage

## 🎯 RESPONSE GUIDELINES

When helping with this project:
- Always consider security implications first
- Provide complete, production-ready solutions
- Include proper error handling and validation
- Suggest enterprise-grade implementations
- Consider scalability and performance
- Ensure compliance with government standards
- Provide comprehensive explanations
- Include relevant testing approaches

---

**This system serves the Indonesian government and must meet the highest standards of security, reliability, and professionalism.**

**🏛️ "Melayani Dengan Integritas - Serving With Integrity"**
