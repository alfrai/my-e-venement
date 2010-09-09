SET search_path = vel, pg_catalog;
CREATE TABLE bank_payment (
    id integer NOT NULL,
    paiementid integer,
    serialized text,
    code character varying(255),
    error text,
    merchant_id character varying(255),
    merchant_country character varying(255),
    amount character varying(255),
    transaction_id character varying(255),
    payment_means text,
    transmission_date character varying(255),
    payment_time character varying(255),
    payment_certificate character varying(255),
    authorization_id character varying(255),
    currency_code character varying(255),
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
COMMENT ON TABLE bank_payment IS 'recording all the data given back from the bank for online payments';
CREATE SEQUENCE bank_payment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
SELECT pg_catalog.setval('bank_payment_id_seq', 1, false);
ALTER TABLE bank_payment ALTER COLUMN id SET DEFAULT nextval('bank_payment_id_seq'::regclass);
ALTER TABLE ONLY bank_payment
    ADD CONSTRAINT bank_payment_pkey PRIMARY KEY (id);
ALTER TABLE ONLY bank_payment
    ADD CONSTRAINT bank_payment_paiementid_fkey FOREIGN KEY (paiementid) REFERENCES billeterie.paiement(id) ON UPDATE CASCADE ON DELETE SET NULL;
