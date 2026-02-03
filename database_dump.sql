--
-- PostgreSQL database dump
--

\restrict esy9nMlQNRxcngZLBaf4dnIwzTEdzfaxgNVGq0TJTRZCAPtfmrYbKp1Ze0nlV2G

-- Dumped from database version 18.1 (Debian 18.1-1.pgdg13+2)
-- Dumped by pg_dump version 18.1 (Debian 18.1-1.pgdg13+2)

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
-- Name: archive_old_transactions(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.archive_old_transactions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE transactions
    SET is_archived = TRUE, archive_date = CURRENT_TIMESTAMP
    WHERE date < CURRENT_DATE - INTERVAL '6 months'
      AND is_archived = FALSE;
    
    RETURN NEW;
END;
$$;


--
-- Name: calculate_user_balance_in_group(integer, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.calculate_user_balance_in_group(p_user_id integer, p_group_id integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_member_count INTEGER;
    v_total_spent NUMERIC;
    v_split_amount NUMERIC;
    v_user_spent NUMERIC;
    v_balance NUMERIC;
BEGIN
    -- Liczba cz┼éonk├│w grupy
    SELECT COUNT(*) INTO v_member_count
    FROM group_members
    WHERE group_id = p_group_id AND user_id != p_user_id;
    
    -- ┼ü─ůczna suma wydatk├│w w grupie
    SELECT COALESCE(SUM(amount), 0) INTO v_total_spent
    FROM transactions
    WHERE group_id = p_group_id AND is_archived = FALSE;
    
    -- ┼Ürednia na osob─Ö (dla ka┼╝dego cz┼éonka)
    v_split_amount := v_total_spent / NULLIF(v_member_count + 1, 0);
    
    -- Ile wyda┼é dany u┼╝ytkownik
    SELECT COALESCE(SUM(amount), 0) INTO v_user_spent
    FROM transactions
    WHERE group_id = p_group_id AND user_id = p_user_id AND is_archived = FALSE;
    
    -- Saldo: ile wyda┼é - ile powinien wyda─ç (dodatnie = wp┼éaci┼é wi─Öcej)
    v_balance := v_user_spent - v_split_amount;
    
    RETURN v_balance;
END;
$$;


--
-- Name: validate_category_exists(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.validate_category_exists() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM categories WHERE id = NEW.category_id) THEN
        RAISE EXCEPTION 'Kategoria o ID % nie istnieje', NEW.category_id;
    END IF;
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categories (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: group_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.group_members (
    id integer NOT NULL,
    group_id integer NOT NULL,
    user_id integer NOT NULL,
    role character varying(20) NOT NULL,
    joined_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT group_members_role_check CHECK (((role)::text = ANY (ARRAY[('owner'::character varying)::text, ('editor'::character varying)::text])))
);


--
-- Name: groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.groups (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    owner_id integer NOT NULL,
    description text,
    budget numeric(12,2) DEFAULT 0.00,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_active boolean DEFAULT true
);


--
-- Name: transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.transactions (
    id integer NOT NULL,
    group_id integer NOT NULL,
    user_id integer NOT NULL,
    category_id integer NOT NULL,
    name character varying(255) NOT NULL,
    amount numeric(10,2) NOT NULL,
    date date NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_archived boolean DEFAULT false,
    archive_date timestamp without time zone,
    CONSTRAINT transactions_amount_check CHECK ((amount > (0)::numeric))
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email character varying(100) NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_active boolean DEFAULT true
);


--
-- Name: group_member_summary; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.group_member_summary AS
 SELECT gm.group_id,
    g.name AS group_name,
    u.id AS user_id,
    u.username,
    count(t.id) AS transaction_count,
    sum(
        CASE
            WHEN (t.user_id = u.id) THEN t.amount
            ELSE (0)::numeric
        END) AS total_spent,
    avg(
        CASE
            WHEN (t.user_id IS NOT NULL) THEN t.amount
            ELSE (0)::numeric
        END) AS avg_transaction,
    gm.role
   FROM (((public.group_members gm
     JOIN public.groups g ON ((gm.group_id = g.id)))
     JOIN public.users u ON ((gm.user_id = u.id)))
     LEFT JOIN public.transactions t ON (((g.id = t.group_id) AND (t.is_archived = false))))
  GROUP BY gm.group_id, g.name, u.id, u.username, gm.role;


--
-- Name: group_members_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.group_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: group_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.group_members_id_seq OWNED BY public.group_members.id;


--
-- Name: groups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.groups_id_seq OWNED BY public.groups.id;


--
-- Name: recent_group_transactions; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.recent_group_transactions AS
 SELECT t.id,
    t.name,
    t.amount,
    t.date,
    u.username,
    c.name AS category,
    g.id AS group_id,
    g.name AS group_name
   FROM (((public.transactions t
     JOIN public.users u ON ((t.user_id = u.id)))
     JOIN public.categories c ON ((t.category_id = c.id)))
     JOIN public.groups g ON ((t.group_id = g.id)))
  WHERE (t.is_archived = false)
  ORDER BY t.date DESC;


--
-- Name: transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.transactions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.transactions_id_seq OWNED BY public.transactions.id;


--
-- Name: user_preferences; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_preferences (
    user_id integer NOT NULL,
    default_group_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: group_members id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members ALTER COLUMN id SET DEFAULT nextval('public.group_members_id_seq'::regclass);


--
-- Name: groups id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.groups ALTER COLUMN id SET DEFAULT nextval('public.groups_id_seq'::regclass);


--
-- Name: transactions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions ALTER COLUMN id SET DEFAULT nextval('public.transactions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categories (id, name, description, created_at) FROM stdin;
2	Transport	Paliwo, komunikacja, przejazdy	2026-02-02 19:05:26.525958
1	Groceries	Zakupy spo┼╝ywcze i artyku┼éy	2026-02-02 19:05:26.525958
3	Food	Restauracje, kawiarnie, bary	2026-02-02 19:05:26.525958
4	Electronics	Sprz─Öt elektroniczny i akcesoria	2026-02-02 19:05:26.525958
5	Clothing	Ubrania, obuwie, akcesoria	2026-02-02 19:05:26.525958
6	Entertainment	Kino, teatr, gry, ksi─ů┼╝ki	2026-02-02 19:05:26.525958
7	Health	Leki, wizyty lekarskie, fitness	2026-02-02 19:05:26.525958
9	Other	Inne	2026-02-03 11:50:59.94678
\.


--
-- Data for Name: group_members; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.group_members (id, group_id, user_id, role, joined_at) FROM stdin;
1	1	1	owner	2026-02-02 19:05:26.53766
2	2	2	owner	2026-02-02 21:58:32.503719
3	2	4	editor	2026-02-02 23:06:32.98829
4	2	3	editor	2026-02-02 23:06:55.724654
5	1	4	editor	2026-02-03 14:29:57.762133
\.


--
-- Data for Name: groups; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.groups (id, name, owner_id, description, budget, created_at, updated_at, is_active) FROM stdin;
2	Family Budget	2		2000.00	2026-02-02 21:58:32.503719	2026-02-02 21:58:32.503719	t
1	Budżet Domowy	1	Wsp├│lny bud┼╝et rodzinny	5000.00	2026-02-02 19:05:26.533963	2026-02-02 19:05:26.533963	t
\.


--
-- Data for Name: transactions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.transactions (id, group_id, user_id, category_id, name, amount, date, created_at, is_archived, archive_date) FROM stdin;
1	1	1	1	Zakupy Biedronka	185.30	2026-02-02	2026-02-02 19:05:26.541572	f	\N
2	1	1	3	Lunch w pracy	45.00	2026-02-01	2026-02-02 19:05:26.541572	f	\N
3	1	1	2	Uber do centrum	32.50	2026-02-01	2026-02-02 19:05:26.541572	f	\N
5	1	1	6	Netflix - subskrypcja	49.00	2026-01-30	2026-02-02 19:05:26.541572	f	\N
6	1	1	1	Zakupy Tesco	150.50	2026-01-28	2026-02-02 19:05:26.541572	f	\N
7	1	1	2	Benzyna 50L	250.00	2026-01-27	2026-02-02 19:05:26.541572	f	\N
8	1	1	3	Obiad w restauracji	120.00	2026-01-26	2026-02-02 19:05:26.541572	f	\N
11	1	1	3	Kawa w kawiarni	18.50	2026-01-25	2026-02-02 19:05:26.541572	f	\N
12	1	1	6	Bilet do kina	35.00	2026-01-24	2026-02-02 19:05:26.541572	f	\N
13	1	1	5	Koszulka adidas	89.99	2026-01-23	2026-02-02 19:05:26.541572	f	\N
15	1	1	2	Karta parkingowa	80.00	2026-01-22	2026-02-02 19:05:26.541572	f	\N
18	1	1	4	Kabel HDMI	29.50	2026-01-21	2026-02-02 19:05:26.541572	f	\N
21	2	3	5	Nike	480.54	2026-01-30	2026-02-02 23:07:52.668785	f	\N
22	2	3	1	Biedronka	300.23	2026-02-02	2026-02-02 23:08:11.772428	f	\N
25	2	3	6	Spotify Premium	30.00	2026-02-01	2026-02-03 11:38:51.67463	f	\N
26	2	3	1	Zakupy Auchan	245.64	2026-01-27	2026-02-03 11:39:37.711431	f	\N
27	2	3	6	Netflix	60.00	2026-01-15	2026-02-03 11:46:22.32327	f	\N
28	2	3	9	Czynsz	1200.00	2026-01-23	2026-02-03 11:50:49.842433	f	\N
29	2	3	1	Zakupy Biedronka	199.98	2026-01-22	2026-02-03 12:09:23.49044	f	\N
30	2	3	1	Zakupy Auchan	180.45	2026-01-09	2026-02-03 12:09:41.939941	f	\N
31	2	3	4	Laptop	800.00	2025-12-19	2026-02-03 12:31:14.692529	f	\N
32	2	3	2	Orlen	95.36	2026-02-03	2026-02-03 12:32:13.66992	f	\N
33	2	3	2	Paliwo	200.00	2026-02-03	2026-02-03 12:46:06.559003	f	\N
34	2	2	1	Biedronka	500.00	2025-12-31	2026-02-03 12:47:46.105496	f	\N
35	2	2	3	Obiad	122.78	2026-02-03	2026-02-03 12:48:10.285395	f	\N
36	2	2	7	Apteka	458.93	2026-02-03	2026-02-03 12:55:07.050363	f	\N
37	2	4	7	Wizyta u lekarza	500.00	2026-02-03	2026-02-03 14:28:11.688749	f	\N
38	1	1	9	TEST	1234.00	2025-01-03	2026-02-03 16:43:25.863244	t	2026-02-03 16:43:25.863244
4	1	1	1	Pieczywo i nabiał	28.90	2026-01-31	2026-02-02 19:05:26.541572	f	\N
9	1	1	1	Warzywa	45.75	2026-01-26	2026-02-02 19:05:26.541572	f	\N
10	1	1	4	Laptop	299.99	2026-01-25	2026-02-02 19:05:26.541572	f	\N
14	1	1	1	Mleko, chleb, masło	32.40	2026-01-23	2026-02-02 19:05:26.541572	f	\N
16	1	1	7	Leki na przeziębienie	25.00	2026-01-22	2026-02-02 19:05:26.541572	f	\N
17	1	1	3	Pizza	45.99	2026-01-21	2026-02-02 19:05:26.541572	f	\N
19	1	1	6	Książka	55.00	2026-01-20	2026-02-02 19:05:26.541572	f	\N
20	1	1	1	Owoce - banany, jabłka	28.70	2026-01-20	2026-02-02 19:05:26.541572	f	\N
23	2	3	3	Żabka	5.99	2026-02-03	2026-02-03 11:37:04.247475	f	\N
24	2	3	4	Słuchawki	299.99	2026-01-23	2026-02-03 11:37:27.512351	f	\N
\.


--
-- Data for Name: user_preferences; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_preferences (user_id, default_group_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, email, username, password_hash, created_at, updated_at, is_active) FROM stdin;
1	admin@example.com	admin	$2y$12$zWi9og5B3qezqQi3fDz6YeGPqIBMlyKYzSLyk65aoWkUGi4GVT28u	2026-02-02 19:05:26.529081	2026-02-02 19:05:26.529081	t
2	user1@example.com	Marcin	$2y$12$oyw8RZT0K/SBesKcZSY8t.omWEfKxcgJZKrHDFRePrlVCt10o.PC.	2026-02-02 21:22:44.099791	2026-02-02 21:22:44.099791	t
3	user2@example.com	Kacper	$2y$12$HUq7aT5hMfVJB4d5yCfsXujHXwBcfLoE7FahEEbd07G3hxMEPukQK	2026-02-02 21:23:20.778678	2026-02-02 21:23:20.778678	t
4	user3@example.com	Dorota	$2y$12$SK0fHsRdbTRbay/8auRGUu85QJLSsFOnEoZBa7jbu5kR9vu2/QvLy	2026-02-02 21:24:09.317061	2026-02-02 21:24:09.317061	t
5	user4@example.com	BezGrupy	$2y$12$zFptgDS5tKV3XL5D9xSNH.ccDpcST3YgAUtdhmXZRXy1FMDDBMsTi	2026-02-02 21:24:40.916248	2026-02-02 21:24:40.916248	t
\.


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories_id_seq', 9, true);


--
-- Name: group_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.group_members_id_seq', 5, true);


--
-- Name: groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.groups_id_seq', 2, true);


--
-- Name: transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.transactions_id_seq', 38, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 5, true);


--
-- Name: categories categories_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_name_key UNIQUE (name);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: group_members group_members_group_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_group_id_user_id_key UNIQUE (group_id, user_id);


--
-- Name: group_members group_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_pkey PRIMARY KEY (id);


--
-- Name: groups groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: user_preferences user_preferences_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_preferences
    ADD CONSTRAINT user_preferences_pkey PRIMARY KEY (user_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: idx_group_members_group; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_group_members_group ON public.group_members USING btree (group_id);


--
-- Name: idx_group_members_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_group_members_user ON public.group_members USING btree (user_id);


--
-- Name: idx_transactions_group_date; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_transactions_group_date ON public.transactions USING btree (group_id, date DESC) WHERE (is_archived = false);


--
-- Name: idx_transactions_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_transactions_user ON public.transactions USING btree (user_id);


--
-- Name: transactions trigger_archive_transactions; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trigger_archive_transactions AFTER INSERT ON public.transactions FOR EACH ROW EXECUTE FUNCTION public.archive_old_transactions();


--
-- Name: transactions trigger_validate_category; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trigger_validate_category BEFORE INSERT OR UPDATE ON public.transactions FOR EACH ROW EXECUTE FUNCTION public.validate_category_exists();


--
-- Name: group_members group_members_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.groups(id) ON DELETE CASCADE;


--
-- Name: group_members group_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: groups groups_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.categories(id);


--
-- Name: transactions transactions_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.groups(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_preferences user_preferences_default_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_preferences
    ADD CONSTRAINT user_preferences_default_group_id_fkey FOREIGN KEY (default_group_id) REFERENCES public.groups(id) ON DELETE SET NULL;


--
-- Name: user_preferences user_preferences_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_preferences
    ADD CONSTRAINT user_preferences_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict esy9nMlQNRxcngZLBaf4dnIwzTEdzfaxgNVGq0TJTRZCAPtfmrYbKp1Ze0nlV2G

