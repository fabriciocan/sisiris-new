# Implementation Plan

-   [x] 1. Setup database structure and migrations

    -   Create migration for tipo_usuarios table with user type definitions
    -   Create migration to enhance users table with tipo_usuario_id and nivel_acesso fields
    -   Create migration to enhance membros table with specific fields for each user type
    -   Create migration for cargo_conselhos table for council positions
    -   Create migration to enhance protocolos table with new workflow fields
    -   Create migration for protocolo_membros pivot table
    -   _Requirements: 1.1, 1.2, 1.3, 2.1, 3.1_

-   [x] 2. Implement core user type system

    -   [x] 2.1 Create TipoUsuario model with validation rules

        -   Define model with fillable fields and relationships

        -   Add validation for required fields per user type
        -   Create factory for testing different user types
        -   _Requirements: 1.1, 1.2, 1.3_

    -   [x] 2.2 Enhance User model with type management

        -   Add tipo_usuario_id and nivel_acesso fields
        -   Create relationship with TipoUsuario model
        -   Add helper methods for checking user type and access level
        -   _Requirements: 1.1, 2.1, 2.2_

    -   [x] 2.3 Enhance Membro model with type-specific fields

        -   Add fields for Tio Maçom (loja_maconica, grau_maconico, dates)
        -   Add fields for Tia Estrela do Oriente (capitulo_estrela, data_iniciacao_arco_iris)
        -   Create validation rules for each user type
        -   Add scopes for filtering by user type
        -   _Requirements: 1.4, 1.5, 1.6, 1.7, 1.8_

-   [x] 3. Implement access control and permissions system

    -   [x] 3.1 Create role and permission structure

        -   Define roles in database seeder (admin_assembleia, membro_jurisdicao, etc.)
        -   Create permissions for protocol operations
        -   Set up role hierarchy and inheritance
        -   _Requirements: 2.1, 2.2, 2.3_

    -   [x] 3.2 Create policy classes for authorization

        -   Implement ProtocoloPolicy with create, approve, manage methods
        -   Implement MembroPolicy for member management
        -   Implement CargoPolicy for position assignment
        -   _Requirements: 2.1, 2.2, 2.4, 2.5_

    -   [x] 3.3 Create middleware for access control

        -   Create middleware to check assembleia access
        -   Create middleware to validate jurisdiction permissions
        -   Add route protection for sensitive operations
        -   _Requirements: 2.1, 2.2, 2.3_

-   [x] 4. Implement position management system

    -   [x] 4.1 Create CargoConselho model

        -   Define model with relationships to Membro and Assembleia
        -   Add validation for position uniqueness
        -   Create methods for automatic permission assignment
        -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

    -   [x] 4.2 Enhance CargoAssembleia model

        -   Add protocolo_id reference for assignment tracking
        -   Create validation for assembleia position rules
        -   Add methods for position history management
        -   _Requirements: 3.1, 3.4, 3.5_

    -   [x] 4.3 Create position assignment services

        -   Implement service for assembleia position assignment
        -   Implement service for council position assignment
        -   Add automatic permission updates when positions change
        -   _Requirements: 3.4, 3.5, 2.4, 2.5_

-   [x] 5. Implement protocol workflow engine

    -   [x] 5.1 Create base protocol workflow system

        -   Create ProtocoloWorkflow class with step management
        -   Define workflow configurations for each protocol type
        -   Implement state transition validation
        -   _Requirements: 4.1, 5.1, 6.1, 7.1, 8.1, 9.1, 10.1, 11.1_

    -   [x] 5.2 Enhance Protocolo model with workflow support

        -   Add tipo_protocolo, etapa_atual, and workflow fields
        -   Create relationships with ProtocoloMembro pivot
        -   Add methods for workflow state management
        -   _Requirements: 4.1, 5.1, 6.1, 7.1, 8.1, 9.1, 10.1, 11.1_

    -   [x] 5.3 Create protocol action classes

        -   Implement AdminAssembleiaAction for protocol creation
        -   Implement MembroJurisdicaoAction for approvals
        -   Implement PresidenteHonrariasAction for honors approval
        -   _Requirements: 4.2, 5.2, 6.2, 7.2, 8.2, 9.2, 10.2, 11.2_

-   [x] 6. Implement Maioridade ceremony protocol

    -   [x] 6.1 Create Maioridade protocol form and validation

        -   Create form for selecting meninas ativas
        -   Add validation for member eligibility
        -   Implement member selection interface
        -   _Requirements: 4.1, 4.2, 4.3_

    -   [x] 6.2 Implement Maioridade workflow logic

        -   Create workflow steps: creation → approval
        -   Add ceremony date requirement for approval
        -   Implement automatic database updates on completion
        -   _Requirements: 4.4, 4.5, 4.6_

