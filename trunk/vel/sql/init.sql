--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: vel; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA vel;


SET search_path = vel, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: authentication; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE authentication (
    id integer NOT NULL,
    ip character varying(255) NOT NULL,
    accountid bigint NOT NULL,
    salt character varying(255) DEFAULT ''::character varying NOT NULL
);


--
-- Name: authentication_id_seq; Type: SEQUENCE; Schema: vel; Owner: -
--

CREATE SEQUENCE authentication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: authentication_id_seq; Type: SEQUENCE OWNED BY; Schema: vel; Owner: -
--

ALTER SEQUENCE authentication_id_seq OWNED BY authentication.id;


--
-- Name: bank_payment; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE bank_payment (
    id integer NOT NULL,
    paiementid integer,
    serialized text,
    code integer,
    error text,
    merchant_id integer,
    merchant_country character varying(255),
    amount integer,
    transaction_id integer,
    payment_means text,
    transmission_date character varying(255),
    payment_time character varying(255),
    payment_certificate character varying(255),
    authorization_id character varying(255),
    currency_code integer,
    card_number character varying(255),
    cvv_flag character varying(255),
    bank_response_code character varying(255),
    complementary_code character varying(255),
    complementary_info character varying(255),
    return_context character varying(255),
    caddie text,
    receipt_complement text,
    merchant_language character varying(255),
    language character varying(255),
    customer_id character varying(255),
    order_id character varying(255),
    customer_email character varying(255),
    customer_ip_address character varying(255),
    capture_day character varying(255),
    capture_mode character varying(255),
    data character varying(255),
    cvv_response_code character varying(255),
    payment_date character varying(255),
    response_code character varying(255)
);


--
-- Name: TABLE bank_payment; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE bank_payment IS 'recording all the data given back from the bank for online payments';


--
-- Name: bank_payment_id_seq; Type: SEQUENCE; Schema: vel; Owner: -
--

CREATE SEQUENCE bank_payment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: bank_payment_id_seq; Type: SEQUENCE OWNED BY; Schema: vel; Owner: -
--

ALTER SEQUENCE bank_payment_id_seq OWNED BY bank_payment.id;


--
-- Name: id; Type: DEFAULT; Schema: vel; Owner: -
--

ALTER TABLE authentication ALTER COLUMN id SET DEFAULT nextval('authentication_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: vel; Owner: -
--

ALTER TABLE bank_payment ALTER COLUMN id SET DEFAULT nextval('bank_payment_id_seq'::regclass);


--
-- Name: authentication_accountid_key; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY authentication
    ADD CONSTRAINT authentication_accountid_key UNIQUE (ip, accountid);


--
-- Name: bank_payment_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY bank_payment
    ADD CONSTRAINT bank_payment_pkey PRIMARY KEY (id);


--
-- Name: authentication_accountid_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY authentication
    ADD CONSTRAINT authentication_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: bank_payment_paiementid_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY bank_payment
    ADD CONSTRAINT bank_payment_paiementid_fkey FOREIGN KEY (paiementid) REFERENCES billeterie.paiement(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

