--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

-- Started on 2025-07-11 00:28:32

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 871 (class 1247 OID 38711)
-- Name: travel_requests_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.travel_requests_status AS ENUM (
    'draft',
    'submitted',
    'in_review',
    'approved',
    'rejected',
    'completed'
);


ALTER TYPE public.travel_requests_status OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 226 (class 1259 OID 39009)
-- Name: approvals; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.approvals (
    id bigint NOT NULL,
    travel_request_id bigint NOT NULL,
    approver_id bigint NOT NULL,
    level integer NOT NULL,
    role character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    comments text,
    revision_notes json,
    approved_at timestamp(0) without time zone,
    rejected_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT approvals_role_check CHECK (((role)::text = ANY ((ARRAY['kasubbag'::character varying, 'sekretaris'::character varying, 'ketua'::character varying])::text[]))),
    CONSTRAINT approvals_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying, 'revision_minor'::character varying, 'revision_major'::character varying])::text[])))
);


ALTER TABLE public.approvals OWNER TO sppd_user;

--
-- TOC entry 225 (class 1259 OID 39008)
-- Name: approvals_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.approvals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.approvals_id_seq OWNER TO sppd_user;

--
-- TOC entry 5103 (class 0 OID 0)
-- Dependencies: 225
-- Name: approvals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.approvals_id_seq OWNED BY public.approvals.id;


--
-- TOC entry 240 (class 1259 OID 39165)
-- Name: cache; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO sppd_user;

--
-- TOC entry 242 (class 1259 OID 39173)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.cache_locks (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cache_locks OWNER TO sppd_user;

--
-- TOC entry 241 (class 1259 OID 39172)
-- Name: cache_locks_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.cache_locks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cache_locks_id_seq OWNER TO sppd_user;

--
-- TOC entry 5104 (class 0 OID 0)
-- Dependencies: 241
-- Name: cache_locks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.cache_locks_id_seq OWNED BY public.cache_locks.id;


--
-- TOC entry 228 (class 1259 OID 39035)
-- Name: documents; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.documents (
    id bigint NOT NULL,
    travel_request_id bigint NOT NULL,
    uploaded_by bigint NOT NULL,
    filename character varying(255) NOT NULL,
    original_filename character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    file_type character varying(255) NOT NULL,
    file_size integer NOT NULL,
    mime_type character varying(255) NOT NULL,
    document_type character varying(255) NOT NULL,
    description text,
    is_required boolean DEFAULT false NOT NULL,
    is_verified boolean DEFAULT false NOT NULL,
    verified_at timestamp(0) without time zone,
    verified_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT documents_document_type_check CHECK (((document_type)::text = ANY ((ARRAY['supporting'::character varying, 'proof'::character varying, 'receipt'::character varying, 'photo'::character varying, 'report'::character varying, 'generated_pdf'::character varying])::text[])))
);


ALTER TABLE public.documents OWNER TO sppd_user;

--
-- TOC entry 227 (class 1259 OID 39034)
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documents_id_seq OWNER TO sppd_user;

--
-- TOC entry 5105 (class 0 OID 0)
-- Dependencies: 227
-- Name: documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.documents_id_seq OWNED BY public.documents.id;


--
-- TOC entry 218 (class 1259 OID 38942)
-- Name: migrations; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO sppd_user;

--
-- TOC entry 217 (class 1259 OID 38941)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO sppd_user;

--
-- TOC entry 5106 (class 0 OID 0)
-- Dependencies: 217
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 237 (class 1259 OID 39125)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO sppd_user;

--
-- TOC entry 238 (class 1259 OID 39136)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO sppd_user;

--
-- TOC entry 230 (class 1259 OID 39064)
-- Name: notifications; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    travel_request_id bigint,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    type character varying(255) NOT NULL,
    data json,
    action_url character varying(255),
    action_text character varying(255),
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp(0) without time zone,
    is_important boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifications_type_check CHECK (((type)::text = ANY ((ARRAY['info'::character varying, 'success'::character varying, 'warning'::character varying, 'error'::character varying, 'approval_request'::character varying, 'status_update'::character varying, 'reminder'::character varying])::text[])))
);


ALTER TABLE public.notifications OWNER TO sppd_user;

