# Working Everywhere API

## Overview

## Prerequisites

## Quick Start

## Details

### Database Schema

```mermaid
erDiagram
    USER {
        int id PK
        string name
        string email
        string password_hash
    }

    PROJECT {
        int id PK
        string name
        string description
        date due_date
        int created_by FK
    }

    TASK {
        int id PK
        string title
        string description
        date due_date
        int priority
        int project_id FK
        int status_id FK
    }

    TASK_STATUS {
        int id PK
        string name
    }

    TASK_ASSIGNEE {
        int id PK
        int task_id FK
        int user_id FK
    }

    COMMENT {
        int id PK
        string content
        date created_at
        int user_id FK
        int task_id FK
    }

    TAG {
        int id PK
        string name
    }

    PROJECT_TAG {
        int project_id FK
        int tag_id FK
    }

    TASK_PROGRESS {
        int id PK
        int task_id FK
        date progress_date
        string progress_description
        int percent_completed
    }

    USER ||--o{ TASK_ASSIGNEE : "can be assigned to"
    USER ||--o{ COMMENT : "can comment on"
    PROJECT ||--o{ TASK : "contains"
    TASK_STATUS ||--o{ TASK : "defines"
    TASK ||--o{ TASK_ASSIGNEE : "can have"
    PROJECT ||--o{ PROJECT_TAG : "can have"
    TAG ||--o{ PROJECT_TAG : "can belong to"
    TASK ||--o{ COMMENT : "can have"
    TASK ||--o{ TASK_PROGRESS : "can have"
```