-   [x] 7. Implement Iniciação protocol


    -   [x] 7.1 Create Iniciação protocol form

        -   Create form for new member registration
        -   Add validation for all required personal data
        -   Implement madrinha selection with member validation
        -   _Requirements: 5.1, 5.2_

    -   [x] 7.2 Implement Iniciação workflow and automation

        -   Create workflow: creation → approval → member creation
        -   Implement automatic user profile creation
        -   Add email sending for first access credentials
        -   _Requirements: 5.3, 5.4, 5.5, 5.6_

-   [ ] 8. Implement honors protocols (Homenageados do Ano)

    -   [ ] 8.1 Create honors protocol base structure

        -   Create form for member selection
        -   Implement multi-step workflow (admin → president → jurisdiction → payment → final approval)
        -   Add tax management and payment tracking
        -   _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

    -   [ ] 8.2 Add file upload and payment verification
        -   Implement file upload for payment receipts
        -   Add validation for supported file types (images, PDFs)
        -   Create payment verification workflow step
        -   _Requirements: 6.4, 6.5, 6.6_

-   [ ] 9. Implement Coração das Cores protocol

    -   [ ] 9.1 Create Coração das Cores specific logic
        -   Filter members who don't have this honor already
        -   Implement uniqueness validation for this honor type
        -   Follow same workflow as general honors protocol
        -   _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

-   [ ] 10. Implement Grande Cruz das Cores protocol

    -   [ ] 10.1 Create Grande Cruz protocol with ceremony attendance
        -   Filter eligible members (without this honor)
        -   Add ceremony attendance selection in final approval
        -   Implement different status based on ceremony attendance
        -   _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

-   [ ] 11. Implement Afastamento protocol

    -   [ ] 11.1 Create member removal protocol
        -   Create form for selecting active members
        -   Add date selection for removal
        -   Implement simple approval workflow
        -   Update member status automatically on approval
        -   _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

-   [ ] 12. Implement position assignment protocols

    -   [ ] 12.1 Create Novos Cargos Assembleia protocol

        -   Create form with all assembleia positions
        -   Add member selection for each position (meninas ativas only)
        -   Implement position uniqueness validation
        -   Update all positions simultaneously on approval
        -   _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

    -   [ ] 12.2 Create Novos Cargos Conselho protocol
        -   Create form with council positions
        -   Filter eligible members by user type for each position
        -   Add special validation for Presidente do Conselho (Tio Maçom Mestre only)
        -   Update positions and permissions automatically
        -   _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

-   [ ] 13. Implement logging and audit system

    -   [ ] 13.1 Create protocol logging system

        -   Enhance ProtocoloHistorico model for comprehensive logging
        -   Create observers to automatically log all protocol changes
        -   Add user, action, and timestamp tracking
        -   _Requirements: 12.1, 12.2, 12.3_

    -   [ ] 13.2 Create audit trail interface
        -   Add logs display to protocol detail pages
        -   Create formatted log entries with user-friendly messages
        -   Implement automatic log updates on state changes
        -   _Requirements: 12.4, 12.5_

-   [ ] 14. Create Filament admin interface

    -   [ ] 14.1 Create protocol management resources

        -   Create ProtocoloResource with dynamic forms based on type
        -   Implement member selection components
        -   Add file upload widgets for payment receipts
        -   _Requirements: 13.1, 13.2, 13.4_

    -   [ ] 14.2 Create user and member management interfaces

        -   Enhance user forms with type-specific fields
        -   Create member management with position assignment
        -   Add bulk operations for common tasks
        -   _Requirements: 13.1, 13.2, 13.3_

    -   [ ] 14.3 Create workflow management interface
        -   Add protocol status tracking and workflow visualization
        -   Create approval interfaces for different user roles
        -   Implement automatic status updates and notifications
        -   _Requirements: 13.5, 13.6_

-   [ ] 15. Create data seeders and initial setup

    -   [ ] 15.1 Create user type and role seeders

        -   Seed tipo_usuarios table with all user types
        -   Create default roles and permissions
        -   Set up initial admin users for testing
        -   _Requirements: 1.1, 2.1, 3.1_

    -   [ ] 15.2 Create protocol type configuration seeder
        -   Define all protocol types with their workflows
        -   Set up default assembleia and council positions
        -   Create sample data for development and testing
        -   _Requirements: 4.1, 10.1, 11.1_

-   [ ]\* 16. Create comprehensive test suite

    -   [ ]\* 16.1 Create unit tests for models and services

        -   Test user type validation and business rules
        -   Test protocol workflow state transitions
        -   Test member eligibility validation
        -   _Requirements: All requirements_

    -   [ ]\* 16.2 Create integration tests for workflows

        -   Test complete protocol flows from creation to completion
        -   Test permission and access control
        -   Test file upload and payment processing
        -   _Requirements: All requirements_

    -   [ ]\* 16.3 Create feature tests for Filament interface
        -   Test admin interfaces for protocol creation
        -   Test approval workflows for different user roles
        -   Test member management and position assignment
        -   _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_