--
-- TOC entry 229 (class 1259 OID 39063)
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notifications_id_seq OWNER TO sppd_user;

--
-- TOC entry 5107 (class 0 OID 0)
-- Dependencies: 229
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- TOC entry 221 (class 1259 OID 38964)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO sppd_user;

--
-- TOC entry 234 (class 1259 OID 39104)
-- Name: permissions; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO sppd_user;

--
-- TOC entry 233 (class 1259 OID 39103)
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO sppd_user;

--
-- TOC entry 5108 (class 0 OID 0)
-- Dependencies: 233
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 239 (class 1259 OID 39147)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO sppd_user;

--
-- TOC entry 236 (class 1259 OID 39115)
-- Name: roles; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO sppd_user;

--
-- TOC entry 235 (class 1259 OID 39114)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO sppd_user;

--
-- TOC entry 5109 (class 0 OID 0)
-- Dependencies: 235
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 222 (class 1259 OID 38971)
-- Name: sessions; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO sppd_user;

--
-- TOC entry 232 (class 1259 OID 39089)
-- Name: settings; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    type character varying(255) DEFAULT 'string'::character varying NOT NULL,
    "group" character varying(255) DEFAULT 'general'::character varying NOT NULL,
    label character varying(255) NOT NULL,
    description text,
    is_editable boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO sppd_user;

--
-- TOC entry 231 (class 1259 OID 39088)
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO sppd_user;

--
-- TOC entry 5110 (class 0 OID 0)
-- Dependencies: 231
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- TOC entry 224 (class 1259 OID 38981)
-- Name: travel_requests; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.travel_requests (
    id bigint NOT NULL,
    kode_sppd character varying(50) NOT NULL,
    user_id bigint NOT NULL,
    tujuan character varying(255) NOT NULL,
    keperluan text NOT NULL,
    tanggal_berangkat date NOT NULL,
    tanggal_kembali date NOT NULL,
    lama_perjalanan integer NOT NULL,
    transportasi character varying(255) NOT NULL,
    tempat_menginap character varying(255),
    biaya_transport numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    biaya_penginapan numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    uang_harian numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    biaya_lainnya numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    total_biaya numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    sumber_dana character varying(255),
    status character varying(32) DEFAULT 'pending'::character varying NOT NULL,
    current_approval_level integer DEFAULT 0 NOT NULL,
    approval_history json,
    catatan_pemohon text,
    catatan_approval text,
    is_urgent boolean DEFAULT false NOT NULL,
    nomor_surat_tugas character varying(255),
    tanggal_surat_tugas date,
    submitted_at timestamp(0) without time zone,
    approved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT travel_requests_status_check CHECK (((status)::text = ANY (ARRAY[('draft'::character varying)::text, ('pending'::character varying)::text, ('in_review'::character varying)::text, ('submitted'::character varying)::text, ('approved_kasubbag'::character varying)::text, ('approved_sekretaris'::character varying)::text, ('approved_ketua'::character varying)::text, ('rejected'::character varying)::text, ('revision_minor'::character varying)::text, ('revision_major'::character varying)::text, ('completed'::character varying)::text])))
);


ALTER TABLE public.travel_requests OWNER TO sppd_user;

--
-- TOC entry 223 (class 1259 OID 38980)
-- Name: travel_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.travel_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.travel_requests_id_seq OWNER TO sppd_user;

--
-- TOC entry 5111 (class 0 OID 0)
-- Dependencies: 223
-- Name: travel_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.travel_requests_id_seq OWNED BY public.travel_requests.id;


--
-- TOC entry 220 (class 1259 OID 38949)
-- Name: users; Type: TABLE; Schema: public; Owner: sppd_user
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    nip character varying(50),
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    jabatan character varying(255),
    role character varying(255) DEFAULT 'staff'::character varying NOT NULL,
    phone character varying(20),
    address text,
    pangkat character varying(255),
    golongan character varying(10),
    unit_kerja character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp(0) without time zone,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    "position" character varying(255),
    department character varying(255),
    profile_photo_path character varying(2048),
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    avatar character varying(255),
    bio text,
    employee_id character varying(255),
    birth_date date,
    gender character varying(255),
    CONSTRAINT users_gender_check CHECK (((gender)::text = ANY ((ARRAY['male'::character varying, 'female'::character varying])::text[]))),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['staff'::character varying, 'kasubbag'::character varying, 'sekretaris'::character varying, 'ketua'::character varying, 'komisioner'::character varying, 'admin'::character varying])::text[]))),
    CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO sppd_user;

