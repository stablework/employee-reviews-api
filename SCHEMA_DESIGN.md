# Employee Reviews API - Database Schema Design

## Overview
This document outlines the complete database schema for the Employee Reviews API. The schema is designed to support a hierarchical organizational structure with role-based access control for managing employee reviews across projects, team members, and managers.

---

## Core Entities

### 1. Users Table
**Purpose**: Stores all user accounts in the system

```
users
├── id (PK): Auto-incrementing integer
├── name: String - User's full name
├── email: String (UNIQUE) - User's email address
├── email_verified_at: Timestamp (nullable) - Email verification status
├── password: String (hashed) - User's password hash
├── api_token: String (80 chars, UNIQUE, nullable) - API authentication token
├── remember_token: String (nullable) - "Remember me" token for web sessions
├── created_at: Timestamp - Record creation timestamp
└── updated_at: Timestamp - Record last update timestamp
```

**Rationale**:
- `api_token` for stateless API authentication
- Email remains unique to prevent duplicate accounts
- Timestamps track audit trail

---

### 2. Roles Table
**Purpose**: Defines available system roles

```
roles
├── id (PK): Auto-incrementing integer
├── name: String (UNIQUE) - Role name (Executive, Manager, Associate, Internal Advisor)
├── created_at: Timestamp
└── updated_at: Timestamp
```

**Available Roles**:
- **Executive**: Full system access, can manage users, teams, projects, and see all reviews with reviewer names
- **Manager**: Can manage their team, view team-related reviews, and leave reviews
- **Associate**: Can view team projects, leave reviews on projects and colleagues
- **Internal Advisor**: Can review projects they're assigned to as advisors

---

### 3. Role-User Relationship Table
**Purpose**: Many-to-many relationship between users and roles

```
role_user
├── id (PK): Auto-incrementing integer
├── user_id (FK → users.id): User reference
├── role_id (FK → roles.id): Role reference
├── created_at: Timestamp
├── updated_at: Timestamp
└── UNIQUE(user_id, role_id): Prevent duplicate role assignments
```

**Rationale**:
- Users can have multiple roles simultaneously
- Composite unique key prevents duplicate role assignments

---

### 4. Teams Table
**Purpose**: Represents organizational teams

```
teams
├── id (PK): Auto-incrementing integer
├── name: String - Team name (e.g., "Development Team")
├── manager_id (FK → users.id): Team manager/leader reference
├── created_at: Timestamp
└── updated_at: Timestamp
```

**Constraints**:
- Each team has exactly one manager
- Manager must exist as a user
- Foreign key has RESTRICT on delete to prevent orphaned teams

**Rationale**:
- Simple hierarchical structure: 1 manager + many associates
- Manager is the team lead responsible for the group

---

### 5. Team-User Relationship Table
**Purpose**: Many-to-many relationship for team membership

```
team_user
├── id (PK): Auto-incrementing integer
├── team_id (FK → teams.id): Team reference
├── user_id (FK → users.id): User reference
├── created_at: Timestamp
├── updated_at: Timestamp
└── UNIQUE(team_id, user_id): Prevent duplicate membership
```

**Rationale**:
- Associates can belong to multiple teams (future expansion)
- Managers are NOT stored here; they're referenced in teams.manager_id
- Composite unique key prevents duplicate memberships

---

### 6. Projects Table
**Purpose**: Represents projects in the organization

```
projects
├── id (PK): Auto-incrementing integer
├── name: String (UNIQUE) - Project name
├── description: Text (nullable) - Project details and scope
├── created_at: Timestamp
└── updated_at: Timestamp
```

**Rationale**:
- Simple project definition
- Multiple teams can work on same project
- Description helps provide context for reviews

---

### 7. Project-Team Relationship Table
**Purpose**: Many-to-many relationship between projects and teams

```
project_team
├── id (PK): Auto-incrementing integer
├── project_id (FK → projects.id): Project reference
├── team_id (FK → teams.id): Team reference
├── created_at: Timestamp
├── updated_at: Timestamp
└── UNIQUE(project_id, team_id): Prevent duplicate assignments
```

**Rationale**:
- Multiple teams can collaborate on a single project
- A team can work on multiple projects simultaneously
- Composite unique key prevents duplicate project-team assignments

---

### 8. Reviews Table
**Purpose**: Stores all reviews in the system

```
reviews
├── id (PK): Auto-incrementing integer
├── reviewer_id (FK → users.id): User submitting the review
├── project_id (FK → projects.id, nullable): Project being reviewed (null if reviewing a person)
├── reviewee_id (FK → users.id, nullable): Person being reviewed (null if reviewing a project)
├── content: Text - Review content/feedback
├── created_at: Timestamp
└── updated_at: Timestamp
```

