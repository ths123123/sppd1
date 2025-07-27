-- PostgreSQL User Password Reset Script
-- Run this in pgAdmin or psql as postgres user

-- Connect to postgres database first
\c postgres;

-- Check if user exists
SELECT rolname FROM pg_roles WHERE rolname = 'sppd_user';

-- Reset password for sppd_user
ALTER USER sppd_user WITH PASSWORD 'sppd_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE sppd_kpu_postgres TO sppd_user;

-- Connect to target database
\c sppd_kpu_postgres;

-- Grant schema privileges
GRANT ALL ON SCHEMA public TO sppd_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO sppd_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO sppd_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO sppd_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO sppd_user;

-- Test the user
SELECT current_user, current_database();

\q