--
-- TOC entry 219 (class 1259 OID 38948)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: sppd_user
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO sppd_user;

--
-- TOC entry 5112 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sppd_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4830 (class 2604 OID 39012)
-- Name: approvals id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.approvals ALTER COLUMN id SET DEFAULT nextval('public.approvals_id_seq'::regclass);


--
-- TOC entry 4844 (class 2604 OID 39176)
-- Name: cache_locks id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.cache_locks ALTER COLUMN id SET DEFAULT nextval('public.cache_locks_id_seq'::regclass);


--
-- TOC entry 4832 (class 2604 OID 39038)
-- Name: documents id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.documents ALTER COLUMN id SET DEFAULT nextval('public.documents_id_seq'::regclass);


--
-- TOC entry 4816 (class 2604 OID 38945)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4835 (class 2604 OID 39067)
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- TOC entry 4842 (class 2604 OID 39107)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 4843 (class 2604 OID 39118)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 4838 (class 2604 OID 39092)
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- TOC entry 4821 (class 2604 OID 38984)
-- Name: travel_requests id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.travel_requests ALTER COLUMN id SET DEFAULT nextval('public.travel_requests_id_seq'::regclass);


--
-- TOC entry 4817 (class 2604 OID 38952)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5080 (class 0 OID 39009)
-- Dependencies: 226
-- Data for Name: approvals; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.approvals (id, travel_request_id, approver_id, level, role, status, comments, revision_notes, approved_at, rejected_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5094 (class 0 OID 39165)
-- Dependencies: 240
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- TOC entry 5096 (class 0 OID 39173)
-- Dependencies: 242
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.cache_locks (id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5082 (class 0 OID 39035)
-- Dependencies: 228
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.documents (id, travel_request_id, uploaded_by, filename, original_filename, file_path, file_type, file_size, mime_type, document_type, description, is_required, is_verified, verified_at, verified_by, created_at, updated_at) FROM stdin;
1	21	2	doc_686ff43c3145e.jpg	WhatsApp Image 2025-07-10 at 20.07.11_748f7300.jpg	public/documents/doc_686ff43c3145e.jpg	jpg	70581	image/jpeg	supporting	Dokumen pendukung SPPD	f	f	\N	\N	2025-07-11 00:11:25	2025-07-11 00:11:25
\.


--
-- TOC entry 5072 (class 0 OID 38942)
-- Dependencies: 218
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	2025_06_24_151931_create_travel_requests_table	1
3	2025_06_24_151932_create_approvals_table	1
4	2025_06_24_151932_create_documents_table	1
5	2025_06_24_151932_create_notifications_table	1
6	2025_06_24_151935_create_settings_table	1
7	2025_06_24_152539_add_fields_to_users_table	1
8	2025_06_25_164854_create_permission_tables	1
9	2025_06_28_040144_add_missing_fields_to_travel_requests_table	1
10	2025_07_01_215118_add_profile_fields_to_users_table	1
11	2025_07_01_220153_check_and_add_missing_profile_fields_to_users_table	1
12	2025_07_02_022654_create_cache_table	1
13	2025_07_02_034346_create_cache_locks_table	1
14	2025_07_02_041800_add_in_review_status_to_travel_requests	1
15	2025_07_03_220103_fix_travel_requests_status_postgresql	1
16	2025_07_04_ensure_status_string_on_travel_requests	1
17	2025_07_05_190717_update_users_role_constraint_add_admin	1
18	2025_07_08_095015_update_pegawai_to_staff_role	1
\.


--
-- TOC entry 5091 (class 0 OID 39125)
-- Dependencies: 237
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- TOC entry 5092 (class 0 OID 39136)
-- Dependencies: 238
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
\.


--
-- TOC entry 5084 (class 0 OID 39064)
-- Dependencies: 230
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.notifications (id, user_id, travel_request_id, title, message, type, data, action_url, action_text, is_read, read_at, is_important, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5075 (class 0 OID 38964)
-- Dependencies: 221
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5088 (class 0 OID 39104)
-- Dependencies: 234
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5093 (class 0 OID 39147)
-- Dependencies: 239
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- TOC entry 5090 (class 0 OID 39115)
-- Dependencies: 236
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5076 (class 0 OID 38971)
-- Dependencies: 222
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
NXlXnivQRwB6cSuDjE7EINrWcgciZs5hjD3GVWcO	2	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTo1OntzOjY6Il90b2tlbiI7czo0MDoicGxmb1dxNlZsSzd4MUFxSHdQZms0dUQxSUVxUzhRYXNqTWpZY2tzNCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=	1752165666
l0FSE1r0KLNPY0sdSnCUxBGkbTzJTCDt3Wh4RrWL	2	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS2hYUTJocUcxNjJIcGtiTDAwOVNJWWFCV3U2T0ZxMW1KbWs2ZkJmYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcHByb3ZhbC9waW1waW5hbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==	1752168509
pbWHYjrCJ6oQnrikqGuzb72dIwxjJnR0W099xLCa	5	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoid0xmR25DY1ozRzBNSFh3OHlCQ2RyR3htUEN1bzFaR0o0VGQyOVRPcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmF2ZWwtcmVxdWVzdHMvY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTt9	1752166940
dQ1Gtd94ylpi1oLSf6wE8DGKSjTc2lOODSIqmycu	2	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoid1ZtMDZHbUNVWURLNEtkYUJ1dUJZQ2E2N1Y4RDM0SHo0bkRVZU5UcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmF2ZWwtcmVxdWVzdHMvY3JlYXRlIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9	1752166187
\.


--
-- TOC entry 5086 (class 0 OID 39089)
-- Dependencies: 232
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.settings (id, key, value, type, "group", label, description, is_editable, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5078 (class 0 OID 38981)
-- Dependencies: 224
-- Data for Name: travel_requests; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.travel_requests (id, kode_sppd, user_id, tujuan, keperluan, tanggal_berangkat, tanggal_kembali, lama_perjalanan, transportasi, tempat_menginap, biaya_transport, biaya_penginapan, uang_harian, biaya_lainnya, total_biaya, sumber_dana, status, current_approval_level, approval_history, catatan_pemohon, catatan_approval, is_urgent, nomor_surat_tugas, tanggal_surat_tugas, submitted_at, approved_at, created_at, updated_at) FROM stdin;
1	SPPD/2025/001	3	Tujuan 1	Keperluan dinas 1	2025-07-10	2025-07-11	2	Mobil Dinas	Hotel 1	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	in_review	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
2	SPPD/2025/002	4	Tujuan 2	Keperluan dinas 2	2025-07-11	2025-07-12	2	Mobil Dinas	Hotel 2	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	submitted	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
3	SPPD/2025/003	2	Tujuan 3	Keperluan dinas 3	2025-07-12	2025-07-15	4	Mobil Dinas	Hotel 3	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
4	SPPD/2025/004	1	Tujuan 4	Keperluan dinas 4	2025-07-13	2025-07-15	3	Mobil Dinas	Hotel 4	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
5	SPPD/2025/005	3	Tujuan 5	Keperluan dinas 5	2025-07-14	2025-07-16	3	Mobil Dinas	Hotel 5	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	rejected	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
6	SPPD/2025/006	4	Tujuan 6	Keperluan dinas 6	2025-07-15	2025-07-18	4	Mobil Dinas	Hotel 6	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	rejected	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
7	SPPD/2025/007	2	Tujuan 7	Keperluan dinas 7	2025-07-10	2025-07-13	4	Mobil Dinas	Hotel 7	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
8	SPPD/2025/008	1	Tujuan 8	Keperluan dinas 8	2025-07-11	2025-07-12	2	Mobil Dinas	Hotel 8	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	rejected	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
9	SPPD/2025/009	3	Tujuan 9	Keperluan dinas 9	2025-07-12	2025-07-14	3	Mobil Dinas	Hotel 9	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	in_review	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
10	SPPD/2025/010	4	Tujuan 10	Keperluan dinas 10	2025-07-13	2025-07-15	3	Mobil Dinas	Hotel 10	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	in_review	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
11	SPPD/2025/011	2	Tujuan 11	Keperluan dinas 11	2025-07-14	2025-07-17	4	Mobil Dinas	Hotel 11	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
12	SPPD/2025/012	1	Tujuan 12	Keperluan dinas 12	2025-07-15	2025-07-17	3	Mobil Dinas	Hotel 12	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	submitted	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
13	SPPD/2025/013	3	Tujuan 13	Keperluan dinas 13	2025-07-10	2025-07-13	4	Mobil Dinas	Hotel 13	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
14	SPPD/2025/014	4	Tujuan 14	Keperluan dinas 14	2025-07-11	2025-07-14	4	Mobil Dinas	Hotel 14	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
15	SPPD/2025/015	2	Tujuan 15	Keperluan dinas 15	2025-07-12	2025-07-13	2	Mobil Dinas	Hotel 15	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
16	SPPD/2025/016	1	Tujuan 16	Keperluan dinas 16	2025-07-13	2025-07-15	3	Mobil Dinas	Hotel 16	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	rejected	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
17	SPPD/2025/017	3	Tujuan 17	Keperluan dinas 17	2025-07-14	2025-07-15	2	Mobil Dinas	Hotel 17	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
18	SPPD/2025/018	4	Tujuan 18	Keperluan dinas 18	2025-07-15	2025-07-16	2	Mobil Dinas	Hotel 18	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	rejected	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
19	SPPD/2025/019	2	Tujuan 19	Keperluan dinas 19	2025-07-10	2025-07-12	3	Mobil Dinas	Hotel 19	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	submitted	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
20	SPPD/2025/020	1	Tujuan 20	Keperluan dinas 20	2025-07-11	2025-07-12	2	Mobil Dinas	Hotel 20	5000000.00	7000000.00	6000000.00	2000000.00	20000000.00	APBD	completed	0	\N	\N	\N	f	\N	\N	\N	\N	2025-07-10 23:40:42	2025-07-10 23:40:42
21	SPPD/2025/021	2	KPU SUKABUMI	RAPAT	2025-07-12	2025-07-20	9	Kereta Api	\N	800000.00	500000.00	250000.00	50000.00	1600000.00	APBD	in_review	1	\N	PLISS	\N	t	\N	\N	2025-07-11 00:11:24	\N	2025-07-11 00:11:24	2025-07-11 00:11:25
\.


--
-- TOC entry 5074 (class 0 OID 38949)
-- Dependencies: 220
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: sppd_user
--

COPY public.users (id, nip, name, email, email_verified_at, password, jabatan, role, phone, address, pangkat, golongan, unit_kerja, is_active, last_login_at, remember_token, created_at, updated_at, "position", department, profile_photo_path, status, avatar, bio, employee_id, birth_date, gender) FROM stdin;
1	STAFFKPU001	Staff KPU	staff@kpu-kab-cirebon.go.id	\N	$2y$12$3sieoKGPucyr0EfI0fIzle5VDiMZW9nIOEkkuAXkg4V3MoVkKPowS	Staff	staff	\N	\N	\N	\N	\N	t	\N	\N	2025-07-10 23:39:08	2025-07-10 23:39:08	\N	\N	\N	active	\N	\N	\N	\N	\N
4	KETUAKPU001	Ketua KPU	ketua@kpu-kab-cirebon.go.id	\N	$2y$12$WwdSetu3DkbHd6BHNSb3.OblRwPgOvvIsIxC14qUZmwgl9aoObwVq	Ketua	ketua	\N	\N	\N	\N	\N	t	\N	\N	2025-07-10 23:39:09	2025-07-10 23:39:09	\N	\N	\N	active	\N	\N	\N	\N	\N
5	ADMINKPU001	Admin KPU	admin@kpu-kab-cirebon.go.id	\N	$2y$12$4mS7gsvAz9krRgmyfNrASO8kEk9q/v6w8B86N2KDAsjODNNnTV7vu	Admin	admin	\N	\N	\N	\N	\N	t	2025-07-10 23:50:26	\N	2025-07-10 23:39:09	2025-07-10 23:50:26	\N	\N	\N	active	\N	\N	\N	\N	\N
3	SEKKPU001	Sekretaris KPU	sekretaris@kpu-kab-cirebon.go.id	\N	$2y$12$jyGOFdp/slAevVMrBX7L7OGV3.khYIXytoinNgemPcxgU50UKrAl.	Sekretaris	sekretaris	\N	\N	\N	\N	\N	t	2025-07-11 00:12:04	\N	2025-07-10 23:39:08	2025-07-11 00:12:04	\N	\N	\N	active	\N	\N	\N	\N	\N
2	KASUBBAGKPU001	Kasubbag KPU	kasubbag@kpu-kab-cirebon.go.id	\N	$2y$12$QBufxE5fVp0.bCnj41OuiOkxij6xLe9Ngg77p3rdHhXSk6Gj.wjqC	Kasubbag	kasubbag	\N	\N	\N	\N	\N	t	2025-07-11 00:18:07	\N	2025-07-10 23:39:08	2025-07-11 00:18:07	\N	\N	\N	active	\N	\N	\N	\N	\N
\.


--
-- TOC entry 5113 (class 0 OID 0)
-- Dependencies: 225
-- Name: approvals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.approvals_id_seq', 1, false);


--
-- TOC entry 5114 (class 0 OID 0)
-- Dependencies: 241
-- Name: cache_locks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.cache_locks_id_seq', 1, false);


--
-- TOC entry 5115 (class 0 OID 0)
-- Dependencies: 227
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.documents_id_seq', 1, true);


--
-- TOC entry 5116 (class 0 OID 0)
-- Dependencies: 217
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 18, true);


--
-- TOC entry 5117 (class 0 OID 0)
-- Dependencies: 229
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.notifications_id_seq', 1, true);


--
-- TOC entry 5118 (class 0 OID 0)
-- Dependencies: 233
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- TOC entry 5119 (class 0 OID 0)
-- Dependencies: 235
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.roles_id_seq', 1, false);


--
-- TOC entry 5120 (class 0 OID 0)
-- Dependencies: 231
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.settings_id_seq', 1, false);


--
-- TOC entry 5121 (class 0 OID 0)
-- Dependencies: 223
-- Name: travel_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.travel_requests_id_seq', 21, true);


--
-- TOC entry 5122 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sppd_user
--

SELECT pg_catalog.setval('public.users_id_seq', 5, true);


--
-- TOC entry 4876 (class 2606 OID 39019)
-- Name: approvals approvals_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.approvals
    ADD CONSTRAINT approvals_pkey PRIMARY KEY (id);


--
-- TOC entry 4879 (class 2606 OID 39033)
-- Name: approvals approvals_travel_request_id_level_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.approvals
    ADD CONSTRAINT approvals_travel_request_id_level_unique UNIQUE (travel_request_id, level);


--
-- TOC entry 4913 (class 2606 OID 39178)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (id);


--
-- TOC entry 4911 (class 2606 OID 39171)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 4881 (class 2606 OID 39045)
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- TOC entry 4854 (class 2606 OID 38947)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 4904 (class 2606 OID 39135)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 4907 (class 2606 OID 39146)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 4885 (class 2606 OID 39074)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 4862 (class 2606 OID 38970)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 4895 (class 2606 OID 39113)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 4897 (class 2606 OID 39111)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 4909 (class 2606 OID 39161)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 4899 (class 2606 OID 39124)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 4901 (class 2606 OID 39122)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 4865 (class 2606 OID 38977)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 4891 (class 2606 OID 39102)
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- TOC entry 4893 (class 2606 OID 39099)
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- TOC entry 4868 (class 2606 OID 39007)
-- Name: travel_requests travel_requests_kode_sppd_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.travel_requests
    ADD CONSTRAINT travel_requests_kode_sppd_unique UNIQUE (kode_sppd);


--
-- TOC entry 4870 (class 2606 OID 38997)
-- Name: travel_requests travel_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.travel_requests
    ADD CONSTRAINT travel_requests_pkey PRIMARY KEY (id);


--
-- TOC entry 4856 (class 2606 OID 38963)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 4858 (class 2606 OID 38961)
-- Name: users users_nip_unique; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_nip_unique UNIQUE (nip);


--
-- TOC entry 4860 (class 2606 OID 38959)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4874 (class 1259 OID 39031)
-- Name: approvals_approver_id_status_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX approvals_approver_id_status_index ON public.approvals USING btree (approver_id, status);


--
-- TOC entry 4877 (class 1259 OID 39030)
-- Name: approvals_travel_request_id_level_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX approvals_travel_request_id_level_index ON public.approvals USING btree (travel_request_id, level);


--
-- TOC entry 4882 (class 1259 OID 39061)
-- Name: documents_travel_request_id_document_type_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX documents_travel_request_id_document_type_index ON public.documents USING btree (travel_request_id, document_type);


--
-- TOC entry 4883 (class 1259 OID 39062)
-- Name: documents_uploaded_by_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX documents_uploaded_by_index ON public.documents USING btree (uploaded_by);


--
-- TOC entry 4902 (class 1259 OID 39128)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 4905 (class 1259 OID 39139)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 4886 (class 1259 OID 39087)
-- Name: notifications_travel_request_id_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX notifications_travel_request_id_index ON public.notifications USING btree (travel_request_id);


--
-- TOC entry 4887 (class 1259 OID 39085)
-- Name: notifications_user_id_is_read_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX notifications_user_id_is_read_index ON public.notifications USING btree (user_id, is_read);


--
-- TOC entry 4888 (class 1259 OID 39086)
-- Name: notifications_user_id_type_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX notifications_user_id_type_index ON public.notifications USING btree (user_id, type);


--
-- TOC entry 4863 (class 1259 OID 38979)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 4866 (class 1259 OID 38978)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 4889 (class 1259 OID 39100)
-- Name: settings_group_key_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX settings_group_key_index ON public.settings USING btree ("group", key);


--
-- TOC entry 4871 (class 1259 OID 39186)
-- Name: travel_requests_status_current_approval_level_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX travel_requests_status_current_approval_level_index ON public.travel_requests USING btree (status, current_approval_level);


--
-- TOC entry 4872 (class 1259 OID 39005)
-- Name: travel_requests_tanggal_berangkat_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX travel_requests_tanggal_berangkat_index ON public.travel_requests USING btree (tanggal_berangkat);


--
-- TOC entry 4873 (class 1259 OID 39185)
-- Name: travel_requests_user_id_status_index; Type: INDEX; Schema: public; Owner: sppd_user
--

CREATE INDEX travel_requests_user_id_status_index ON public.travel_requests USING btree (user_id, status);


--
-- TOC entry 4915 (class 2606 OID 39025)
-- Name: approvals approvals_approver_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.approvals
    ADD CONSTRAINT approvals_approver_id_foreign FOREIGN KEY (approver_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4916 (class 2606 OID 39020)
-- Name: approvals approvals_travel_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.approvals
    ADD CONSTRAINT approvals_travel_request_id_foreign FOREIGN KEY (travel_request_id) REFERENCES public.travel_requests(id) ON DELETE CASCADE;


--
-- TOC entry 4917 (class 2606 OID 39046)
-- Name: documents documents_travel_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_travel_request_id_foreign FOREIGN KEY (travel_request_id) REFERENCES public.travel_requests(id) ON DELETE CASCADE;


--
-- TOC entry 4918 (class 2606 OID 39051)
-- Name: documents documents_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4919 (class 2606 OID 39056)
-- Name: documents documents_verified_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES public.users(id);


--
-- TOC entry 4922 (class 2606 OID 39129)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 4923 (class 2606 OID 39140)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 4920 (class 2606 OID 39080)
-- Name: notifications notifications_travel_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_travel_request_id_foreign FOREIGN KEY (travel_request_id) REFERENCES public.travel_requests(id) ON DELETE CASCADE;


--
-- TOC entry 4921 (class 2606 OID 39075)
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4924 (class 2606 OID 39150)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 4925 (class 2606 OID 39155)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 4914 (class 2606 OID 38998)
-- Name: travel_requests travel_requests_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sppd_user
--

ALTER TABLE ONLY public.travel_requests
    ADD CONSTRAINT travel_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5102 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO sppd_user;


--
-- TOC entry 2117 (class 826 OID 16391)
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO sppd_user;


--
-- TOC entry 2116 (class 826 OID 16390)
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES TO sppd_user;


-- Completed on 2025-07-11 00:28:32

--
-- PostgreSQL database dump complete
--