**Constraints**:
- At least one of `project_id` OR `reviewee_id` must be non-null
- Reviewer cannot be null
- Foreign keys cascade on delete

**Rationale**:
- Supports two types of reviews: project reviews and peer reviews
- Allows tracking who wrote the review, when, and on what
- Timestamps enable sorting and audit trails

---

### 9. Internal Advisors Table
**Purpose**: Tracks when employees take on advisory roles on other teams' projects

```
internal_advisors
├── id (PK): Auto-incrementing integer
├── user_id (FK → users.id): User acting as advisor
├── project_id (FK → projects.id): Project they're advising on
├── team_id (FK → teams.id): Team owning the project
├── created_at: Timestamp
├── updated_at: Timestamp
└── UNIQUE(user_id, project_id): One advisor role per person per project
```

**Rationale**:
- Managers/Associates can temporarily advise on other teams' projects
- Tracks which team they're supporting
- Defines who can review that project
- Composite unique key prevents duplicate advisor assignments

---

## Access Control Rules (Enforced via Application Layer)

### Executive
- ✅ Access all projects
- ✅ See all reviews (with reviewer names)
- ✅ Create/manage users, teams, projects
- ✅ Delete any review
- ❌ Cannot edit reviews (only delete)

### Manager
- ✅ See reviews of their team's projects
- ✅ See reviews of their team members
- ✅ See their own reviews
- ✅ Create reviews (project & peer)
- ✅ Edit/delete their own reviews
- ❌ Cannot see reviewer names (except their own)
- ❌ Cannot delete others' reviews

### Associate
- ✅ See reviews of their team's projects
- ✅ See their own reviews
- ✅ Create reviews (project & peer for same team)
- ✅ Edit/delete their own reviews
- ❌ Cannot see reviewer names (except their own)
- ❌ Cannot delete others' reviews

### Internal Advisor
- ✅ See project reviews they're assigned to
- ✅ Create reviews for assigned projects
- ✅ Edit/delete their own reviews on assigned projects

---

## Relationship Diagram

```
users (1) ────────────────(many) role_user (many)────────────(1) roles
          ├─ manager ──→ teams
          ├─ member  ──→ team_user ──→ teams
          ├─ advisor ──→ internal_advisors ──→ projects
          ├─ reviewer ─→ reviews
          └─ reviewee ─→ reviews

projects (1) ──────────────(many) project_team (many)───────(1) teams
         └─ (many) reviews
         └─ (many) internal_advisors

teams (1) ──────(many) team_user (many)────────(1) users
```

---

## Indexing Strategy

**Primary Keys**:
- All tables have `id` as primary key (default index)

**Foreign Keys**:
- All foreign keys are indexed by Laravel/MySQL automatically

**Recommended Additional Indexes** (for performance):
```sql
-- For review filtering by reviewer
CREATE INDEX idx_reviews_reviewer_id ON reviews(reviewer_id);

-- For review filtering by reviewee
CREATE INDEX idx_reviews_reviewee_id ON reviews(reviewee_id);

-- For review filtering by project
CREATE INDEX idx_reviews_project_id ON reviews(project_id);

-- For team lookup by manager
CREATE INDEX idx_teams_manager_id ON teams(manager_id);

-- For role lookup by name
CREATE INDEX idx_roles_name ON roles(name);

-- For email lookup (auth)
CREATE INDEX idx_users_email ON users(email);

-- For API token lookup
CREATE INDEX idx_users_api_token ON users(api_token);
```

---

## Data Integrity Rules

1. **User Deletion**: Cascade delete from role_user, team_user, reviews (as reviewer)
   - Preserve reviews where user is reviewee
   - Manager users cannot be deleted if they manage teams (restrict)

2. **Role Deletion**: Cascade delete from role_user

3. **Team Deletion**: Cascade delete from team_user, project_team, internal_advisors

4. **Project Deletion**: Cascade delete from project_team, reviews, internal_advisors

5. **Review Deletion**: Direct deletion (no cascades to other entities)

---

## Future Extensibility

This schema supports:
- ✅ Multiple roles per user (e.g., user can be both Manager and Internal Advisor)
- ✅ Cross-team collaboration (projects with multiple teams)
- ✅ Flexible review types (project vs peer)
- ✅ Audit trails (created_at/updated_at timestamps)
- ✅ Anonymous reviewer names (application logic hides names from non-executives)

---

## Migration Strategy

The schema will be implemented via Laravel migrations in sequential order:
1. Create base tables: `users`, `roles`, `role_user`
2. Create organizational tables: `teams`, `team_user`
3. Create project tables: `projects`, `project_team`
4. Create advisory structure: `internal_advisors`
5. Create review system: `reviews`

Each migration will include:
- Full up/down methods for rollback safety
- Foreign key constraints with appropriate on-delete actions
- Unique constraints for preventing duplicates
- Proper indexing for performance
